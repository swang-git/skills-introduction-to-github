#!/Users/swang/myenv/bin/python
import sys
import argparse
# from more_itertools import chunked

from Utils import boldIt, redIt, greenIt, headerTop, headerHdr, headerBot, dispRow, closeLine, closeBottom, padsp
from Models import dbsession, MyPortfolio
from constants import NUM_PORTFOLIO_SEC, tabw, headers
from sty import ef, rs, FgRegister
fg = FgRegister()

parser = argparse.ArgumentParser()
parser.add_argument("sub_days", metavar='int', type=int, nargs='?', default='0', help='sub days from today(must be < 0), default 0 for today')
# optional arguments
parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
# parser.add_argument('-d', '--db', type=str, default='devx', help='check quotes in this database default database: devx')
args = parser.parse_args()
sub_days = args.sub_days
if sub_days > 0:
    print("sub_days must negative, %s given, exiting..."%sub_days)
    sys.exit(1)
database = args.db
# print("sub_days=%d db=%s"%(sub_days, database))

def get_formated_data(diff):
    sdiff = f"{diff:,.2f}"  #currency format like 2,550.45
    difflen = len(sdiff)
    sdiffstr = str(sdiff)
    fgcolor = fg.red if diff < 0 else (fg.yellow if diff == 0 else fg.green)
    if diff < 0: sdiffstr = str(sdiff)[1:]; difflen -= 1
    cdiff =  boldIt(fgcolor + sdiffstr + fg.rs)
    return cdiff, difflen
    
if __name__=="__main__":
    rows = dbsession(database).query(MyPortfolio).order_by((MyPortfolio.asof_time).desc(), MyPortfolio.account.desc())\
    .limit(2*NUM_PORTFOLIO_SEC).offset(-sub_days*NUM_PORTFOLIO_SEC).all()
    rowst = rows[0:NUM_PORTFOLIO_SEC]
    rowsy = rows[NUM_PORTFOLIO_SEC:]

    totaly = sum(row.current_value for row in rowsy)
    totalt = sum(row.current_value for row in rowst)
    diff1 = totalt - totaly # totalValue of current day - totalValue of prior day
    diff2 = sum(row.price_change * row.quantity for row in rowst) # based on current day
    # print_rows_prior_day(rowsy)
    
    cdiff1, difflen1 = get_formated_data(diff1)
    cdiff2, difflen2 = get_formated_data(diff2)
    cdiff = 4*' ' + 'G/L(compare to preday):' + cdiff1 + ' G/L(by price change):' + cdiff2

    tablename = database + '.MyPortfolio'
    headerTop(tabw)
    headerHdr(tabw, headers)
    headerBot(tabw)
    for row in rowsy: dispRow(tabw, row)
    closeLine(tabw)
    dday = rowsy[0].asof_time.strftime('%Y-%m-%d')
    totalValy = sum([row.current_value for row in rowsy])
    stockValy = sum([row.current_value for row in rowsy if row.account == 'My-stocks'])
    totalValt = sum([row.current_value for row in rowst])
    stockValt = sum([row.current_value for row in rowst if row.account == 'My-stocks'])
    portfy = totalValy - stockValy
    portft = totalValt - stockValt
    tpdiff = portft - portfy
    prtfy = f"{portfy:,.2f}"
    prtft = f"{portft:,.2f}"
    TPdiff =  f"{tpdiff}"
    if tpdiff > 0: 
        TPdiff = boldIt(greenIt(TPdiff))
    elif tpdiff < 0:
        TPdiff = f"{-1*tpdiff}"
        TPdiff = boldIt(redIt(TPdiff))
    TPdiffExp = prtft + ' - ' + prtfy
    bline1 = ' ║ '+dday+' Market Value:'+f"{totalValy:,.2f}"+' Stock Value:'+f"{stockValy:,.2f}"+'     Prev Portf:'+prtfy+' Today Portf:'+prtft
    bline2 = TPdiffExp + ' = ' + TPdiff
    print(bline1 + (sum(tabw) + len(tabw) - len(bline1) - len(bline2) + 19)*' ' + bline2 + ' ║')

    headerTop(tabw)
    headerHdr(tabw, headers)
    headerBot(tabw)
    for row in rowst: dispRow(tabw, row)
    closeLine(tabw)
    dday = rowst[0].asof_time.strftime('%Y-%m-%d')
    lline1 = ' ║' + dday + ' Market Value:' + f"{totalValt:,.2f}" + ' Stock Value:' + f"{stockValt:,.2f}" + cdiff
    lline2 = 'data from ' + tablename + ' ║'
    print(lline1, (sum(tabw) + len(tabw) - len(bline1) - len(bline2) + 17)*' ', lline2)
    closeBottom(tabw)
