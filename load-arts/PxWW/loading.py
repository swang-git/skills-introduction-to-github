#!/Users/swang/myenv/bin/python
import sys, os
from os.path import dirname
sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
sys.path.append(os.path.join(dirname(sys.path[0])))
from Utils import Txt
from Utils import get_dates
from Utils import my_argparse, TeeToFileAndScreen, getLogFile
from UpdateHomePage import updHomePage
from PxWW.LoadingSites import getHeadlineNews, getBreakingNews

# sit1 = "http://news.creaders.net/breaking/index.html"
# sit2 = "http://news.creaders.net/headline/index.html"
sit1 = "http://news.creaders.net/breaking"
sit2 = "http://news.creaders.net/headline"
tag = 'PXWW'
args = my_argparse()
dyx = args.dyx
MAX_PAGES = args.max
# logFile = '/home/swang/tmp/logs/px/load' + tag + '_' + str(abs(dyx)) + '_' + wkdayname() + '.log' 
logFile = getLogFile(tag, dyx)
print('the log file is', logFile)
[ymd, theday, prvday] = get_dates(dyx)
tee = TeeToFileAndScreen(logFile, 'w')
print('\n >>> Loading %s for %s with ymd: %s and at most %s top web pages to be processed %s' %(tag, theday, ymd, MAX_PAGES, '\n'))
daList = []
idx = 0
rets = getBreakingNews(sit1, MAX_PAGES, idx, daList, tag, ymd, theday, prvday)
idx = rets[0]
daList = rets[1]
print('returned form getBreakingNews:[%s]'%(rets[0]))
rets = getHeadlineNews(sit2, MAX_PAGES, idx, daList, tag, ymd, theday, prvday)
# print('returned form getHeadlineNews:[%s][%s]'%(rets[0], rets[1]))
updHomePage(daList)
print('Processed pages: \n%s\n%s'%(sit1, sit2))
tee.close()
print('the log file is', logFile)
sys.exit(0)
