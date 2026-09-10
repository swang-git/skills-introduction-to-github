#!/Users/swang/myenv/bin/python

import yfinance as yf
import sys, pprint, time
from datetime import datetime, date, timedelta
from decimal import Decimal
from sqlalchemy import desc

# from dataclasses import dataclass

# @dataclass
# class StockQ:
#     pass
#     # asof_time: datetime
#     # symbo: str
#     # price: float
#     # price_change: float
#     # low_52_week: float
#     # high_52_week: float

# data = {"name": "Bob", "age": 35}
# obj = User(**data)
# print(obj.name)  # Bob

from Utils import padsp, get_data_from_table, build_dict, get_meta, get_quantity, get_total_cost, get_basis_price, get_last_portfolio, wkdayname, TeeFS
from MyPortfolio_Models import get_connection, CSV_TO_DB_MAP, TYPE_CONVERTERS, MyPortfolio, HealthRecord, GlucoseCheck, StockQuote

import argparse
parser = argparse.ArgumentParser()
parser.add_argument('-t', '--test', action="store_true", help='for testing get data from database(instead of yfinance)')
parser.add_argument('-d', '--db', type=str, default='devx', help='upsert stock quotes to table and upsert csv data to my_portfolios')
# parser.add_argument('-d', '--db', type=str, required=True, help='upsert stock quotes to database DB<devx/prod> table:my_portfolios')
args = parser.parse_args()
database = args.db
testing = args.test
print("database:", database)
print('testing...') if testing else None
# sys.exit(0)

def get_weight(db):
    wt = None
    wt_row = db.query(GlucoseCheck).order_by(desc(GlucoseCheck.datetime)).limit(1).first()
    if wt_row: wt = wt_row.weight
    print('wt=[%s]'%wt)
    return wt

def show_stock_data(row):
    leng = 15
    print((leng - len('symbol'))*' ' + "symbol: ", row['symbol'])
    print((leng - len('price'))*' ' + "price: ", row['price'])
    print((leng - len('price_change'))*' ' + "price_change: ", row['price_change'])
    print((leng - len('low_52_week'))*' ' + "low_52_week: ", row['low_52_week'])
    print((leng - len('high_52_week'))*' ' + "high_52_week: ", row['high_52_week'])

def get_fake_stock_data(cursor, symb):
    # print('-fn-get_stock_data[%s]'%symb)
    wcond = 'asof_time>="2026-05-06" AND symbol="' + symb + '" AND status="A"'
    dax = get_data_from_table(cursor, 'my_portfolios', wcond, 1)
    dict = build_dict(cursor, dax)
    dict[symb]['company'] = '__company__'
    stock_data = dict[symb]
    # show_stock_data(stock_data)
    return stock_data

def get_myp_data(symb, asoftime, stock_data, meta_dict, fake_data=False):
    data = {}
    data['asof_time'] = asoftime
    data['account'] = 'My-stocks'
    data['account_name'] = 'Chase Account'
    data['symbol'] = symb
    data['quantity'] = get_quantity(meta_dict, symb)
    data['company'] = stock_data['company']
    data['price'] = stock_data['price']
    data['price_change'] = stock_data['price_change']
    data['current_value'] = data['price'] * get_quantity(meta_dict, symb)
    data['today_gl'] = data['price_change'] * get_quantity(meta_dict, symb)
    data['today_gl_pct'] = data['price_change'] / (data['price'] - data['price_change']) * 100
    data['total_gl'] = data['current_value'] - get_total_cost(meta_dict, symb)
    data['total_gl_pct'] = data['total_gl'] / get_total_cost(meta_dict, symb) * 100
    data['total_cost'] = get_total_cost(meta_dict, symb)
    data['cost_per_share'] = get_basis_price(meta_dict, symb)
    data['low_52_week'] = stock_data['low_52_week']
    data['high_52_week'] = stock_data['high_52_week']
    
    return data

def show_myp(stocks, datx):
    for symb in stocks:
        print('=======' + datx[symb]['symbol'] + "=======")
        record = MyPortfolio(**datx[symb])
        pprint.pprint(record.__dict__)
    
def save_to_myp_table(db, stocks, datx):
    for num, symb in enumerate(stocks):
        datx[symb]["asof_time"] += timedelta(seconds=num+1)
        asof = datx[symb]["asof_time"]
        # redx = MyPortfolio(**datx[symb])
        # pprint.pprint(redx.__dict__)
        price = padsp(datx[symb]['price'], 7)
        price_change = padsp(datx[symb]['price_change'], 6)
        low = padsp(datx[symb]['low_52_week'], 7)
        high = padsp(datx[symb]['high_52_week'], 7)
        # --------------------------
        # Step 4: UPSERT (Update if exists, else Insert)
        # --------------------------
        existing = db.query(MyPortfolio).filter(MyPortfolio.asof_time == asof, MyPortfolio.symbol == symb).first()

        adjsp = (4-len(symb)) * ' '
        if existing:
            # Update all fields
            for key, value in datx[symb].items():
                setattr(existing, key, value)
            print(f"🔄 Upd |{adjsp} {symb} | {asof} | price: {price} | price change: {price_change} | 52wk_low: {low} | 52wk_high: {high}")
        else:
            # Create new record (NO __init__ needed!)
            new_record = MyPortfolio(**datx[symb])
            db.add(new_record)
            print(f"✅ Add |{adjsp} {symb} | {asof} | price: {price} | price change: {price_change} | 52wk_low: {low} | 52wk_high: {high}")

    # Save all changes
    db.commit()
    print(f"🎉 myp data added/updated successfully!")

def save_to_stock_quotes_table(db, stocks, datx):
    for num, symb in enumerate(stocks):
        # datx[symb]["asof_time"] += timedelta(seconds=num+1)
        asof = datx[symb]["asof_time"]
        # redx = MyPortfolio(**datx[symb])
        # pprint.pprint(datx[symb].__dict__)
        price = padsp(datx[symb]['price'], 7)
        price_change = padsp(datx[symb]['price_change'], 6)
        low = padsp(datx[symb]['low_52_week'], 7)
        high = padsp(datx[symb]['high_52_week'], 7)
        # --------------------------
        # Step 4: UPSERT (Update if exists, else Insert)
        # --------------------------
        existing = db.query(StockQuote).filter(StockQuote.asof_time == asof, StockQuote.symbol == symb).first()

        adjsp = (4-len(symb)) * ' '
        if existing:
            # Update all fields
            for key, value in datx[symb].items():
                setattr(existing, key, value)
            print(f"🔄 Upd |{adjsp} {symb} | {asof} | price: {price} | price change: {price_change} | 52wk_low: {low} | 52wk_high: {high}")
        else:
            dtsx = datx[symb]
            asof_time = dtsx["asof_time"]
            symbol = dtsx["symbol"]
            price = dtsx["price"]
            price_change = dtsx["price_change"]
            low_52_week = dtsx["low_52_week"]
            high_52_week = dtsx["high_52_week"]
            new_record = StockQuote(asof_time, symbol, price, price_change, low_52_week, high_52_week)
            db.add(new_record)
            price = padsp(datx[symb]['price'], 7)
            price_change = padsp(datx[symb]['price_change'], 6)
            low = padsp(datx[symb]['low_52_week'], 7)
            high = padsp(datx[symb]['high_52_week'], 7)
            print(f"✅ Add |{adjsp} {symb} | {asof} | price: {price} | price change: {price_change} | 52wk_low: {low} | 52wk_high: {high}")

    # Save all changes
    db.commit()
    print(f"🎉 stock_quotes data added/updated successfully!")

def import_indices(db, cursor, date):
    print("-fn- import_indices -- get data from yf")
    indices = {
        "DOW_JONES": "^DJI",
        "NASDAQ": "^IXIC",
        "SP500": "^GSPC",
        "FTSE100": "^FTSE",
        "NIKKEI": "^N225"
    }
    dbx = {}
    for name, ticker in indices.items():
        info = yf.Ticker(ticker).info
        dbx[name] = info.get("regularMarketPrice")
    dbx['date'] = date
    dbx['portfolio'] = get_last_portfolio(cursor)

    existing = db.query(HealthRecord).filter(HealthRecord.date == date).first()

    dowj = dbx['DOW_JONES']
    nasd = dbx['NASDAQ']
    sp500 = dbx['SP500']
    dbx['weight'] = get_weight(db) * Decimal('0.45359237')
    wt = dbx['weight']
    if existing:
        # Update all fields
        for key, value in dbx.items():
            setattr(existing, key, value)
        print(f"🔄 Upd | date: {date} | Dow Jones: {dowj} | Nasdaq: {nasd} | SP500: {sp500} | weight: {wt}")
    else:
        # Create new record (NO __init__ needed!)
        new_record = HealthRecord(**dbx)
        db.add(new_record)
        print(f"✅ Add | date: {date} | Dow Jones: {dowj} | Nasdaq: {nasd} | SP500: {sp500} | weight: {wt}")
    # Save all changes
    db.commit()
    print(f"🎉 Indices imported successfully!")

def get_stock_data(symb):
    data = {}
    try:
        ticker = yf.Ticker(symb)
        info = ticker.info

        data["company"] = info.get("longName")
        # Current price (required, NOT NULL)
        current_price = info.get("currentPrice") or info.get("regularMarketPrice")
        if current_price:
            data["price"] = Decimal(str(round(current_price, 3)))

        # 52-week low & high
        data["low_52_week"] = round(info.get("fiftyTwoWeekLow", 0), 3) if info.get("fiftyTwoWeekLow") else None
        data["high_52_week"] = round(info.get("fiftyTwoWeekHigh", 0), 3) if info.get("fiftyTwoWeekHigh") else None

        # Price change (daily $ and %)
        prev_close = info.get("previousClose")
        if current_price and prev_close:
            p_change = round(current_price - prev_close, 3)
            p_change_pct = round(((current_price / prev_close) - 1) * 100, 2)
            
            data["price_change"] = Decimal(str(p_change))
            data["today_gl_pct"] = p_change_pct

    except Exception:
        # If API fails: keep all missing values as NULL
        pass

    print("sleeping for 2 second")
    time.sleep(2)
    return data

# =============================================================================
# RUN THE SCRIPT __main__
# =============================================================================
if __name__ == "__main__":
    logFile = '/Users/swang/tmp/logs/cn/imp-skt-' + wkdayname() + '.log'
    print('===== Starting import stock data to my_portfolios, logfile[%s] ====='%logFile)
    tee = TeeFS(logFile, 'w')

    db, conn = get_connection(database)
    cursor = conn.cursor()
    # get_weight(db); sys.exit(0)
    # get_last_portfolio(cursor); sys.exit(0)

    # ASOF_TIME = datetime(2026, 5, 7, 14, 30, 0)
    today = date.today()
    ASOF_TIME = datetime(today.year, today.month, today.day, 16, 30, 0)

    # import_indices(db, cursor, today); sys.exit(0)
    if not testing: import_indices(db, cursor, today)

    meta_dict = get_meta(cursor)
    datx = {}
    stocks = ['MSFT', 'CSCO', 'DELL', 'CHTR', 'WBD', 'T']
    stks = stocks
    for symb in stocks: 
        if testing:
            stock_data = get_fake_stock_data(cursor, symb) ## get data from my_portfolios for testing
            data = get_myp_data(symb, ASOF_TIME, stock_data, meta_dict)
        else:
            stock_data = get_stock_data(symb) ## get real data from yf
            data = get_myp_data(symb, ASOF_TIME, stock_data, meta_dict) ## populate data for my_portfolios
            ## import_indices(db, cursor, today)
        datx[symb] = data

    total_value = sum(da['current_value'] for da in datx.values())
    # print(total_value)
    for symb in stks:
        datx[symb]['pct_of_account'] = 100 * datx[symb]['current_value'] / total_value
        # print(symb, ':', datx[symb]['pct_of_account'])
    # show_myp(stocks, datx)
    save_to_myp_table(db, stocks, datx)
    save_to_stock_quotes_table(db, stocks, datx)

    # Start import
    # add_stock_data(db, csv_data_file, dict, ASOF_TIME)

    print('===== ENDED import stock data to my_portfolios =====')
    cursor.close()
    conn.close()
    tee.close()
    sys.exit(0)
