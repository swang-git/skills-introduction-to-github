from bs4 import BeautifulSoup
from datetime import datetime
import re
import sys
from urllib import request
from Utils import conv_dt
from Utils import dlout
from Utils import Img
from Utils import padsp
from Utils import pedsp
from Utils import get_http_line
from Utils import get_x_http_line
from Utils import get_x_http_line_x
from Utils import repl_puncts
import time

class Art:
    "this class for creating the object of article from web site washing.net"
    def __getattr__(self, key): return None
    def __init__(self, idx, lnk, tag, ymd):
        self.idx = idx
        self.lnk = lnk
        self.tag = tag
        self.ymd = ymd
        self.yer = ymd[0:4]
        self.afz = 0
        self.fsz = 0
        self.ffz = 0
        self.lvl = 0
        self.vdo = 0
        self.img = 0
        self.nwd = 0
        self.tit = ''
        self.txt = ''
        self.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    def show(self,tag):
        print(tag,padsp(self.idx,3),self.tim,self.qid,self.fid,padsp(self.nwd,5),padsp(self.flw,3),padsp(self.lvl,2),self.tit[0:30],'--',self.aut, self.addtm)
        print(self.txt, '\n\n')
        self.show_flws()

    def process_vdo(self):
        for vdo in self.soup.find_all('iframe'):
            vdo_src = vdo.get('src')
            if vdo_src == None: continue
            new_tag = self.soup.new_tag("p")
            new_tag.string = '<iframe src="' + vdo_src + '" width="100%" height="85%"></iframe>'
            try:
                # ptag.insert(1, new_tag)
                vdo.replace_with(new_tag)
                self.vdo += 1
            except Exception as e:
                print('vdo.replace_with Exception, exception msg:', str(e), 'NEW_TAG', new_tag)
                sys.exit(0)

    def process_img(self):
        imgobj = Img(self.tag, self.ymd, self.qid)
        for img in self.soup.find_all('img'):
            new_tag = self.soup.new_tag("p")
            imgsrc = img.get('src')
            # print('OLD_IMG_SRC', imgsrc, img)
            img_string = imgobj.sav(imgsrc, self.img+1)
            if img_string is None: continue
            new_tag.string = img_string
            try:
                # ptag.insert(1, new_tag)
                img.replace_with(new_tag)
                # print('NEW_IMG_SRC', imgsrc, new_tag.string)
                self.img += 1
            except Exception as e:
                print('Img.replace_with Exception, exception msg:', str(e), 'P_TAG:', ptag, 'NEW_TAG', new_tag)
                # sys.exit(0)
        # print(self.soup.prettify())

    def getArt(self):
        # self.soup = BeautifulSoup(request.urlopen(self.lnk), 'html5lib')
        self.soup = BeautifulSoup(request.urlopen(self.lnk), 'html.parser')
        # self.soup = BeautifulSoup(request.urlopen(self.lnk), 'xml')

        # self.tit = self.soup.find('title').get_text().strip()
        self.tit = re.sub('/n.*$', '', self.soup.find('title').get_text()).strip()
        self.tit = re.sub('\s+\+\s+w/i.*$', '', self.tit)
        self.qid = int(re.match('(.*)/(\d+).shtml', self.lnk).group(2))
        dlout(5, 'tit', self.tit)

        self.process_img()
        self.process_vdo()

        # for br in self.soup.find_all('br'): br.replace_with('\n')
        items = self.soup.find('ul')

        # for item in items: print('AAA', item, 'XXX')
        # sys.exit(0)

        self.artLines = []
        for i, line in enumerate(items):
            # if line.find('img') is not None: print('AAA', line, 'ZZZ')
            self.artLines.append(line)
            if i == 2: break

        dlout(5, self.artLines[0])
        dlout(5, self.artLines[1].get_text().strip())
        dlout(5, self.artLines[2])
        self.aut = re.sub('作者: \n', '',  self.artLines[0]).strip()
        dlout(5, 'aut', self.aut)
        # self.qid = re.sub(':\d+', '', self.artLines[1].get_text().strip('[|]'))
        self.fid = self.qid
        dlout(5, 'qid', self.qid)
        self.tim = conv_dt(self.artLines[2].strip(',|:'))
        dlout(5, 'tim', self.tim)

        # self.process_img()
        # self.process_vdo()


        start_mark = 'body_top|- http://washeng.net/'
        # start_mark1 = '- http://washeng.net/'
        # start_mark2 = 'body_top'
        start_txt = False
        txt = ''
        for line in self.soup(text=re.compile(r'\w+')):
            line = line.strip().replace('...华岳论坛 - "http://washeng.net"', '').replace('...华岳论坛 - "http://hua-yue.net"', '')
            # line = line.replace('body_top', '')
            if len(line) == 0:
                continue
            elif re.match(start_mark, line):
            # elif '- http://washeng.net/' in line or 'body_top' in line:
            # elif '- http://washeng.net/' in line:
            # elif 'body_top' in line:
                start_txt = True
                continue
            elif not start_txt:
                continue
            elif 'body_end' in line:
                break
            elif '<img ' in line:
                txt += line
            else:
                line = get_http_line(line)
                # line = get_x_http_line(line)
                # line = get_x_http_line_x(line)
                # print('LLLL', line, 'EEEE')
                line = repl_puncts(line)
                txt += line + "\n\n"

        self.txt = txt.strip()
        self.afz = len(self.txt)
        self.nwd = self.afz

        ## processing followups
        self.get_flws()
        self.flw = 0
        if self.flws is not None: self.flw = len(self.flws)
        self.set_flw_lvl()
        self.get_flw_txt()
        # self.show_flws()
        if self.flws != None: self.removeFlwDups()
    
    def removeFlwDups(self):
        # for i, flw in enumerate(self.flws):
        i = 0
        for flw in self.flws:
            if flw.aut.strip() != '不平则引12': continue
            flw.tit = flw.tit.strip()
            flw.txt = flw.txt.strip()
            tlen = min(40, len(flw.tit))
            # print('----flw.qid: %s, tix: %s, flw-idx:%s'%(self.qid, self.tit, i))
            # print('----flw.tit: %s\n====flw.txt: %s, isTrue: %s\nxxxxflw.tlen: %s xlen: %s' %(flw.tit[0:tlen], flw.txt[0:tlen], flw.tit[0:tlen]==flw.txt[0:tlen], tlen, len(flw.txt[0:tlen])))
            if flw.tit[0:tlen] == flw.txt[0:tlen]:
                print('-x--flw.qid: %s, tix: %s, flw-idx:%s'%(self.qid, self.tit, i))
                print('-x--flw.tit: %s\n====flw.txt: %s\nxxxxflw.tlen: %s xlen: %s'%(flw.tit,flw.txt[0:tlen], tlen, len(flw.txt[0:tlen])))
                # flw.tit = flw.txt
                # flw.tit = ''
                if len(flw.txt) > len(flw.tit): flw.txt = (flw.txt[len(flw.tit):]).strip()
                # del self.flws[i]
                # flw.tit = flw.txt[0:tlen].strip()
                # flw.txt = flw.txt[tlen:].strip()
            i += 1

    def get_flw_txt(self):
        start_flw = False
        idx = 0
        lines = (line.strip() for line in self.soup.find_all(text=True))
        for i, line in enumerate(lines):
            line = line.replace('...华岳论坛 - "http://hua-yue.net"', '').replace('...华岳论坛 - "http://washeng.net"', '')
            # dlout(3, line)
            # if len(line) == 0 or line == ']': continue
            if len(line) == 0 or line == ']' or re.match('^\d+$', line): continue
            # dlout(3, line)
            if re.match('原\s+帖\s+\[', line):
                start_flw = True
                continue
            if not start_flw: continue
            # print('===FLW TXT AAA', i, line, i, 'ZZZ')
            if re.match('\[\s+\d+:\d+\s+\]', line):
                start_flw = False
                idx += 1
                if idx >= len(self.flws): break
                continue
            line = get_http_line(line)
            self.flws[idx].txt += re.sub('/no(.*)', '', line) + "\n"
            self.flws[idx].nwd += len(self.flws[idx].txt)
            self.fsz += self.flws[idx].nwd

    def get_flws(self):
        fols = self.soup.find('ol')
        self.fols = fols
        # print(fols.prettify())
        if len(fols.text.strip()) == 0: return

        self.flws = []
        idx = 1
        li = fols.find('li')
        while li is not None:
            # print(li.prettify())
            lines = (line.strip() for line in li.get_text().splitlines())
            for line in lines:
                if len(line) == 0: continue
                [tit,aut,tim, hhmmss] = self.get_flw_tat(line)
                flw = Art(idx, None, self.tag, self.ymd)
                flw.tag = self.tag
                flw.qid = self.qid
                flw.fid = '1' + hhmmss.replace(':', '')
                idx += 1
                flw.tit = tit
                flw.aut = aut
                flw.tim = tim
                flw.txt = ''
                self.flws.append(flw)
            li = li.find_next_sibling()

    def set_flw_lvl(self):
        lis = self.fols.find_all('li')
        for i, li in enumerate(lis):
            self.flws[i].lvl += len(li.find_parents('ul'))
            if li is None: break

    def get_flw_tat(self, line):
        line = line.strip()
        # print('LINE[', line, ']')
        # sys.exit(0)
        x = line.split(' - ')
        # xlen = len(xx)
        # print('x[]', x[0], x[1])
        # tit = re.sub('/n(.*)', '', x[0]).strip().replace(' ', '')
        timpcs = x.pop();
        # tit = re.sub('/n(.*)', '', x[0]).strip()
        if len(x) == 1: tit = re.sub('/n(.*)', '', x[0]).strip()
        else: tit = re.sub('/n(.*)', '', ' —— '.join(x)).strip()
        print('TIT', tit)
        pcs = timpcs.strip().split(' ')
        print('PCS', pcs)
        # sys.exit(0)
        aut = pcs[0]
        tim = conv_dt(pcs[1] + ' ' + pcs[2])
        return [tit, aut, tim, pcs[1]]

    def show_flws(self):
        if self.flws is not None:
            for flw in self.flws:
                # print(flw.idx, flw.lvl, flw.tim, pedsp(flw.aut,5), flw.tit, flw.txt)
                print(flw.idx, flw.qid, flw.fid, flw.lvl, flw.tim, pedsp(flw.aut,5))
                # print(flw.tit)
                print(flw.txt)
