#!/usr/bin/python3
import sys
import os
import time
from datetime import date
import requests
# from lxml import html
from urllib import request
import re
# from os.path import dirname
# sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
# sys.path.append(os.path.join(dirname(sys.path[0])))
sys.path.insert(0, '/sites/projects/load-arts')
# print('path', dirname(dirname(sys.path[0])))
# sys.exit(0)

from bs4 import BeautifulSoup

from Utils import Txt
from Utils import dlout
from Utils import conv_dt
from Utils import get_cn_tit
from Utils import get_cn_dat
from Utils import get_yyyymmdd, my_argparse, TeeToFileAndScreen, getLogFile
import DB
from Models import DailyDat
from Models import DailyArt
from Models import HomePage
from TEST_WX_ArtItem import Art
from UpdateHomePage import updHomePage

import ssl

try:
    _create_unverified_https_context = ssl._create_unverified_context
except AttributeError:
    # Legacy Python that doesn't verify HTTPS certificates by default
    pass
else:
    # Handle target environment that doesn't support HTTPS verification
    ssl._create_default_https_context = _create_unverified_https_context

pre_sit = "http://www.wenxuecity.com/news/morenews/"
bas_url = "http://www.wenxuecity.com"
tag = 'PXWX'
# dyx = 0
# MAX_PAGES = 2
# if len(sys.argv) >= 2 : dyx = int(sys.argv[1])
# [ymd, theday, prvday] = get_yyyymmdd(dyx)
# print('\nProcessing', tag, 'the day:', theday, 'previous day:', prvday, 'ymd:', ymd,)

args = my_argparse()
dyx = args.dyx
MAX_PAGES = args.max
# logFile = '/home/swang/tmp/logs/px/load' + tag + '_' + str(abs(dyx)) + '_' + wkdayname() + '.log' 
logFile = getLogFile(tag, dyx)
print('the log file is', logFile)
[ymd, theday, prvday] = get_yyyymmdd(dyx)
tee = TeeToFileAndScreen(logFile, 'w')
print('\n >>> Loading %s for %s with ymd: %s and at most %s top web pages to be processed %s' %(tag, theday, ymd, MAX_PAGES, '\n'))

##### unit testing
# art = Art(0, 'file:///home/swang/7576934.html', tag, ymd, 'test-title', theday)
# art.bas_url = bas_url
# art.getArt()
# # DB.addDailyDatWX(art)
# art.show('TXT')
# # art.show_flws('FLW')
# sys.exit(0)
def get_artList(pre_sit, MAX_PAGES):
    artList = []
    for pageIdx in range(1, MAX_PAGES):
        url = pre_sit + '?page=' + str(pageIdx)
        print('processing page: ' + url)
        page = request.urlopen(url)
        # soup = BeautifulSoup(page,  "html5lib")
        soup = BeautifulSoup(page,  "html.parser")
        # print(soup.prettify())

        uls = soup.findAll('div', class_='list')[0].findAll('ul')
        print(uls[0])
        idx = 0
        for ul in uls:
            items = ul.findAll('li')
            # print(items)
            # sys.exit(0)
            for item in items:
                tim = item.findAll('span')[0].getText()
                # print('---- idx: %s tim: %s'%(idx, tim))
                if tim == '0000-00-00': tim = '2020-03-19'
                # print('----', tim, theday, item);print(time.strptime(tim, '%Y-%m-%d'), '<'); print(time.strptime(theday, '%Y/%m/%d'))
                if time.strptime(tim, '%Y-%m-%d') > time.strptime(theday, '%Y/%m/%d'): continue
                if time.strptime(tim, '%Y-%m-%d') < time.strptime(theday, '%Y/%m/%d'):
                    print('BREAK here for hitting prev day:', tim, theday)
                    return [artList, url]
                ank = item.findAll('a', href=True)[0]
                lnk = bas_url + ank['href']
                # tit = item.get_text()
                tit = ank.get_text()
                art = Art(idx, lnk, tag, ymd, tit, tim)
                art.bas_url = bas_url
                art.status = 'A'
                idx += 1
                artList.append(art)
                # print(tim, lnk, tit)

[artList, sit] = get_artList(pre_sit, MAX_PAGES)
if len(artList) <= 0:
    print('\nthere are no articles for ', theday, 'exiting...\n')
    exit(0)
else: print('Total Articles:', len(artList) + 1)

for art in artList:
    art.getArt()
    # art.show('ART')
    if art.flws != None: art.show_flws('FLW')
    #__ DB.addDailyDatWX(art)

#__ updHomePage(artList)
print('Total Articles:', len(artList) + 1)
print('\nDone for Loading %s for %s with ymd: %s and at most %s top web pages to be processed' %(tag, theday, ymd, MAX_PAGES))
print('Stopped at page:', sit)
tee.close()
# del tee # should do nothing
print('the log file is', logFile)
sys.exit(0)
