#!/usr/bin/python3

#Example usage:
    
import urllib.request

# set up authentication info
# authinfo = urllib.request.HTTPBasicAuthHandler()
# authinfo.add_password(realm='PDQ Application',
#                     uri='https://www.fidelity.com',
#                     user='wangshengli',
#                     passwd='Yjwlej11ll')

# # set up authentication info
# authinfo = urllib.request.HTTPBasicAuthHandler()
# authinfo.add_password(realm='PDQ Application',
#                     uri='https://mahler:8092/site-updates.py',
#                     user='klem',
#                     passwd='geheim$parole')

# set up authentication info
realm = 'PDQ'
uri = 'https://oltx.fidelity.com/ftgw/fbc/oftop/portfolio#summary'
user = 'XXXX'
passwd = 'XXXX'
# uri = 'http://prod/health/weight'
# user = 'sXXX'
# passwd = 'XXXX'
authinfo = urllib.request.HTTPBasicAuthHandler()
authinfo.add_password(realm, uri, user, passwd)

# proxy_support = urllib.request.ProxyHandler({"http" : "http://ahad-haam:3128"})

# build a new opener that adds authentication and caching FTP handlers
# opener = urllib.request.build_opener(proxy_support, authinfo, urllib.request.CacheFTPHandler)
opener = urllib.request.build_opener(authinfo, urllib.request.CacheFTPHandler)

# install it
urllib.request.install_opener(opener)

# f = urllib.request.urlopen('http://www.python.org/')
f = urllib.request.urlopen(uri)
print(f.read())

