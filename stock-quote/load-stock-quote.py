#!/Users/swang/myenv/bin/python
from Models import StockQuote
import argparse, sys

def my_argparse():
    parser = argparse.ArgumentParser(description='load stocks/funds quotes from website')
    parser.add_argument("database", metavar='str', type=str, nargs='?', default='prod', help='load today quotes to database, default=devx')
    return parser.parse_args()


if __name__=="__main__": print('')
# symbols = ['WBD']
symbols = ['T', 'WBD', 'CHTR', 'CSCO', 'DELL', 'MSFT', 'BEKE']
database = my_argparse().database
# print(f'database={database}')
for symbol in symbols:
    # load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    stock = StockQuote(symbol)
    # stock.getFakeQuote()
    # stock.getQuote(database)
    # print(stock.gotData())
    if stock.getQuote(database) == 'exist this hour': sys.exit(0)
    elif stock.gotData():
        stock.showData()
        stock.saveToDB(database)
    else:
        print(f"It seems scratching stock quote from web for [{stock.symbol}] FAILED, exiting ...")
        sys.exit(1)

