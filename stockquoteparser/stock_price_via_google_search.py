#!/usr/bin/python -W "ignore"

# from pypdf import PdfReader
import pdfplumber
from datetime import datetime
from Models import StockQuote
import argparse, re, sys
from decimal import Decimal
import logging

def my_argparse():
    parser = argparse.ArgumentParser(description='load stocks/funds quotes from website')
    parser.add_argument("database", metavar='str', type=str, nargs='?', default='prod', help='load today quotes to database, default=devx')
    return parser.parse_args()

def is_number(s):
    if s == None: return False
    pattern = r'^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$'
    return bool(re.fullmatch(pattern, s))

def check_number(name, n, stock, line):
    if not is_number(n):
        print("%s=%s is not a nmuber for %s: from line[%s]"%(n, name, stock, line))
        sys.exit(-1)

def write_lines_to_file(date, stock, lines):
    filename = "/sites/tmp/" + date + "_g_" + stock
    with open(filename, 'w') as file:
        file.writelines(f"{i}. {line}\n" for i, line in enumerate(lines, start=1))

# def get_pdf_text(date, stock):
#     pdf_path = "/sites/webdata/docs/gstocks/" + date + '_' + stock + ".pdf"
#     # print("pdf_path=%s"%pdf_path)
#     reader = PdfReader(pdf_path)

#     # Loop through each page
#     pdf_lines = []
#     for page_num in range(len(reader.pages)):
#         page = reader.pages[page_num]
#         text = page.extract_text()  # Extract text from the page
#         print(text)
        
#         # Split text into lines and process
#         lines = text.split('\n')  # Split by newlines
#         pdf_lines += lines

#     write_lines_to_file(date, stock, pdf_lines)

def get_pdf_text(date, stock):
    pdf_path = "/sites/webdata/docs/gstocks/" + date + '_' + stock + ".pdf"
    # logging.getLogger("pdfplumber").setLevel(logging.WARNING)
    # logging.getLogger("pdfminer").setLevel(logging.WARNING)

    pdf_lines = []
    # with pdfplumber.open(pdf_path) as pdf, open("output.txt", "w", encoding="utf-8") as f:
    try:
        with pdfplumber.open(pdf_path) as pdf:
            for page in pdf.pages:
                ptxt = page.extract_text()
                if ptxt and not re.match(r'None', ptxt):   
                    lines = ptxt.split('\n')       
                    pdf_lines += lines
    except pdfplumber.pdfminer.pdfparser.PDFSyntaxError as e:
        print(f"Error parsing PDF: {e}")
        # Log the error and continue, or handle it as needed
    except FileNotFoundError:
        print("The file was not found.")
    except Exception as e:
        print(f"An unexpected error occurred: {e}")      

    write_lines_to_file(date, stock, pdf_lines)
    # for line in pdf_lines: print(line)
    # sys.exit(0)
    return pdf_lines

def get_stock_quote(date, stock):
    pdf_lines = get_pdf_text(date, stock)

    price=None; pchange=''; day_low=None; day_high=None; wk52_low=None; wk52_high=None
    for idx, line in enumerate(pdf_lines):
        line = pdf_lines[idx]
        # print(f"{idx}: {line}")
        # if re.match(r'Mark\s*et Summar\s*y', line):
        # if re.match(r'^(.*)\s*USD$', line):
        if re.match(r'Market Summary', line):
            # price = line.split(' ')[0]
            price = pdf_lines[idx + 1]
            check_number('price', price, stock, line)
            # print("price=%s"%price)
            # if not is_number(price):
            #     print("price=%s is not a nmuber for %s: from line[%s]"%(price, stock, line))
            #     sys.exit(-1)

            pchange = pdf_lines[idx+3].split(' ')[0].replace('+', '')
            if re.match(r'−', pchange):
                pchange = pchange.replace('−', '-')
                # print("pchange=%s"%pchange)
                check_number('pchange', pchange, stock, line)
            # if not is_number(pchange):
            #     print("pchange=%s is not a nmuber for %s: from line[%s]"%(pchange, stock, line))
            #     sys.exit(-1)

        elif re.match(r'^High\s+\d', line):
            x = line.split(' ')
            day_high = x[1]
            # print("DAY_HIGH=%s"%day_high)
            check_number('day_high', day_high, stock, line)
            # if not is_number(day_high):
            #     print("day_high=%s is not a nmuber for %s: from line[%s]"%(day_high, stock, line))
            #     sys.exit(-1)
            # wk52_high = x[6]
            # check_number('wk52_high', wk52_high, stock, line)
            wk52_low = x[7]
            # print("WK52_LOW=%s"%wk52_low)
            check_number('wk52_low', wk52_low, stock, line)

        elif re.match(r'^Low\s*\d', line):
            x = line.split(' ')
            # print("day_low=%s"%day_low)
            day_low = x[1]
            # print("DAY_LOW=%s"%day_low)
            check_number('day_low', day_low, stock, line)
            # if not is_number(day_high):
            #     print("day_high=%s is not a nmuber for %s: from line[%s]"%(day_high, stock, line))
            #     sys.exit(-1)
            # wk52_low = x[7]
            # print("WK52_LOW=%s"%wk52_low)
            # check_number('wk52_low', wk52_low, stock, line)

        elif re.search(r'52-wk\s+high\s+\d', line):
            x = line.split(' ')
            # if len(x) <= 3: wk52_high = line.split('high')[1]
            # else: wk52_high = line.split(' ')[4]
            # print("wk52_high=%s"%wk52_high)
            wk52_high = x[7]
            check_number('wk52_high', wk52_high, stock, line)

        # elif re.match(r'52-wk\s+low\s+\d', line):
        #     x = line.split(' ')
        #     # if len(x) <= 2: wk52_low = line.split('low')[1]
        #     # else: wk52_low = line.split(' ')[2]
        #     # print("wk52_low=%s"%wk52_low)
        #     wk52_low = x[7]
        #     print("WK52_LOW=%s"%wk52_low)
        #     check_number('wk52_low', wk52_low, stock, line)
    
    # print(stock, price, pchange, day_low, day_high, wk52_low, wk52_high)
    return [stock, price, pchange, day_low, day_high, wk52_low, wk52_high]
        
#=========== main ============

if __name__=="__main__": print('')
database = my_argparse().database

date = datetime.now().strftime("%Y%m%d")
stocks = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
# stocks = ['MSFT']
# stocks = ['T']
for stock in stocks:
    # print("processing stock=%s"%stock)
    try:
        pdata = get_stock_quote(date, stock)
        # print(pdata)
    except Exception as ex:
        print("get_stock_quote(%s, %s) failed, error=%s"%(date, stock, ex.message))
    finally: print(pdata)
    quote = StockQuote(stock)
    if quote.isQuoteAlreadyInDBforToday(database):
        # print("data is already loaded for %s"%stock)
        continue
    quote.setData(pdata)
    quote.saveToDB(database)
