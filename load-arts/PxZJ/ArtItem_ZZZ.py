import re
from urllib import request
from bs4 import BeautifulSoup
import sys
import os
sys.path.append(os.path.join(os.path.dirname(sys.path[0])))
from Utils import padsp
from Utils import dlout
from Utils import Img
from Utils import get_parsed_text

class Art:
    def __getattr__(self, key): return None
    def __init__(self, idx, tag, item, theday, TESTING=False):
        self.TESTING = TESTING
        self.idx = idx
        self.lvl = 0
        self.tag = tag
        self.item = item
        self.theday = theday
        self.ymd = theday.replace('-', '')
        self.year = theday[0:4]
        self.img = 0
        self.vdo = 0
        self.afz = 0
        self.fsz = 0
        self.ffz = 0
        if item is not None:
            self.spans = self.item.find_all('span')
            self.spdelta = self.get_spans_delta()
            self.set_tim()
            self.set_dng()
    def is_item_for_theday(self):
        return self.dng or self.theday in self.tim
    def parse_and_set_attrs(self):
        self.set_lnk()
        self.set_tit()
        self.set_aut()
        self.set_qid()
        self.fid = self.qid
        self.set_nwd()
        self.set_clk_flw()
        self.get_flws()
    def get_spans_delta(self):
        delta = 1
        if len(self.spans) == 3: delta = 0
        return delta
    def set_dng(self):
        dngimg = self.item.find('a', class_='treeReply').find('img')
        if dngimg == None: self.dng = False
        elif 'ding.jpg'in dngimg.get('src'): self.dng = True
    def set_lnk(self): self.lnk = self.item.find('a', class_='treeReply').get('href').strip()
    def set_qid(self): self.qid = re.match('(.*)/(\d{5,}).html', self.lnk).group(2)
    def set_tit(self): self.tit = self.item.find('a', class_='treeReply').get_text().strip()
    def set_aut(self):
        aut = self.item.find('font')
        if aut == None: aut = self.item.find('a', class_='userNick')
        self.aut = aut.get_text().strip(':')
    def set_tim(self):
        txm = self.spans[self.spdelta].get_text().replace('\t', '').replace('\n', '')
        dlout(4, 'txm', txm)
        tim = re.match(r'(.*)(\d\d)(.*)(\d\d)(.*)\s+(\d\d:\d\d)(.*)', txm)
        self.tim = self.year + '-' + tim.group(2) + '-' + tim.group(4) + ' ' + tim.group(6)
    def set_nwd(self): self.nwd = self.spans[self.spdelta + 1].get_text().replace('字', '').strip()
    def set_clk_flw(self):
        dlout(4, 'clk, flw', self.spans[self.spdelta + 2].get_text())
        [self.clk, self.flw] = re.sub('[(|)]', '', self.spans[self.spdelta + 2].get_text()).split('/')
    def open_and_get_flw(self):
        if int(self.flw) <= 0:
            print('no flw, return')
            return
        page = request.urlopen(self.lnk)
        soup = BeautifulSoup(page, 'html5lib')
        item = soup.find('li', class_='treeReplyItem')
        art = Art(0, self.tag, item, self.theday)
        art.parse_and_set_attrs()
        art.get_flws()
        self.flws = art.flws
    def get_flws(self):
        uls = self.item.find('ul', class_='subUL')
        if uls == None: return
        self.flws = []
        lis = uls.find_all('li')
        # for li in enumerate(lis):
        for idx, li in enumerate(lis):
            flw = Art(idx, self.tag, li, self.theday)
            flw.idx += 1
            flw.parse_and_set_attrs()
            flw.qid = self.qid
            flw.lvl = len(li.find_parents('ul', class_="subUL"))
            lnk = li.find('a', class_='treeReply').get('href')
            flw.txt = flw.tit.strip()
            self.flws.append(flw)

    def get_hex(self):
        id_to_hex = int(self.qid)
        if self.qid != self.fid: id_to_hex = int(self.fid)
        x = hex(id_to_hex).upper()
        return '/'.join(['Y0', '0'+x[2:3],x[3:5],x[5:7],x[7:9]])
    def get_html_url(self): return 'http://bbs1.people.com.cn/posts/' + self.get_hex() + '/content_html.txt'
    def get_txt(self):
        url = self.get_html_url()
        # dlout(3, 'processing', url)
        # if self.TESTING: url = 'file:///home/swang/tmp/content_html.txt_05'
        try:
            self.htmltxt = request.urlopen(url)
            self.process_html_txt()
        except Exception as e:
            print('Exception', str(e), url, self.txt)

    def show(self,tag): print(tag,self.lnk, padsp(self.idx,3),self.tim,self.qid,self.fid,padsp(self.nwd,5),padsp(self.clk,4),padsp(self.flw,3),padsp(self.lvl,2),self.tit[0:30],'--',self.aut)
    def show_long(self,tag): print(tag,padsp(self.idx,3),self.tim,self.qid,self.fid,padsp(self.nwd,5),padsp(self.clk,4),padsp(self.flw,3),padsp(self.lvl,2),self.tit,'--',self.aut)

    def process_vdo(self):
        for vdo in self.soup.find_all('iframe'):
            new_tag = self.soup.new_tag('p')
            new_tag.string = '<iframe width="315" src="' + vdo.get('src') + '"></iframe>'
            vdo.replace(new_tag)
            self.vdo += 1

    def process_img(self):
        imgobj = Img(self.tag, self.ymd, self.qid)
        for img in self.soup.find_all('img'):
            ptag = img.find_parent()
            new_tag = self.soup.new_tag("p")
            new_tag.string = "\n" + imgobj.sav(img.get('src'))

            try:
                ptag.replace_with(new_tag)
                self.img += 1
            except Exception as e:
                print('Got Exception', str(e), 're-try img.replace_wtih')
                img.replace_with(new_tag)
                self.img += 1

    def process_html_txt(self):
        self.html_txt = ''
        for line in self.htmltxt:
            # print('LINE', line.decode())
            self.html_txt += '<p>' + line.decode() + '</p>'
        txt = ''
        self.soup = BeautifulSoup(self.html_txt, 'html5lib')

        dlout(5, self.soup.prettify())

        self.process_img()
        self.process_vdo()

        for br in self.soup.find_all('br'): br.replace_with('\n')

        items = self.soup.find_all(['p'])
        for item in items:
            # print('\n\nITEM AAA', item, 'ITEM ZZZ\n\n')
            text = item.get_text().strip()
            if len(text) == 0: continue
            txt += "\n" + text + "\n"

        self.txt = txt.strip()
        self.afz = len(self.txt)

        # print('GET TXT', self.txt, self.afz, 'GOT TXT')
        # text = self.soup.get_text().strip()
        # print(len(text))
        # sys.exit(0)


        ## checking and re_parse text
        text = self.soup.get_text().strip()
        text = text.replace('  ', '')
        if self.afz < len(text):
            print('\n NEED to reparse and get more text\n')
            self.txt = get_parsed_text(text)

        if self.TESTING:
            print('GET TXT', self.txt, 'GOT TXT')
            sys.exit(0)
