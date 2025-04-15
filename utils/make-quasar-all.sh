#!/usr/bin/bash
if [ $# -ne 1 ]; then
	echo "Please env <prod|devx>"
	exit
fi
env=$1
chars=ABCDEFGHIJKLMNOPQRSTUVWXYZ
ver=`echo -n "${chars:RANDOM%${#chars}:1}"`
echo $ver

for app in expense shopping reminder memo watcher bankstatement holdings bank golf arts
do
	/home/swang/bin/make-quasar $env $ver $app
	if [ $? -ne 0 ]; then
		echo "make-quasar $app build failed, exit"
		exit
	fi
done
