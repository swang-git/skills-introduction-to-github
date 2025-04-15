from Utils import get_cn_tit;
from Utils import get_cn_dat;
from Models import HomePage
from DB import addHomePage, getCount
from datetime import datetime

def updHomePage(datList):
    cnt = len(datList)
    if (cnt == 0): return
    x = datList[0]
    tag = x.tag
    ymd = x.ymd
    tit = get_cn_tit(tag) + ' ' + get_cn_dat(ymd)
    pix = 0
    vdo = 0
    flw = 0
    fsz = 0
    afz = 0
    ffz = 0

    for a in datList:
        fsz += a.fsz
        pix += a.img
        vdo += a.vdo
        afz += a.afz
        ffz += a.fsz + a.afz
        if a.flw is not None: flw += int(a.flw)

    ## save or update HomePage
    cnt = getCount(tag, ymd)
    print('COUNT for', tag, ymd, cnt)
    if cnt == 0: return
    hop = HomePage(tag=tag, ymd=ymd, cnt=cnt, tit=tit, flw=flw, fsz=fsz, afz=afz, ffz=ffz, img=pix, vdo=vdo, addby='PY', addtm=datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
    # hop = {}
    # hop.tag=tag
    # hop.ymd=ymd
    # hop.cnt=cnt
    # hop.tit=tit
    # hop.flw=flw
    # hop.fsz=fsz
    # hop.afz=afz
    # hop.ffz=ffz
    # hop.img=pix
    # hop.vdo=vdo
    # hop.addby='PY3'   
    # # hop = HomePage(tag, ymd, cnt, tit, flw, fsz, afz, ffz, pix, vdo, 'PY3')
    # hp = HomePage(hop)
    addHomePage(hop)
