#!/usr/bin/python3
import os
import sys
import requests
from urllib import request
import re
# sys.path.append(os.path.join(os.path.dirname(sys.path[0])))
from os.path import dirname
sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
sys.path.append(os.path.join(dirname(sys.path[0])))
from Utils import Txt
from Utils import dlout
from Utils import conv_dt
from Utils import get_cn_tit
from Utils import get_cn_dat
from Utils import get_cn_zone_dates
from Utils import padsp
import DB
from Models import DailyDat
from Models import DailyArt
from Models import HomePage

from bs4 import BeautifulSoup
from urllib import request
from ArtItemQGZJ import Art
import time
from UpdateHomePage import updHomePage


TESTING = False
# TESTING = True

START_PAGE = 6
MAX_PAGES = 90

tag = 'PXZJ'
CAT_INDEX = '60'   # for PXZJ change this to '60'
print('Loading ' + tag + ' ......')
dyx = 0
if len(sys.argv) >= 2 : dyx = int(sys.argv[1])
[ymd, theday, prvday] = get_cn_zone_dates(dyx)
print('proc_day:', theday, 'prev_day:', prvday, 'ymd', ymd, tag, '\n')

if TESTING: MAX_PAGES = 2
test_site = 'file:///home/swang/tmp/qglt26.html'
# soup = BeautifulSoup(open(sit), "html.parser")
# soup = BeautifulSoup(request.urlopen(sit), "html5lib")

artList = []
process_done = False
for page in range(START_PAGE, MAX_PAGES):
    if process_done: break
    url = 'http://bbs1.people.com.cn/board/' + CAT_INDEX + '_' + str(page) + '.html'   # http://bbs1.people.com.cn/board/1/2_1.html
    if TESTING: url = test_site
    print('processing page', url)
    page = None;
    try:
        page = request.urlopen(url)
    except Exception as e:
        print(e)
        print("urlopen dailed, re-run for [" + url + "]")
        page = request.urlopen(url)

    soup = BeautifulSoup(page,  "html5lib")
    # soup = BeautifulSoup(page,  "html.parser")
    # print(soup.prettify())

    items = soup.find_all('li', {'class' : 'treeReplyItem'})
    idx = 0
    for item in items:
        print('processing item', idx)
        art = Art(idx, tag, item, theday, TESTING)
        if art.is_item_for_theday():
            art.parse_and_set_attrs()
            if art.dng and int(art.flw) > 0: art.open_and_get_flw()
            artList.append(art)
            if not art.dng:
                art.idx += 1
                idx += 1
            continue
        elif time.strptime(theday, '%Y-%m-%d') > time.strptime(art.tim, '%Y-%m-%d %H:%M'):
            process_done = True
            break

        # if TESTTING: break

for art in artList:
    if art.dng and DB.needs_to_get_flws: art.open_and_get_flw()
    art.show('ART')
    DB.addDailyDatQG(art)
    print()
    if TESTING: sys.exit(0)

updHomePage(artList)

print("finished loading for", theday, 'prev_day:', prvday, 'ymd', ymd, tag)

sys.exit(0)

#soup.prettify()
# for item in soup.find_all('li', {'class' : 'treeReplyItem'}):
#     print('\n ART AAA\n', art, 'ART ZZZ\n\n')

# def show_art(art):
#     print('ART', art.idx, art.dng, art.qid, art.fid, art.tit, art.aut, art.tim, art.nwd, art.clk, art.flw)
#     if art.flws != None:
#     # if hasattr(art, 'flws') and art.flws != None:
#         for flw in art.flws: print('FLW', flw.idx, flw.lvl, flw.qid, flw.fid, flw.aut, flw.txt, flw.tim, flw.nwd, flw.has_more)
#

# parser = PageParser(sit, tag, ymd, prvday)
# parser.getArt()
# for art in parser.artList: show_art(art)
# sys.exit(0)
