import sys, re, time
from urllib import request
from bs4 import BeautifulSoup
# from Utils import Img
from ArtItem import Art

def get_artList(pre_sit, bas_url, tag, ymd, theday, MAX_PAGES):
    artList = []
    for pageIdx in range(1, MAX_PAGES):
        url = pre_sit + '?page=' + str(pageIdx)
        print('processing page: ' + url)
        # session = requests_html.HTMLSession()
        # soup = BeautifulSoup(r.content, 'lxml')
        # r = session.get(url)
        page = request.urlopen(url)
        # soup = BeautifulSoup(page,  "html5lib")
        soup = BeautifulSoup(page,  "html.parser")
        # print(soup.prettify())
        uls = soup.findAll('div', class_='list')[0].findAll('ul')
        print(uls[0])
        idx = 0
        for ul in uls:
            items = ul.findAll('li')
            # print(items)
            # sys.exit(0)
            for item in items:
                tim = item.findAll('span')[0].getText()
                # print('---- idx: %s tim: %s'%(idx, tim))
                if tim == '0000-00-00': tim = '2020-03-19'
                # print('----', tim, theday, item);print(time.strptime(tim, '%Y-%m-%d'), '<'); print(time.strptime(theday, '%Y/%m/%d'))
                if time.strptime(tim, '%Y-%m-%d') > time.strptime(theday, '%Y/%m/%d'): continue
                if time.strptime(tim, '%Y-%m-%d') < time.strptime(theday, '%Y/%m/%d'):
                    print('BREAK here for hitting prev day:', tim, theday)
                    return [artList, url]
                ank = item.findAll('a', href=True)[0]
                lnk = bas_url + ank['href']
                # tit = item.get_text()
                tit = ank.get_text()
                art = Art(idx, lnk, tag, ymd, tit, tim)
                art.bas_url = bas_url
                art.status = 'A'
                idx += 1
                artList.append(art)
                # print(tim, lnk, tit)
