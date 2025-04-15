if [ $# -ne 1 ]; then
	echo "Please env <prod|devx>"
	exit
fi
env=$1
/home/swang/bin/build-arts $env arts
/home/swang/bin/build-quasarQV1 $env expense
/home/swang/bin/build-quasarQV1 $env watcher
/home/swang/bin/build-quasarQV1 $env shopping
/home/swang/bin/build-quasarQV1 $env reminder
/home/swang/bin/build-quasarQV1 $env memo
