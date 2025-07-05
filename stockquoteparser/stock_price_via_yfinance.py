#!/Users/swang/myenv/bin/python

from PyPDF2 import PdfReader
from datetime import datetime
from Models import StockQuote
import argparse, sys

def my_argparse():
    parser = argparse.ArgumentParser(description='load stocks/funds quotes from website')
    parser.add_argument("database", metavar='str', type=str, nargs='?', default='prod', help='load today quotes to database, default=devx')
    return parser.parse_args()

def write_lines_to_file(date, stock, lines):
    filename = "/Users/swang/sites/tmp/" + date + "_" + stock
    with open(filename, 'w') as file:
        file.writelines(f"{i}. {line}\n" for i, line in enumerate(lines, start=1))

def get_stock_quote(date, stock):
    pdf_path = "/Users/swang/sites/webdata/docs/stocks/" + date + '_' + stock + ".pdf"
    ## print("pdf_path=%s"%pdf_path)
    reader = PdfReader(pdf_path)

    # Loop through each page
    pdf_lines = []
    wk52_range = ''
    for page_num in range(len(reader.pages)):
        page = reader.pages[page_num]
        text = page.extract_text()  # Extract text from the page
        
        # Split text into lines and process
        lines = text.split('\n')  # Split by newlines
        pdf_lines += lines
        # for line in lines:
        #     line = line.strip()  # Remove extra whitespace
        #     if line:  # Skip empty lines
        #         print(f"Page {page_num + 1}: {line}")  # Process each line

    write_lines_to_file(date, stock, pdf_lines)

    for idx, line in enumerate(pdf_lines):
        line = pdf_lines[idx]
        # print(f"{idx}: {line}")
        if line == 'Previous Close':
            pclose_price = pdf_lines[idx+1]
            open_price = pdf_lines[idx+3]
            pchange = round(float(open_price) - float(pclose_price), 2)
            # print("pclose_price=%s"%pclose_price)
            # print("open_price=%s"%open_price)
            # print("pchange=%s"%pchange)
        elif line == "Day's Range":
            day_range = pdf_lines[idx+1]
            # print("day_range=%s"%day_range)
            x = day_range.split(' - ')
            day_low = x[0]
            day_high = x[1]
            # print("day_low=%s"%x[0])
            # print("day_high=%s"%x[1])
        elif line == "52 Week Range":
            wk52_range = pdf_lines[idx+1]
            # print("wk52_range=%s"%wk52_range)
            x = wk52_range.split(' - ')
            wk52_low = x[0]
            wk52_high = x[1]
            # print("wk52_low=%s"%x[0])
            # print("wk52_high=%s"%x[1])
            return [stock, open_price, pchange, day_low, day_high, wk52_low, wk52_high]

#=========== main ============

if __name__=="__main__": print('')
database = my_argparse().database

date = datetime.now().strftime("%Y%m%d")
stocks = ['T', 'WBD', 'CHTR', 'CSCO', 'DELL', 'MSFT']
#stocks = ['MSFT']
for stock in stocks:
    pdata = get_stock_quote(date, stock)
    print(pdata)
    quote = StockQuote(stock)
    if quote.isQuoteAlreadyInDBforToday(database):
        # print("data is already loaded for %s"%stock)
        continue
    quote.setData(pdata)
    quote.saveToDB(database)
    # quote.showData()
