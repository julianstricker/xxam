PIDFILE=/var/run/xxammailspoolcronjob.pid

if [ -e "$PIDFILE" ] ; then
    # our pidfile exists, let's make sure the process is still running though
    PID=`/bin/cat "$PIDFILE"`
    if /bin/kill -0 "$PID" > /dev/null 2>&1 ; then
        # indeed it is, i'm outta here!
        /bin/echo 'The script is still running, forget it!'
        exit 0
    fi
 fi

 # create or update the pidfile
 /bin/echo "$$" > $PIDFILE

 /usr/bin/php /var/www/vhosts/xxam.com/xxam/bin/console xxam:mailspool  --env=prod --no-debug

 /bin/rm -f "$PIDFILE"

