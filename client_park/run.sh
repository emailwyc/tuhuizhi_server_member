#! /bin/sh
### BEGIN INIT INFO
#  sheell run parking
### END INIT INFO
do_start () {
	nohup npm start > ~/node.log 2>&1 &
	nohup npm run h5 > ~/node_run.log 2>&1 &
	echo "park success!"
}
do_stop () {
	pkill node
	echo "stop OK!"
}

case "$1" in
  start)
        do_start
        ;;
  restart|reload|force-reload)
        do_stop
        do_start
        ;;
  stop)
        do_stop
        ;;
  *)
        echo "Usage: $0 start|stop" >&2
        exit 3
        ;;
esac

