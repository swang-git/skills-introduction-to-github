import sys, os, re
import requests_html
from bs4 import BeautifulSoup, Comment
from os.path import dirname
sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
sys.path.append(os.path.join(dirname(sys.path[0])))
from Utils import Img

class Art:
    def __getattr__(self, key): return None
    def __init__(self, idx, lnk, tag, ymd, tit='tit_tesing', tim='2018-11-21', TESTING=False):
        self.TESTING = TESTING
        self.idx = idx
        self.lnk = lnk
        self.tag = tag
        self.ymd = ymd
        self.tit = tit
        self.tim = tim
        if tim == "": self.tim = ymd
        self.aut = 'WX'
        self.yer = ymd[0:4]
        if lnk == None: self.qid = 0
        # else: self.qid = int(re.match('(.*)/(\d+)\.html', lnk).group(2))
        else: self.qid = int(re.match('(.*)/([0-9]+).html', lnk).group(2))
        # print('self.qid = %d' % self.qid)
        # sys.exit(0)
        self.fid = self.qid
        self.afz = 0
        self.fsz = 0
        self.ffz = 0
        self.lvl = 0
        self.vdo = 0
        self.img = 0
        self.nwd = 0
        self.txt = ''
        self.status = 'A'
        # self.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    def show(self,tag):
        print(tag,padsp(self.idx,3),self.tim,self.qid,'afz:',self.afz,'img:',self.img,self.tit,'--',self.aut, self.lnk)
        if tag == 'TXT': print('\n\n', self.txt, '\n\n')
        sys.stdout.flush()
    def show_flws(self,tag):
        for flw in self.flws:
            # if flw.idx == 20:
            # print(tag,padsp(flw.idx,3),flw.fid,flw.tim,flw.aut,flw.nwd,'\n'+flw.tit)
            print(tag,padsp(flw.idx,3),flw.fid,flw.tim,flw.aut,flw.nwd)
        sys.stdout.flush()

    def process_vdo(self):
        for vdo in self.art.find_all('iframe'):
            if vdo.get('src') != None:
                new_tag = self.soup.new_tag("pVDO")
                new_tag.string = '<iframe width="100%" height="85%" src="' + vdo.get('src') + '"></iframe>'
                try:
                    # ptag.insert(1, new_tag)
                    vdo.replace_with(new_tag)
                    self.vdo += 1
                except Exception as e:
                    print('vdo.replace_with Exception, exception msg:', str(e), 'NEW_TAG', new_tag)
                    # sys.exit(0)

    def process_img(self):
        imgobj = Img(self.tag, self.ymd, self.qid)
        # for img in self.bs.find_all('img'):
        for img in self.art.find_all('img'):
            img_src = img.get('src')
            if 'http' not in img_src: img_src = self.bas_url + img_src
            if img_src is None or 'button.jpg' in img_src: continue

            # if re.search('images/(tu_)((03|06).gif)|((ts|ys).png)', img_src) is not None:
            #     img.decompose()
            #     continue

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
    def is_add_newline(self, line):
        # ret = re.search('([。|！|!|？|?])|(\d{4}-\d\d-\d\d \d\d:\d\d:\d\d)', line)
        ret = re.search('([。|！|!|？|?])|([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2})', line)
        # print('ret =%s' % ret)
        # sys.exit(0)
        if ret is None: 
            # self.prev_line_has_newline = False
            return False
        else:
            # self.prev_line_has_newline = True
            return True

    def process_line(self, line):
        line = re.sub(' {2,}',  ' ', line)
        line = re.sub('。{2,}',  '……', line)
        line = line.strip()
        # decoding string like r'\u53EF\u80FD\u539F\u56E0\uFF1A'.encode("ascii").decode("unicode-escape")
        if re.search(r'\\u[0-9a-fA-F]+', line): 
            try:
                line = line.encode("ascii").decode("unicode-escape")
            except Exception as e:
                print('== processing LINE exception', line)
                print('== process_line Exception, return None and the error is:', e)
                return 'X ucode error X'
        
        # if re.search('(^)([=|\-|\+|@|*|~|—|_]{4,})', line) != None: print('XXXX_', line, '_XXXX')
        # if re.search('发表评论于\s+\d{4}-\d\d-\d\d \d\d:\d\d:\d\d', line): line += '    '  ## for Unicode
        if re.search('发表评论于 +[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}', line): line += '    ' ## for ASCII
        line = re.sub('[=|-|+|@|*|~|—|_]{4,}', '～～～～～～～～～\n', line)
        line = re.sub('^～～～～～～～～～', '\n～～～～～～～～～', line)
        return line

    def process_txt(self):
        lines = (line.strip() for line in self.art.stripped_strings)
        txt = ''
        for line in lines:
            line = line.strip()
            line = line.replace('\n', ' ')
            if len(line) == 0 or line == '原文链接>>': continue;
            line = self.process_line(line)
            txt += line
            if self.is_add_newline(line): txt += '\n'  # add one more for article
            else: txt += ' '
        self.txt = txt.strip()
        self.afz = len(self.txt) - self.img * 120
        self.nwd = self.afz

    def process_flw(self):
        def hasFid(fid):
            for flw in flws:
                if flw.fid == fid:
                    # print('XXXXXXX', flw.fid, fid)
                    return True
            return False
        flws = []
        infs = self.soup.find_all('div', class_='reply')
        flxs = self.soup.find_all('div', class_='summary')
        # print(self.soup.prettify())
        idx = 0
        i = 0
        for inf in infs:
            if flxs[i].getText().strip() == 'undefined':
                i += 1
                continue
            fcont = flxs[i]
            ahref = fcont.findAll('a')
            lines = (line.strip() for line in fcont.stripped_strings)
            if len(ahref) > 0:
                furl = ahref[0].get('href')
                # if re.search('^http', furl) is None: furl = 'http://www.wenxuecity.com' + furl
                if re.search('^http', furl) is None: furl = self.bas_url + furl
                trycnt = 1
                print('%d TRY: getting flw extra from[%s]'%(trycnt,furl))
                extr = self.getUrlContent(trycnt, furl)
                isExtraEmpty = len(extr.prettify())
                if isExtraEmpty:
                    trycnt += 1
                    extr = self.getUrlContent(trycnt, furl)
                print('%d TRY: is flw extra empty[%s]'%(trycnt, isExtraEmpty))
                cont = extr.findAll('body')[0]
                print(cont)
                cont.find('h1').decompose()
                cont.find('hr').decompose()
                cont.find('p').decompose()
                cont.find('script').decompose()
                cont.find('script').decompose()
                cont.find('noscript').decompose()
                for element in cont(text=lambda text: isinstance(text, Comment)): element.extract()
                # print(cont)
                lines = cont.getText().strip()

            i += 1
            tit = ''
            for line in lines:
                if len(line) == 0: continue
                tit += self.process_line(line)

            aut = inf.findAll('strong')[0].getText().strip()
            tim = inf.findAll('time')[0].getText().strip()
            idx += 1
            flw = Art(idx, None, self.tag, self.ymd, tit, tim)
            flw.aut = aut
            # flw.fid = int(tim[11:19].replace(':', '')) - flw.idx
            flw.fid = int(tim[11:19].replace(':', '')) + 1000000
            if hasFid(flw.fid):
                flw.fid += sum([ord(c) for c in flw.aut])
                # print('ZZZZ', flw.fid)
            flw.qid = self.qid
            flw.txt = tit
            flw.status = 'A'
            flw.nwd = len(tit)
            flws.append(flw)
            # print(aut, tim, '\n')
            # print(tit, '\n\n')
        self.flws = flws

    def getUrlContent(self, trycnt, url):
        session = requests_html.HTMLSession()
        try:
            print('%d TRY: url[%s]'%(trycnt, url))
            # self.soup = BeautifulSoup(request.urlopen(self.lnk), 'html5lib')
            # self.soup = BeautifulSoup(request.urlopen(self.lnk), 'html.parser')
            r = session.get(url)
            return BeautifulSoup(r.content, 'lxml')
            # self.soup = BeautifulSoup(r.content, 'html.parser')
        except Exception as e:
            print('WX getArt Exception, return None and the error is:', e)
            # sys.exit(-100)
            return None
    def getArt(self):
        trycnt = 1
        self.soup = self.getUrlContent(trycnt, self.lnk)
        isEmpty = len(self.soup.prettify()) == 0
        if isEmpty:
            # time.sleep(3.5)
            trycnt += 1
            print("self.lnk = [%s]" % self.lnk)
            # self.soup = self.getUrlContent(self.lnk)
            self.soup = self.getUrlContent(trycnt, self.lnk)
        isEmpty = len(self.soup.prettify()) == 0
        print('is soup empty:[%s]'%isEmpty)
        if isEmpty: return
        tmx = self.soup.find_all('time')
        # print(tmx.prettify())
        tim = tmx[0].get_text()
        self.tim = tim.strip()
        aut = self.soup.find_all('span', { 'itemprop': 'author' })[0].get_text()
        self.aut = aut.strip()
        # self.art = self.soup.find_all('div', class_='article')[0]
        self.art = self.soup.find('article', class_='article')
        self.process_img()
        self.process_vdo()
        self.process_txt()
        self.process_flw()
