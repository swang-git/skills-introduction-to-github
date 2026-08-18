from datetime import date, datetime, timedelta
from sty import ef, rs, FgRegister
##from mysql.connector import Error

from constants import spx

def displaySec(sec, sp):
    tag = '+=-'
    if sec.price_change > 0: tag = '+'
    elif sec.price_change == 0: tag = '='
    elif sec.price_change < 0: tag = '-'
    sec.tag = tag
    if tag == '-': sec.price_change = -1 * sec.price_change
    colorShow(sp, sec)
    if tag == '-': sec.price_change = -1 * sec.price_change

def printHeader(sp):
    print(sp, '╔══════════════════╤═════════╤═══════╤═╤═══════════╤════════╤═════════╤═════════╤═════════╤═══════════╤══════════════╤═════════╤═════════╤════════╤════════╗')
    print(sp, '║   Loading Time   │ Account │ Symbol│ │  TotalGL  │ Change │  Price  │ TodayGL │ PCTAcct │   Shares  │ CurrentValue │ 52WK Lo │ 52WK Hi │ PRC-Lo │ Hi-PRC ║')
    print(sp, '╟──────────────────┼─────────┼───────┼─┼───────────┼────────┼─────────┼─────────┼─────────┼───────────┼──────────────┼─────────┼─────────┼────────┼────────╢')
def printTailer(sp, totalValue, dday, tablename):
    print(sp, '╟──────────────────┴─────────┴───────┴─┴───────────┴────────┴─────────┴─────────┴─────────┴───────────┴──────────────┴─────────┴─────────┴────────┴────────╢')
    print(sp, '║ Market Value:', totalValue, ' ', dday, tablename + sp, '║')
    # print(sp, '╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╝')

fg = FgRegister()
# print("FG", fg.__dict__)
# print("FG", dir(fg))
# print(type(fg))
# import pprint
# pprint.pprint(fg.__dict__)
def colorShow(sp, sec):
    # print("total_gl=[%s]"%sec.total_gl)
    # print('====sec:', sec.change, sec.price)
    prlow = '--' if sec.low_52_week == 0 or sec.low_52_week == None else float(sec.price) - float(sec.low_52_week)
    higpr = '--' if sec.high_52_week == 0 or sec.high_52_week == None else float(sec.high_52_week) - float(sec.price)
    # prlow = '{:6.2f}'.format(float(sec.price) - float(sec.low_52_week))
    # higpr = '{:6.2f}'.format(float(sec.high_52_week) - float(sec.price))
    chnge = str(sec.price_change) # + sec.intradayChange
    price = str(sec.price) # + sec.intradayPrice
    # symbl = fg.cyan + pedsp(sec.symbol, 5) + fg.rs
    # daygl = fg.cyan + padsp(sec.today_gl, 8) + fg.rs
    pctac = '' if sec.pct_of_account == None else f"{sec.pct_of_account:,.2f}" + '%'
    pctAc = padsp(pctac, 8)
    totalgl = sec.total_gl
    if totalgl == None: totalGL = '--'
    else:
        totalGL = padsp(totalgl, 10) if totalgl>=0 else padsp(-totalgl, 10)
        if totalgl == 0: totalGL = fg.yellow + totalGL + fg.rs
        elif totalgl > 0: totalGL = fg.green + totalGL + fg.rs
        else: totalGL = fg.red + totalGL + fg.rs

    todaygl = sec.today_gl
    if todaygl == None: todayGL = '--'
    else:
        todayGL = padsp(todaygl, 8) if todaygl>=0 else padsp(-todaygl, 8)
        if todaygl == 0: todayGL = fg.yellow + todayGL + fg.rs
        elif todaygl > 0: todayGL = fg.green + todayGL + fg.rs
        else: todayGL = fg.red + todayGL + fg.rs

    if '-' in sec.tag:
        chnge = fg.red + padsp(chnge, 7) + fg.rs
        price = fg.red + padsp(price, 8) + fg.rs
        symbl = fg.red + pedsp(sec.symbol, 6) + fg.rs
        # daygl = fg.red + padsp(sec.today_gl, 8) + fg.rs
        accnt = fg.red + padsp(sec.account, 8) + fg.rs
    elif '=' in sec.tag:
        chnge = fg.yellow + padsp(chnge, 7) + fg.rs
        price = fg.yellow + padsp(price, 8) + fg.rs
        symbl = fg.yellow + pedsp(sec.symbol, 6) + fg.rs
        # daygl = fg.yellow + padsp(sec.today_gl, 8) + fg.rs
        accnt = fg.yellow + padsp(sec.account, 8) + fg.rs
    else:
        chnge = fg.green + padsp(chnge, 7) + fg.rs
        price = fg.green + padsp(price, 8) + fg.rs
        symbl = fg.green + pedsp(sec.symbol, 6) + fg.rs
        # daygl = fg.green + padsp(sec.today_gl, 8) + fg.rs
        accnt = fg.green + padsp(sec.account, 8) + fg.rs
    
    fprlow = '--' if (prlow == None or prlow == '--') else '{:6.2f}'.format(prlow)
    fhigpr = '--' if (higpr == None or higpr == '--') else '{:6.2f}'.format(higpr)
    if is_number(prlow):
        if prlow == 0: prlow = fg.yellow + fprlow + fg.rs
        elif prlow > 0: prlow = fg.green + fprlow + fg.rs
        elif prlow < 0: prlow = fg.red + fprlow + fg.rs
    else: prlow = padsp(prlow, 6)
    if is_number(higpr):
        if higpr == 0: higpr = fg.yellow + fhigpr + fg.rs
        elif higpr > 0: higpr = fg.green + fhigpr + fg.rs
        elif higpr < 0: higpr = fg.red + fhigpr + fg.rs
    else: higpr = padsp(higpr, 6)

    pctAc = boldIt(pctAc)
    accnt = boldIt(accnt)
    symbl = boldIt(symbl)
    todayGL = boldIt(todayGL)
    totalGL = boldIt(totalGL)
    chnge = boldIt(chnge)
    prlow = boldIt(prlow)
    price = boldIt(price)
    quant = padsp(str(sec.quantity) + ' ', 11) # Shares
    value = sec.current_value
    value = padsp(f"{value:,.2f}", 13)
    value = boldIt(value)
    higpr = boldIt(higpr)
    lo52w = padsp(sec.low_52_week, 8)
    hi52w = padsp(sec.high_52_week,8)
    ## don't touch this line below
    ptxt = sp + ' ║ {} │{}│ {}│{}│{} │{} │{} │{} │{} │{}│{} │{} │{} │ {} │ {} ║'\
        .format(sec.asof_time.strftime("%Y-%m-%d %H:%M"), accnt, symbl, sec.tag, totalGL, chnge, price, todayGL, pctAc, quant, value, lo52w, hi52w, prlow, higpr)
    print(ptxt)
    # sys.stdout.flush()

def boldIt(str): return ef.bold + str + rs.bold_dim
def redIt(str): return fg.red + str + fg.rs
def greenIt(str): return fg.green + str + fg.rs
def yellowIt(str): return fg.yellow + str + fg.rs

def padsp(s, width): return f"{s or '':>{width}}" # 如果 s 是 None，换成空字符串，再右对齐
def pedsp(s, width): return f"{s or '--':<{width}}"
def padding(s, fill, align, width): return '{msg:{fill}{align}{width}}'.format(msg=s, fill=' ', align='<', width=width)

def is_number(value):
    try:
        float(value)  # Try to convert to float
        return True
    except (ValueError, TypeError):
        return False
    
def prev_business_day(d: date = None) -> date:
    if d is None:
        d = date.today()
    one_day = timedelta(days=1)
    while True:
        d -= one_day
        if d.weekday() < 5:  # 0=周一 … 4=周五，5/6 周末
            return d
        
def get_previous_business_day(date_input=None):
    # 1. 如果没传值，用今天
    if date_input is None:
        d = datetime.today().date()
    # 2. 如果传的是字符串，自动转成日期
    elif isinstance(date_input, str):
        d = datetime.strptime(date_input, "%Y-%m-%d").date()
    # 3. 如果已经是日期对象
    else:
        d = date_input

    one_day = timedelta(days=1)

    # 往前找，直到找到非周末的工作日
    while True:
        d -= one_day
        # 0=周一 ... 4=周五，5=周六，6=周日
        if d.weekday() < 5:
            return d.strftime("%Y-%m-%d")  # 直接返回 YYYY-mm-dd 字符串


def drawTopHeader(tabw):
    strtop = spx + '╔' 
    for w in tabw[:-1]: strtop += w * '═' + '╤'
    strtop += tabw[-1] * '═' + '╗'
    print(strtop)

def showHeaderCxt(tabw, cxt):
    strcxt = spx + '║'
    for i, c in enumerate(cxt): strcxt += c.center(tabw[i]) + '│' if i < len(tabw)-1 else ''
    strcxt += cxt[-1].center(tabw[-1]) + '║'
    print(strcxt)

def drawBotHeader(tabw):
    strbot = spx + '╟' 
    for w in tabw[:-1]: strbot += w * '─' + '┼'
    strbot += tabw[-1] * '─' + '╢'
    print(strbot)

def dispRow(tabw, row):
    fg = FgRegister()
    pg = row.price_change
    tag = fg.yellow + '=' + fg.rs
    if pg!=None and pg>0: tag = fg.green + '+' + fg.rs 
    # elif pg != None and pg < 0: tag = fg.magenta + '━' + fg.rs
    # elif pg != None and pg < 0: tag = fg.red + '━' + fg.rs
    # elif pg != None and pg < 0: tag = fg.red + '━' + fg.rs
    elif pg != None and pg < 0: tag = fg.red + '═' + fg.rs

    tgl = 0 if row.total_gl == None else row.total_gl
    acct = row.account
    acct = fg.green + acct + fg.rs if tgl > 0 else (acct if tgl == 0 else fg.red + acct + fg.rs)

    idx  = 0; rowstr = spx + '║' + row.asof_time.strftime('%Y-%m-%d %H:%M').center(tabw[idx]) + '│'
    idx += 1; rowstr += boldIt(acct.center(tabw[idx])) + '│'
    idx += 1; rowstr += (tabw[idx]*' ' if row.total_gl == None else boldIt(procCol(tabw[idx], row.total_gl, False))) + '│'
    idx += 1; rowstr += (tabw[idx]*' ' if row.total_gl_pct == None else boldIt(procCol(tabw[idx], row.total_gl_pct, False))) + '│'
    idx += 1; rowstr += padsp(row.symbol, tabw[idx]-1) + spx + ' │'
    idx += 1; rowstr += boldIt(tag.center(tabw[idx])) + '│'
    idx += 1; rowstr += (tabw[idx]*' ' if row.price_change == None else boldIt(procCol(tabw[idx], row.price_change, False, 3))) + '│'
    idx += 1; rowstr += (tabw[idx]*' ' if row.price == None else boldIt(procCol(tabw[idx], row.price, True, 3))) + '│'
    idx += 1; rowstr += (tabw[idx]*' ' if row.today_gl == None else boldIt(procCol(tabw[idx], row.today_gl))) + '│'
    idx += 1; rowstr += (tabw[idx]*' ' if row.pct_of_account == 0 else procCol(tabw[idx], row.pct_of_account, True, 3)) + '│'
    idx += 1; rowstr += (tabw[idx]*' ' if row.quantity == None else procCol(tabw[idx], row.quantity, True, 3)) + '│'    ## Shares 
    idx += 1; rowstr += boldIt(padsp(f"{row.current_value:,.2f}", tabw[idx]-1)) +  spx + ' │'
    idx += 1; rowstr += boldIt(padsp(row.low_52_week, tabw[idx]-1)) + spx + ' │'
    idx += 1; rowstr += boldIt(padsp(row.high_52_week, tabw[idx]-1)) + spx + ' │'
    idx += 1; rowstr += padsp('', tabw[idx]) + '│' if row.low_52_week == None else boldIt(procCol(tabw[idx], row.price - row.low_52_week, False)) + '│'
    idx += 1; rowstr += padsp('', tabw[idx]) + '║' if row.high_52_week == None else boldIt(procCol(tabw[idx], row.high_52_week - row.price, False)) + '║'
    print(rowstr)

# def drawBotLine(tabw):
#     clsline = ' ╟' 
#     for w in tabw[:-1]: clsline += w * '━' + '┷'
#     clsline += tabw[-1] * '━' + '╢'
#     print(clsline)

def drawBotLineDownTick(tabw):
    sprline = spx + '╟' 
    for w in tabw[:-1]: sprline += w * '━' + '┯'
    sprline += tabw[-1] * '━' + '╢'
    print(sprline)

def drawBotLineUpTick(tabw):
    sprline = spx + '╟' 
    for w in tabw[:-1]: sprline += w * '━' + '┷'
    sprline += tabw[-1] * '━' + '╢'
    print(sprline)

def drawBotLineCrossTick(tabw):
    crsline = spx + '╟' 
    for w in tabw[:-1]: crsline += w * '━' + '┿'
    crsline += tabw[-1] * '━' + '╢'
    print(crsline)

def drawBotLine(tabw):
    clsline = spx + '╚' 
    clsline += (sum(tabw) + len(tabw) - 1) * '═' 
    clsline += '╝'
    print(clsline)

def procCol(tw:int, fl:float, no_color=False, dml=2):
    if fl == None: fl = 0
    fg = FgRegister()
    strfl = '--'
    strfl = padsp(f"{fl:.{dml}f}", tw-1) + ' ' if fl>=0 else padsp(-fl, tw-1) + ' ' ##__ append a space
    if no_color: return strfl
    if fl == 0: strfl = fg.yellow + strfl + fg.rs
    elif fl > 0: strfl = fg.green + strfl + fg.rs
    elif fl < 0: strfl = fg.red + strfl + fg.rs
    return strfl

def center_perfect_NOT_WORKING(string: str, target_length: int) -> str:
    """
    Perfectly centers a string to EXACT target length (lx).
    Uses half-spaces for balanced centering when padding is odd.
    """
    HALF_SPACE = "\u200A"  # -width space (visual 0.5 space)
    HALF_SPACE = "\u2006"  # -width space (visual 0.5 space)
    HALF_SPACE = "\u2005"  # -width space (visual 0.5 space)
    str_len = len(string)

    # If string is longer than target, return as-is
    if str_len >= target_length:
        return string

    total_pad = target_length - str_len
    base_pad = total_pad // 2   # Full spaces on each side
    extra = total_pad % 2       # 0 = even, 1 = odd padding

    # YOUR CORRECT LOGIC 👇
    if extra == 1:
        # Odd padding: full spaces + HALF-SPACE on BOTH sides
        return " " * base_pad + HALF_SPACE + string + HALF_SPACE + " " * base_pad
    else:
        # Even padding: normal full spaces
        return " " * base_pad + string + " " * base_pad
    
def get_data_from_table(cur, tabname: str, condition: str = None, orderby: str = None, limit: int = None, cols: str = None):
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
        if cols: query = f"SELECT {cols} FROM {tabname}"
        
        # Add WHERE condition if provided
        if condition:
            query += f" WHERE {condition}"

        # Add orderBy condition if provided
        if orderby:
            query += f" ORDER BY {orderby}"
        
        # Add LIMIT if provided
        if limit:
            query += f" LIMIT {limit}"

        # print('query[%s]'%query)

        # Execute and fetch
        cur.execute(query)
        results = cur.fetchall()  # List of dicts

        return results

    except Error as e:
        print(f"❌ MySQL Query Error: {e}")
        return []  # Return empty list on failure

def build_dict(cursor, results):  ## results from get_data_from_table
    """
    Convert query results into a dictionary:
    { 'MSFT': row1, 'CSCO': row2, ... }
    """
    # --------------------------
    # 3. Get column names (critical!)
    # --------------------------
    columns = [col[0] for col in cursor.description]
    
    result_dict = {}
    for row in results:
        # Convert raw tuple row → dictionary {column: value}
        row_dict = dict(zip(columns, row))
        # SET YOUR KEY HERE (e.g., key = account column)
        key = row_dict["symbol"]
    
        # Add to final dict
        result_dict[key] = row_dict

    return result_dict


def get_52_week_low(symbol, dict):
    if symbol == None: return None
    elif symbol not in dict: return None
    return dict[symbol]['wk52_low']
    # ret = retx['wk52_low']
    # print("ret:[%s]"%retx)
    # return ret
def get_52_week_high(symbol, dict):
    if symbol == None: return None
    elif symbol not in dict: return None
    return dict[symbol]['wk52_high']

def get_last_portfolio(cursor):
    print('-fn-get_last_portfolio')
    portf = get_data_from_table(cursor, 'health_records', 'status="A"', 'date desc', 1, 'portfolio')
    # print(float(portf[0][0]))
    return float(portf[0][0])

def get_meta(cursor):
    # print('-fn-get_meta[%s]'%symb)
    metax = get_data_from_table(cursor, 'security_metas', 'status="A"')
    # print(metax)
    meta_dict = build_dict(cursor, metax)
    # print(meta_dict)
    return meta_dict

def get_quantity(meta_dict, symb):
    quantity = meta_dict[symb]['quantity']
    # print("quantity=[%s]"%quantity)
    return quantity

def get_total_cost(meta_dict, symb):
    total_cost = meta_dict[symb]['total_cost']
    # print("total_cost=[%s]"%total_cost)
    return total_cost

def get_basis_price(meta_dict, symb):
    basis_price = meta_dict[symb]['basis_price']
    # print("basis_price=[%s]"%basis_price)
    return basis_price
