#!/usr/bin/python
import sys, os, re, pytz
from glob import glob
from datetime import datetime, timedelta, tzinfo, UTC
from time import gmtime, strftime, tzset

# from pathlib import Path
# import calendar
# import sqlalchemy
# from sqlalchemy import create_engine, func, desc
# from sqlalchemy.orm import sessionmaker
# from Models import recorded, channel_ALL, channel

def getLastMythBackendLogFile():
    # print('-fn-getLastMythBackendLogFile')
    pth = '/var/log/mythtv/mythbackend'
    sfl = sorted(glob(pth+"*.log"), key=os.path.getmtime)
    # sfl = sorted(glob(pth+"*.log"), key=os.path.getsize)
    # for f in sfl: print(f)
    return sfl[-1]

def getCommflagLines(filename):
    lines = []
    for line in open(filename, 'r'):
        if re.search('Commflag_', line):
            lines.append(line)
    return lines

print('========= MythCommflagCheck ==========')
mlogfile = getLastMythBackendLogFile()
print("-CK- the log file is:[%s]"%mlogfile)
lines = getCommflagLines(mlogfile)

utctz = pytz.timezone('UTC')
tz = pytz.timezone('America/New_York')
dtfmt = '%Y-%m-%d %H:%M:%S' 
cmm = []
for line in lines:
    if line == '' or re.search(' E ', line) or re.search('Broken filename', line): continue
    # print(line)
    x = line.split('Starting for ')
    xx = x[0].split(' ')
    xa = x[1].split(' recorded ')
    nm = xa[0].replace('"', '')
    # print(xa)
    xc = xa[1]
    ch = xc.split(' ')[2]
    xt = xc.split(' ')[4]
    sx = re.sub('[T|Z]', ' ', xt.strip()).strip()
    # print('====== utc time[%s]'%sx)
    utcdt = datetime.strptime(sx, dtfmt)
    utctm = utcdt.replace(tzinfo=utctz)
    utctx = utctm.astimezone(tz)
    st = utctx.strftime(dtfmt)
    dt = xx[0] + ' ' + xx[1].split('.')[0]
    cmm.append([dt, ch + ' ' + st + ' ' + nm])

for i, x in enumerate(cmm):
    d1 = x[0]
    nm = re.sub(r':00\s+', ' ', x[1])
    d2 = datetime.now(tz).strftime(dtfmt)
    try: d2 = cmm[i+1][0]
    except IndexError: pass
    df = datetime.strptime(d2, dtfmt) - datetime.strptime(d1, dtfmt)
    n = 1
    if i + 1 >= 10: n = 0
    idx = f'{" "*n}' + str(i+1)
    print("%s: %s [%s] %s"%(idx, d1, df, nm))

sys.exit(0)


limitNum = 20
if len(sys.argv) > 1: limitNum = int(sys.argv[1])
print('==== starting link-myth-title.py limitNum:%s -- using sqlchemy version:%s'%(limitNum,sqlalchemy.__version__))

dbconf="mysql://mythtv:mythtvVVKK0#@localhost/mythconverg?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

from datetime import datetime
import time

def minuteDiff(starttime, endtime): # duration in minutes
    fmt = '%Y-%m-%d %H:%M:%S'
    d1 = datetime.strptime(str(starttime), fmt)
    d2 = datetime.strptime(str(endtime), fmt)

    # Convert to Unix timestamp
    d1_ts = time.mktime(d1.timetuple())
    d2_ts = time.mktime(d2.timetuple())
    return int((d2_ts-d1_ts) / 60)

def getFullpath(basename):
    if   os.path.exists('/home/swang/atv/' + basename): return '/home/swang/atv/' + basename
    elif os.path.exists('/home/swang/btv/' + basename): return '/home/swang/btv/' + basename
    elif os.path.exists('/home/swang/ctv/' + basename): return '/home/swang/ctv/' + basename
    elif os.path.exists('/home/swang/dtv/' + basename): return '/home/swang/dtv/' + basename

def getFullDirname(basename):
    if   os.path.exists('/home/swang/atv/' + basename): return '/home/swang/atv/'
    elif os.path.exists('/home/swang/btv/' + basename): return '/home/swang/btv/'
    elif os.path.exists('/home/swang/ctv/' + basename): return '/home/swang/ctv/'
    elif os.path.exists('/home/swang/dtv/' + basename): return '/home/swang/dtv/'
    
def cleanupRecording():
    for idx, rec in enumerate(session.query(recorded).filter(recorded.watched == 2).order_by(recorded.starttime.desc())):
        recordedfile = getFullpath(rec.basename)
        if recordedfile == None:
            print('No file found in file system for [%s] [%s] [%s] delete record in recorded table'%(rec.basename, rec.starttime, rec.title))
            session.query(recorded).filter(recorded.basename == rec.basename).delete(synchronize_session=False)
            continue
        basename = rec.basename
        dirname = getFullDirname(basename)
        for path in Path(dirname).rglob(basename+'*'):
            print("removing [%s]"%(dirname + path.name))
            try:
                os.remove(dirname + path.name)
            except OSError as errmsg:
                print(errmsg)

        if len(rec.title) > 0 and recordedfile != None: print("Removing [%s] [%s]"%(recordedfile, rec.title))
        session.query(recorded).filter(recorded.basename == rec.basename).delete(synchronize_session=False)

########### main ###########
cleanupRecording()

misfiles = []
videodir = '/home/swang/vlnk/'
# videodir = '/sites/tmp/vlnk/'
# files = os.walk(videodir)
files = os.listdir(videodir)
from datetime import date
today = str(date.today()) + '%'
for file in files:
    file = videodir + file
    # print('=== remove symbolic link [%s]'%file)
    os.unlink(file)

# for idx, rec in enumerate(session.query(recorded).order_by(recorded.starttime)):
# for idx, rec in enumerate(session.query(recorded).filter(recorded.starttime.like(today)).order_by(recorded.starttime)):
# for idx, rec in enumerate(session.query(recorded).filter(recorded.watched == 0, recorded.chanid < 16804).order_by(recorded.starttime.desc()).limit(limitNum)):
for idx, rec in enumerate(session.query(recorded).filter(recorded.watched == 0).order_by(recorded.starttime.desc()).limit(limitNum)):
    # stm = datetime.strftime(rec.starttime + timedelta(hours=-5), '%m-%d %H:%M') # winter time
    delta = calendar.timegm(datetime.now(tz=UTC).timetuple()) - calendar.timegm(datetime.now().timetuple())
    stm = datetime.strftime(rec.starttime + timedelta(hours=-round(delta/60/60)), '%m-%d %H:%M')  # work with daylight time saving
    durationMinutes = str(minuteDiff(rec.starttime, rec.endtime))

    recordedfile = getFullpath(rec.basename)
    if recordedfile == None:
        if idx + 1 < limitNum: print('idx[%d]delete record row for no recorded file with basename = [%s]'%(idx, rec.basename)) 
        session.query(recorded).filter(recorded.basename == rec.basename).delete(synchronize_session=False)
        continue

    channux = session.query(channel_ALL).filter_by(chanid = rec.chanid).first()
    if channux == None: 
        channux = session.query(channel).filter_by(chanid = rec.chanid).first()
        if channux == None: 
            print('No such chanid[%s] [%s] in channel_ALL' %(rec.chanid, rec.title))
            continue
    channum = channux.channum.replace('_', '.')
    # chan = session.query(channel).filter_by(chanid = rec.chanid).first()
    # if chan == None: chan = session.query(channel_old_id).filter_by(chanid = rec.chanid).first()
    # channum = chan.channum
    nsp = 1
    if len(durationMinutes) == 2: durationMinutes = f'{" "*nsp}' + durationMinutes
    if len(channum) == 3: channum = f'{" "*nsp}' + channum
    fsz = format(rec.filesize / 1024 / 1024 / 1024, '.2f') + 'G'
    if len(fsz) == 5: fsz = f'{" "*nsp}' + fsz
    tit = re.sub('/', '-', rec.title.strip())
    # chanid = str(rec.chanid)[1:3] + '-' + str(rec.chanid)[4:]
    chanid = str(rec.chanid)
    head = str(idx+1)
    if idx+1 < 10: head = '00' + str(idx+1)
    elif idx+1 < 100: head = '0' + str(idx+1)
    dst = channum + ' ' + stm + ' ' + fsz + ' ' + durationMinutes + 'm 《' + tit + '》'
    # dst = head + ' ' + channum + ' ' + stm + ' ' + fsz + ' 《' + tit + '》'
    # dst = head + ' ' + channum + ' ' + stm + ' [' + tit + '] ' + fsz
    # dst = head + ' ' + chanid + ' ' + channum + ' ' + stm + ' [' + tit + '] ' + fsz
    # dst = re.sub('[(|)]', '', dst).replace(' ', '_').replace('"', '').replace('&', 'n').replace('\'', '’')
    dst = re.sub('[(|)]', '', dst).replace('"', '').replace('&', 'n').replace('\'', '’')
    dst += '.ts'
    # print(idx+1, recordedfile, ' ==> ', dst)
    os.chdir(videodir)
    print(head, recordedfile, ' ==> ', dst)
    os.symlink(recordedfile, dst)
for misf in misfiles: print(misf)
# print('==== ended link-myth-title.py')

# cleanupRecording()
sys.exit(0)
