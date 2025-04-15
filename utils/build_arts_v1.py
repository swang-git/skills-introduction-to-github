#!/usr/bin/python3
import sys
import os
import shutil
import datetime

# print(len(sys.argv))
if len(sys.argv) != 3:
    print('Please provide  [ devx || prod ] [ project name ]\n\n')
    sys.exit(0)

env = sys.argv[1]
app = sys.argv[2]
app_js =  os.path.join(app, 'js')
app_fonts =  os.path.join(app, 'fonts')
home_dir = "/home/swang/"
home_sites = "/home/swang/sites/"
app_dir = home_dir + "projects/" + app
dist_dir = app_dir + "/dist/spa"
pub_dir = home_sites + env + "/public/"
app_dist_dir = home_sites + env + "/public/" + app
view_file = os.path.join(home_sites, env) + '/resources/views/' + app + '.blade.php'


os.chdir(app_dir)

build_options = "--debug"
if env == "prod": build_options = ""
##if env == "prod" or env == "golf": build_options = ""

print('\nbuilding the ' + app + ' app in ' + env + ' with options ' + build_options + ' ...\n')

exit_code = os.WEXITSTATUS(os.system("quasar build " + build_options))
if exit_code != 0:
    print('quasar build -d failed, exit ...\n')
    sys.exit(-1)


print('\n -- remove view file: ', view_file)
if os.path.exists(view_file): os.remove(view_file)
print('\n -- copy the view file: ', view_file)
shutil.copyfile(dist_dir + '/index.html', view_file)

print('\n -- remove ', app_dist_dir)
if os.path.exists(app_dist_dir): shutil.rmtree(app_dist_dir)
print('\n -- copy dist files to : ', app_dist_dir)
shutil.copytree(dist_dir, app_dist_dir)

# deploy to laptop
# if env == 'dev' || env == 'prod:
# to_rdir = '/mnt/swang/Sites/' + env
# to_pdir = to_rdir + '/public/' + app
# timestamp = datetime.datetime.now().strftime("%Y%m%d-%H%M%S")
# to_view_file = to_rdir + '/resources/views/' + app + '.blade.php'
# if os.path.exists(to_view_file): 
#     shutil.copyfile(to_view_file, to_view_file + '_' + timestamp) # backup old
#     shutil.copyfile(dist_dir + '/index.html', to_view_file)
# if os.path.exists(to_pdir): 
#     shutil.copytree(to_pdir, to_pdir + '_' + timestamp)       # backup old
#     shutil.rmtree(to_pdir)       # delete old
# shutil.copytree(dist_dir, to_pdir)


sys.exit(0)
