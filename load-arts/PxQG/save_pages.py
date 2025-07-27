#!/usr/bin/python3
import os
import sys
import re
from os.path import dirname
sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
sys.path.append(os.path.join(dirname(sys.path[0])))
from Utils import Txt
from Utils import get_now
from Utils import dlout
from Utils import conv_dt
from Utils import get_cn_tit
from Utils import get_cn_dat
from Utils import get_cn_zone_dates
from Utils import padsp
# from Utils import urlopener
from bs4 import BeautifulSoup
from ArtItemQG import Art
import time
from UpdateHomePage import updHomePage
import urllib.request

tag = 'PXQG'
CAT_INDEX = '2'   # for PXZJ change this to '60'

MAX_PAGES = 6
artList = []
process_done = False
for page in range(1, MAX_PAGES):
    if process_done: break
    url = 'http://bbs1.people.com.cn/board/1/' + CAT_INDEX + '_' + str(page) + '.html'   # http://bbs1.people.com.cn/board/1/2_1.html
    print('processing page', url)
    request = urllib.request.Request(url, headers = {"User-Agent": "Mozilla/5.0"})
    try:
        response = urllib.request.urlopen(request)
    except:
        print("can not do urllib.request.urlopen() -- something wrong")
        sys.exit(0)
    page_file = '/mntx/BAK/' + tag + '_' + str(page) + '.html'
    print(page_file)
    # print(type(response))
    htmlBytes = response.read()
    # print(type(htmlBytes))
    htmlStr = htmlBytes.decode("utf8")
    # print(type(htmlStr))
    pfile = open(page_file, 'w')
    pfile.write(htmlStr)
    pfile.close()
for page in range(1, MAX_PAGES):
  page_file = '/mntx/BAK/' + tag + '_' + str(page) + '.html'
sys.exit(0)
