#!/usr/bin/python
import sys
import argparse
import warnings

from Utils import boldIt, redIt, greenIt, yellowIt, drawTopHeader, showHeaderCxt, drawBotHeader, dispRow, drawBotLine, drawBotLineDownTick, drawBotLineUpTick
from Models import dbsession, MyPortfolio
from constants import NUM_PORTFOLIO_SEC, headers, tabw
from sty import FgRegister
fg = FgRegister()

parser = argparse.ArgumentParser()
parser.add_argument("sub_days", metavar='int', type=int, nargs='?', default='0', help='sub days from today(must be < 0), default 0 for today')
# optional arguments
parser.add_argument('-m', '--mfmt', type=int, default=1, help='show money format by default show float number -m 0')
parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
args = parser.parse_args()
sub_days = args.sub_days
money_format = args.mfmt
if sub_days > 0:
    warnings.warn("sub_days must negative, %s given, exiting..."%sub_days)
    sys.exit(1)
database = args.db
warnings.warn("sub_days=%d db=%s money_format:%s"%(sub_days, database, money_format))
# sys.exit(0)

def get_formated_data(diff):
    sdiff = f"{diff:,.2f}"  #currency format like 2,550.45
    difflen = len(sdiff)
    sdiffstr = str(sdiff)
    fgcolor = fg.red if diff < 0 else (fg.yellow if diff == 0 else fg.green)
    if diff < 0: sdiffstr = str(sdiff)[1:]; difflen -= 1
    cdiffx =  boldIt(fgcolor + sdiffstr + fg.rs)
    return cdiffx, difflen
    
if __name__=="__main__":
    db = dbsession(database)
    lmt = 2 * NUM_PORTFOLIO_SEC
    sub = -sub_days * NUM_PORTFOLIO_SEC
    tab = MyPortfolio
    rows = db.query(tab).order_by((tab.asof_time).desc(), tab.account.desc()).limit(lmt).offset(sub).all()
    
    rowst = rows[0:NUM_PORTFOLIO_SEC]
    rowsy = rows[NUM_PORTFOLIO_SEC:]

    totaly = sum(row.current_value for row in rowsy)
    totalt = sum(row.current_value for row in rowst)
    diff1 = totalt - totaly # totalValue of current day - totalValue of prior day
    diff2 = sum(row.price_change * row.quantity for row in rowst if row.price_change != None and row.quantity != None) # based on current day
    # diff2 = sum(row.current_value for row in rowst) # based on current day
    # print_rows_prior_day(rowsy)
    
    cdiff1, difflen1 = get_formated_data(diff1)
    cdiff2, difflen2 = get_formated_data(diff2)
    cdiff = 3*' ' + 'G/L(compare to preday): ' + cdiff1 + ' G/L(by price change): ' + cdiff2
    
    tablename = database + '.MyPortfolio'

    drawTopHeader(tabw)
    showHeaderCxt(tabw, headers)
    drawBotHeader(tabw)
    for row in rowsy: dispRow(tabw, row)
    drawBotLineUpTick(tabw)
    dday = rowsy[0].asof_time.strftime('%Y-%m-%d')
    totalValy = sum([row.current_value for row in rowsy])
    stockValy = sum([row.current_value for row in rowsy if row.account == 'My-stocks'])
    totalValt = sum([row.current_value for row in rowst])
    stockValt = sum([row.current_value for row in rowst if row.account == 'My-stocks'])
    portfy = totalValy - stockValy
    portft = totalValt - stockValt
    tpdiff = portft - portfy
    if money_format:
        prtfy = f"{portfy:,.2f}"
        prtft = f"{portft:,.2f}"
    else:
        prtfy = f'{portfy}'
        prtft = f'{portft}'
    TPdiff =  f"{tpdiff}"
    if tpdiff == 0: TPdiff = boldIt(yellowIt(TPdiff))
    elif tpdiff > 0: TPdiff = boldIt(greenIt(TPdiff))
    elif tpdiff < 0:
        TPdiff = f"{-1*tpdiff}" if money_format else -1*tpdiff
        TPdiff = boldIt(redIt(TPdiff))
    TPdiffExp = f'{prtft}' + ' - ' + f'{prtfy}'
    if money_format:
        bline1 = '║ '+dday+' Market Value: '+f"{totalValy:,.2f}"+' Stock Value: '+f"{stockValy:,.2f}"+'   Prev Portf: '+prtfy+' Today Portf: '+prtft
    else:
        bline1 = '║ '+dday+' Market Value: ' + f"{totalValy}" + ' Stock Value: ' + f"{stockValy}" + '   Prev Portf: ' + prtfy + ' Today Portf: ' + prtft
    bline2 = TPdiffExp + ' = ' + TPdiff
    print(bline1 + (sum(tabw) + len(tabw) - len(bline1) - len(bline2) + 18)*' ' + bline2 + ' ║')

    drawBotLineDownTick(tabw)
    showHeaderCxt(tabw, headers)
    drawBotHeader(tabw)
    for row in rowst: dispRow(tabw, row)
    drawBotLineUpTick(tabw)
    dday = rowst[0].asof_time.strftime('%Y-%m-%d')
    if money_format:
        lline1 = '║ ' + dday + ' Market Value: ' + f"{totalValt:,.2f}" + ' Stock Value: ' + f"{stockValt:,.2f}" + cdiff
    else:
        lline1 = '║ ' + dday + ' Market Value: ' + f'{totalValt}' + ' Stock Value: ' + f'{stockValt}' + cdiff
    lline2 = 'data from ' + tablename
    print(lline1 + (sum(tabw) + len(tabw) - len(lline1) - len(lline2) + 37)*' ' + lline2 + ' ║')
    drawBotLine(tabw)
