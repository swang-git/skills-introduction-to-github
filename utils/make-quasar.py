#!/Users/swang/myenv/bin/python
import sys
import os
import shutil
from datetime import datetime
import argparse
from random import randint
from sty import fg, bg, rs, ef
parser = argparse.ArgumentParser(description='build quasar application')
parser.add_argument('environment', nargs='?', default='devx', metavar='environment', type=str, help='build application for devx or prod, default to devx')
parser.add_argument('programName', nargs='?', default='apps', metavar='programName', type=str, help='applications, one of [apps|arts|expense|shopping|memo|reminder|golf|watcher] default to apps')
# parser.add_argument('modern', default=True, metavar='modern', type=bool, nargs='?', help='--modern option support ES6+, if this argument not given it will be legacy build otherwise modern build. NOTE: not support Microsoft IE11')
args = parser.parse_args()
verstr = '普林斯顿中美高尔夫俱乐部王胜利'
# print(args); sys.exit(0)
env = args.environment
ver = verstr[randint(0, 14)]
app = args.programName
# modern = args.modern
appx = ' ' + app + ' '
# print("build quasar application '%s' in '%s'" %(app, env))

app_js =  os.path.join(app, 'js')
app_fonts =  os.path.join(app, 'fonts')
##home_dir = "/home/swang/"
home_dir = "/Users/swang/sites/"
##home_dir = "/sites/"
sites = "/Users/swang/sites/"
##sites = "/home/sites/"
app_dir = home_dir + "projects/" + app
dist_dir = app_dir + "/dist/spa"
pub_dir = sites + env + "/public/"
app_dist_dir = sites + env + "/public/" + app
view_file = os.path.join(sites, env) + '/resources/views/' + app + '.blade.php'

def fgcolor(color, str): return fg(color) + str + rs.fg
def bgcolor(color, str): return bg(color) + str + rs.bg
def inverse(str): return ef.inverse + str + rs.inverse
def boldit(str): return ef.bold + str + rs.bold_dim
def underline(str): return ef.u + str + rs.u
def blinkit(str): return ef.blink + str
# def blinkit(str): return ef.blink + str + rs.blink

os.chdir(app_dir)

modern=0
build_options = " --debug"
if env == "prod": build_options = ""
# if modern: build_options += " --modern"

ptxt = fgcolor('yellow', '║') + ' Build ' + bgcolor('yellow', fgcolor('da_blue', underline(boldit(appx)))) + ' in ' + env + ' with OPTIONS' + build_options
def print_header(ptxt):
    pad = 39 + len(app) if env == 'devx' else 31 + len(app)
    if modern: pad += 9
    print(fgcolor('yellow', '╔'), end='')
    print(fgcolor('yellow', pad*'═'), end='')
    print(fgcolor('yellow', '╗'))
    print(ptxt, fgcolor('yellow', '║'))
    print(fgcolor('yellow', '╚'), end="")
    print(fgcolor('yellow', pad*'═'), end='')
    print(fgcolor('yellow', '╝'))
def print_tailer(ptxt):
    pad = env == 'devx' and 71 + len(app) or 51 + len(app)
    if modern: pad += 9
    print(fgcolor('green', '╔'), end='')
    print(fgcolor('green', pad*'═'), end='')
    print(fgcolor('green', '╗'))
    print(ptxt, fgcolor('green', '║'))
    print(fgcolor('green', '╚'), end="")
    print(fgcolor('green', pad*'═'), end='')
    print(fgcolor('green', '╝'))
print_header(ptxt)
# ptxt = '║ App ' + bgcolor('li_green', fgcolor('da_blue', underline(boldit(appx)))) + ' built ' + fg.green + 'SUCCESSFULLY in ' + fg.rs + env + ' with OPTIONS ' + build_options
# print_tailer(ptxt)
# sys.exit(0)

# PRODUCT_NAME will used in quasar.conf.js to inject app.XXXXX.js/css and vendor.XXXXX.js/css etc. into index.template.html for index.html copied to public/PRODUCT_NAME(i.e. golf) folder
buildstr = "PRODUCT_NAME=" + app + " PRODUCT_VER=" + ver + " quasar build " + build_options
exit_code = os.WEXITSTATUS(os.system(buildstr))
# exit_code = os.WEXITSTATUS(os.system("PRODUCT_NAME={app} quasar build " + build_options))
if exit_code != 0:
    print('quasar build -d "' + app + '" failed, exit ...\n')
    sys.exit(-1)

dst_dir = sites + env + '/public/' + app + '/'
now_str=datetime.now().strftime('%Y-%m-%d_%H_%M')
# backup_dir = home_dir + 'tmp/backups/laravel_' + env + '/' + now_str + '-' + app
backup_dir = home_dir + 'tmp/make_quasar_backups/laravel_' + env + '/' + now_str + '-' + app
print(' == back up ' + app + ' to ' + backup_dir)
shutil.move (dst_dir, backup_dir)

print(' -- remove view file: ', view_file)
if os.path.exists(view_file): os.remove(view_file)
print(' -- copy the view file: ', view_file)
shutil.copyfile(dist_dir + '/index.html', view_file)

print(' -- remove ', app_dist_dir)
if os.path.exists(app_dist_dir): shutil.rmtree(app_dist_dir)
print(' -- copy dist files to : ', app_dist_dir)
shutil.copytree(dist_dir, app_dist_dir)
exit_code = os.WEXITSTATUS(os.system("chcon -Rt httpd_sys_content_rw_t " + app_dist_dir))

ptxt = fgcolor('green', '║') + ' App ' + bgcolor('li_green', fgcolor('da_blue', underline(boldit(appx))))
if env == 'devx':
    ptxt+= ' built ' + fg.green + 'SUCCESSFULLY in ' + fg.rs + env + ' with OPTIONS' + build_options + ' and Version ' + ver
elif env == 'prod':
    ptxt+= ' built ' + fg.green + 'SUCCESSFULLY in ' + fg.rs + env + ' with version ' + ver
print_tailer(ptxt)
sys.exit(0)

# fix warning:
# (node:19618) Warning: Accessing non-existent property 'lineno' of module exports inside circular dependency
# (Use `node --trace-warnings ...` to show where the warning was created)
# (node:19618) Warning: Accessing non-existent property 'column' of module exports inside circular dependency
# (node:19618) Warning: Accessing non-existent property 'filename' of module exports inside circular dependency
# (node:19618) Warning: Accessing non-existent property 'lineno' of module exports inside circular dependency
# Fixed with following: (backup file index.js_O in the directory below)
# 搜索到项目中文件： \node_modules\stylus\lib\nodes\index.js ,代码最前面加入一下：
# exports.lineno = null;
# exports.column = null;
# exports.filename = null;


