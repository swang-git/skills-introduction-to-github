import re
from urllib import request
from urllib.request import Request, urlopen
from bs4 import BeautifulSoup
import sys, os
# from os.path import dirname
# sys.path.append(os.path.join(dirname(sys.path[0])))
# sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
from Utils import padsp
from Utils import dlout
from Utils import Img, get_imgcomp, get_vdocomp
from Utils import get_parsed_text
from Utils import get_html_link
from Utils import get_x_http_line_x
from Utils import repl_puncts

class Art:
    def __getattr__(self, key): return None
    def __init__(self, cat_idx, idx, tag, item, theday, TESTING=False):
        self.TESTING = TESTING
        self.cat_idx = cat_idx
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
        return (self.dng and self.tim[0:10] <= self.theday) or self.theday in self.tim
    def parse_and_set_attrs(self, get_flw=True):
        self.set_lnk()
        self.set_tit()
        self.set_aut()
        self.set_qid()
        self.fid = self.qid
        self.set_nwd()
        self.set_clk_flw()
        if get_flw: self.get_flws()
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
        tim = re.match(r'(.*)(\d\d)(.*)(\d\d)(.*)\s+(\d\d:\d\d)(.*)', txm)
        if tim is None and self.spdelta - 1 >= 0:
            self.spdelta -= 1
            txm = self.spans[self.spdelta-1].get_text().replace('\t', '').replace('\n', '')
            tim = re.match(r'(.*)(\d\d)(.*)(\d\d)(.*)\s+(\d\d:\d\d)(.*)', txm)
        else:
            # print('cannot get time for', self.spans, manually set tim)
            self.tim = self.theday
        dlout(5, 'txm:', txm, 'tim:', tim)
        if tim is not None:
            try:
                self.tim = self.year + '-' + tim.group(2) + '-' + tim.group(4) + ' ' + tim.group(6)
            except Exception as e:
                print('cannot match grouping for tim from txm for', txm, self.tit)
                print(e)
        else:
            self.tim = self.theday
            print('cannot get tim from txm, manually set tim', txm, 'tit:', self.tit, self.qid)
    def set_nwd(self): self.nwd = self.spans[self.spdelta + 1].get_text().replace('字', '').strip()
    def set_clk_flw(self):
        dlout(5, 'clk, flw', self.spans[self.spdelta + 2].get_text().split('/'))
        [self.clk, self.flw] = re.sub('[(|)]', '', self.spans[self.spdelta + 2].get_text()).split('/')
        dlout(5, 'clk', self.clk)
    def open_and_get_flw(self):
        if int(self.flw) <= 0:
            print('no flw, return')
            return
        # page = request.urlopen(self.lnk)
        req = Request(self.lnk, headers={'User-Agent': 'Mozilla/5.0'})
        page = urlopen(req).read()
        # soup = BeautifulSoup(page, 'html.parser')
        soup = BeautifulSoup(page, 'lxml')
        # print(soup.prettify())
        item = soup.find('li', class_='treeReplyItem')
        art = Art(self.cat_idx, 0, self.tag, item, self.theday)
        art.parse_and_set_attrs()
        if self.TESTING: art.show('DING')
        art.get_flws()
        self.flws = art.flws
    def get_flws(self):
        uls = self.item.find('ul', class_='subUL')
        if uls == None: return
        self.flws = []
        lis = uls.find_all('li')
        # for li in enumerate(lis):
        for idx, li in enumerate(lis):
            flw = Art(self.cat_idx, idx, self.tag, li, self.theday)
            flw.idx += 1
            get_flw = False
            flw.parse_and_set_attrs(get_flw) # don't get flw in this case(or will recursively called)
            flw.qid = self.qid
            flw.lvl = len(li.find_parents('ul', class_="subUL"))
            flw.txt = flw.tit.strip()
            if len(flw.txt) > 0:
                self.flws.append(flw)
                if len(flw.txt) < int(flw.nwd) - 12:
                    # print('check_4_MORE_FLW_NWD flw.txt from Art', self.qid, flw.fid, 'len(flw.txt):', len(flw.txt), 'flw.nwd:', flw.nwd, (len(flw.txt) < int(flw.nwd)), 'flw.txt=['+flw.txt+']')
                    # print('check_got flw.txt from Ding Art', self.qid, flw.fid, len(flw.txt) < flw.nwd, 'flw.txt=['+flw.txt+']')
                    dlout(3, 'get more flw.txt for', self.qid, flw.fid, flw.lnk, flw.nwd, 'flw.txt=['+flw.txt+']')
            else:
                print('Empty flw.txt from Ding Art, skip this flw['+flw.txt+']', self.qid, self.fid, self.tit)
        # if self.TESTING:
        # print(len(self.flws))
        # for flw in self.flws:
        #     flw.show('FLW')

    def get_hex(self):
        id_to_hex = int(self.qid)
        if self.qid != self.fid: id_to_hex = int(self.fid)
        x = hex(id_to_hex).upper()
        return '/'.join(['Y0', '0'+x[2:3],x[3:5],x[5:7],x[7:9]])
    def get_html_url(self): return 'http://bbs1.people.com.cn/txt_new/' + self.get_hex() + '/content_html.txt'
    # def get_html_url(self): return 'http://bbs1.people.com.cn/posts/' + self.get_hex() + '/content_html.txt'
    # def get_html_url(self): return 'http://bbs1.people.com.cn/post/' + self.cat_idx + '/1/2/' + self.fid + '.html'
    def get_txt(self):
        dlout(4, 'self.nwd', self.nwd)
        if int(self.nwd) == 0 and self.qid == self.fid:
            self.txt = '无内容'
            return
        elif int(self.nwd) == 0 and self.qid != self.fid:
            return

        url = self.get_html_url()
        dlout(4, 'get_text', url)
        # if self.TESTING: url = 'file:///home/swang/tmp/content_html.txt_05'
        try:
            # self.htmltxt = request.urlopen(url)
            req = Request(url, headers={'User-Agent': 'Mozilla/5.0'})
            self.htmltxt = urlopen(req).read()
            self.process_html_txt()
        except Exception as e:
            print('Exception', str(e), url, self.txt, e)
            # print('Exception', str(e), url, self.txt, e.code, e.reason)
            # print('Exception', str(e), url, self.txt, e)
            # if e.code == 404 or e.code == 403: self.txt = None

    # def show(self,tag): print(tag,self.lnk, padsp(self.idx,3),self.tim,self.qid,self.fid,padsp(self.nwd,5),padsp(self.clk,4),padsp(self.flw,3),padsp(self.lvl,2),self.tit[0:30],'--',self.aut)
    # def show(self,tag): print('idx:',padsp(self.idx,3),self.tim,self.qid,'afz:',padsp(self.afz,5),'nwd:',padsp(self.nwd,5),'flw:',padsp(self.flw,3),self.tit[0:40],'--',self.aut)
    def show(self,tag):
        print('\nidx:',padsp(self.idx,3),self.tim,self.qid,'nwd:',padsp(self.nwd,5),'flw:',padsp(self.flw,3),self.tit[0:40],'--',self.aut, '\n')
        # print('\nidx:',padsp(self.idx,3),self.tim,self.qid,'nwd:',padsp(self.nwd,5),'flw:',padsp(self.flw,3),self.tit[0:40],'--',self.aut,self.lnk, '\n')
        sys.stdout.flush()
    def show_long(self,tag): print(tag,padsp(self.idx,3),self.tim,self.qid,self.fid,padsp(self.nwd,5),padsp(self.clk,4),padsp(self.flw,3),padsp(self.lvl,2),self.tit,'--',self.aut)

    def XXXprocess_vdo(self):
        # # ins_tag = self.soup.find('ins').find_parent()  ## commercials, remove it
        # ins_tag = self.soup.find('ins')  ## commercials, remove it
        # # print('INS_TAG', ins_tag)
        # if ins_tag is not None: self.soup.find('ins').decompose()
        # # ins_tag.clear()
        # print(self.soup.prettify())
        vdocomp = 0
        for vdo in self.soup.find_all('iframe'):
            # print('vdo', vdo)
            # sys.exit(0)
            new_tag = self.soup.new_tag('p')
            vdo_src = vdo.get('src')
            # vdocomp += len(str(vdo)) - (len(vdo_src) + 38)
            vdocomp += get_vdocomp(vdo)
            if vdo_src is not None and 'ad_ids' not in vdo_src:     ## no commercials
            # if vdo_src is not None and not re.match('ad_ids', vdo_src):     ## no commercials
                new_tag.string = '<iframe width="100%" height="85%" src="' + vdo_src + '"></iframe>'
                vdo.replace_with(new_tag)
                self.vdo += 1
        return vdocomp
    def process_vdo(self):
        dlout(3, 'process_vdo()')
        vdocomp = 0
        for vdo in self.soup.find_all('iframe'):
            # print('vdo', vdo)
            vdo_src = vdo.get('src')
            if vdo_src is None: vdo_src = vdo.get('data-src')
            if vdo_src is None:
                vdo.decompose()
                continue

            new_tag = self.soup.new_tag('p')
            vdocomp += get_vdocomp(vdo)
            if re.match('http://img.adbox.sina.com.cn/ad/\d+.html', vdo_src):
                # new_tag.string = '<img src="' + vdo_src + '" style="max-width:600px;float:left;margin:9px 9px 0 0" />'
                vdo.decompose()
                continue
            else:
                new_tag.string = '<iframe width="100%" height="85%" src="' + vdo_src + '"></iframe>'

            vdo.replace_with(new_tag)
            self.vdo += 1
        return vdocomp

    def XXXprocess_img(self):
        imgobj = Img(self.tag, self.ymd, self.qid)
        imgcomp = 0
        for img in self.soup.find_all('img'):
            # print('XXXXX len(img)', len(str(img)), str(img))
            # ptag = img.find_parent()
            # imgsrc = img.get('src')
            # fnlen = len(os.path.basename(imgsrc))
            # imgcomp += len(str(img)) - (fnlen + 6 + 110)
            imgcomp += get_imgcomp(img)
            new_tag = self.soup.new_tag("pIMG")  ## to avoid duplicated pictures shown
            # new_tag = self.soup.new_tag("p")  ## to avoid duplicated pictures shown
            try:
                new_tag.string = imgobj.sav(img.get('src'))
                # print('img string', new_tag.string)
                img.replace_with(new_tag)
                # img_string = imgobj.sav(img.get('src'))
                # print('IMG_STR_AAA', img_string, 'IMG ZZZ')
                # img.replace_with(img_string)
                self.img += 1
            except Exception as e:
                print('Got Exception, ignored', e)
                # img.replace_with(new_tag)
                # self.img += 1
        return imgcomp
    def process_img(self):
        dlout(3, 'process_img()')
        # print(self.soup.prettify())
        imgobj = Img(self.tag, self.ymd, self.qid)
        imgcomp = 0
        for imgidx, img in enumerate(self.soup.find_all('img')):
            imgidx += 1
            imgcomp += get_imgcomp(img)
            imgsrc = img.get('src')
            if imgsrc is not None and 'file:///' not in imgsrc:
                img_str = imgobj.sav(imgsrc, imgidx)
                if img_str is None: continue

                new_tag = self.soup.new_tag("p")  ## to avoid duplicated pictures shown
                new_tag.string = img_str
                img.replace_with(new_tag)
                # print('\n\n XXXX img string', img, '\n\nZZZ')
                self.img += 1
            else:
                print('NO_ imgsrc for', self.tag, self.ymd, self.qid)
        return imgcomp
    def XXXZZparsed_by(self, tags):
        # print(self.soup.prettify())
        txt = ''
        items = self.soup.find_all(['p', 'div'])
        i = 0
        for item in items:
            if len(item.find_all(['p', 'div'])) == 0:
                i += 1
                # print("ITEMAA\n\n", i, item, i, '\n\nITEMZXZ')
        sys.exit(0)

        for item in items:
            # print('\n\nITEM AAA', item, 'ITEM ZZZ\n\n')
            text = item.get_text().strip()
            if len(text) == 0: continue
            # txt += "\n" + text + "\n"
            if item.find('pIMG') is None:
                txt += text + "\n\n"
            else:
                txt += text

            chunks = (chunk.strip() for chunk in txt.split('　'))
            txt = '\n'.join(chunks) + '\n\n'
        return txt

    def XXXZZparsed_by(self, tags):
        # print(self.soup.prettify())
        txt = ''
        # items = self.soup.find_all(tags)
        items = self.soup.find_all(tags, recursive=False)
        # items = self.soup.find_all(tags)
        for item in items:
            # print('\n\nITEM AAA', item, 'ITEM ZZZ\n\n')
            # text = item.get_text().strip().replace('A+A-', '')
            # if len(item.find_all(tags)) > 0: continue

            lines = (line.strip() for line in item.stripped_strings)
            # lines = (line.strip() for line in item.get_text().splitlines())
            for line in lines:
                line = line.strip()
                chunks = (chunk.strip() for chunk in line.split('&nbsp;&nbsp;'))
                for chunk in chunks:
                    if len(chunk) == 0: coninue
                    if "<img " not in chunk: line += "\n\n"
                    txt += chunk
        print(txt)
        sys.exit(0)
        return txt.strip()
    def XXXparsed_by(self, tags):
        # print(self.soup.prettify())
        txt = ''
        # items = self.soup.find_all(tags)
        items = self.soup.find_all(tags, recursive=False)
        # items = self.soup.find_all(tags)
        for item in items:
            # print('\n\nITEM AAA', item, 'ITEM ZZZ\n\n')
            # text = item.get_text().strip().replace('A+A-', '')
            # if len(item.find_all(tags)) > 0: continue
            # lines = (line.strip() for line in item.stripped_strings)
            lines = (line.strip() for line in item.get_text().splitlines())
            for line in lines:
                line = line.strip()
                if len(line) == 0: coninue
                if "<img " not in line: line += "\n\n"
                txt += line
        # print(txt)
        # sys.exit(0)美国为何会输掉朝鲜战争?我军参谋说出原因
        return txt.strip()
    def parsed_by(self, tags):
        # print('_PRETTIFY_', self.soup.prettify())
        for br in self.soup.find_all('br'): br.replace_with('\n')
        txt = ''
        items = self.soup.find_all(tags)
        for item in items:
            # print('\n\nITEM AAA', item, 'ITEM ZZZ\n\n')
            text = item.get_text().strip().replace('A+A-', '')
            text = text.strip()
            if len(item.find_all(tags)) > 0 or text is None or len(text) == 0: continue
            # print('\n\nTEXT AAA', text, 'TEXT ZZZ\n\n')
            # text = text.lstrip('\n').lstrip('\n').lstrip('\n').lstrip('\n')
            if item.find('pIMG') is None or item.find('img') is None:
                txt += text + "\n"
            else:
                txt += text

        # chunks = (chunk.strip() for chunk in txt.split('　'))
        # txt = '\n'.join(chunks) + '\n\n'
        lines = (line.strip() for line in txt.splitlines())
        txt = ''
        for line in lines:
            if len(line) == 0: continue
            # if '<img ' in line or '<iframe ' in line:
            if re.match('<(img|iframe)\s+', line):
                txt += line
            else:
                line = get_html_link(line)
                line = get_x_http_line_x(line)
                line = repl_puncts(line)
                txt += line + '\n\n'
            # print("AAAA", line, "ZZZZ")
        # sys.exit(0)
        return txt.strip()

    def XX_parse_and_get_txt(self, cont):
        print(cont.prettify())
        tags = ['div', 'p', 'br']
        # tags = ['br']
        # for tag in cont.find_all(tags):
        #     if tag is not None: tag.replace_with('\n\n')

        lines = (line.strip() for line in cont.stripped_strings)
        # lines = (line.strip() for line in self.soup.get_text().strip().splitlines())

        # for line in lines:print('LINAA', line, 'LINZZ')

        # chunks = (phrase.strip() for line in lines for phrase in line.split("　　"))
        # txt = '\n'.join(chunks) + "\n\n"
        txt = ''
        for line in lines:
            if len(line) == 0: continue;
            txt += line
            if '<img ' not in line: txt += "\n\n\n"
        return txt.strip()

    def get_soup_text(self, tags):
        # print(self.soup.prettify())

        # for nbsp in self.soup.find_all(tags): nbsp.replace_with('\n')   ## why do we need

        txt = ""
        lines = (line.strip() for line in self.soup.get_text().splitlines())
        # lines = (line.strip() for line in self.soup.stripped_strings)
        for line in lines:
            line = line.replace(' ', '').strip()
            if len(line) == 0: continue;
            line = line.replace('>　　', '>')
            line = line.lstrip('　')
            line = line.replace('　', '\n')
            if re.match('<(img|iframe)\s+', line):
                txt += line + '\n'
            else:
                txt += line + '\n\n'
        return txt
    def process_html_txt(self):
        dlout(4, 'process_html_txt()')
        self.soup = BeautifulSoup(self.htmltxt, 'html.parser')
        imgcomp = self.process_img()
        vdocom4 = self.process_vdo()
        # print(self.soup.prettify())
        # sys.exit(0)
        for br in self.soup.find_all('br'): br.replace_with('\n')

        # tags = ['p', 'div', 'span']
        tags = ['p', 'div']
        pdivs = self.soup.find_all(tags)
        txt = ''
        if len(pdivs) > 0: txt = self.parsed_by(tags)
        txt = txt.replace('A+A-', '')
        # atxt = ''
        # lines = (line.strip() for line in self.soup.get_text().splitlines())

        # print('TXTXT', atxt, 'ZZZZ')
        gtxt = self.get_soup_text(tags)
        # gtxt = gtxt.replace('<imgsrc=', '\n\n<img src=').replace('"style=', '" style=').replace('A+A-', '')
        gtxt = gtxt.replace('<imgsrc=', '\n\n<img src=').replace('"style=', '" style=').replace('A+A-', '').replace('margin:9px9px00', 'margin:9px 9px 0 0')

        # print('TXTXT', gtxt, 'ZZZZ')

        # if len(gtxt) < len(atxt): gtxt = atxt

        if len(gtxt) == 0:
            self.txt = txt.strip()
        # elif len(txt) < len(gtxt) + imgcomp + vdocomp:  ##_ no need to compensate since gtxt = self.get_soup_text(tags) is done after self.process_img / vdo
        elif len(txt) < len(gtxt) - 100:
            # print('txt < gtxt', 'len(txt)=', len(txt), '<', len(gtxt), 'len(gtxt)=', 'imgcomp=', imgcomp, 'vdocomp=', vdocomp)
            print('txt < gtxt', 'len(txt)=', len(txt), '<', len(gtxt))
            self.txt = gtxt.strip()
        else:
            self.txt = txt.strip()

        self.afz = len(self.txt)
        if self.TESTING: print(self.txt)
        return
    def get_flw_txt(self):
        dlout(4, 'get_flw_txt self.nwd', self.nwd)
        # hexurl = self.get_html_url()
        html_url = self.get_html_url()
        dlout(4, 'get_flw_txt for', html_url)
        # if self.TESTING: url = 'file:///home/swang/tmp/content_html.txt_05'
        try:
            # self.htmltxt = request.urlopen(hexurl)
            # req = Request(self.lnk, headers={'User-Agent': 'Mozilla/5.0'})
            req = Request(html_url, headers={'User-Agent': 'Mozilla/5.0'})
            self.htmltxt = urlopen(req).read()
            soup = BeautifulSoup(self.htmltxt,  "html.parser")
            dlout(4, 'get_flw_txt.self.txt', soup.get_text())
            self.txt = soup.get_text().strip()
            # self.process_html_txt()
        except Exception as e:
            # print('Exception', str(e), hexurl, self.txt, e)
            print('Exception', str(e), html_url, self.txt, e)
            # print('Exception', str(e), url, self.txt, e.code, e.reason)
            # print('Exception', str(e), url, self.txt, e)
            # if e.code == 404 or e.code == 403: self.txt = None
####### END #######
