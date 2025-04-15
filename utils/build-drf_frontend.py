#!/usr/bin/python3
import sys
import os
import shutil
from datetime import datetime

# print(len(sys.argv))
if len(sys.argv) != 3:
    print('Please provide [ devx || prod ] [ project name(like article watcher, etc.) ]\n')
    sys.exit(0)

env = sys.argv[1]
app = sys.argv[2]
home_dir = "/home/swang/"
frontend_dir = os.path.join(home_dir, 'projects/drf_frontend/')
frontend_dir_app = frontend_dir + app + '/'
src_dir = frontend_dir_app + 'dist/spa/'
dst_dir = home_dir + 'sites/' + env + '/django/' + app + '/'

print('\n == chdir to', frontend_dir_app)
os.chdir(frontend_dir_app)

build_options = "--debug"
if env == "prod": build_options = ""
##if env == "prod" or env == "golf": build_options = ""

print('\n == building ' + app + ' for ' + env + ' with options ' + build_options + ' ......')
exit_code = os.WEXITSTATUS(os.system("quasar build " + build_options))
if exit_code != 0:
    print('\n =F= quasar build -d failed, exit ......')
    sys.exit(-1)

now_str=datetime.now().strftime('%Y-%m-%d_%H_%M')
backup_dir = home_dir + 'tmp/backups/django_' + env + '/' + now_str + '-' + app
print('\n == back up ' + app + ' to ' + backup_dir)
shutil.move (dst_dir, backup_dir)
print('\n == copy all ' + src_dir + 'js | css| fonts| img | statics |index.html files to ' + dst_dir)
shutil.copytree(src_dir, dst_dir)
sys.exit(0)
