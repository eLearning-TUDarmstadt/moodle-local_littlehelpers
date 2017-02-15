# moodle-local_littlehelpers

## cli/cron_task_supervisor.php
This script checks whether the Moodle cron tasks are running properly. A task does not if

```nextruntime < time() - $GRACE_TIME```

In case of a problem mails are sent to defined addresses (adjust them in the script) by the shell mail
command (make sure it is working!).
You can adjust the $GRACE-TIME variable directly in the script. 

Add the script to the crontab of your webserver user. An example:

```50 23 * * *     /usr/bin/php -f /var/www/html/moodle/local/categorybackup/cron_task_supervisor.php >/dev/null```
