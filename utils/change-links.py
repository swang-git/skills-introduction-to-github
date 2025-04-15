#!/bin/python
import sys, os
from Utils import my_argparse

print("==== start =====")
args = my_argparse()
infile = args.infile
print('infile', infile)

file1 = open(infile, 'r')
count = 0
 
while True:
    count += 1
 
    # Get next line from file
    line = file1.readline()
 
    # if line is empty
    # end of file is reached
    if not line:
        break
    # print("Line{}: {}".format(count, line.strip()))
    x = line.strip().split()
    # print(x[8], x[10].replace('/sites/', '/home/swang/'))
    print("ln -s {} {}".format(x[10].replace('/sites/', '/home/swang/'), x[8]))
file1.close()

print("==== end =====")