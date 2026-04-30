# import urllib.request
# import hashlib
from datetime import date, datetime, timedelta
# import time, sys
from sty import ef, rs, FgRegister

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
def colorShow(sp, sec):
    # print("total_gl=[%s]"%sec.total_gl)
    # fg = FgRegister()
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


def headerTop(tabw):
    strtop = ' ╔' 
    for w in tabw[:-1]: strtop += w * '═' + '╤'
    strtop += tabw[-1] * '═' + '╗'
    print(strtop)

def headerHdr(tabw, cxt):
    strcxt = ' ║'
    for i, c in enumerate(cxt): strcxt += c.center(tabw[i]) + '│' if i < len(tabw)-1 else ''
    strcxt += cxt[-1].center(tabw[-1]) + '║'
    print(strcxt)

def headerBot(tabw):
    strbot = ' ╟' 
    for w in tabw[:-1]: strbot += w * '─' + '┼'
    strbot += tabw[-1] * '─' + '╢'
    print(strbot)

def dispRow(tabw, row):
    fg = FgRegister()
    pg = row.price_change
    tag = fg.yellow + '=' + fg.rs
    if pg > 0: tag = fg.green + '+' + fg.rs
    elif pg < 0: tag = fg.red + '-' + fg.rs
    tgl = row.total_gl
    acct = row.account
    acct = fg.green + acct + fg.rs if tgl > 0 else (acct if tgl == 0 else fg.red + acct + fg.rs)
    # if tgl > 0: acct = fg.green + acct + fg.rs
    # elif tgl < 0: acct = fg.red + acct + fg.rs

    idx  = 0; rowstr = ' ║' + row.asof_time.strftime('%Y-%m-%d %H:%M').center(tabw[idx]) + '│'
    idx += 1; rowstr += acct.center(tabw[idx]) + '│'
    idx += 1; rowstr += row.symbol.center(tabw[idx]) + '│'
    idx += 1; rowstr += tag.center(tabw[idx]) + '│'
    idx += 1; rowstr += procCol(tabw[idx], row.total_gl) + '│'
    idx += 1; rowstr += procCol(tabw[idx], row.price_change) + '│'
    idx += 1; rowstr += procCol(tabw[idx], row.price) + '│'
    idx += 1; rowstr += procCol(tabw[idx], row.today_gl) + '│'
    idx += 1; rowstr += procCol(tabw[idx], row.pct_of_account) + '│'
    idx += 1; rowstr += procCol(tabw[idx], row.quantity) + '│'
    idx += 1; rowstr += boldIt(padsp(f"{row.current_value:,.2f}", tabw[idx]-1)) + ' │'
    idx += 1; rowstr += padsp(row.low_52_week, tabw[idx]-1) + ' │'
    idx += 1; rowstr += padsp(row.high_52_week, tabw[idx]-1) + ' │'
    idx += 1; rowstr += padsp('', tabw[idx]) + '│' if row.low_52_week == 0 else procCol(tabw[idx], row.price - row.low_52_week) + '│'
    idx += 1; rowstr += padsp('', tabw[idx]) + '║' if row.high_52_week == 0 else procCol(tabw[idx], row.high_52_week - row.price) + '║'
    print(rowstr)

def closeLine(tabw):
    clsline = ' ╟' 
    for w in tabw[:-1]: clsline += w * '─' + '┴'
    clsline += tabw[-1] * '─' + '╢'
    print(clsline)

def closeBottom(tabw):
    clsline = ' ╚' 
    clsline += (sum(tabw) + len(tabw) - 1) * '═' 
    clsline += '╝'
    print(clsline)

def procCol(tw, fl):
    fg = FgRegister()
    strfl = '--'
    strfl = padsp(fl, tw-1) + ' ' if fl>=0 else padsp(-fl, tw-1) + ' ' ##__ append a space
    if fl == 0: strfl = fg.yellow + strfl + fg.rs
    elif fl > 0: strfl = fg.green + strfl + fg.rs
    elif fl < 0: strfl = fg.red + strfl + fg.rs
    return strfl
