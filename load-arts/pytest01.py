#!/usr/bin/python3
import re

str0='他们不信，能有欧元吗？欧盟发展到现在这样，人类史上没'
str1='\u5F53\u5E74\u514B\u6797\u987F\u4E5F\u79DB\u67E5\uFF0C\u8C03\u67E5\u62A5\u544A'
str2='\u6350\u4E867\u5343\u4E07\uFF0C\u4E00\u5BB6\u4EE5\u6559\u80B2\u4E3A\u8363\u3002\u8FD9\u548C\u67D0'

if (re.match(r'\\u[0-9a-fA-F]+', str2)):
    print('---', str1)
    print('---', str2)
# print('==', str0)
# print('==', str1)
# print('==', str2)

file_path = '/home/swang/projects/load-arts/test-ufile.txt'
ufile = open(file_path, 'r')
lines = ufile.readlines()
print('====', lines)
for line in lines:
    # print(' ===', line)
    if re.search('\\\\u[0-9a-fA-F]+', line):
        print('=u=', line)
        print('=x=', line.encode("ascii").decode("unicode-escape"))
    else:
        print('=o=', line)

ufile.close()

