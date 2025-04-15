import sys
import requests_html
from bs4 import BeautifulSoup
# sys.path.insert(0, '/sites/projects/load-arts')
print(sys.path)
from ArtItem_ww_test import Art
from DB_test import addDailyDatWW
def getBreakingNews(urlbase, MAX_PAGES, idx, daList, tag, ymd, theday, prvday):
    for page in range(1, MAX_PAGES):
        url = urlbase + '/?page=' + str(page)
        print('getting[%s]'%url)
        session = requests_html.HTMLSession()
        r = session.get(url)
        soup = BeautifulSoup(r.content, 'lxml')
        # print(soup.prettify())
        # tab = soup.find('div', { "id": "newsList" })
        tab = soup.find('div', id='newsList')
        trs = tab.find('table').findAll('tr')
        for tr in trs:
            # print('tr[%s]'%tr)
            tds = tr.find_all('td')
            if len(tds) < 2: continue
            tim = tds[1].text
            # if prvday in tim: break
            if prvday in tim: return idx, daList
            lnk = tds[0].find('a')['href']
            tit = tds[0].text.replace('·', '')
            # print('[%s]\n[%s]\n[%s]\n[%s]'%(tr, tim, tit, lnk))
            # print('[%s][%s]\t\t[%s]'%(tim, lnk, tit))
            if 'javascript' in lnk: continue
            # print('%s\t%s\t%s'%(tit, tim[0:10], lnk))
            print("create WW Art Breaking News", idx, tim, lnk, ' -- ', tit)
            art = Art(idx, lnk, tag, ymd, tit, tim)
            if theday in tim: addDailyDatWW(art)  ## check date again
            idx += 1
            daList.append(art)
    return idx, daList

def getHeadlineNews(urlbase, MAX_PAGES, idx, daList, tag, ymd, theday, prvday):
    session = requests_html.HTMLSession()
    for page in range(1, MAX_PAGES):
        url = urlbase + '/?page=' + str(page)
        r = session.get(url)
        soup = BeautifulSoup(r.content, 'lxml')
        newslist = soup.find('ul', class_="newslist")
        lis = newslist.find_all('li')
        for li in lis:
            # print(li)
            tm = li.find('time')
            if tm == None: continue
            tim = tm.text
            # if prvday in tim: break
            if prvday in tim: return idx, daList
            ax = li.find_all('a', href=True)[1]
            tit = ax.text
            lnk = ax['href']
            if 'javascript' in lnk: continue
            # print('%s\t%s\t%s'%(tit, tim[0:10], lnk))
            print("create WW Art", idx, tim, lnk, ' -- ', tit)
            art = Art(idx, lnk, tag, ymd, tit, tim)
            if theday in tim: addDailyDatWW(art)  ## check date again
            idx += 1
            daList.append(art)
    return idx, daList
