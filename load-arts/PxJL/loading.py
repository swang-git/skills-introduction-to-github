#!/usr/bin/env python3
import os
import sys
from urllib.request import Request, urlopen
import re
from os.path import dirname
# sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
sys.path.append(os.path.join(dirname(sys.path[0])))
# print(sys.path)
# sys.exit(0)
from Utils import Txt
from Utils import dlout
from Utils import conv_dt
from Utils import get_cn_tit
from Utils import get_cn_dat
from Utils import get_cn_zone_dates
from Utils import padsp, TeeToFileAndScreen, getLogFile
import DB
from Models import DailyDat
from Models import DailyArt
from Models import HomePage

from bs4 import BeautifulSoup
from PxJL.ArtItemJL import Art
import time
from UpdateHomePage import updHomePage

import requests_html
import lxml.html as lh

TESTING = False
# TESTING = True
MAX_PAGES = 3 


tag = 'PXJL'
CAT_INDEX = '7'   # for PXZJ change this to '60'
# print('Loading ' + tag + ' ......')
dyx = 0
start_page = 1
if len(sys.argv) >= 2 : dyx = int(sys.argv[1])
if len(sys.argv) >= 3 :
    dyx = int(sys.argv[1])
    start_page = int(sys.argv[2])
[ymd, theday, prvday] = get_cn_zone_dates(dyx)
print('proc_day:', theday, 'prev_day:', prvday, 'ymd', ymd, tag, dyx, '\n')
# logFile = '/Users/swang/tmp/logs/px/load' + tag + '_' + str(abs(dyx)) + '_' + wkdayname() + '.log' 
logFile = getLogFile(tag, dyx)
tee = TeeToFileAndScreen(logFile, 'w')
# sys.exit(0)

if TESTING: MAX_PAGES = 2
test_site = 'file:///Users/swang/tmp/qglt0920.html'
# soup = BeautifulSoup(open(sit), "html.parser")
# soup = BeautifulSoup(request.urlopen(sit), "html5lib")

artList = []
process_done = False
for page in range(start_page, MAX_PAGES):
    if process_done: break
    url = 'http://bbs1.people.com.cn/board/' + CAT_INDEX + '_' + str(page) + '.html'   # http://bbs1.people.com.cn/board/1/2_1.html
    if TESTING: url = test_site
    print('processing page', url)
    # req = Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    # page = urlopen(req).read()
    # soup = BeautifulSoup(page,  "html.parser")
    
    session = requests_html.HTMLSession()
    r = session.get(url)
    soup = BeautifulSoup(r.content, 'lxml')

    items = soup.find_all('li', {'class' : 'treeReplyItem'})
    idx = 0
    for item in items:
        dlout(5, 'processing item', idx)
        art = Art(CAT_INDEX, idx, tag, item, theday, TESTING)
        if art.is_item_for_theday():
            art.parse_and_set_attrs()
            art_flw_cnt = DB.get_art_flw_count(art)
            if  art.dng and int(art.flw) > 0 and art_flw_cnt  < int(art.flw) + 1:
                dlout(1, 'art_flw_cnt for ding art if', art.flw.rjust(3), '<', art_flw_cnt, 'needing get MORE for', art.tit)
                # dlout(3, 'art_flw_cnt for ding art if', '{0:3d}'.format(art.flw), '<', art_flw_cnt, 'needing get MORE for', art.tit)
                art.open_and_get_flw()
            elif art.dng and int(art.flw) > 0 and art_flw_cnt == int(art.flw) + 1:
                dlout(3, 'updating flw count for', art.tim, art.ymd, art.tag, art.qid, art.tit)
                DB.upd_art_flw_count(art)

            artList.append(art)
            # if not art.dng:
            art.idx += 1
            idx += 1
            continue
        elif time.strptime(theday, '%Y-%m-%d') > time.strptime(art.tim, '%Y-%m-%d %H:%M'):
            process_done = True
            break

for art in artList:
    # if art.dng and DB.needs_to_get_flws: art.open_and_get_flw()
    art.show('ART')
    if TESTING and art.flws is not None and len(art.flws) > 0:
        for flw in art.flws: flw.show('FLW')
    DB.addDailyDatQG(art)

    if TESTING: sys.exit(0)

updHomePage(artList)

print("finished loading for", theday, 'prev_day:', prvday, 'ymd', ymd, tag)

tee.close()
print('logFile:', logFile)
sys.exit(0)
