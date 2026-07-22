<<<<<<< HEAD
#!/usr/bin/python
=======
#!/Users/swang/myenv/bin/python
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be

import sys, os, csv
from datetime import datetime, timedelta, date

from Utils import get_data_from_table, build_dict, get_52_week_low, get_52_week_high, padsp
from MyPortfolio_Models import get_connection, CSV_TO_DB_MAP, TYPE_CONVERTERS, MyPortfolio
<<<<<<< HEAD
=======
# from MyPortfolio_Models import get_connection, Csv_To_Db_Map, Csv_to_db_map, TYPE_CONVERTERS, MyPortfolio
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be

import argparse
parser = argparse.ArgumentParser()
parser.add_argument('-s', '--sub_days', metavar='int', type=int, nargs='?', default='0', help='for dated csv file: sub days from today(must be < 0), default 0 for today, -1 yesterday, etc')
parser.add_argument('-d', '--db', type=str, required=True, help='upsert csv data to database <devx/prod> table: my_portfolios')
args = parser.parse_args()
database = args.db
subdays = args.sub_days
print("database:%s, subdays:%i"%(database,subdays))
# sys.exit(0)

<<<<<<< HEAD
=======
# # =============================================================================
# # 5.0 get proper csv header to db map based on Account Number/number
# # =============================================================================
# def getCSV_TO_DB_MAP(reader):
#     for row_num, row in enumerate(reader, 1):
#         if row.get('Account Number') == None:
#             print('row_num[%d]'%row_num)
#             return Csv_to_db_map.items()
#     return Csv_To_Db_Map.items()

>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
# =============================================================================
# 5. CORE FUNCTION: READ CSV → UPSERT TO MYSQL
# =============================================================================
def import_portfolio_csv(db, csv_file_path, dict, asof_time: datetime):
    # db = get_sessionLocal()
    added_seconds = 0
    try:
        with open(csv_file_path, 'r', encoding='utf-8-sig') as f:
            csv_dict = csv.DictReader(f)
<<<<<<< HEAD
            reader = list(csv_dict)[::-1] ## reverse the order

            for row_num, row in enumerate(reader, 1):
                added_seconds += 1
=======
            # reader = list(csv_dict)[::-1] ## reverse the order
            reader = list(csv_dict) ## no reverse
            # print("reader[10]", reader[10])

            # csv2dbMap = getCSV_TO_DB_MAP(reader)
            for row_num, row_ in enumerate(reader, 1):
                row = {k.lower() if isinstance(k, str) else k: v for k, v in row_.items()} # covert all kyes(csv_header) to lower case
                # print("\n==row[%d] account[%s], symbol[%s]"%(row_num, row['account number'], row['symbol']))
                # print('row_num[%d]row'%row_num, row.keys())

                # added_seconds += 1
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
                # --------------------------
                # Step 1: Map CSV → DB fields
                # --------------------------
                data = {}
<<<<<<< HEAD
=======
                # for csv_header, db_col in csv2dbMap:
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
                for csv_header, db_col in CSV_TO_DB_MAP.items():
                    raw_val = row.get(csv_header, "")
                    # Convert types
                    if db_col in TYPE_CONVERTERS:
                        data[db_col] = TYPE_CONVERTERS[db_col](raw_val)
                    else:
                        data[db_col] = raw_val.strip() if raw_val else None
                        if db_col == 'symbol': 
                            data["low_52_week"] = get_52_week_low(raw_val, dict)
                            data["high_52_week"] = get_52_week_high(raw_val, dict)
                            # data["high_52_week"] = 77.77 # for testing updated_at column

                # ==============================
                # 🔥 CLEANUP / FILTER ROWS HERE
                # ==============================
                account = data.get("account")
                
                # SKIP ROW IF: no account OR length > 20
<<<<<<< HEAD
                if not account or len(str(account)) > 20:
                    # print(f"⚠️ Skipping row {row_num}: Invalid account: {account}")
                    added_seconds = added_seconds - 1 ## keep original started datetime
=======
                if not account or len(str(account)) > 10: # account = 'X85275143' len(account) = 9
                    # print(f"⚠️ Skipping row {row_num}: Invalid account: {account}")
                    # added_seconds = added_seconds - 1 ## keep original started datetime
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
                    continue

                # SKIP ROW IF: no symbo OR length > 8 # skip Pending activity line
                symbol = data.get("symbol")
                if not symbol or len(str(symbol)) > 8:
                    print(f"⚠️ Skipping row {row_num}: Invalid symbol: {symbol}")
<<<<<<< HEAD
                    added_seconds = added_seconds - 1 ## keep original started datetime
=======
                    # added_seconds = added_seconds - 1 ## keep original started datetime
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
                    continue

                # --------------------------
                # Step 2: Add asof_time (datetime)
                # --------------------------
<<<<<<< HEAD
=======
                added_seconds += 1
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
                data["asof_time"] = asof_time + timedelta(seconds=added_seconds)

                # --------------------------
                # Step 3: Unique key for upsert
                # --------------------------
                asof = data.get("asof_time")
                price = padsp('' if data.get('price') == None else f"{data.get('price'):.2f}", 6)
                # price = padsp(prc,  7)
                price_change = padsp('' if data.get('price_change') == None else f"{data.get('price_change'):.2f}", 5)
                # price_change = padsp(prc_chng, 7)
                low = padsp('' if data.get("low_52_week") == None else data.get("low_52_week"), 7)
                high = padsp('' if data.get("high_52_week") == None else data.get("high_52_week"), 7)
                # created_at = padsp('' if data.get("created_at") == None else data.get("created_at"), 10)
                # updated_at = padsp(data.get("updated_at"), 20)
                data["symbol"] = data["symbol"].strip("*")
                symbl = data["symbol"]
                symb = padsp(symbl, 6)

                # --------------------------
                # Step 4: UPSERT (Update if exists, else Insert)
                # --------------------------
                existing = db.query(MyPortfolio).filter( MyPortfolio.asof_time == asof, MyPortfolio.account == account, MyPortfolio.symbol == symbl).first()

<<<<<<< HEAD
=======
                # rwn = f"{row_num:2d}"
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
                if existing:
                    # Update all fields
                    for key, value in data.items():
                        setattr(existing, key, value)
                    # print(f"🔄 Updated | Account: {account} | As-of: {asof} | 52wk_low: {low} | 52wk_high: {high} | created_at: {created_at} | updated_at: {updated_at}")
                    # print(f"🔄 Updated | Account: {account} | Asof: {asof} | price: {price} | price_change: {price_change} | 52wk_low: {low} | 52wk_high: {high} | updated_at: {value}")
<<<<<<< HEAD
                    print(f"🔄 Updated | Account: {account} | Asof: {asof} | {symb}: price: {price} | price_change: {price_change} | 52wk_low: {low} | 52wk_high: {high}")
=======
                    # print(f"🔄 Updated {rwn} | Account: {account} | Asof: {asof} | {symb}: price: {price} | price_change: {price_change} | 52wk_low: {low} | 52wk_high: {high}")
                    print(f"🔄 Upd | Account: {account} | {asof} | {symb}: price: {price} | price_change: {price_change} | 52wk_high: {high}")
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
                else:
                    # Create new record (NO __init__ needed!)
                    new_record = MyPortfolio(**data)
                    db.add(new_record)
<<<<<<< HEAD
                    print(f"✅ Added   | Account: {account} | Asof: {asof} | {symb}: price: {price} | price_change: {price_change} | 52wk_low: {low} | 52wk_high: {high}")
=======
                    # print(f"✅ Added {rwn} | Account: {account} | Asof: {asof} | {symb}: price: {price} | price_change: {price_change} | 52wk_low: {low} | 52wk_high: {high}")
                    print(f"✅ Add | Account: {account} | {asof} | {symb}: price: {price} | price_change: {price_change} | 52wk_high: {high}")
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be

        # Save all changes
        db.commit()
        print(f"🎉Import data to my_portfolios table completed successfully!")

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
<<<<<<< HEAD
    csvfile = 'snapshot_' + today.strftime('%Y%m%d') + '.csv'
    print('--subdays=[%d]'%subdays)
    if subdays < 0: csvfile = 'snapshot_' + (today - timedelta(days=-subdays)).strftime('%Y%m%d') + '.csv'
=======
    theday = today + timedelta(days=subdays)
    csvfile = 'snapshot_' + theday.strftime('%Y%m%d') + '.csv'
    print('--subdays=[%d]'%subdays)
    # if subdays < 0: csvfile = 'snapshot_' + (today - timedelta(days=-subdays)).strftime('%Y%m%d') + '.csv'
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
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
<<<<<<< HEAD
    ASOF_TIME = datetime(today.year, today.month, today.day, 17, 41, 0)
=======
    ASOF_TIME = datetime(theday.year, theday.month, theday.day, 17, 41, 0)
>>>>>>> f67d697ec603fc6e69dd4d286f3f63a3be8036be
    # ASOF_TIME = datetime(2026, 5, 7, 17, 30, 0)
    # Start import
    import_portfolio_csv(db, csv_data_file, dict, ASOF_TIME)
    cursor.close()
    conn.close()

    # with open(data_csv, 'r', encoding='utf-8-sig') as csv_file:
    #     # Read rows as dictionaries
    #     csv_dict = csv.DictReader(csv_file)
    #     show_dict(csv_dict)
