#!/bin/python
import sys
print('===== start compfile =====')
file1 = "/home/swang/lll4"
file2 = "/home/swang/lll6"
if len(sys.argv) == 1: file1 = "/home/swang/" + sys.argv[1]
elif len(sys.argv) >= 2:
    file1 = "/home/swang/" + sys.argv[1]
    file2 = "/home/swang/" + sys.argv[2]
    # print("file1 %s, file2 %s"%(file1, file2))
    # sys.exit(0)

with open(file1, 'r') as f: list1 = f.readlines()
with open(file2, 'r') as f: list2 = f.readlines()
for line in list1:
    # line1 = line.strip()
    # for linx in list2:
    #     line2 = linx.strip()
    #     if (line1 == line2):
    #         print("    match for %s"%line1)
    #         break
    # print("NOT match for %s"%line1)
    if line in list2: print("   match for %s"%line)
    else:print("NO match for %s"%line)
     



    
