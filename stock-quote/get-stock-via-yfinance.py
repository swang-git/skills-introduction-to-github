#!/Users/swang/myenv/bin/python
import sys
import yfinance as yf
from datetime import datetime
import mysql.connector
from mysql.connector import Error
import time
import argparse

parser = argparse.ArgumentParser()
parser.add_argument("sub_days", metavar='int', type=int, nargs='?', default='0', help='sub days from today(must be < 0), default 0 for today')
# optional arguments
parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
# parser.add_argument('-d', '--db', type=str, default='devx', help='check quotes in this database default database: devx')
parser.add_argument('-t', '--tab', type=str, default='my_portfolios', help='populate table=health_records in database default health_records')
args = parser.parse_args()
sub_days = args.sub_days
if sub_days > 0:
    print("sub_days must negative, %s given, exiting..."%sub_days)
    sys.exit(1)
database = args.db
table = args.tab

def get_db_connection():
    """Create and return ONE connection + ONE cursor"""
    db_config = { "host": "localhost", "database": database, "user": "swang", "password": "Ybsjll11" }
    try:
        conn = mysql.connector.connect(**db_config)
        cur = conn.cursor(dictionary=True)  # dictionary for easy access
        return conn, cur
    except Error as e:
        print(f"Connection failed: {e}")
        return None, None

def get_stock_data_for_portfolio(symbol: str):
    """
    Fetch stock data and return a dict MATCHING YOUR my_portfolios TABLE SCHEMA.
    Missing data = None (MySQL NULL)
    """
    # Default ALL fields to NULL (None) where allowed
    # MATCHES YOUR describe my_portfolios; OUTPUT 1:1
    data = {
        "asof_time": datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "account": "My-stocks",          # you can change this
        "account_name": "Chase Account",  # you can change this
        "symbol": symbol.upper(),
        "company": None,
        "price": None,
        "price_change": None,
        "today_gl": None,
        "today_gl_pct": None,
        "total_gl": None,
        "total_gl_pct": None,
        "current_value": None,
        "pct_of_account": None,
        "quantity": None,
        "total_cost": None,
        "cost_per_share": None,
        "low_52_week": None,
        "high_52_week": None,
        "status": "A"  # your default: A = Active
    }

    data = {}
    try:
        ticker = yf.Ticker(symbol)
        info = ticker.info

        # Company name
        data["company"] = info.get("longName")

        # Current price (required, NOT NULL)
        current_price = info.get("currentPrice") or info.get("regularMarketPrice")
        if current_price:
            data["price"] = round(current_price, 3)

        # 52-week low & high
        data["low_52_week"] = round(info.get("fiftyTwoWeekLow", 0), 3) if info.get("fiftyTwoWeekLow") else None
        data["high_52_week"] = round(info.get("fiftyTwoWeekHigh", 0), 3) if info.get("fiftyTwoWeekHigh") else None

        # Price change (daily $ and %)
        prev_close = info.get("previousClose")
        if current_price and prev_close:
            p_change = round(current_price - prev_close, 3)
            p_change_pct = round(((current_price / prev_close) - 1) * 100, 2)
            
            data["price_change"] = p_change
            data["today_gl_pct"] = p_change_pct

    except Exception:
        # If API fails: keep all missing values as NULL
        pass

    print("sleeping for 1 second")
    time.sleep(2)
    return data

# ------------------------------
# OPTIONAL: INSERT DIRECTLY TO MYSQL
# ------------------------------
def XXXinsert_to_my_portfolios(stock_data):
    connection = None
    cursor = None
    try:
        # Connect to YOUR MySQL database
        # connection = mysql.connector.connect(
        #     host="localhost",
        #     database="YOUR_DATABASE",  # CHANGE THIS
        #     user="YOUR_USER",          # CHANGE THIS
        #     password="YOUR_PASSWORD"   # CHANGE THIS
        # )
        # # Connect to MySQL
        connection = mysql.connector.connect(**db_config)
        # cursor = connection.cursor(dictionary=True)  # Returns rows as DICTs
        cursor = connection.cursor()

        # MySQL INSERT query (matches your columns)
        insert_query = """
        INSERT INTO my_portfolios (
            asof_time, account, account_name, symbol, company, price, price_change,
            today_gl, today_gl_pct, total_gl, total_gl_pct, current_value,
            pct_of_account, quantity, total_cost, cost_per_share,
            low_52_week, high_52_week, status
        ) VALUES (
            %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s
        )
        """

        # Tuple of values in ORDER
        values = (
            stock_data["asof_time"],
            stock_data["account"],
            stock_data["account_name"],
            stock_data["symbol"],
            stock_data["company"],
            stock_data["price"],
            stock_data["price_change"],
            stock_data["today_gl"],
            stock_data["today_gl_pct"],
            stock_data["total_gl"],
            stock_data["total_gl_pct"],
            stock_data["current_value"],
            stock_data["pct_of_account"],
            stock_data["quantity"],
            stock_data["total_cost"],
            stock_data["cost_per_share"],
            stock_data["low_52_week"],
            stock_data["high_52_week"],
            stock_data["status"]
        )

        cursor.execute(insert_query, values)
        connection.commit()
        print(f"✅ Inserted {stock_data['symbol']} into my_portfolios successfully")

    except Error as e:
        print(f"❌ MySQL Error: {e}")

    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def get_data_from_table(cur, tabname: str, condition: str = None, limit: int = None):
    """
    Query data from a MySQL table (qtable) with optional WHERE condition and LIMIT.
    
    Args:
        qtable: Name of your table (e.g., "my_portfolios", "stocks", "transactions")
        condition: Optional WHERE clause (e.g., "symbol = 'MSFT'", "status = 'A'")
        limit: Optional max rows to return
    
    Returns:
        List of dictionaries (each dict = 1 row, column names as keys)
        Empty list if no data / error
    """
    try:
        # Connect to MySQL
        # Build safe SQL query
        query = f"SELECT * FROM {tabname}"
        
        # Add WHERE condition if provided
        if condition:
            query += f" WHERE {condition}"
        
        # Add LIMIT if provided
        if limit:
            query += f" LIMIT {limit}"

        # Execute and fetch
        cur.execute(query)
        results = cur.fetchall()  # List of dicts

        return results

    except Error as e:
        print(f"❌ MySQL Query Error: {e}")
        return []  # Return empty list on failure

    # finally:
    #     # Always close connection
    #     if connection.is_connected():
    #         cursor.close()
    #         connection.close()

# --------------------------
# 2. PRETTY PRINT FUNCTION (formatted output)
# --------------------------
def print_query_results(results):
    if not results:
        print("No data found.")
        return

    # Print header
    print("=" * 120)
    print(f"📊 Total rows found: {len(results)}")
    print("=" * 120)

    # Print each row neatly
    for idx, row in enumerate(results, 1):
        print(f"\n--- Row {idx} ---")
        for key, value in row.items():
            print(f"{key:<18} | {value}")

    print("\n" + "=" * 120)

# --------------------------
# 3. Convert query results → meta = {symbol: row}
# --------------------------
def build_meta_dict(results):
    """
    Convert query results into a dictionary:
    { 'MSFT': row1, 'CSCO': row2, ... }
    """
    meta = {}
    for row in results:
        symbol = row["symbol"]  # Get symbol from the row
        meta[symbol] = row     # Assign row to its symbol key
    return meta

def print_meta(meta, symbol):
    for key, val in meta[symbol]:
        print(key, val)

def get_data_for_my_portfolio(stock: str):
    dax = get_stock_data_for_portfolio(stock)
    # Print all fields (matches your table)
    print("📊 My Portfolio Data (matches my_portfolios table):")
    for key, val in dax.items():
        print(f"{key:<15} = {val}")

def get_full_data_for_my_portfolio(cur, stocks):
    stock_dict = {}
    stock_data = []
    total_stock_value = 0
    for stock in stocks:
        dax = get_stock_data_for_portfolio(stock)
        mts = get_data_from_table(cur, 'security_metas', 'status="A"')
        mtx = build_meta_dict(mts)
        dax['quantity'] = float(mtx[stock]['quantity'])
        dax['today_gl'] = dax['price_change'] * dax['quantity']
        dax['current_value'] = dax['price'] * dax['quantity']
        dax['total_cost'] = float(mtx[stock]['total_cost'])
        dax['cost_per_share'] = mtx[stock]['basis_price']
        dax['total_gl'] = dax['current_value'] - dax['total_cost']
        dax['total_gl_pct'] = dax['total_gl'] / dax['total_cost'] * 100
        total_stock_value += dax['current_value']
        stock_dict[stock] = dax
    
    for stock in stocks:
        stock_dict[stock]['pct_of_account'] = stock_dict[stock]['current_value'] / total_stock_value * 100
        # Print all fields (matches your table)
        stock_data.append(stock_dict[stock])
        print("📊 My Portfolio Data (matches my_portfolios table):")
        # for key, val in stock_dict[stock].items(): print(f"{key:<15} = {val}")
        # for row in stock_data: ##print(row)
        #     for key, val in row.items():
        #         print(f"{key:<15} = {val}")
    return stock_data

def insert_to_my_portfolios(conn, cur, stock_data):
    try:
        values_list = []
        for s in stock_data:
            values = (
                s["asof_time"], s["account"], s["account_name"], s["symbol"], s["company"],
                s["price"], s["price_change"], s["today_gl"], s["today_gl_pct"],
                s["total_gl"], s["total_gl_pct"], s["current_value"], s["pct_of_account"],
                s["quantity"], s["total_cost"], s["cost_per_share"],
                s["low_52_week"], s["high_52_week"], s["status"]
            )
            values_list.append(values)

        # 4. INSERT ALL ROWS IN ONE BULK QUERY
        insert_query = """
        INSERT INTO my_portfolios (
            asof_time, account, account_name, symbol, company, price, price_change,
            today_gl, today_gl_pct, total_gl, total_gl_pct, current_value,
            pct_of_account, quantity, total_cost, cost_per_share,
            low_52_week, high_52_week, status
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        # for row in values_list:
            # for x in row: print(x)
        cur.executemany(insert_query, values_list)
        conn.commit()

        print(f"✅ Success: Inserted {cur.rowcount} rows")

    except Error as e:
        print(f"❌ MySQL Error: {e}")

def get_indices():
    indices = {
        # "DJIA": "^DJI",
        "DOW_JONES": "^DJI",
        "NASDAQ": "^IXIC",
        "SP500": "^GSPC",
        "FTSE100": "^FTSE",
        "NIKKEI": "^N225"
    }
    data = {}
    for name, ticker in indices.items():
        info = yf.Ticker(ticker).info
        data[name] = info.get("regularMarketPrice")
    return data

print(get_indices())

def insert_to_health_records(conn, cur, data):
    """
    Insert indices to health_records TABLE SCHEMA.
    """
    try:
        # MySQL INSERT query (matches your columns)
        insert_query = """
        INSERT INTO health_records (
            date, weight, portfolio, DOW_JONES, NASDAQ, SP500, FTSE100, NIKKEI, note
        ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        # Tuple of values in ORDER
        values = (
            datetime.now().strftime("%Y-%m-%d"),
            68.8, # weight
            ### 1976543.21,      ### portfolio
            data["DOW_JONES"],
            data["NASDAQ"],
            data["SP500"],
            data["FTSE100"],
            data["NIKKEI"],
            datetime.now().strftime("%Y-%m-%d %H:%M:%S") ### note
        )

        cur.execute(insert_query, values)
        conn.commit()
        print(f"✅ Inserted indices into health_records successfully")

    except Error as e:
        print(f"❌ MySQL Error(insert to health_records): {e}")


# ------------------------------
# Main function USAGE
# ------------------------------
if __name__ == "__main__":
    # ==============================================
    # ✅ STEP 1: OPEN CONNECTION + CURSOR ONCE HERE
    # ==============================================
    conn, cur = get_db_connection()
    print("table:%s"%table)
    try:
        data = get_indices()
        insert_to_health_records(conn, cur, data)
        stocks = ['MSFT', 'CSCO', 'DELL', 'CHTR', 'WBD', 'T']
        stock_data = get_full_data_for_my_portfolio(cur, stocks)
        for row in stock_data: ##print(row)
            for key, val in row.items():
                print(f"{key:<15} = {val}")
        insert_to_my_portfolios(conn, cur, stock_data)
        # if table == 'health_records':
        #     data = get_indices()
        #     insert_to_health_records(conn, cur, data)
        # else:
        #     stocks = ['MSFT', 'CSCO', 'DELL', 'CHTR', 'WBD', 'T']
        #     stock_data = get_full_data_for_my_portfolio(cur, stocks)
        #     for row in stock_data: ##print(row)
        #         for key, val in row.items():
        #             print(f"{key:<15} = {val}")
        #     insert_to_my_portfolios(conn, cur, stock_data)
    
    finally:
        # ==============================================
        # ✅ STEP 2: CLOSE ONLY AFTER BOTH FUNCTIONS
        # ==============================================
        cur.close()
        conn.close()
        print("\n✅ Cursor & connection closed safely")

