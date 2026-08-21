#!/Users/swang/myenv/bin/python

import sys, os, csv
from datetime import datetime, timedelta, date

from Utils import get_data_from_table, build_dict, get_52_week_low, get_52_week_high, padsp, wkdayname, TeeFS
from MyPortfolio_Models import get_connection, CSV_TO_DB_MAP, TYPE_CONVERTERS, MyPortfolio, StockQuote

import argparse
parser = argparse.ArgumentParser()
parser.add_argument('-s', '--sub_days', metavar='int', type=int, nargs='?', default='0', help='sub days from today(must be < 0), default 0 for today')
# optional arguments
# parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
parser.add_argument('-d', '--db', type=str, required=True, help='upsert csv data to database <devx/prod> table: my_portfolios')
args = parser.parse_args()
database = args.db
subdays = args.sub_days
print("database:", database)

# =============================================================================
# 5. CORE FUNCTION: READ CSV → UPSERT TO MYSQL
# =============================================================================
def import_portfolio_csv(db, csv_file_path, dict, asof_time: datetime):
    # db = get_sessionLocal()
    added_seconds = 0
    try:
        with open(csv_file_path, 'r', encoding='utf-8-sig') as f:
            csv_dict = csv.DictReader(f)
            reader = list(csv_dict)[::-1] ## reverse the order

            for row_num, row in enumerate(reader, 1):
                added_seconds += 1
                # print(row_num, row, row['Account number'])
                # convert keys to lowercase # e.g. Account Number to account number
                row = { (k.lower() if k is not None else None): v for k, v in row.items() }

                # --------------------------
                # Step 1: Map CSV → DB fields
                # --------------------------
                # now we can ignore this since we convert to lowercase:check the upper/low case of the csv_header which should match exactly with CSV_TO_DB_MAP
                data = {}
                for csv_header, db_col in CSV_TO_DB_MAP.items(): 
                    raw_val = row.get(csv_header, "")
                    # print("csv_header", csv_header)
                    # print("db_col", db_col)
                    # print("row_val", raw_val)
                    # Convert types
                    if db_col in TYPE_CONVERTERS:
                        data[db_col] = TYPE_CONVERTERS[db_col](raw_val)
                    else:
                        data[db_col] = raw_val.strip() if raw_val else None
                        if db_col == 'symbol': 
                            data["low_52_week"] = get_52_week_low(raw_val, dict)
                            data["high_52_week"] = get_52_week_high(raw_val, dict)

                    # print("db_col", db_col)
                    # if db_col == 'symbol': print(data)
                    # if db_col == 'account': print(data)

                # ==============================
                # 🔥 CLEANUP / FILTER ROWS HERE
                # ==============================
                account = data.get("account")
                #print("data:", data) # check if all values are empty or None for data (csv data)
                
                # SKIP ROW IF: no account OR length > 20
                if not account or len(str(account)) > 20:
                    # print(f"⚠️ Skipping row {row_num}: Invalid account: {account}")
                    added_seconds = added_seconds - 1 ## keep original started datetime
                    continue

                # SKIP ROW IF: no symbo OR length > 8 # skip Pending activity line
                symbol = data.get("symbol")
                if not symbol or len(str(symbol)) > 8:
                    print(f"⚠️ Skipping row {row_num}: Invalid symbol: {symbol}")
                    added_seconds = added_seconds - 1 ## keep original started datetime
                    continue

                # --------------------------
                # Step 2: Add asof_time (datetime)
                # --------------------------
                data["asof_time"] = asof_time + timedelta(seconds=added_seconds)

                # --------------------------
                # Step 3: Unique key for upsert
                # --------------------------
                asof = data.get("asof_time")
                low = padsp('' if data.get("low_52_week") == None else data.get("low_52_week"), 7)
                high = padsp('' if data.get("high_52_week") == None else data.get("high_52_week"), 7)
                data["symbol"] = data["symbol"].strip("*")
                symb = data["symbol"]
                sym = padsp(symb, 5)
                price = padsp('' if data.get("price") == None else data.get("price"), 7)
                price_change = padsp('' if data.get("price_change") == None else data.get("price_change"), 7)

                # --------------------------
                # Step 4: UPSERT (Update if exists, else Insert)
                # --------------------------
                existing = db.query(MyPortfolio).filter(
                    MyPortfolio.asof_time == asof,
                    MyPortfolio.account == account,
                    MyPortfolio.symbol == symb
                ).first()

                if existing:
                    # Update all fields
                    for key, value in data.items():
                        setattr(existing, key, value)
                    print(f"🔄 Upd |{account}| {asof} |{sym}| price: {price} | pchange: {price_change} | 52wk_low: {low} | 52wk_high: {high}")
                else:
                    # Create new record (NO __init__ needed!)
                    new_record = MyPortfolio(**data)
                    db.add(new_record)
                    print(f"✅ Add |{account}| {asof} |{sym}| price: {price} | pchange: {price_change} | 52wk_low: {low} | 52wk_high: {high}")

        # Save all changes
        db.commit()
        print(f"\n🎉 Import completed successfully!")

    except Exception as e:
        db.rollback()
        # print(f"\n❌ ERROR importing CSV: {str(e)}")
    finally:
        db.close()

# =============================================================================
# RUN THE SCRIPT
# =============================================================================
if __name__ == "__main__":
    rootdir = "/Users/swang/sites/webdata/docs/Portfolio/"
    today = date.today()
    csvfile = 'snapshot_' + today.strftime('%Y%m%d') + '.csv'
    logFile = '/Users/swang/tmp/logs/cn/imp-csv-' + wkdayname() + '.log'
    if subdays < 0: logFile += '_' + str(abs(subdays))
    tee = TeeFS(logFile, 'w')

    print('===== starting imp-csv subdays=[%d] logFile=[%s]'%(subdays, logFile))
    if subdays < 0: csvfile = 'snapshot_' + (today - timedelta(days=-subdays)).strftime('%Y%m%d') + '.csv'
    csv_data_file = rootdir + csvfile
    if os.path.exists(csv_data_file):
        print("✅ [%s] File exists!"%csv_data_file)
    else:
        print("❌ [%s] File not found!"%csv_data_file)
        sys.exit(-1)
    # print('csv_data_file:', csv_data_file); sys.exit(0)
    db, conn = get_connection(database)
    cursor = conn.cursor()
    meta = get_data_from_table(cursor, 'security_metas', 'status="A"')
    dict = build_dict(cursor, meta)
    asof_date = today + timedelta(days=subdays)
    # ASOF_TIME = datetime(today.year, today.month, today.day, 17, 41, 0)
    ASOF_TIME = datetime(asof_date.year, asof_date.month, asof_date.day, 17, 41, 0)
    # ASOF_TIME = datetime(2026, 5, 7, 17, 30, 0)
    # Start import
    import_portfolio_csv(db, csv_data_file, dict, ASOF_TIME)
    cursor.close()
    conn.close()

    # with open(data_csv, 'r', encoding='utf-8-sig') as csv_file:
    #     # Read rows as dictionaries
    #     csv_dict = csv.DictReader(csv_file)
    #     show_dict(csv_dict)
