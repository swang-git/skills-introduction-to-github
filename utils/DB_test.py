import sqlalchemy
from datetime import datetime
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from Models import User
from Models import DailyDat
from Models import DailyArt
from Models import HomePage
from Models import ArtItem
import sys, os
# from os.path import dirname
# sys.path.append(os.path.join(dirname(dirname(sys.path[0]))))
from Utils import dlout

# dbconf="mysql://swang:VVKKll11@@@localhost/MyWeb?charset=utf8mb4"
dbconf="mysql://swang:VVKKll11@@@localhost/dev?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)

Session = sessionmaker(bind=engine)
session = Session()
class ArtClass:
    def __init__(self, idx, tag, qid, fid):
        self.idx = idx 
        self.tag = tag
        self.qid = qid
        self.fid = fid

    def __eq__(self, other): 
        if not isinstance(other, ArtClass):
            # don't attempt to compare against unrelated types
            return NotImplemented

        return self.idx == other.idx and self.tag == other.tag and self.qid == other.qid and self.fid == other.fid

def needs_to_get_flws(art):
    rec = session.query(DailyDat.id, DailyDat.flw).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid,flw=art.flw)
    return art.idx == 0 and (rec.scalar() is None or art.flw > rec.flw) # need to get new flws

def art_exists(art):
    rec = session.query(DailyDat).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid,flw=art.flw)
    # if rec.scalar() is None: return False  # need to get every thing flws and art.txt
    if rec.scalar() is None or rec.first().txt == '': return False  # need to get every thing flws and art.txt
    else: return art.flw <= rec.flw        # need to get new flws

def addupdArtItem(art):
    artItem_rec = session.query(ArtItem).filter_by(tag=art.tag,pge=art.pge,qid=art.qid)
    if artItem_rec.scalar() is None:
        dlout(3, 'adding ArtItem', art.tag, art.idx, art.qid, art.aut, art.nwd, art.tim, art.tit)
        artItem = ArtItem(art)
        session.add(artItem)
        session.commit()
    else:
        if int(art.flw) > artItem_rec.first().flw:
            dlout(3, 'upding ArtItem flw', art.flw, artItem_rec.first().flw)
            artItem_rec.update({ 'flw':art.flw })
            session.commit()

def addDailyDatWX(art):
    # dat_rec = session.query(DailyDat.ymd, DailyDat.afz, DailyDat.fsz, DailyDat.ffz, DailyDat.img, DailyDat.vdo).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    dat_rec = session.query(DailyDat).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    if dat_rec.scalar() is None:
        # dlout(3, 'getting getArt()', art.lnk, art.tit)
        if len(art.txt) < 0 or art.txt is None or art.txt == '':
            print('NO_TXT ADD it anyway', art.idx, art.tag, art.qid, art.tit, art.tim, art.lnk)
            # print('NO_TXT NO_ADDING, return', art.tag, art.qid, art.tit, art.tim, art.lnk)
            # return
        dlout(3, 'adding:DailyDat', art.tag, art.ymd, art.qid, art.aut, art.afz, art.img, art.tit)
        # art.status = 'A'
        art.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        dat = DailyDat(art)
        session.add(dat)
        session.commit()
        art.idx = 0
        daily_art = DailyArt(art)
        # addDailyArt(daily_art)
        session.add(daily_art)
        session.commit()
    else:
        ####### -- debugging check -- ########
        dat_obj = dat_rec.one()
        arec = session.query(DailyArt).filter_by(tag=art.tag,qid=art.qid,fid=art.qid)
        print('TesTing:qid[%d] aut[%s] tit[%s] scalar[%s]'%(dat_obj.qid, dat_obj.aut, dat_obj.tit, arec.scalar()))
        if dat_obj.qid > 0:
            print('checkTesTing -- qid:[%d], aut:[%s] tit:[%s]'%(dat_obj.qid, dat_obj.aut, dat_obj.tit))
            # sys.exit(0)
        ######################################
        dat_rec.update({ 'idx':art.idx })
        session.commit()
        # art.afz = dat_rec.one().afz
        # art_rec = session.query(DailyArt).filter_by(tag=art.tag,qid=art.qid,fid=art.qid)
        art_rec = session.query(DailyArt).filter_by(tag=art.tag,qid=art.qid,fid=art.qid)
        # art_rec = DailyArt.query.filter_by(tag=art.tag,qid=art.qid,fid=art.qid)
        if art_rec.scalar() is None:
            print('adding DailyArt PXWX -- Dat exists but no Art', art.idx, art.lnk, art.qid, art.aut, art.tit)
            art.status = 'A'
            art.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            daily_art = DailyArt(art)
            session.add(daily_art)
            session.commit()
        else:
            dbtxt = art_rec.one().txt
            emptyTXT = True if len(dbtxt) == 0 else False
            autIsWX = True if dat_rec.one().aut == 'WX' else False
            if emptyTXT or autIsWX:
                print('checking DailyArt.txt txt:[%s] qry[%s]'%(emptyTXT, art_rec))
                if emptyTXT:
                    # txt_empty_rec = session.query(DailyArt).filter_by(tag=art.tag,qid=art.qid,fid=art.qid).one()
                    txt_empty_rec = art_rec.one()
                    session.delete(txt_empty_rec)
                    session.commit()
                print('upd DailyDat for qid[%s] tit[%s] aut[%s] flw[%s]'%(art.qid, art.tit, art.aut, art.flw))
                datRec = session.query(DailyDat).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid).first()
                datRec.aut = art.aut
                datRec.flw = len(art.flws)
                datRec.afz = art.afz
                artfsz = sum([flw.nwd for flw in art.flws])
                datRec.fsz = artfsz
                datRec.ffz = artfsz + art.afz
                datRec.img = art.img
                datRec.vdo = art.vdo
                session.commit()
                if emptyTXT: addDailyDatWX(art)
                # sys.exit(0)
            else:
                print('Exist:DailyArt PXWX, update idx',art.idx, art.qid, art.lnk, art.tit)
                # art_rec.update({ 'idx':art.idx })
                art_rec.update({ 'idx':0 })
                session.commit()

    if art.flws is not None:
        # print('Add or Upding flws:DailyArt PXWX, idx',art.idx, art.qid, art.tit)
        # checking if there are any new flws
        num_existing_flws = session.query(func.count(DailyArt.idx)).filter_by(tag=art.tag,qid=art.qid).scalar() - 1
        if num_existing_flws >= len(art.flws):
            print('there are no new flws, return ...')
            return

        print('ins_upd flws -- existing flws:', num_existing_flws, ' < ', len(art.flws))
        art.fsz = 0
        new_flws = []
        for flw in reversed(art.flws):
            print('checking FLW for idx', flw.idx, art.tag, art.qid, flw.fid, flw.tim, flw.aut)
            if art.qid == 8670233 and flw.fid == 1120100: continue
            # art_rec = session.query(DailyArt.idx).filter_by(tag=art.tag,qid=art.qid,aut=flw.aut,tim=flw.tim)
            # art_rec = session.query(DailyArt.idx).filter_by(tag=art.tag,idx=flw.idx,qid=art.qid,fid=flw.fid)
            art_rec = session.query(DailyArt.idx).filter_by(tag=art.tag,qid=art.qid,fid=flw.fid)
            print('CHECK_idx', art_rec.scalar(), flw.idx, art.tag, art.qid, flw.fid, flw.tim, flw.aut)
            # sys.exit(0)
            try:
                if art_rec.scalar() is None:
                    new_flws.append(flw)
                else:
                    try:
                        print('upding flw on DailyArt', art.tag, art_rec.one().idx, flw.idx, art.qid, flw.fid, flw.aut, flw.tim, flw.txt)
                    except UnicodeEncodeError:
                        flw.txt = flw.txt.encode('utf-16', 'surrogatepass').decode('utf-16') # converting emojis
                        print('\n=X= UnicodeEncodeError upd flw', flw.idx, art.qid, flw.fid, flw.aut, flw.tim, flw.txt)
                    art_rec.update({ 'idx':flw.idx,'fid':flw.fid })
                    session.commit()
            except sqlalchemy.orm.exc.MultipleResultsFound:
                print('=X= MultipleResultsFound for ', art.tag, art.qid, flw.fid, 'skip...')
                continue

            art.fsz += int(flw.nwd)
            if flw.img is not None and art.img is not None: art.img += flw.img
            if flw.vdo is not None and art.vdo is not None: art.vdo += flw.vdo

        art.ffz = art.afz + art.fsz
        art.flw = len(art.flws)
        dat_rec.update({ 'idx':art.idx,"flw":art.flw, "fsz":art.fsz,"ffz":art.ffz,"img":art.img,"vdo":art.vdo })
        session.commit()

        for flw in new_flws:
            # if art.qid == 8469127 and flw.fid == 1064200: continue
            # if art.qid == 8471785 and flw.fid == 1080200: continue
            # if art.qid == 8481898 and flw.fid == 1065800: continue
            # if art.qid == 8498440 and flw.fid == 1124600: continue
            # if art.qid == 8787110 and flw.fid == 1155600: continue
            # if art.qid == 8803245 and flw.fid == 1084600: continue
            # if art.qid == 8808799 and flw.fid == 1091500: continue
            # if art.qid == 8841926 and flw.fid == 1063600: continue
            # if art.qid == 8873111 and flw.fid == 1191700: continue
            # if art.qid == 8906360 and flw.fid == 1112700: continue
            try:
                print('adding new flw to DailyArt', art.tag, flw.idx, art.qid, flw.fid, flw.aut, flw.tim, flw.txt)
            except UnicodeEncodeError:
                flw.txt = flw.txt.encode('utf-16', 'surrogatepass').decode('utf-16') # converting emojis
                print('\n=X= UnicodeEncodeError add flw', flw.idx, art.qid, flw.fid, flw.aut, flw.tim, flw.txt)
            flw_art = DailyArt(flw)
            flw_art.status = 'A'
            flw_art.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            session.add(flw_art)
            session.commit()

def addDailyDatWW(art):
    # if len(art.txt) < 0 or art.txt is None or art.txt == '':
    #     print('NO_TXT NO_ADDING, return', art.tag, art.qid, art.tit, art.tim, art.lnk)
    #     return
    # dat_rec = session.query(DailyDat.aut, DailyDat.ymd, DailyDat.afz, DailyDat.fsz, DailyDat.ffz, DailyDat.img, DailyDat.vdo).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    dat_rec = session.query(DailyDat).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    # print('=== dat_rec[%s]\n'%dat_rec.one().ymd)
    print('=== dat_rec[%s]\n'%dat_rec.one().idx)
    sys.exit(0)
    if dat_rec.scalar() is None:
        print('getting getArt()', art.lnk, art.tit)
        art.getArt()

        if len(art.txt) < 0 or art.txt is None or art.txt == '':
            print('NO_TXT ADD it anyway', art.idx, art.tag, art.qid, art.tit, art.tim, art.lnk)
            # print('NO_TXT NO_ADDING, return', art.tag, art.qid, art.tit, art.tim, art.lnk)
            # return

        # dlout(3, 'adding:DatWW', art.lnk, art.tag, art.ymd, art.qid, art.aut, art.afz, art.img, art.tit.decode(gb2312, ignore))
        dlout(3, 'adding:DatWW', art.lnk, art.tag, art.ymd, art.qid, art.aut, art.afz, art.img, art.tit)
        # sys.exit(0)
        dat = DailyDat(art)
        session.add(dat)
        session.commit()
        daily_art = DailyArt(art)
        # daily_art.idx = 0
        addDailyArt(daily_art)
    else:
        dat_rec.update({ 'idx':art.idx })
        session.commit()
        art.afz = dat_rec.one().afz
        art_rec = session.query(DailyArt.idx, DailyArt.txt).filter_by(tag=art.tag,qid=art.qid,fid=art.qid)
        if art_rec.scalar() is None or art_rec.first().txt == '' or dat_rec.first().aut == 'WW':
            print('adding Art WW -- exist Dat but no Art', art.idx, art.lnk, art.qid, art.aut, art.tit)
            art.getArt()
            if dat_rec.first().aut == 'WW':
                dat_rec.update({ 'aut':art.aut, 'img':art.img, 'vdo':art.vdo, 'afz':art.afz, 'ffz':art.ffz })
                session.commit()
            daily_art = DailyArt(art)
            # daily_art.idx = 0
            addDailyArt(daily_art)
        else:
            print('Exist:Art WW, update idx',art.idx, art.qid, art.lnk, art.tit)
            art_rec.update({ 'idx':art.idx })
            session.commit()

    if art.flws is not None:
        art.fsz = 0
        for flw in art.flws:
            flw_art = DailyArt(flw)
            addDailyArt(flw_art)
            art.fsz += int(flw.nwd)
            if flw.img is not None and art.img is not None: art.img += flw.img
            if flw.vdo is not None and art.vdo is not None: art.vdo += flw.vdo

        art.ffz = art.afz + art.fsz
        art_rec.update({ 'idx':art.idx,"flw":art.flw, "fsz":art.fsz,"ffz":art.ffz,"img":art.img,"vdo":art.vdo })
        session.commit()

def addDailyDatHY(art):
    if len(art.txt) < 0 or art.txt is None or art.txt == '':
        print('NO_TXT NO_ADDING, return', art.tag, art.qid, art.tit, art.tim, art.lnk)
        return
    art_rec = session.query(DailyDat.ymd, DailyDat.afz, DailyDat.fsz, DailyDat.ffz, DailyDat.img, DailyDat.vdo).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    if art_rec.scalar() is None:
        dlout(4, 'adding:DatHY', art.tag, art.ymd, art.qid, art.aut, art.afz, art.img, art.tit)
        dat = DailyDat(art)
        session.add(dat)
        session.commit()
        dlout(4, 'BBB adding daily_art', art.qid)
        daily_art = DailyArt(art)
        daily_art.idx = 0
        addDailyArt(daily_art)
        dlout(4, 'CCC adding daily_art', art.qid)
    else:
        art_rec.update({'idx':art.idx})
        session.commit()
        art.afz = art_rec.one().afz
        if session.query(DailyArt.idx).filter_by(tag=art.tag,qid=art.qid,fid=art.qid).scalar() is None:
            print('adding Art', art.flw, art.qid, art.aut, art.tit)
            # art.get_txt()
            daily_art = DailyArt(art)
            daily_art.idx = 0
            addDailyArt(daily_art)
            print('Exist:DatHY but no Art ', art.flw, art.qid, art.aut, art.tit)

    if art.flws is not None:
        art.fsz = 0
        for flw in art.flws:
            flw_art = DailyArt(flw)
            addDailyArt(flw_art)
            art.fsz += int(flw.nwd)
            # art.flw += 1
            if flw.img is not None and art.img is not None: art.img += flw.img
            if flw.vdo is not None and art.vdo is not None: art.vdo += flw.vdo

        art.ffz = art.afz + art.fsz
        art_rec.update({ 'idx':art.idx,"flw":art.flw, "fsz":art.fsz,"ffz":art.ffz,"img":art.img,"vdo":art.vdo })
        session.commit()
def addDailyDatQG(art):
    if art == 'testing':
        rec = session.query(DailyDat).filter_by(tag='PXZZ', ymd='20171016', qid='2299791').one()
        print(rec.tag, rec.ymd, rec.tit, rec.afz)
        # session.add(rec)
        # rec.update({'tag': 'PXHX'})
        # session.commit()
        return

    # art_rec = session.query(DailyDat.idx).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    # art_rec = session.query(DailyDat.ymd, DailyDat.afz, DailyDat.fsz, DailyDat.ffz, DailyDat.img, DailyDat.vdo).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    art_rec = session.query(DailyDat.afz, DailyDat.fsz, DailyDat.ffz, DailyDat.img, DailyDat.vdo).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    # art_rec = session.query(DailyDat.fsz, DailyDat.ffz, DailyDat.img, DailyDat.vdo).filter_by(tag=art.tag,ymd=art.ymd,qid=art.qid)
    # row = art_rec.one()
    if art_rec.scalar() is None:
        art.get_txt()
        if art.txt is None or len(art.txt) < 0 or art.txt == '无内容':
            print('NO_TXT NO_ADDING, return', art.tag, art.qid, art.tit, art.tim, art.lnk)
            # return

        dlout(1, 'adding:DAT', art.tag, art.ymd, art.lnk, art.qid, art.aut, art.afz, art.img, art.tit)
        dat = DailyDat(art)
        session.add(dat)
        session.commit()
        daily_art = DailyArt(art)
        daily_art.idx = 0
        addDailyArt(daily_art)
    else:
        art.afz = art_rec.one().afz
        if session.query(DailyArt.idx).filter_by(tag=art.tag,qid=art.qid,fid=art.qid).scalar() is None:
            print('adding Art', art.flw, art.qid, art.aut, art.tit)
            art.get_txt()
            daily_art = DailyArt(art)
            daily_art.idx = 0
            dlout(3, 'Exist:DAT no Art, adding art', art.lnk, art.flw, art.qid, art.aut, art.tit)
            addDailyArt(daily_art)

    if art.flws is not None:
        art.fsz = 0
        for flw in art.flws:
            if len(flw.txt.strip()) <= 0:
                print('EMPTY flw.txt for:', art.qid, flw.fid, art.tit, flw.txt)
                continue   ##_ skip the empty flw.txt
            addDailyArtQG(flw, art_rec)
            art.fsz += len(flw.txt.strip())
            # if flw.img is not None and art.img is not None: art.img += flw.img
            # if flw.vdo is not None and art.vdo is not None: art.vdo += flw.vdo

        # art.ffz = art.afz + art.fsz
        dlout(3, 'UPDATING flw, idx', art.idx, "flw", len(art.flws), "fsz", art.fsz)
        all_dat_rec = session.query(DailyDat.afz, DailyDat.fsz, DailyDat.ffz, DailyDat.img, DailyDat.vdo).filter_by(tag=art.tag,qid=art.qid)
        all_dat_rec.update({ 'idx':art.idx, "flw":len(art.flws), "fsz":art.fsz })
        # art_rec.update({ 'idx':art.idx, "flw":art.flw })
        # art_rec.update({ 'idx':art.idx, "flw":art.flw, "ffz":art.ffz })
        # art_rec.update({ 'idx':art.idx,"flw":art.flw, "fsz":art.fsz,"ffz":art.ffz,"img":art.img,"vdo":art.vdo })
        session.commit()

def upd_art_flw_count(art):
    art_rec = session.query(DailyDat.fsz, DailyDat.afz).filter_by(tag=art.tag, qid=art.qid)
    flw_all_nwd = art_rec.order_by(desc(DailyDat.fsz))
    flw_total_nwd = flw_all_nwd.first().fsz
    new_ffz = flw_all_nwd.first().afz + flw_total_nwd
    flw_cnt = flw_rec = session.query(func.count(DailyArt.id)).filter_by(tag=art.tag,qid=art.qid).scalar() - 1
    # print('updating flw count for', art.ymd, art.tag, art.qid, art.tim, flw_total_nwd)
    art_rec.update({ 'flw': flw_cnt, 'fsz':flw_total_nwd, 'ffz': new_ffz })
    session.commit()

def get_art_flw_count(art):
    flw_rec = session.query(func.count(DailyArt.id)).filter_by(tag=art.tag,qid=art.qid)
    cnt = flw_rec.scalar()
    # dlout(3, 'art_flw_count for ding art if ', cnt, '<', int(art.flw) + 1, 'needing get MORE for', art.tit )
    art_flw_cnt = str(int(art.flw) + 1).ljust(3)
    art_flw_cnt_db = str(cnt).rjust(3)
    # print('art_flw_cnt for ding art if', '{0:3d}'.format(cnt), '<', art_flw_cnt, 'needing get MORE for', art.tit)
    return cnt
def addDailyArtQG(flw, art_rec):
    e1 = ArtClass(1, 'PXJL', '172977349', '172977471')
    e2 = ArtClass(1, 'PXJL', '172742339', '172739475')
    e3 = ArtClass(1, 'PXJL', '172737498', '172739475')
    ex = ArtClass(1, 'PXJL', flw.qid, flw.fid)
    # if flw.tag == 'PXJL' and flw.qid in qidList and flw.fid in fidList and (flw.idx == 1 or flw.idx == 2):
    if ex == e3:
        print('SKIP_insert for duplicated (idx-qid-fid) for ', flw.tag, flw.idx, flw.qid, flw.fid)
        return
    flw_rec = session.query(DailyArt.id, DailyArt.nwd, DailyArt.txt).filter_by(tag=flw.tag,qid=flw.qid,fid=flw.fid)
    if flw_rec.scalar() is None:
        # if flw_rec.scalar() is None or flw_rec.first().txt == '':
        # if int(flw.nwd) - 2 > len(flw.txt) or len(flw.txt) == 0 or int(flw.nwd) == 0:
        if int(flw.nwd) - 2 > len(flw.txt) or len(flw.txt) == 0:
            # print('DB_debugging A0 flw.txt for short flw.txt', flw.qid, flw.fid, flw.nwd, '['+flw.txt+']\n')
            [afz, fsz, ffz, img, vdo] = art_rec.one()
            # print('DB_debugging AA', flw.qid, flw.fid, flw.nwd, flw.idx, '['+flw.txt+']')
            txtbak = flw.txt
            flw.get_flw_txt()
            if len(flw.txt) < len(txtbak): 
                # print('DB_debugging AB', flw.qid, flw.fid, flw.nwd, '['+flw.txt+']')
                if len(flw.txt) == 0: flw.txt = txtbak
                # print('DB_debugging AC', flw.qid, flw.fid, flw.nwd, '['+flw.txt+']')
            flw.nwd = len(flw.txt)
            fsz += flw.nwd
            ffz = afz + fsz
            img += flw.img
            vdo += flw.vdo
            dlout(1,'Get MORE TEXT - get_txt for flw.nwd', flw.qid, flw.fid, 'nwd', int(flw.nwd), 'img:', flw.img, 'vdo:', flw.vdo)
            # if flw.img is not None and img is not None: art.img += flw.img
            # if flw.vdo is not None and art.vdo is not None: art.vdo += flw.vdo
            art_rec.update({ "fsz":fsz,"ffz":ffz,"img":img,"vdo":vdo })
            # art_rec.update({ 'idx':art.idx,"flw":art.flw, "fsz":art.fsz,"ffz":art.ffz,"img":art.img,"vdo":art.vdo })
            session.commit()
            # print('get more text and adding:ArtQG', flw.qid, flw.fid, flw.nwd, flw.get_html_url(), flw.txt, flw.img, flw.vdo)
            print('adding:ArtQG to DB', flw.qid, flw.fid, flw.nwd, flw.get_html_url(), flw.txt, flw.img, flw.vdo)
        if (len(flw.txt) == 0 or flw.txt == '' or flw.txt is None):
            print('got empty flw.txt', flw.qid, flw.fid, 'flw.txt=['+flw.txt+']', len(flw.txt), flw.nwd, 'return ...get it next round')
            return
            # sys.exit(-1)
        art_flw = DailyArt(flw)
        art_flw.status = 'A'
        session.add(art_flw)
        session.commit()
    else:
        dlout(5, 'Exist:ArtQG, upd', flw.qid, flw.fid, flw.txt, flw.aut)
        flw.nwd = flw_rec.one().nwd
        flw_rec.update({ 'idx':flw.idx })  ##,"":dat.,"":dat.,)
        # art_rec.update({ 'idx':art.idx,"flw":art.flw,"afz":art.afz,"fsz":art.fsz,"ffz":art.ffz,"img":art.img,"vdo":art.vdo })  ##,"":dat.,"":dat.,)
        session.commit()

def addDailyDat(dat):
    rec = session.query(DailyDat).filter_by(tag=dat.tag,ymd=dat.ymd,qid=dat.qid)
    if rec.scalar() is None:
        print('adding:DAT', dat.qid, dat.tit)
        session.add(dat)
        session.commit()
    else:
        print('Exist:DAT, upd', dat.flw, dat.qid, dat.aut, dat.tit)
        rec.update({ 'idx':dat.idx,"flw":dat.flw,"afz":dat.afz,"fsz":dat.fsz,"ffz":dat.ffz,"img":dat.img,"vdo":dat.vdo })  ##,"":dat.,"":dat.,)
        session.commit()

def addDailyArt(art):
    flw = session.query(DailyArt.id, DailyArt.idx, DailyArt.txt).filter_by(tag=art.tag,qid=art.qid,fid=art.fid)
    if flw.scalar() is None:
        art.status = 'A'
        art.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        # if len(art.txt) >= 0:
        if (art.tag == 'PXHY' and art.nwd is not None and int(art.nwd) > 0) or (art.txt is not None and len(art.txt) >= 0):
            print('adding:DailyArt', art.tag, art.qid, art.fid, art.nwd, art.aut, art.tim, art.txt)
            session.add(art)
            session.commit()
        else: print('NO_adding:Art, NO_TXT', art.tag, art.qid, art.fid, art.nwd, art.tim)
    elif flw.first().txt == '':
        dlout(3, 'Exist:Art but empty_txt upd txt', art.idx, art.tag, art.qid, art.fid, art.txt[0:40], art.aut)
        flw.update({ "txt":art.txt})
        session.commit()
    else:
        dlout(3, 'Exist:Art and upd idx', art.idx, art.tag, art.qid, art.fid, art.txt[0:40], art.aut)
        # flw.update({"idx": flw.idx})
        flw.update({"idx": art.idx})
        session.commit()

def addHomePage(dat):
    hop = session.query(HomePage).filter_by(tag=dat.tag,ymd=dat.ymd)
    if hop.scalar() is not None:
        dlout(3, 'Exist:HomePage and updating', dat.tag, dat.ymd, dat.tit)
        hop.update({"cnt": dat.cnt, 'flw':dat.flw, 'fsz':dat.fsz, 'afz':dat.afz, 'ffz':dat.ffz, 'img':dat.img, 'vdo':dat.vdo})
        session.commit()
    else:
        dlout(2, 'adding:HomePage', dat.tag, dat.ymd, dat.tit)
        session.add(dat)
        session.commit()
def getCount(tag, ymd):
    cnt_rec = session.query(func.count(DailyDat.id)).filter_by(tag=tag,ymd=ymd)
    return cnt_rec.scalar()
