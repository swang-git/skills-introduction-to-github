#!/usr/bin/bash
if [ $# -ne 1 ]; then
	echo "Please env <prod|devx>"
	exit
fi
env=$1
chars=ABCDEFGHIJKLMNOPQRSTUVWXYZ
ver=`echo -n "${chars:RANDOM%${#chars}:1}"`
echo $ver

app="expense"
/home/swang/bin/make-quasar $env $ver $app
if [ $? -ne 0 ]; then
	echo "make-quasar $app build failed, exit"
	exit
fi
app="shopping"
/home/swang/bin/make-quasar $env $ver $app
if [ $? -ne 0 ]; then
	echo "make-quasar arts $app failed, exit"
	exit
fi
/home/swang/bin/make-quasar $env $ver shopping
if [ $? -ne 0 ]; then
	echo "make-quasar shopping build failed, exit"
	exit
fi
/home/swang/bin/make-quasar $env $ver reminder
if [ $? -ne 0 ]; then
	echo "make-quasar reminder build failed, exit"
	exit
fi
/home/swang/bin/make-quasar $env $ver memo
if [ $? -ne 0 ]; then
	echo "make-quasar memo build failed, exit"
	exit
fi
/home/swang/bin/make-quasar $env $ver watcher
if [ $? -ne 0 ]; then
	echo "make-quasar watcher build failed, exit"
	exit
fi
/home/swang/bin/make-quasar $env $ver bank
if [ $? -ne 0 ]; then
	echo "make-quasar bank build failed, exit"
	exit
fi
/home/swang/bin/make-quasar $env $ver expense
if [ $? -ne 0 ]; then
	echo "make-quasar expense build failed, exit"
	exit
fi
/home/swang/bin/make-quasar $env $ver golf
if [ $? -ne 0 ]; then
	echo "make-quasar golf build failed, exit"
	exit
fi
