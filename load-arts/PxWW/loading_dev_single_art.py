#!/usr/bin/python3
import sys
import os
import time
import json
from datetime import date
import requests
from lxml import html
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
from Utils import get_mmddyyyy
from Utils import get_dates
import DB
from Models import DailyDat
from Models import DailyArt
from Models import HomePage
from ArtItem_dev import Art
from UpdateHomePage import updHomePage

sit1 = "http://news.creaders.net/breaking/index.html"
sit2 = "http://news.creaders.net/headline/index.html"
tag = 'PXWW'
ymd = "2019-11-09"
idx = 1
lnk = "http://news.creaders.net/world/2018/07/16/1973865.html"
lnk = "http://ent.creaders.net/2018/07/16/1973859.html"
lnk = "http://news.creaders.net/us/2019/11/09/2155032.html"
tit = "testing single art-item"
dat = ymd
art = Art(idx, lnk, tag, ymd, tit, dat)
art.getArt()
art.show('ART')
print('loading single art item for testing -- DONE')
sys.exit(0)




if len(sys.argv) >= 2 : dyx = int(sys.argv[1])
[ymd, theday, prvday] = get_dates(dyx)
print('Processing', tag, 'the day:', theday, 'previous day:', prvday, 'ymd:', ymd, "\n")

def get_json_objects(sit):
    page = request.urlopen(sit)
    linex = None
    for line in page:
        if b'newsList_json' in line:
            linex = line.decode('gb2312', 'ignore')
            break

    aobjs = linex.split('},{')
    aobjs.pop()
    fst = aobjs[0].split('[{')[1].strip()
    aobjs[0] = fst
    return aobjs

aobjs1 = get_json_objects(sit1)
aobjs2 = get_json_objects(sit2)
aojs = aobjs1 + aobjs2

class JsonObject(object):
    def __init__(self, json_content):
        data = json.loads(json_content)
        for key, value in data.items():
            self.__dict__[key] = value

datList = []
idx = 0
for json_string in aojs:
    dlout(4, 'JSON_SRTING', json_string)
    dat = JsonObject('{'+ json_string +'}')
    if theday in dat.rdate or dat.rdate == "":
        if len(dat.link) > 0:
            # print("Processing", dat.rdate, dat.link, ' -- ', dat.title)
            art = Art(idx, dat.link, tag, ymd, dat.title, dat.rdate)
            # art.getArt()
            # art.show('ART')
            # print('try to add WW art', art.lnk, art.tit)
            if theday in art.tim: DB.addDailyDatWW(art)  ## check date again
            idx += 1
            datList.append(art)
            # sys.exit(0)
    if prvday in dat.rdate: break

cnt = DB.getCount(tag, ymd)
print('COUNT for', tag, ymd, cnt)
updHomePage(datList)
sys.exit(0)
