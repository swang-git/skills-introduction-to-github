#!/usr/bin/python3
from bs4 import BeautifulSoup
import sys
import os
import re
from urllib import request
sys.path.append(os.path.join(os.path.dirname(sys.path[0])))
from ArtItemQGZJ import Art
from DB import addDailyDatWW

sit = 'file:///home/swang/tmp/content_html.txt_ZJ_01'
if len(sys.argv) > 1: sit = 'file:///home/swang/tmp/content_html.txt_ZJ_' + sys.argv[1]

page = request.urlopen(sit)
art = Art(0, 'PXZJ', None, '20171030', TESTING=True)
art.htmltxt = page
# art.hexlnk = sit
art.qid = 'testing'
art.process_html_txt()
sys.exit(0)
