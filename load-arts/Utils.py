import argparse
import os
import sys
# import urllib.request
from urllib.request import Request, urlopen
import hashlib
from datetime import date
from datetime import datetime
import time
from bs4 import BeautifulSoup
import re
import os
import ssl

from contextlib import contextmanager
# import argparse

# import nltk
# global DEBUG_LEVEL
DEBUG_LEVEL = 4

# @contextmanager
# def urlopener(inURL):
#     """Open a URL and yield the fileHandle then close the connection when leaving the 'with' clause."""
#     fileHandle = urllib2.urlopen(inURL)
#     try:     yield fileHandle
#     finally: fileHandle.close()


def wkdayname():
    return date.fromtimestamp(time.time()).strftime('%a')


def getLogFile(tag, dyx):
    logFile = '/Users/swang/tmp/logs/cn/load' + tag + \
        '_' + str(abs(dyx)) + '_' + wkdayname() + '.log'
    return logFile


def dlout(dl, *args):
    if dl < DEBUG_LEVEL:
        print(get_now(), "%s" % list(args))
        sys.stdout.flush()
# class Debug:
#     def out(self, dlvl, *args):
#         if dlvl > DEBUG_LEVEL: print("%s" % list(args))


def dout(tag, *args):
    debug = 0
    if (debug):
        print(tag, "%s" % list(args))

# def print_sec_header(): print('\n Load Date', ' Symbol', 'Price', ' Change', '52W_low', '52W_high')


def print_sec_header_dt(): print('\n Loading DateTime  ', 'Symbol  ',
                                 ' Price', '  Change', '52_W_low', '52W_high')
def get_now(): return datetime.fromtimestamp(
    time.time()).strftime('%Y-%m-%d %H:%M:%S')


def get_today(): return datetime.fromtimestamp(time.time()).strftime('%Y-%m-%d')

# def padsp(s, cnt): return '{msg:<'+str(cnt)+'}'.format(msg=s)


def pedsp(s, width): return '{msg:<{width}}'.format(msg=s, width=width)


def padsp(s, width):
    if s == None:
        return 'Empty'
    else:
        return '{msg:>{width}}'.format(msg=s, width=width)


def padding(s, fill, align, width): return '{msg:{fill}{align}{width}}'.format(
    msg=s, fill=' ', align='<', width=width)


def get_cn_zone_dates(dyx):
    o12x60x60 = 12 * 60 * 60

    ymd = date.fromtimestamp(time.time() + dyx * 2 *
                             o12x60x60 + o12x60x60).strftime('%Y%m%d')
    theday = datetime.fromtimestamp(
        time.time() + dyx * 2 * o12x60x60 + o12x60x60).strftime('%Y-%m-%d')
    prvday = date.fromtimestamp(
        time.time() + (dyx - 1) * 2 * o12x60x60 + o12x60x60).strftime('%Y-%m-%d')
    # theday = datetime.fromtimestamp(time.time() + dyx *       2 * o12x60x60 + o12x60x60).strftime('%Y-%m-%d %H:%M:%S')
    # print('proc_day:', theday, 'prev_day:', prvday, 'ymd', ymd, tag, '\n')
    return [ymd, theday, prvday]


def get_dates(dyx):
    ymd = date.fromtimestamp(time.time() + dyx * 24 *
                             60 * 60).strftime('%Y%m%d')
    theday = date.fromtimestamp(
        time.time() + dyx * 24 * 60 * 60).strftime('%Y-%m-%d')
    prvday = date.fromtimestamp(
        time.time() + (dyx - 1) * 24 * 60 * 60).strftime('%Y-%m-%d')
    # print('proc_day:', theday, 'prev_day:', prvday, 'ymd', ymd, tag, '\n')
    return [ymd, theday, prvday]


def get_mmddyyyy(dyx):
    ymd = date.fromtimestamp(time.time() + dyx * 24 *
                             60 * 60).strftime('%Y%m%d')
    theday = date.fromtimestamp(
        time.time() + dyx * 24 * 60 * 60).strftime('%m/%d/%Y')
    prvday = date.fromtimestamp(
        time.time() + (dyx - 1) * 24 * 60 * 60).strftime('%m/%d/%Y')
    return [ymd, theday, prvday]


def get_yyyymmdd(dyx):
    ymd = date.fromtimestamp(time.time() + dyx * 24 *
                             60 * 60).strftime('%Y%m%d')
    theday = date.fromtimestamp(
        time.time() + dyx * 24 * 60 * 60).strftime('%Y/%m/%d')
    prvday = date.fromtimestamp(
        time.time() + (dyx - 1) * 24 * 60 * 60).strftime('%Y/%m/%d')
    return [ymd, theday, prvday]


def get_cn_tit(tag):
    if tag == 'PXHY':
        return '华山论剑'
    elif tag == 'PXQG':
        return '强国论坛'
    elif tag == 'PXWW':
        return '即时新闻'
    elif tag == 'PXWX':
        return '焦点新闻'
    elif tag == 'PXZJ':
        return '史海钩沉'
    elif tag == 'PXJL':
        return '军事历史'
    else:
        return '没有定义'


def get_cn_dat(ymd):
    ymd = str(ymd)
    yy = ymd[:4]
    mm = ymd[4:6]
    dd = ymd[6:8]
    # dout('TEST', yy, mm, dd)
    dt = date(int(yy), int(mm), int(dd))
    wd = dt.weekday()
    # dout('DT', dt.weekday())
    wdat = '星期天'
    if wd == 0:
        wdat = '星期一'
    elif wd == 1:
        wdat = '星期二'
    elif wd == 2:
        wdat = '星期三'
    elif wd == 3:
        wdat = '星期四'
    elif wd == 4:
        wdat = '星期五'
    elif wd == 5:
        wdat = '星期六'
    cn_dat = '(' + yy + '年' + mm + '月' + dd + '日' + wdat + ')'
    return cn_dat


def conv_dt(line):
    x = line.strip().split(' ')
    d = x[1].split('/')
    tim = d[2] + '-' + d[0] + '-' + d[1] + ' ' + x[0]
    return tim


class Img:
    '''docstring: save the picture file to local disk and return img tag string using local image'''
    "the instance of this calss saves the picture file to local disk and returns img tag string string using local image"

    def __init__(self, tag, ymd, qid):
        self.tag = tag
        self.ymd = ymd
        self.qid = qid
        self.year = str(ymd)[0:4]

    def sav(self, lnk, imgidx):
        # turn off the certificate verification for ssl link (https://)
        if (not os.environ.get('PYTHONHTTPSVERIFY', '') and getattr(ssl, '_create_unverified_context', None)):
            ssl._create_default_https_context = ssl._create_unverified_context

        dlout(5, 'A_LNK:', lnk)
        if lnk == None:
            print('IMGlnk is empty:', lnk)
            return None
            # self.img_txt = '<img src="" alt="img url is empty or invalid">'
            # self.img_txt = '<img src="" alt="img url:' + str(lnk) + '">'
        elif re.match('^http', lnk) is None and re.match('(.*).(com|net|cn|org|cc|webp)/(.*)', lnk) is None:
            print('IMGlnk is invalid:', lnk, ' -- return None')
            # self.img_txt = ''
            # return self.img_txt
            return None
        elif re.match('^http', lnk) is None and re.match('(.*).(com|net)/(.*)', lnk):
            if re.match('^//(.*)/', lnk):
                # print('looks like a valid link, no http but has // .com, .net', lnk)
                lnk = 'http:' + lnk
            else:
                # print('LOOKS like a valid link, no http but has .com, .net', lnk)
                lnk = 'http://' + lnk
        dlout(5, 'B_LNK:', lnk)

        x = os.path.basename(lnk)
        imdx = str(imgidx)
        if imgidx < 10:
            imdx = '0' + str(imgidx)
        imgfile = str(self.qid) + "_" + imdx + "_x.png"
        # imgfilename =  os.path.basename(lnk).split('?')[0]
        # if not imgfilename.lower().endswith(('.png', '.jpg', '.jpeg', '.gif')):
        #     imgfile += 'x_.png'
        #     # imgfile += hashlib.md5(lnk.encode('utf')).hexdigest() + '.png'
        # else: imgfile += imgfilename

        savroot = "/Users/swang/sites/webdata"
        savpath = "/daily_data/" + self.tag + "/" + \
            str(self.year) + "/" + str(self.ymd) + "/images/"
        os.makedirs(savroot + savpath, exist_ok=True)
        savfile = savpath + imgfile
        dlout(4, 'QID', self.qid)
        if self.qid == 'testing':
            tofile = "/Users/swang/tmp/" + self.tag + "_" + self.ymd + "_" + imgfile
        else:
            tofile = savroot + savfile

        if os.path.isfile(tofile):
            dlout(3, 'IMG EXIST', tofile + ' exists, skip')
        else:
            if 'http' in lnk:
                print(get_now(), 'savImg ' + lnk)
                print(get_now(), 'to file://' + tofile)
                sys.stdout.flush()
                try:
                    headers = {}
                    headers['User-Agent'] = "Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.27 Safari/537.17"
                    # headers['User-Agent'] = "Mozilla/5.0 (X11; Fedora; Linu…) Gecko/20100101 Firefox/57.0"  ##_ BAD BAD can't encode latin-1 char
                    if self.tag == 'PXQG' or self.tag == 'PXZJ' or self.tag == 'PXJL':
                        headers['Referer'] = 'http://bbs1.people.com.cn/'
                        # headers['Referer'] = 'http://upload.stnn.cc/'
                        # headers['Host'] = 'upload.stnn.cc'
                        # headers['Accept'] = 'text/html,application/xhtml+xm…plication/xml;q=0.9,*/*;q=0.8'
                        # headers['Accept-Encoding'] = 'gzip, deflate'
                        # headers['Accept-Language'] = 'zh,en-US;q=0.7,en;q=0.3'
                        # headers['Cache-Control'] = 'no-cache'
                        # headers['Connection'] = 'keep-alive'
                        # headers['Pragma']  = 'no-cache'
                        # headers['Upgrade-Insecure-Requests'] = 1

                    # req = urllib.request.Request(lnk, headers = headers)
                    # resp = urllib.request.urlopen(req)
                    req = Request(lnk, headers=headers)
                    resp = urlopen(req, timeout=60)
                    respData = resp.read()
                    with open(tofile, 'wb') as img_file:
                        img_file.write(respData)
                        img_file.close()
                    # saveFile = open(tofile,'wb')
                    # saveFile.write(respData)
                    # saveFile.close()
                except Exception as e:
                    print(
                        'Img.sve Exception, return the origianl imgsrc lnk and error is:', e)
                    # raise e
                    self.img_txt = '<img src="' + lnk + \
                        '" style="max-width:600px;float:left;margin:9px 9px 0 0">'
                    return self.img_txt

        self.img_txt = '<img src="' + savfile + \
            '" style="max-width:600px;float:left;margin:9px 9px 0 0">'
        # self.img_txt = '<img src="' + imgsrc + '" style="max-width:500">'
        return self.img_txt

    def getImgtxt(): return self.img_txt


class Txt:
    def __init__(self, line):
        self.line = line

    def rmBadChars(self):
        pos = self.line.find(b'</a> - ')
        print('possible bad char position:', pos,
              self.line.decode('gb18030', 'ignore'))
        # print('possible bad char position:', pos, self.line[:136].decode('gb18030', 'ignore'))
        # print('possible bad char position:', pos, self.line[:100].decode())
        if pos == 140:
            # rline = self.line[:136] + self.line[140:]
            rline = self.line.decode('gb18030', 'ignore')
            # print(bytes(rline, 'gb18030').decode('gb18030'))
            return rline
        else:
            return self.line


class ProcessHtmlText:
    def __init__(self, htmltxt):
        print(htmltxt)
        self.htmltxt = htmltxt

    def processTxt(self, item):
        txt = ''
        pix = 0
        for img in item.find_all(["img", "iframe"]):
            pix += 1
            txt += img.get('src') + '\n'
        text = item.get_text().strip()
        if len(text) == 0:
            return [None, None]
        # break into lines and remove leading and trailing space on each
        lines = (line.strip() for line in text.splitlines())
        # break multi-headlines into a line each
        chunks = (phrase.strip()
                  for line in lines for phrase in line.split(' '))
        # chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
        # drop blank lines
        txt += '\n\n'.join(chunk for chunk in chunks if chunk)
        return [txt, pix]

    def has_no_real_tags(self):
        items = self.soup(["div", "p"])
        txt = ''
        for item in items:
            txt += item.get_text().strip()
        return txt == ''

    def soup_strip_tags(self):
        self.soup = BeautifulSoup(self.htmltxt, 'html.parser')
        # kill all script and style elements
        for script in self.soup(["script", "style"]):
            script.extract()    # rip it out
        txt = ''
        pix = 0
        # get text
        # items = soup(["div", "p"])
        items = self.soup(["div", "p"])
        # print(items)
        if self.has_no_real_tags():
            return self.processTxt(self.soup)

        for item in items:
            for img in item.find_all(["img", "iframe"]):
                pix += 1
                txt += img.get('src') + '\n'
            text = item.get_text().strip()
            if len(text) == 0:
                continue
            # text = text.replace('　　', '\n')
            # break into lines and remove leading and trailing space on each
            lines = (line.strip() for line in text.splitlines())
            # break multi-headlines into a line each
            # chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
            # chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
            chunks = (phrase.strip()
                      for line in lines for phrase in line.split('  '))
            # drop blank lines
            txt += '\n'.join(chunk for chunk in chunks if chunk)
            txt += '\n\n'
        return [txt, pix]

    def NLTK_strip_tags(self): return nltk.clean_html(self.htmltxt)
    def re_strip_tags(self): return re.sub('<[^<]+?>', '', str(self.htmltxt))


def get_parsed_text(text):
    # break into lines and remove leading and trailing space on each
    lines = (line.strip() for line in text.splitlines())
    chunks = (phrase.strip()
              for line in lines for phrase in line.split("  "))  # GB 2 spaces
    return '\n\n'.join(chunk for chunk in chunks if chunk)


def get_imgcomp(img):
    if img is None:
        return 0
    imgsrc = img.get('src')
    if imgsrc is not None:
        fnlen = len(os.path.basename(imgsrc))
        return len(str(img)) - (fnlen + 6 + 110)
    return 0


def get_vdocomp(vdo):
    if vdo is None:
        return 0
    vdo_src = vdo.get('src')
    # if vdo_src is None: return len(str(vdo)) - 38
    if vdo_src is None:
        return 0
    return len(str(vdo)) - (len(vdo_src) + 38)


def get_fin_filename(tag):
    return "/Users/swang/tmp/" + tag + datetime.fromtimestamp(time.time()).strftime('%Y%m%d')
    # return "/Users/swang/tmp/" + tag + "_market_" + datetime.fromtimestamp(time.time()).strftime('%Y%m%d')
    # return "/Users/swang/tmp/" + tag + "_market_" + datetime.fromtimestamp(time.time()).strftime('%Y%m%d_%H_%M_%S')


def get_fin_header():
    return 'Load Date'.center(15) + ' Symbol' + 'Price' + ' Change' + '52W_low' + '52W_high\n'


def print_fin_dats(h, s):
    loadtm = datetime.fromtimestamp(time.time()).strftime('%Y-%m-%d %H:%M:%S')
    print(loadtm.rjust(s[0]), h[1].rjust(s[1]), str(h[2]).rjust(s[2]), str(h[3]).rjust(
        s[3]), str(h[4]).rjust(s[4]), str(h[5]).rjust(s[5]), h[6].rjust(s[6]))
    sys.stdout.flush()


def get_html_link(line):
    pattern = re.compile('【(.*)http://(.*).html；(.*)】')
    m = pattern.match(line)
    if m is None:
        return line
    # print("PATTERN", m.group)
    if m.group is not None:
        lnk = '【' + m.group(1)+'<a href="http://'+m.group(2) + \
            '.html" target="_blank">'+m.group(3)+'</a>】'
        return lnk


def get_http_line(line):  # _ assuming http://xxx a single line
    pattern = re.compile('^(http|https)://(.*)')
    m = pattern.match(line)
    if m is None or '<a href' in line:
        return line
    # print('LINE', line)
    # pattern = re.compile('(.*)http://(.*)\s+(.*)')
    # print('PATTERN M', m)
    if m.group is None:
        return line
    return '<a href="' + m.group(1) + '://' + m.group(2) + '" target="_blank">链接</a>'


# _ assuming '新闻链接：http://finance.eastmoney.com/news/1354,20180109820201821.html'
def get_x_http_line_x(line):
    pattern = re.compile('(.*)http(.*).html(.*)')
    m = pattern.match(line)
    if m is None or '<a href' in line:
        return line
    if m.group is None:
        return line
    return '<a href="http'+m.group(2)+'.html" target="_blank">'+m.group(1).replace('：', '')+'</a>'+m.group(3)


# _ assuming '新闻链接：http://finance.eastmoney.com/news/1354,20180109820201821.html'
def get_x_http_line(line):
    pattern = re.compile('(.*)http(.*)')
    m = pattern.match(line)
    if m is None or '<a href' in line:
        return line
    if m.group is None:
        return line
    return '<a href="http'+m.group(2)+'.html" target="_blank">'+m.group(1).replace('：', '')+'</a>'


def repl_comma(mo):
    # if re.match('\d', mo.group(1)) and not re.match('”', mo.group(1)) and re.match('\d', mo.group(3)):
    if re.match(r'\D', mo.group(1)) or re.match(r'\D', mo.group(3)): ## \D means non-digit
        # print(mo.group(1), mo.group(2), mo.group(3))
        return mo.group(1) + '，' + mo.group(3)
    else:
        return mo.group(1) + ',' + mo.group(3)


def repl_semi_colon(mo):
    # if re.match('\d', mo.group(1)) and not re.match('”', mo.group(1)) and re.match('\d', mo.group(3)):
    if re.match(r'\D', mo.group(1)) and re.match(r'\D', mo.group(3)):
        # print(mo.group(1), mo.group(2), mo.group(3))
        return mo.group(1) + ':' + mo.group(3)
    else:
        return mo.group(1) + '：' + mo.group(3)


def repl_period(mo):
    # if re.match('\d', mo.group(1)) and not re.match('”', mo.group(1)) and re.match('\d', mo.group(3)):
    if re.match('[a-zA-Z0-9]', mo.group(1)) and re.match('([a-zA-Z0-9]|r\s+)', mo.group(3)):
        # print('repl_p, g1:', mo.group(1), 'g2:', mo.group(2), 'g3:', mo.group(3))
        return mo.group(1) + '.' + mo.group(3)
    else:
        # print('repl_p, g1:', mo.group(1), 'g2:', mo.group(2), 'g3:', mo.group(3))
        return mo.group(1) + '。' + mo.group(3)


def repl_puncts(line):
    # if 'http://' in line: return line
    # pattern=re.compile('(.*)http(|s)://(.*)')
    pattern = re.compile('(.*)(http(|s)://|@(.*).(com|cn|org|net|gov))')
    # if pattern.match(line) is not None: return line
    if pattern.match(line):
        return line
    if re.match('<(img|iframe)r\s+', line):
        return line

    line = re.sub('。{3,}', '……', line)
    line = re.sub('r\.{3,}', '……', line)
    line = re.sub('(r\D)(,)(r\D)', repl_comma, line)
    line = re.sub('(\D)(,)(\d)', repl_comma, line)
    line = re.sub('(\D)(\.)(\D)', repl_period, line)
    line = re.sub('(\D)(\.)(\d)', repl_period, line)
    line = re.sub('\.\s*$', '。', line)

    line = line.replace('--', ' —— ')
    line = re.sub('（(\D)(:)\D', repl_semi_colon, line)
    # line = re.sub(':\s*', '：', line)
    line = re.sub('!\s*', '！', line)
    line = re.sub(';\s*', '；', line)
    line = re.sub('\?\s*', '？', line)
    return line


def my_argparse():
    parser = argparse.ArgumentParser(
        description='load online public posted articles by scraping webpage')
    # parser.add_argument('tag', metavar='str', nargs=1, type=str, help = 'tag=QG|HY|JL|WW|WX')
    # parser.add_argument('tag', metavar='str', type=str, help = 'tag=QG|HY|JL|WW|WX')
    parser.add_argument("dyx", metavar='int', type=int,
                        help='for dyx, 0 for today or -1 for yesterday or -2 for the day before yesterday, etc.')
    parser.add_argument("max", metavar='int', type=int,
                        help='for MAX_PAGES at most 3|4|5|etc to be processed - until hit the prev day')
    return parser.parse_args()


class TeeToFileAndScreen(object):
    def __getattr__(self, key): return None

    def __init__(self, name, mode):
        self.file = open(name, mode)
        self.stdout = sys.stdout
        sys.stdout = self

    def close(self):
        if self.stdout is not None:
            sys.stdout = self.stdout
            self.stdout = None
        if self.file is not None:
            self.file.close()
            self.file = None

    def write(self, data):
        self.file.write(data)
        self.stdout.write(data)

    def flush(self):
        self.file.flush()
        self.stdout.flush()

    def __del__(self):
        self.close()


def show_struct(items):
    for ix, item in enumerate(items):
        print('======== +++ %s +++ ========' % ix)
        spans = item.find_all('span')
        for xp, sp in enumerate(spans):
            print(xp, sp.prettify())
    print('======== --- %s --- ========\n' % ix)
