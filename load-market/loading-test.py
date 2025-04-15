#!/usr/bin/python3
import sys
import os
import time
from datetime import date
import requests
from lxml import html
from urllib import request
import re
from os.path import dirname
sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
sys.path.append(os.path.join(dirname(sys.path[0])))
# print('path', dirname(dirname(sys.path[0])))
# sys.exit(0)

from bs4 import BeautifulSoup

from Utils import Txt
from Utils import dlout
from Utils import conv_dt
from Utils import get_cn_tit
from Utils import get_cn_dat
from Utils import get_yyyymmdd
import DB
from Models import DailyDat
from Models import DailyArt
from Models import HomePage
from ArtItemTest import Art
from UpdateHomePage import updHomePage

pre_sit = "http://www.wenxuecity.com/news/morenews/"
bas_url = "http://www.wenxuecity.com"
tag = 'PXWX'
dyx = 0
MAX_PAGES = 2
if len(sys.argv) >= 2 : dyx = int(sys.argv[1])
[ymd, theday, prvday] = get_yyyymmdd(dyx)
print('Processing', tag, 'the day:', theday, 'previous day:', prvday, 'ymd:', ymd, "\n")

##### unit testing
# art = Art(0, 'file:///home/swang/Documents/7857215.html', tag, ymd, 'test-title', theday)
# art = Art(0, 'file:///home/swang/Documents/PXWXtest1234.html', tag, ymd, 'test-title', theday)
# art = Art(0, 'file:///home/swang/Documents/PXWXtest1235.html', tag, ymd, 'test-title', theday)
art = Art(0, 'file:///home/swang/Documents/PXWXtest1236.html', tag, ymd, 'test-title', theday)
art.bas_url = bas_url
art.getArt()
# DB.addDailyDatWX(art)
art.show('TXT')
art.show_flws('FLW')
sys.exit(0)

for pageIdx in range(1, MAX_PAGES):
    url = pre_sit + '?page=' + str(pageIdx)
    print('processing page: ' + url)
    page = request.urlopen(url)
    soup = BeautifulSoup(page,  "html5lib")
    # soup = BeautifulSoup(page,  "html.parser")
    # print(soup.prettify())

    uls = soup.findAll('div', class_='list')[0].findAll('ul')
    # print(uls[1])
    artList = []
    idx = 0
    for ul in uls:
        items = ul.findAll('li')
        for item in items:
            tim = item.findAll('span')[0].getText()
            if time.strptime(tim, '%Y-%m-%d') < time.strptime(theday, '%Y/%m/%d'): break
            elif time.strptime(tim, '%Y-%m-%d') != time.strptime(theday, '%Y/%m/%d'): break
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

for art in artList:
    art.getArt()
    # art.show('ART')
    art.show_flws('FLW')
    DB.addDailyDatWX(art)

updHomePage(artList)
sys.exit(0)
