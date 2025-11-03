#!/usr/bin/python3
from bs4 import BeautifulSoup
import sys
import os
import re
from urllib import request
sys.path.append(os.path.join(os.path.dirname(sys.path[0])))
from DB import addDailyDatQG
from ArtItem import Art

# print('os.path.dirname(sys.path[0])', os.path.dirname(sys.path[0]), sys.path[0])
# sys.exit(0)

# sit = 'file:///home/swang/tmp/content_html.txt_HY_01'
# if len(sys.argv) > 1: sit = 'file:///home/swang/tmp/content_html.txt_HY_' + sys.argv[1]

sit = 'http://washeng.net/HuaShan/BBS/shishi/gbcurrent/2299909.shtml'

print('testing', sit)

art = Art(idx=0, lnk=sit, tag='PXHY', ymd='20171102')
art.getArt()
art.show('ART')
sys.exit(0)


soup = BeautifulSoup(request.urlopen(sit), 'html5lib')
# for dv in soup.find_all('div'): dv.wrap(soup.new_tag('p'))
print(soup.prettify())
for br in soup.find_all('br'): br.replace_with('\n')
# print('ul AAA', soup.find('ul').get_text(), 'ZZZ')
# pvs = soup.find_all('p')
# print(pvs[1].prettify())
# sys.exit(0)

# page = request.urlopen(sit)
art = Art(0, 'PXHY', None, '2017-10-27')
# art.htmltxt = page
art.hexlnk = sit
art.qid = 'testing'
art.process_html_txt()
sys.exit(0)

sit = '/home/swang/tmp/qglt_20_1.html'
soup = BeautifulSoup(open(sit), "html.parser")
print(soup.prettify())
sys.exit(0)

item = soup.find('li', class_='treeReplyItem')
# print('len of item', len(item))
art = Art(0, item, '2017-10-20')
art.parse_and_set_attrs()
art.qid = 964912729
art.get_flws()
art.show_long('ART')
addDailyDatQG(art)
if art.flws != None:
    for flw in art.flws:
        flw.show_long('FLW')
