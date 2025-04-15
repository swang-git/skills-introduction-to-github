#!/Users/swang/myenv/bin/python
import os, sys, time
from urllib import request
from urllib.request import Request, urlopen
from os.path import dirname
sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
sys.path.append(os.path.join(dirname(sys.path[0])))
from Utils import get_cn_zone_dates
from Utils import get_now
from Utils import dlout
from Utils import TeeToFileAndScreen, getLogFile
import DB

from bs4 import BeautifulSoup
from PxQG.ArtItemQG import Art
from UpdateHomePage import updHomePage


TESTING = False
# TESTING = True
MAX_PAGES = 60
tag = 'PXQG'
INDEX_1 = '1'   # for PXZJ change this to '60'
INDEX_2 = '2'   # for PXZJ change this to '60'
# print('Loading ' + tag + ' ......')
dyx = 0
if len(sys.argv) >= 2 : dyx = int(sys.argv[1])
[ymd, theday, prvday] = get_cn_zone_dates(dyx)
print('proc_day:', theday, 'prev_day:', prvday, 'ymd', ymd, tag, dyx, '\n')
# sys.exit(0)
# logFile = '/home/swang/tmp/logs/px/load' + tag + '_' + str(abs(dyx)) + '_' + wkdayname()+ '.log'
logFile = getLogFile(tag, dyx)
tee = TeeToFileAndScreen(logFile, 'w')

if TESTING: MAX_PAGES = 2
test_site = 'file:///home/swang/Downloads/lllqg1.html'
# soup = BeautifulSoup(open(sit), "html.parser")
# soup = BeautifulSoup(request.urlopen(sit), "html5lib")
# soup = BeautifulSoup(request.urlopen(sit), "html.parser")

artList = []
process_done = False
for page in range(1, MAX_PAGES):
    if process_done: break
    # http://bbs1.people.com.cn/board/1/1_1.html
    # http://bbs1.people.com.cn/board/1/2_1.html
    url1 = 'http://bbs1.people.com.cn/board/1/' + INDEX_1 + '_' + str(page) + '.html'
    url2 = 'http://bbs1.people.com.cn/board/1/' + INDEX_2 + '_' + str(page) + '.html'
    if TESTING: url = test_site
    print('processing page', url1)
    req = Request(url1, headers={'User-Agent': 'Mozilla/5.0'})
    page = urlopen(req).read()
    soup = BeautifulSoup(page,  "html.parser")
    # print(soup.prettify())
    items1 = soup.find_all('li', {'class' : 'treeReplyItem'})

    print('processing page', url2)
    req = Request(url2, headers={'User-Agent': 'Mozilla/5.0'})
    page = urlopen(req).read()
    soup = BeautifulSoup(page,  "html.parser")
    # print(soup.prettify())
    items2 = soup.find_all('li', {'class' : 'treeReplyItem'})

    items = items1 + items2

    idx = 0
    for item in items:
        dlout(5, 'processing ART:', idx)
        art = Art(INDEX_2, idx, tag, item, theday, TESTING)
        # if art.dng: continue
        if art.is_item_for_theday():
            art.parse_and_set_attrs()
            art_flw_cnt = DB.get_art_flw_count(art)
            if  art.dng and int(art.flw) > 0 and art_flw_cnt  < int(art.flw) + 1:
                dlout(1, 'art_flw_cnt for ding art if', art.flw.rjust(3), '>', art_flw_cnt, 'needing get MORE for', art.tit)
                # dlout(3, 'art_flw_cnt for ding art if', '{0:3d}'.format(art.flw), '<', art_flw_cnt, 'needing get MORE for', art.tit)
                art.open_and_get_flw()
            elif art.dng and int(art.flw) > 0 and art_flw_cnt == int(art.flw) + 1:
                dlout(3, 'updating flw count for', art.tim, art.ymd, art.tag, art.qid, art.tit)
                DB.upd_art_flw_count(art)

            artList.append(art)
            art.idx += 1
            idx += 1
            continue
        elif time.strptime(theday, '%Y-%m-%d') > time.strptime(art.tim, '%Y-%m-%d %H:%M'):
            process_done = True
            break
i=0
for art in artList:
    i += 1
    print('\n>>>>>>>', 'Processing for', theday, str(i)+'/'+str(len(artList)), 'running at', get_now(), '>>>>>>>')
    art.show('ART')
    if TESTING and art.flws is not None and len(art.flws) > 0:
        for flw in art.flws: flw.show('FLW')
    DB.addDailyDatQG(art)

    if TESTING: sys.exit(0)

updHomePage(artList)

print("finished loading for", theday, 'prev_day:', prvday, 'ymd', ymd, tag)

tee.close()
print('logFile is', logFile)

sys.exit(0)
