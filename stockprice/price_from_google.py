#!/usr/bin/python

# from pypdf import PdfReader
# import pdfplumber
from datetime import datetime
from Models import StockQuote
import argparse, re, sys
# from decimal import Decimal
# import logging

def my_argparse():
    parser = argparse.ArgumentParser(description='load stocks/funds quotes from website')
    parser.add_argument("database", metavar='str', type=str, nargs='?', default='prod', help='load today quotes to database, default=devx')
    return parser.parse_args()

def is_number(s):
    if s == None: return False
    pattern = r'^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$'
    return bool(re.fullmatch(pattern, s))


def write_lines_to_file(date, stock, lines):
    filename = "/sites/tmp/" + date + "_g_" + stock
    with open(filename, 'w') as file:
        file.writelines(f"{i}. {line}\n" for i, line in enumerate(lines, start=1))

def check_number(name, n, stock, line):
    if not is_number(n):
        print("%s=%s is not a nmuber for %s: from line[%s]"%(n, name, stock, line))
        sys.exit(-1)
    # else: print('check_number %s=%s'%(name, n))

def get_txt_lines(date, stock):
    txt_path = "/sites/webdata/docs/gstocks/" + date + '_' + stock + ".txt"
    with open(txt_path, encoding='utf-8') as f:
        lines = f.readlines()          # list with '\n' still attached
        lines = [line.rstrip('\n') for line in lines]   # drop the newlines
        # for line in lines: print(line)
    return lines

def get_stock_quote_txt(date, stock):
    lines = get_txt_lines(date, stock)

    price=None; pchange=''; day_low=None; day_high=None; wk52_low=None; wk52_high=None

    for idx, line in enumerate(lines):
        line = lines[idx]
        if re.match(r'^\d+.\d+\s+USD', line):
            price = line.split(' ')[0]
            # print('-CK- line=[%s]'%price)
            check_number('price', price, stock, line)

        elif re.match(r'^[+|−]\d+.\d(.*)today', line):
            pchange = line.split(' ')[0]
            pchange = pchange.replace('+', '')
            if re.match(r'−', pchange): pchange = pchange.replace('−', '-')
            check_number('pchange', pchange, stock, line)

        elif re.match(r'^High', line):
            day_high = lines[idx + 1]
            check_number('day_high', day_high, stock, line)

        elif re.match(r'^Low', line):
            day_low = lines[idx + 1]
            check_number('day_low', day_low, stock, line)

        elif re.match(r'^52-wk\s+high', line):
            wk52_high = lines[idx + 1]
            check_number('wk52_high', wk52_high, stock, line)

        elif re.match(r'^52-wk\s+low', line):
            wk52_low = lines[idx + 1]
            check_number('wk52_low', wk52_low, stock, line)
    
    return [stock, price, pchange, day_low, day_high, wk52_low, wk52_high]
        
#=========== main ============

if __name__=="__main__": print('')
database = my_argparse().database

date = datetime.now().strftime("%Y%m%d")
stocks = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
# stocks = ['MSFT']
# stocks = ['T']
pdata = None
for stock in stocks:
    # print("processing stock=%s"%stock)
    try:
        pdata = get_stock_quote_txt(date, stock)
        print(pdata)
    except Exception as ex:
        print("get_stock_quote(date=[%s], stock=[%s]) failed, error=%s"%(date, stock, ex))
    # finally: print(pdata)
    if pdata == None:
        print("get_stock_quote FAILED, exiting...")
        sys.exit(-1)
    
    quote = StockQuote(stock)
    if quote.isQuoteAlreadyInDBforToday(database): continue
    quote.setData(pdata)
    quote.saveToDB(database)
