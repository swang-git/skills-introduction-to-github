#!/Users/swang/myenv/bin/python

from PyPDF2 import PdfReader
from datetime import datetime
from Models import StockQuote
import argparse, re, sys
from decimal import Decimal

def my_argparse():
    parser = argparse.ArgumentParser(description='load stocks/funds quotes from website')
    parser.add_argument("database", metavar='str', type=str, nargs='?', default='prod', help='load today quotes to database, default=devx')
    return parser.parse_args()

def is_number(s):
    pattern = r'^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$'
    return bool(re.fullmatch(pattern, s))

def check_number(name, n, stock, line):
    if not is_number(n):
        print("%s=%s is not a nmuber for %s: from line[%s]"%(n, name, stock, line))
        sys.exit(-1)

def write_lines_to_file(date, stock, lines):
    filename = "/Users/swang/sites/tmp/" + date + "_g_" + stock
    with open(filename, 'w') as file:
        file.writelines(f"{i}. {line}\n" for i, line in enumerate(lines, start=1))

def get_stock_quote(date, stock):
    pdf_path = "/Users/swang/sites/webdata/docs/gstocks/" + date + '_' + stock + ".pdf"
    # print("pdf_path=%s"%pdf_path)
    reader = PdfReader(pdf_path)

    # Loop through each page
    pdf_lines = []
    for page_num in range(len(reader.pages)):
        page = reader.pages[page_num]
        text = page.extract_text()  # Extract text from the page
        
        # Split text into lines and process
        lines = text.split('\n')  # Split by newlines
        pdf_lines += lines

    write_lines_to_file(date, stock, pdf_lines)

    price=None; pchange=''; day_low=None; day_high=None; wk52_low=None; wk52_high=None
    for idx, line in enumerate(pdf_lines):
        line = pdf_lines[idx]
        # print(f"{idx}: {line}")
        if re.match(r'Mark\s*et Summar\s*y', line):
            price = pdf_lines[idx+1].split(' ')[0]
            check_number('price', price, stock, line)
            # print("price=%s"%price)
            # if not is_number(price):
            #     print("price=%s is not a nmuber for %s: from line[%s]"%(price, stock, line))
            #     sys.exit(-1)

            pchange = pdf_lines[idx+2].split(' ')[0].replace('+', '')
            if re.match(r'−', pchange):
                pchange = pchange.replace('−', '-')
                # print("pchange=%s"%pchange)
                check_number('pchange', pchange, stock, line)
            # if not is_number(pchange):
            #     print("pchange=%s is not a nmuber for %s: from line[%s]"%(pchange, stock, line))
            #     sys.exit(-1)

        elif re.match(r'High', line):
            day_high = line.split(' ')[1]
            # print("day_high=%s"%day_high)
            check_number('day_high', day_high, stock, line)
            # if not is_number(day_high):
            #     print("day_high=%s is not a nmuber for %s: from line[%s]"%(day_high, stock, line))
            #     sys.exit(-1)

        elif re.match(r'Low', line):
            day_low = line.split(' ')[1].replace('Mkt', '')
            # print("day_low=%s"%day_low)
            check_number('day_low', day_low, stock, line)
            # if not is_number(day_high):
            #     print("day_high=%s is not a nmuber for %s: from line[%s]"%(day_high, stock, line))
            #     sys.exit(-1)

        elif re.search(r'52-wk high', line):
            wk52_high = line.split(' ')[4]
            # print("wk52_high=%s"%wk52_high)
            check_number('wk52_high', wk52_high, stock, line)

        elif re.match(r'52-wk low', line):
            wk52_low = line.split(' ')[2]
            # print("wk52_low=%s"%wk52_low)
            check_number('wk52_low', wk52_low, stock, line)
            return [stock, price, pchange, day_low, day_high, wk52_low, wk52_high]
        
#=========== main ============

if __name__=="__main__": print('')
database = my_argparse().database

date = datetime.now().strftime("%Y%m%d")
stocks = ['T', 'WBD', 'CHTR', 'CSCO', 'DELL', 'MSFT']
# stocks = ['T']
for stock in stocks:
    pdata = get_stock_quote(date, stock)
    print(pdata)
    quote = StockQuote(stock)
    if quote.isQuoteAlreadyInDBforToday(database):
        # print("data is already loaded for %s"%stock)
        continue
    quote.setData(pdata)
    quote.saveToDB(database)
