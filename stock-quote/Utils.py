import urllib.request
import hashlib
from datetime import date
from datetime import datetime
import time, sys
from sty import ef, rs, FgRegister

DEBUG_LEVEL = 4

def displaySec(sec, sp):
    tag = '+=-'
    if sec.price_change > 0: tag = '+'
    elif sec.price_change == 0: tag = '='
    elif sec.price_change < 0: tag = '-'
    sec.tag = tag
    if tag == '-': sec.price_change = -1 * sec.price_change
    colorShow(sp, sec)

def printHeader(sp):
    print(sp, '╔══════════════════╤═════════╤═══════╤═╤════════╤═════════╤═════════╤═════════╤═════════════╤══════════════╤═════════╤═════════╤════════╤════════╗')
    print(sp, '║   Loading Time   │ Account │ Symbol│ │ Change │  Price  │ TodayGL │ PCTAcct │   Shares    │ CurrentValue │ 52WK Lo │ 52WK Hi │ PRC-Lo │ Hi-PRC ║')
    print(sp, '╟──────────────────┼─────────┼───────┼─┼────────┼─────────┼─────────┼─────────┼─────────────┼──────────────┼─────────┼─────────┼────────┼────────╢')
def printTailer(sp, totalValue, dday, tablename):
    print(sp, '╟──────────────────┴─────────┴───────┴─┴────────┴─────────┴─────────┴─────────┴─────────────┴──────────────┴─────────┴─────────┴────────┴────────╢')
    print(sp, '║ Market Value:', totalValue, ' ', dday, tablename + sp, '║')
    # print(sp, '╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╝')

def colorShow(sp, sec):
    fg = FgRegister()
    # print('====sec:', sec.change, sec.price)
    prlow = '--' if sec.low_52_week == 0 or sec.low_52_week == None else float(sec.price) - float(sec.low_52_week)
    higpr = '--' if sec.high_52_week == 0 or sec.high_52_week == None else float(sec.high_52_week) - float(sec.price)
    # prlow = '{:6.2f}'.format(float(sec.price) - float(sec.low_52_week))
    # higpr = '{:6.2f}'.format(float(sec.high_52_week) - float(sec.price))
    chnge = str(sec.price_change) # + sec.intradayChange
    price = str(sec.price) # + sec.intradayPrice
    # symbl = fg.cyan + pedsp(sec.symbol, 5) + fg.rs
    daygl = fg.cyan + padsp(sec.today_gl, 8) + fg.rs
    pctAc = fg.green + padsp(sec.pct_of_account, 8) + fg.rs
    if '-' in sec.tag:
        chnge = fg.red + padsp(chnge, 7) + fg.rs
        price = fg.red + padsp(price, 8) + fg.rs
        symbl = fg.red + pedsp(sec.symbol, 6) + fg.rs
        daygl = fg.red + padsp(sec.today_gl, 8) + fg.rs
    elif '=' in sec.tag:
        chnge = fg.yellow + padsp(chnge, 7) + fg.rs
        price = fg.yellow + padsp(price, 8) + fg.rs
        symbl = fg.yellow + pedsp(sec.symbol, 6) + fg.rs
        daygl = fg.yellow + padsp(sec.today_gl, 8) + fg.rs
    else:
        chnge = fg.green + padsp(chnge, 7) + fg.rs
        price = fg.green + padsp(price, 8) + fg.rs
        symbl = fg.green + pedsp(sec.symbol, 6) + fg.rs
        daygl = fg.green + padsp(sec.today_gl, 8) + fg.rs
    
    fprlow = '--' if (prlow == None or prlow == '--') else '{:6.2f}'.format(prlow)
    fhigpr = '--' if (higpr == None or higpr == '--') else '{:6.2f}'.format(higpr)
    # gprlow = fg.green + fprlow + fg.rs
    # rprlow = fg.red + fprlow + fg.rs
    # yprlow = fg.yellow + fprlow + fg.rs
    # prlow = {prlow > 0:gprlow, prlow < 0:rprlow}.get(True, yprlow)
    # ghigpr = fg.green + fhigpr + fg.rs
    # rhigpr = fg.red + fhigpr + fg.rs
    # yhigpr = fg.yellow + fhigpr + fg.rs
    # higpr = {higpr > 0:ghigpr, higpr < 0:rhigpr}.get(True, yhigpr)
    # higpr = {higpr > 0:fg.green + fhigpr + fg.rs, higpr < 0:fg.red + fhigpr + fg.rs}.get(True, fg.yellow + fhigpr + fg.rs)
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

    chnge = boldit(chnge)
    prlow = boldit(prlow)
    price = boldit(price)
    quant = padsp(str(sec.quantity) + ' ', 13) # Shares
    value = sec.current_value
    value = padsp(f"{value:,.2f}", 13)
    higpr = boldit(higpr)
    lo52w = padsp(sec.low_52_week, 8)
    hi52w = padsp(sec.high_52_week,8)
    ## don't touch this line below
    ptxt = sp + ' ║ {} │{}│ {}│{}│{} │{} │{} │{} │{}│{} │{} │{} │ {} │ {} ║'\
        .format(sec.asof_time.strftime("%Y-%m-%d %H:%M"), sec.account, symbl, sec.tag, chnge, price, daygl, pctAc, quant, value, lo52w, hi52w, prlow, higpr)
    print(ptxt)
    # sys.stdout.flush()

# fg = lambda text, color: "\33[38;5;" + str(color) + "m" + text + "\33[0m"
def boldit(str):
    return ef.bold + str + rs.bold_dim

# def colorit(str, wid):
#     color = 42
#     if '-' in str:
#         color = 160 
#         str = str.replace('-', '')
#         str = pedsp(str, wid)
#         colored_str = fg(str, color)
#     return pedsp(colored_str, wid)

def padsp(s, width): return f"{s or '':>{width}}" # 如果 s 是 None，换成空字符串，再右对齐
def pedsp(s, width): return f"{s or '--':<{width}}"
def padding(s, fill, align, width): return '{msg:{fill}{align}{width}}'.format(msg=s, fill=' ', align='<', width=width)

def is_number(value):
    try:
        float(value)  # Try to convert to float
        return True
    except (ValueError, TypeError):
        return False