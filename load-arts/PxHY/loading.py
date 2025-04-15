#!/usr/bin/python3
from Utils import my_argparse, TeeToFileAndScreen
from UpdateHomePage import updHomePage
from ArtItem import Art
from Models import HomePage
from Models import DailyArt
from Models import DailyDat
import DB
from Utils import get_mmddyyyy, getLogFile
from Utils import get_cn_dat
from Utils import get_cn_tit
from Utils import conv_dt
from Utils import dlout
from Utils import Txt
import sys
import os
import time
from datetime import date
import requests
# from lxml import html
from urllib import request
import re
from os.path import dirname
sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
sys.path.append(os.path.join(dirname(sys.path[0])))
# print('path', dirname(dirname(sys.path[0])))
# sys.exit(0)


# pre_sit = "http://washeng.net/HuaShan/BBS/shishi/gbcurrent"
# base_url = "http://washeng.net/HuaShan/BBS/shishi/"
pre_sit = "http://huayue.fatcow.com/HuaShan/BBS/shishi/gbcurrent"
base_url = "http://huayue.fatcow.com/HuaShan/BBS/shishi/"

tag = 'PXHY'
args = my_argparse()
dyx = args.dyx
MAX_PAGES = args.max
# logFile = '/home/swang/tmp/logs/px/load' + tag + '_' + str(abs(dyx)) + '_' + wkdayname() + '.log'
logFile = getLogFile(tag, dyx)
# print('the log file is', logFile)
[ymd, theday, prvday] = get_mmddyyyy(dyx)
tee = TeeToFileAndScreen(logFile, 'w')
print('\n >>> Loading %s for %s with ymd: %s and at most %s top web pages to be processed %s' %
      (tag, theday, ymd, MAX_PAGES, '\n'))
# sys.exit(0)

for page in range(1, MAX_PAGES):
    sit = pre_sit + '.html'
    if page > 1:
        sit = pre_sit + str(page) + '.html'
    print('processing page: ' + sit)
    datList = []
    lines = request.urlopen(sit)
    idx = 0
    for line in lines:
        line = line.decode('gb18030', 'ignore')
        # print(line)
        if re.match('<!--num', line):
            if prvday in line:
                break
            if theday in line:
                # dlout(3, 'AAA', idx, line, 'ZZZ', idx)
                idx += 1
                lnk = base_url + \
                    html.fromstring(line).xpath('//li/a/@href')[0].strip('^./')
                if '/1.shtml' not in lnk:
                    datList.append(Art(idx, lnk, tag, ymd))
                # line = Txt(line).rmBadChars()
                # line = line.decode('gb18030', 'ignore')
                # line = bytes(line, 'gbk').decode('gb18030')
                # line = bytes(Txt(line).rmBadChars(), 'gb18030').decode('gb18030')
                # datList.append(ArtList(line, idx).getDailyDat())
    # else: continue
    # break
    for art in datList:
        art.getArt()
        # print('AA=============', art.tit)
        art.tit = art.tit.strip('�*')
        # print('AA=============', art.tit)
        art.show('ART')
        DB.addDailyDatHY(art)

    # cnt = DB.getCount(tag, ymd)
    # print('COUNT for', tag, ymd, cnt)
    updHomePage(datList)
    break
print('\nDone for Loading %s for %s with ymd: %s and at most %s top web pages to be processed' %
      (tag, theday, ymd, MAX_PAGES))
print('Stopped at page:', sit)
tee.close()
# del tee # should do nothing
print('the log file is', logFile)
sys.exit(0)
