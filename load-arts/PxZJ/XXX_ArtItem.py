import re
from urllib import request
from bs4 import BeautifulSoup
import sys
import os
sys.path.append(os.path.join(os.path.dirname(sys.path[0])))
from Utils import padsp
from Utils import dlout
from Utils import Img

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
        # print(self.item.find('a', class_='treeReply').find('img').get('src'), self.dng, self.theday, self.tim)
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
        # print('spdelta', self.spdelta, self.spans)
        txm = self.spans[self.spdelta].get_text().replace('\t', '').replace('\n', '')
        dlout(4, 'txm', txm)
        tim = re.match(r'(.*)(\d\d)(.*)(\d\d)(.*)\s+(\d\d:\d\d)(.*)', txm)
        self.tim = self.year + '-' + tim.group(2) + '-' + tim.group(4) + ' ' + tim.group(6)
        # self.tim = re.sub('[(|)]', '', txm)
        # print('tim XXXX', txm)
    def set_nwd(self): self.nwd = self.spans[self.spdelta + 1].get_text().replace('字', '').strip()
    def set_clk_flw(self):
        dlout(4, 'clk, flw', self.spans[self.spdelta + 2].get_text())
        [self.clk, self.flw] = re.sub('[(|)]', '', self.spans[self.spdelta + 2].get_text()).split('/')

    def XXXXopen_and_get_flw(self):
        if int(self.flw) <= 0:
            print('no flw, return')
            return
        page = request.urlopen(self.lnk)
        soup = BeautifulSoup(page, 'html5lib')
        # soup = BeautifulSoup(page, 'html.parser')
        # dlout(3, soup.prettify())
        item = soup.find('li', class_='treeReplyItem')
        # print('AAA_ITEM', soup.find_all('ul', class_='subUL'), 'ZZZ_ITEM')
        # print('AAA_ITEM', soup.find('ul', {'class':'subUL','a':'1'}), 'ZZZ_ITEM')
        art = Art(0, item, self.theday)
        art.parse_and_set_attrs()
        art.get_flws()
        self.flws = art.flws

    def get_flws(self):
        uls = self.item.find('ul', class_='subUL')
        if uls == None: return
        self.flws = []
        lis = uls.find_all('li')
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
        if self.TESTING: url = 'file:///home/swang/tmp/content_html.txt_05'
        self.hexlnk = url
        self.process_html_txt()

    def show(self,tag): print(tag,padsp(self.idx,3),self.tim,self.qid,self.fid,padsp(self.nwd,5),padsp(self.clk,4),padsp(self.flw,3),padsp(self.lvl,2),self.tit[0:30],'--',self.aut)
    def show_long(self,tag): print(tag,padsp(self.idx,3),self.tim,self.qid,self.fid,padsp(self.nwd,5),padsp(self.clk,4),padsp(self.flw,3),padsp(self.lvl,2),self.tit,'--',self.aut)

    def process_html_txt(self):
        self.html_txt = ''
        try:
            htmltxt_page = request.urlopen(self.hexlnk)
            for line in htmltxt_page: self.html_txt += '<p>' + line.decode() + '</p>'
            # for line in htmltxt_page: self.html_txt += '<div>' + line.decode() + '</div>'
        except Exception as e:print('urlopen Exception, exception msg:', str(e), 'IN HEX PAGE', self.hexlnk)

        txt = ''
        imgobj = Img(self.tag, self.ymd, self.qid)
        soup = BeautifulSoup(self.html_txt, 'html5lib')
        # dlout(3, soup.prettify())

        for img in soup.find_all('img'):
            ptag = img.find_parent()
            new_tag = soup.new_tag("p")
            new_tag.string = imgobj.sav(img.get('src'))
            try:
                # ptag.insert(1, new_tag)
                # ptag.replace_with(new_tag)
                img.replace_with(new_tag)
                self.img += 1
                # img.clear()
            except Exception as e:
                print('Img.replace_with Exception, exception msg:', str(e), 'ptag:', ptag, 'new_tag:', new_tag, 'IN HEX PAGE',self.hexlnk)
                sys.exit(0)

        # print(soup.prettify())

        for br in soup.find_all('br'): br.replace_with('\n')

        # items = soup.find_all(['p'])
        text = soup.get_text()
        # dlout(3, 'text', text)
        lines = (line.strip() for line in text.splitlines())
        chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
        # chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
        # for line in chunks: print('AAA', line, 'BBB')
        txt += "\n\n".join(chunk for chunk in chunks if chunk)
        txt += "\n\n"
        self.txt = txt.strip()
        if self.TESTING:
            print('GET TXT', self.txt, 'GOT TXT')
            sys.exit(0)


        #
        # # items = soup.find_all(['p'])
        # for item in items:
        #     text = item.get_text().strip()
        #     if len(text) == 0: continue
        #     self.afz += len(text)
        #     # break into lines and remove leading and trailing space on each
        #     lines = (line.strip() for line in text.splitlines())
        #     chunks = (phrase.strip() for line in lines for phrase in line.split("   "))        # if len(text) == 0: continue
        #     # for chunk in chunks: if chunk and chunk not in txt: txt += "\n\n" + chunk
        #     txt += '\n\n'.join(chunk for chunk in chunks if chunk)
        #     txt += '\n\n'
        #
        # self.txt = txt.strip()
        # print('GET TXT', self.txt, 'GET TXT')
