#!/usr/bin/python3
from datetime import date as date
# from datetime import strptime as strptime
import time
from calendar import day_abbr as dayabbr
import os, sys
import argparse
parser = argparse.ArgumentParser(description='check filesize of daily db dumps')
parser.add_argument("wdy", metavar='int', type=int, help='for weekday abbr name, 1 for Monday, 2 for Tuesday, etc.')
pxday = parser.parse_args().wdy - 1
wday = dayabbr[pxday]
pday = dayabbr[6 if pxday == 0 else pxday - 1] 
print('\n======= compare db dump file size for [%s %s] ======'%(pday, wday))
bakpath = '/etv/BAK/db'
allfiles = os.listdir(bakpath)
def get_dumpfiles(day, allfiles):
    filenames = []
    for f in allfiles:
        if 'dumpz_' + day in f: filenames.append(f)
    return filenames
pfiles = get_dumpfiles(pday, allfiles)
wfiles = get_dumpfiles(wday, allfiles)
def checking():
    print(pfiles)
    print(wfiles)
    print('[%s, %s]'%(os.path.getsize(bakpath + '/' + wfiles[1]), wfiles[1]))
# checking()
def getsz_fmt(pp):
    px = pp / 1024
    if px < 1024: return '%5.1f'%px + 'K'
    py = px / 1024
    if py < 1024: return '%5.1f'%py + 'M'
    pz = py / 1024
    return '%5.1f'%pz + 'G'

def show(wfiles, pfiles):
    arr = []
    for wf in wfiles:
        wz = os.path.getsize(bakpath + '/' + wf)
        # tm = time.ctime(os.path.getmtime(bakpath + '/' + wf))[0:16] getctime = getmtime on *nix OS
        tm = time.ctime(os.path.getctime(bakpath + '/' + wf))[0:16]
        # tm = time.strftime('%a %b %d %H:%M', time.strptime(time.ctime(os.path.getctime(bakpath + '/' + wf)), '%a %b %d %H:%M:%S %Y'))
        wn = wf[10:-7]
        for pf in pfiles:
            if wn not in pf: continue
            else:
                pz = os.path.getsize(bakpath + '/' + pf)
        # print('[%s, %s, %s, %s]'%(wn, wz, pz, wz - pz))
        arr.append('%s %s: %s, %6.1fK'%(tm, wn.rjust(20), getsz_fmt(wz), (wz - pz) / 1000.0))
    arr.sort()
    for line in arr: print(line)
show(wfiles, pfiles)
sys.exit(0)
