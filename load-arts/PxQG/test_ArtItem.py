#!/usr/bin/python3
from bs4 import BeautifulSoup
import sys
import os
import re
from urllib import request
sys.path.append(os.path.join(os.path.dirname(sys.path[0])))
from ArtItemQGZJ import Art
from DB import addDailyDatQG
from Utils import dlout

# print('os.path.dirname(sys.path[0])', os.path.dirname(sys.path[0]), sys.path[0])
# sys.exit(0)

sit = 'file:///home/swang/tmp/content_html.txt_QG_01'
sit = 'file:///home/swang/tmp/content_html.txt_QG_18'

if len(sys.argv) > 1: sit = 'file:///home/swang/tmp/content_html.txt_QG_' + sys.argv[1]

page = request.urlopen(sit)
# soup = BeautifulSoup(page, 'html5lib')
# for img in soup.find_all('img'):
#     ptag = img.find_parent()
#     new_tag = soup.new_tag("p")
#     new_tag.string = '<img src="' + img.get('src') + '">'
#     ptag.replace_with(new_tag)
# print('PRETTIFY', soup.prettify(), 'ZZZ PRETTIFY')
# sys.exit(0)

art = Art(idx=0, tag='PXQG', item=None, theday='2017-10-30', TESTING=True)
art.htmltxt = page
art.qid = 'testing'
art.img = 0
art.afz = 0
art.txt = ''
art.process_html_txt()
dlout(3, 'img', art.img)
sys.exit(0)

sit = '/home/swang/tmp/qglt_20_1.html'
soup = BeautifulSoup(open(sit), "html.parser")
print(soup.prettify())
sys.exit(0)

item = soup.find('li', class_='treeReplyItem')
# print('len of item', len(item))
art = Art(0, 'PXQG', item, '2017-10-20')
art.parse_and_set_attrs()
art.qid = 964912729
art.get_flws()
art.show_long('ART')
addDailyDatQG(art)
if art.flws != None:
    for flw in art.flws:
        flw.show_long('FLW')
