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
    print(sp, '╔═════════════════════╤══════╤═╤════════╤═════════╤═════════╤═════════╤═══════╤════════════╤═════════╤═════════╤════════╤════════╗')
    print(sp, '║     Loading Time    │ STCK │ │ Change │  Price  │ Day Low │ DayHigh │ Share │ TotalValue │ 52WK Lo │ 52WK Hi │ PRC-Lo │ Hi-PRC ║')
    print(sp, '╟─────────────────────┼──────┼─┼────────┼─────────┼─────────┼─────────┼───────┼────────────┼─────────┼─────────┼────────┼────────╢')
def printTailer(sp, totalValue):
    print(sp, '╟─────────────────────┴──────┴─┴────────┴─────────┴─────────┴─────────┴───────┴────────────┴─────────┴─────────┴────────┴────────╢')
    print(sp, '║', 63*' ', 'Market Value:', totalValue, 37*' ', '║')
    print(sp, '╚════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╝')

def colorShow(sp, sec):
    fg = FgRegister()
    # print('====sec:', sec.change, sec.price)
    prlow = float(sec.price) - float(sec.low_52_week)
    higpr = float(sec.high_52_week) - float(sec.price)
    # prlow = '{:6.2f}'.format(float(sec.price) - float(sec.low_52_week))
    # higpr = '{:6.2f}'.format(float(sec.high_52_week) - float(sec.price))
    chnge = str(sec.price_change) # + sec.intradayChange
    price = str(sec.price) # + sec.intradayPrice
    # symbl = fg.cyan + pedsp(sec.symbol, 5) + fg.rs
    daylo = fg.cyan + padsp(sec.day_low, 8) + fg.rs
    dayhi = fg.green + padsp(sec.day_high, 8) + fg.rs
    if '-' in sec.tag:
        chnge = fg.red + padsp(chnge, 7) + fg.rs
        price = fg.red + padsp(price, 8) + fg.rs
        symbl = fg.red + pedsp(sec.symbol, 5) + fg.rs
    elif '=' in sec.tag:
        chnge = fg.yellow + padsp(chnge, 7) + fg.rs
        price = fg.yellow + padsp(price, 8) + fg.rs
        symbl = fg.yellow + pedsp(sec.symbol, 5) + fg.rs
    else:
        chnge = fg.green + padsp(chnge, 7) + fg.rs
        price = fg.green + padsp(price, 8) + fg.rs
        symbl = fg.green + pedsp(sec.symbol, 5) + fg.rs
    
    fprlow = '{:6.2f}'.format(prlow)
    fhigpr = '{:6.2f}'.format(higpr)
    # gprlow = fg.green + fprlow + fg.rs
    # rprlow = fg.red + fprlow + fg.rs
    # yprlow = fg.yellow + fprlow + fg.rs
    # prlow = {prlow > 0:gprlow, prlow < 0:rprlow}.get(True, yprlow)
    # ghigpr = fg.green + fhigpr + fg.rs
    # rhigpr = fg.red + fhigpr + fg.rs
    # yhigpr = fg.yellow + fhigpr + fg.rs
    # higpr = {higpr > 0:ghigpr, higpr < 0:rhigpr}.get(True, yhigpr)
    # higpr = {higpr > 0:fg.green + fhigpr + fg.rs, higpr < 0:fg.red + fhigpr + fg.rs}.get(True, fg.yellow + fhigpr + fg.rs)
    if prlow == 0: prlow = fg.yellow + fprlow + fg.rs
    elif prlow > 0: prlow = fg.green + fprlow + fg.rs
    elif prlow < 0:prlow = fg.red + fprlow + fg.rs
    if higpr == 0: higpr = fg.yellow + fhigpr + fg.rs
    elif higpr > 0: higpr = fg.green + fhigpr + fg.rs
    elif higpr < 0: higpr = fg.red + fhigpr + fg.rs

    chnge = boldit(chnge)
    prlow = boldit(prlow)
    price = boldit(price)
    quant = padsp(str(sec.shares) + ' ', 7)
    value = sec.shares * sec.price
    value = padsp(f"{value:,.2f}", 11)
    higpr = boldit(higpr)
    lo52w = padsp(sec.low_52_week, 8)
    hi52w = padsp(sec.high_52_week,8)
    ## don't touch this line below
    ptxt = sp + ' ║ {} │ {}│{}│{} │{} │{} │{} │{}│{} │{} │{} │ {} │ {} ║'\
        .format(sec.load_time, symbl, sec.tag, chnge, price, daylo, dayhi, quant, value, lo52w, hi52w, prlow, higpr)
    print(ptxt)
    # sys.stdout.flush()

fg = lambda text, color: "\33[38;5;" + str(color) + "m" + text + "\33[0m"
def boldit(str):
    return ef.bold + str + rs.bold_dim

def colorit(str, wid):
    color = 42
    if '-' in str:
        color = 160 
        str = str.replace('-', '')
        str = pedsp(str, wid)
        colored_str = fg(str, color)
    return pedsp(colored_str, wid)

def padsp(s, width): return f"{s:>{width}}"
def pedsp(s, width): return f"{s:<{width}}"
def padding(s, fill, align, width): return '{msg:{fill}{align}{width}}'.format(msg=s, fill=' ', align='<', width=width)

