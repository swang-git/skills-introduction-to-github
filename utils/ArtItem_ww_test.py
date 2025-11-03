from bs4 import BeautifulSoup
import re
import sys
from urllib import request
from Utils import conv_dt
from Utils import dlout
from Utils import Img
from Utils import padsp
from Utils import pedsp
from Utils import repl_puncts

class Art:
    def __getattr__(self, key): return None
    def __init__(self, idx, lnk, tag, ymd, tit='tit_tesing', tim='2017-10-29', TESTING=False):
        self.TESTING = TESTING
        self.idx = idx
        self.lnk = lnk
        self.tag = tag
        self.ymd = ymd
        self.tit = tit
        self.tim = tim
        if tim == "": self.tim = ymd
        self.aut = 'WW'
        self.yer = ymd[0:4]
        self.qid = re.match('(.*)/(\d+)\.html', lnk).group(2)
        self.fid = self.qid
        self.afz = 0
        self.fsz = 0
        self.ffz = 0
        self.lvl = 0
        self.vdo = 0
        self.img = 0
        self.nwd = 0
        self.txt = ''
    def show(self,tag):
        print(tag,padsp(self.idx,3),self.tim,self.qid,'afz:',self.afz,'img:',self.img,self.tit,'--',self.aut)
        print(self.txt, '\n\n')
        sys.stdout.flush()

    def process_vdo(self):
        # print(self.bs.prettify())
        for vdo in self.bs.find_all('iframe'):
            if vdo.get('src') is None: return
            new_tag = self.soup.new_tag("pVDO")
            new_tag.string = '<iframe width="100%" height="85%" src="' + vdo.get('src') + '"></iframe>'
            # new_tag.string = '<iframe style="padding:20px 0 0 10px;width:315px" src="' + vdo.get('src') + '"></iframe>'
            # new_tag.string = '\n\n<div style="margin:0 auto;padding:20px 0 0 0;width:315px"><iframe src="' + vdo.get('src') + '"></iframe></div>'
            # new_tag.string = '\n\n<div style="margin:0 auto;width:315px"><iframe src="' + vdo.get('src') + '" width="315"></iframe>'
            try:
                # ptag.insert(1, new_tag)
                vdo.replace_with(new_tag)
                self.vdo += 1
            except Exception as e:
                print('vdo.replace_with Exception, exception msg:', str(e), 'NEW_TAG', new_tag)
                # sys.exit(0)

    def process_img(self):
        imgobj = Img(self.tag, self.ymd, self.qid)
        for img in self.bs.find_all('img'):
            img_src = img.get('src')
            if img_src is None: continue
            if re.search('images/(tu_)((03|06).gif)|((ts|ys).png)', img_src) is not None:
                img.decompose()
                continue
            # new_tag = self.soup.new_tag("pIMG")  ## avoid duplicated pictures
            # new_tag = self.soup.new_tag("img")  ## avoid duplicated pictures
            img_string = imgobj.sav(img_src, self.img+1)
            if img_string is None: continue
            try:
                img.replace_with(img_string)
                self.img += 1
            except Exception as e:
                print('Img.replace_with Exception, exception msg:', str(e), 'P_TAG:', ptag, 'NEW_TAG', new_tag)
                # sys.exit(0)
    def XXXprocess_img(self):
        imgobj = Img(self.tag, self.ymd, self.qid)
        for img in self.bs.find_all('img'):
            img_src = img.get('src')
            if re.search('images/(tu_)((03|06).gif)|((ts|ys).png)', img_src) is not None:
                img.decompose()
                continue
            new_tag = self.soup.new_tag("pIMG")  ## avoid duplicated pictures
            new_tag.string = imgobj.sav(img_src)
            try:
                img.replace_with(new_tag)
                # print('PPP', img, 'PZZ')
                self.img += 1
            except Exception as e:
                print('Img.replace_with Exception, exception msg:', str(e), 'P_TAG:', ptag, 'NEW_TAG', new_tag)
                # sys.exit(0)

    def get_videoArt(self):
        dlout(3, 'in get_videoArt')
        # soup = BeautifulSoup(request.urlopen(self.lnk), 'html5lib')
        soup = BeautifulSoup(request.urlopen(self.lnk), 'html.parser')
        titline = soup.find('div', class_='name1')
        if titline is not None: self.tit = titline.get_text().strip()
        timline = soup.find('div', class_='new')
        dlout(3, "TIM_AUT_LINE", timline)
        if timline is not None:
            timaut = timeline.get_text().strip()
            x = timaut.split('&nbsp;&nbsp;')
            self.tim = re.match('(.*)(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)', timaut).group(2).strip()
            self.aut = re.match('(.*)(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)', timaut).group(1).strip()

        vdo_src = soup.find('iframe').get('src')
        self.txt = '<iframe style="margin:0 auto; padding:10px;width="315" src="' + vdo_src + '"></iframe>'

    def process_gen_text(self, text):
        txt = ''
        lines = (line.strip() for line in text.splitlines())
        # break multi-headlines into a line each
        # chunks = (phrase.strip() for line in lines for phrase in line.split("　　"))
        chunks = (phrase.strip() for line in lines for phrase in line.split("　"))
        # drop blank lines
        # txt += '\n\n'.join(chunk for chunk in chunks if chunk) + "\n\n"
        for line in chunks:
            line = line.strip()
            if 'img' in line: txt += line
            else: txt += line + "\n"

        self.txt = txt.strip()
        self.afz = len(self.txt)

    def get_mian_nr_Art(self):
        dlout(3, 'in get_mian_nr_Art')
        self.bs = self.soup.find('div', class_="mian_nr")
        self.bs.find('div', class_='pinglun').decompose()
        self.bs.find('div', class_='you').decompose()
        # print(self.soup.prettify())
        for script in self.soup(["script", "style"]): script.extract()    # rip it out

        self.process_img()
        self.process_vdo()

        text = self.bs.get_text().strip()
        self.process_gen_text(text)

    def get_bcbayArt(self):
        dlout(3, 'in get_bcbayArt')
        self.bs = self.soup.find('div', id="cont")
        # self.bs.find('div', class_='you').decompose()
        # print(self.soup.prettify())
        for script in self.soup(["script", "style"]): script.extract()    # rip it out

        self.process_img()
        self.process_vdo()

        text = self.bs.get_text().strip()
        self.process_gen_text(text)

    def parse_and_get_txt(self, cont):
        # print(cont.prettify())
        # sys.exit(0)
        tags = ['br', 'div']
        for tag in cont.find_all(tags):
            if tag is not None: tag.replace_with('\n\n')
        # for tag in cont.find_all('p'):
            # if tag is not None: tag.replace_with('\n')

        # lines = (line.strip() for line in cont.get_text().splitlines())
        lines = (line.strip() for line in cont.stripped_strings)
        txt = ''
        prev_line_type = ''
        for line in lines:
            # line = line.strip('\n')
            # if len(line) == 0: continue
            if len(line) == 0 or 'window.vitag.Init' in line: continue
            line = line.replace('>　　', '>')
            line = line.lstrip('　')
            line = line.replace('　', '\n')
            line = repl_puncts(line)
            print(' == LINE[' +  line + ']')
            if re.match('<img\s+', line) and prev_line_type != 'img':
                prev_line_type = 'img'
                txt += line
            elif re.match('<img\s+', line) and prev_line_type == 'img':
                prev_line_type = 'img'
                txt += "\n" + line 
            elif re.match('<iframe\s+', line):
                prev_line_type = 'iframe'
                txt += line + '\n'
            else:
                prev_line_type = 'txt'
                txt += line + '\n\n\n'
            # if "<img " not in line: line += '\n\n\n'
            # txt += line

            # if '/daily_data/' not in line or '<iframe ' not in line: txt += "\n\n\n"
            txt = txt.replace('\n\n\n', '\n\n')
        # return bytes(txt.strip(), 'gb18030').decode('gb18030', 'ignore')
        # return bytes(txt.strip(), 'utf-8').decode('utf-8', 'ignore')
        # return txt.strip().encode().decode('utf-8', 'ignore')
        # return txt.strip().encode('utf-16').decode('GB18030', 'strict')
        return txt.strip()
    def TEST_parse_and_get_txt(self, cont):
        print(cont.prettify())

        for br in cont.find_all('br'): br.replace_with('')

        # tags = ['br', 'div']
        # for tag in cont.find_all(tags):
        #     if tag is not None: tag.replace_with('\n\n')
        # lines = (line.strip() for line in cont.get_text().splitlines())

        # tags = ['span', 'br', 'div', 'p']
        txt = ''
        tags = ['div', 'p']
        for tag_soup in cont.find_all(tags):
            print(tag_soup.contents)
        #     if len(tag_soup.contents) == 0: continue
        #     for line in tag_soup.contents:
        #         if line is None: continue
        #         line = str(line)
        #         line = line.strip()
        #         if len(line) == 0: continue
        #         txt += line
        #         if '<img ' not in line: txt += "\n\n"
        # return txt.strip()
        # print(txt)
        sys.exit(0)

        lines = (line.strip() for line in cont.stripped_strings)
        # chunks = (phrase.strip() for line in lines for phrase in line.split("　　"))
        # txt = '\n'.join(chunks) + "\n\n"
        txt = ''
        for line in lines:
            # line = line.strip('\n')
            if len(line) == 0: continue;
            line = line.replace('>　　', '>')
            line = line.lstrip('　')
            line = line.replace('　', '\n')
            txt += line
            if '<img ' not in line or '<iframe ' not in line: txt += "\n\n\n"
            txt = txt.replace('\n\n\n', '\n\n')
        return txt.strip()

    def getArt(self):
        try:
            # self.soup = BeautifulSoup(request.urlopen(self.lnk), 'html5lib')
            resourse = request.urlopen(self.lnk)
            # content = resourse.read().decode(resource.headers.get_content_charset())
            content = resourse.read().decode('GB18030', 'ignore')
            self.soup = BeautifulSoup(content, 'html.parser')
        except Exception as e:
            print('WW getArt Exception, return None and the error is:', e)
            return None
        if self.soup.title == None: return None
        tat = self.soup.title.string.split('-')
        # print(' -- TAT[', tat, ']')
        self.tit = tat[0].strip()
        # print(' -- TIT[' + self.tit + ']')
        if len(tat) >= 2: self.aut = tat[1].strip()
        self.tim = self.ymd
        if 'video' in self.lnk: return self.get_videoArt()
        if 'bcbay' in self.lnk: return self.get_bcbayArt()

        # dlout(5, self.soup.prettify())

        if self.soup.find('div', class_='mian_nr') is not None: return self.get_mian_nr_Art()
        # if self.soup.find('div', id='cont') is not None: return self.get_bcbayArt();

        # divz = self.soup.find('div', class_='zuo', attrs={'id':'zuo'})
        divz = self.soup.find('div', class_='zuo', id='zuo')
        self.bs = divz.find('table', attrs={'border':'0','width':'675'})
        dlout(4, 'PRETTIFY', self.bs.prettify())

        # self.bs = self.soup.find('div', class_='zuo').find('table', attrs={'border':'0','width':'675'})
        # self.bs = self.soup.find('div', class_='zuo')

        # tblx = self.bs.find('table', {'align':'left', 'border':"0" cellpadding="0" cellspacing="0"})
        tblx = self.bs.find('table', {'align':'left', 'border':"0"})
        if tblx is not None: tblx.decompose()
        # self.bs.find('td', attrs={"align":"right", "valign":"middle"}).decompose()
        tit = self.bs.find('span', class_='STYLE3').get_text().strip()
        if tit is not None: self.tit = tit

        timautline = self.bs.find('span', class_='STYLE4').get_text().strip()
        # print(timautline)
        self.tim = re.match('(.*)(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)\s+(.*)\s+|(.*)', timautline).group(2)
        dlout(5, 'tim', self.tim)
        x = timautline.split(' ')  ## based on gbk space
        self.aut = x[4]
        dlout(5, 'aut', self.aut)
        self.flw = re.match('^(\d+).*', x[6]).group(1)
        dlout(5, 'flw', self.flw)

        self.process_img()
        self.process_vdo()

        cont = self.bs.find('div', id="newsContent")
        # dlout(3, cont.prettify())

        ##__ temp TESTING
        self.txt = self.parse_and_get_txt(cont)
        self.afz = len(self.txt)
        return

        #
        #
        conts = cont.contents
        # if len(conts) > 40:
        if len(conts) > 4000000:
            print('processing contents for newsContent')
            mtxt = ''
            lines = (line.strip() for line in cont.find_all(text=True, recursive=False))  ## text with tag <p>
            for line in lines:
                if len(line) == 0: continue
                mtxt += line

            idx = 4   ## find the position of the text without tag <p>
            for i, line in enumerate(cont.contents):
                if mtxt[3:10] in line:
                    idx = i
                    break
            ##__comment replace mtxt with <p>mtext</p> so that it will not be missed
            new_tag = self.soup.new_tag('p')
            new_tag.string = mtxt
            cont.contents[idx].replace_with(new_tag)

        for br in cont.find_all('br'): br.replace_with('\n')

        txt = ''
        # print(cont.prettify())
        tags = ['p', 'div']
        items =  cont.find_all(tags)
        for item in items:
            text = item.get_text().strip()
            if len(item.find_all(tags)) > 0 or text is None or len(text) == 0: continue

            lines = (line.strip() for line in text.splitlines())
            for line in lines:
                # print('LINAA', line, 'LINZZ')
                txt += line + ' '
                # txt += line + '\n\n'

            if item.find('pIMG') is None: txt += '\n\n'
                # if item.find('pIMG') is None: txt += line + "\n\n"
                # else: txt += line

                # lines = (line.strip() for line in text.splitlines())
                # for line in lines:
                #     if item.find('pIMG') is None: txt += line + "\n\n"
                # else: txt += line
            # # break multi-headlines into a line each
            # chunks = (phrase.strip() for line in lines for phrase in line.split("　　"))
            # # drop blank lines
            # # txt += ''.join(chunk for chunk in chunks if chunk) + "\n\n"
            # txt += ''.join(chunk for chunk in chunks if chunk)

        self.txt = txt.strip()
        self.afz = len(self.txt)

        cont_txt = cont.get_text().strip().replace('　', '')
        # cont_txt = cont.get_text().strip()
        # self.process_gen_text(cont_txt)

        if self.afz < len(cont_txt):
            # print('Missing some info, getting all text via cont.get_text()', self.afz, len(cont_txt), cont_txt,'XXX')
            print('Missing some info, getting all text via cont.get_text()', self.afz, len(cont_txt))
            self.process_gen_text(cont_txt)
