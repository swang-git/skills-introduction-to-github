#!/usr/bin/python3
import sys
import os
import shutil
import datetime

# print(len(sys.argv))
if len(sys.argv) != 3:
    print('Please provide [ drf || prod ] [ project name ]\n\n')
    sys.exit(0)

env = sys.argv[1]
app = sys.argv[2]
# app_js =  os.path.join(app, 'js')
# app_fonts =  os.path.join(app, 'fonts')
home_dir = "/home/swang/"
app_dir = home_dir + "projects/" + env + "/" + app
dist_dir = home_dir + "projects/drf_frontend/" + app + "/dist/spa"
html_file = home_dir + 'sites/devx/django/templates/' + app + '.html'
# app_dist_dir = app_dir + '/static/' + app + '/'

front_end_app_dir = home_dir + 'projects/drf_frontend/' + app

print('chdir to', front_end_app_dir)
os.chdir(front_end_app_dir)

build_options = "--debug"
if env == "prod": build_options = ""
##if env == "prod" or env == "golf": build_options = ""

print('\nbuilding the ' + app + ' app in ' + env + ' with options ' + build_options + ' ...\n')

exit_code = os.WEXITSTATUS(os.system("quasar build " + build_options))
if exit_code != 0:
    print('quasar build -d failed, exit ...\n')
    sys.exit(-1)

print('\n -- remove index.html file: ', html_file)
if os.path.exists(html_file): os.remove(html_file)
print('\n -- copy the html_file file: ', dist_dir + '/index.html', html_file)
shutil.copyfile(dist_dir + '/index.html', html_file)

# print('\n -- remove app_dist_dir', app_dist_dir)
# if os.path.exists(app_dist_dir): shutil.rmtree(app_dist_dir)
# print('\n -- copy dist files to : ', app_dist_dir)
# shutil.copytree(dist_dir, app_dist_dir)

print('\n -- remove app_dir/js', app_dir + '/js')
if os.path.exists(app_dir + '/js'): shutil.rmtree(app_dir + '/js')
print('\n -- copy dist files to : ', app_dir)
shutil.copytree(dist_dir + '/js', app_dir + '/js')

print('\n -- remove app_dir/css', app_dir + '/css')
if os.path.exists(app_dir + '/css'): shutil.rmtree(app_dir + '/css')
print('\n -- copy dist files to : ', app_dir)
shutil.copytree(dist_dir + '/css', app_dir + '/css')

print('\n -- remove app_dir/fonts', app_dir + '/fonts')
if os.path.exists(app_dir + '/fonts'): shutil.rmtree(app_dir + '/fonts')
print('\n -- copy dist files to : ', app_dir)
shutil.copytree(dist_dir + '/fonts', app_dir + '/fonts')

print('\n -- remove app_dir/img', app_dir + '/img')
if os.path.exists(app_dir + '/img'): shutil.rmtree(app_dir + '/img')
if os.path.exists(dist_dir + '/img'):
    print('\n -- copy dist files to : ', app_dir)
    shutil.copytree(dist_dir + '/img', app_dir + '/img')



sys.exit(0)
