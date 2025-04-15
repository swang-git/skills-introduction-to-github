#!/bin/bash
for f in $(ls *.pdf); do fn=`echo $f | cut -b1-13`; echo $f ${fn}s.pdf; done
