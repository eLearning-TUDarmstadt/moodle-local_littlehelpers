# moodle-local_littlehelpers

## cli/cron_task_supervisor.php
This script checks whether the Moodle cron tasks are running properly. A task does not if

```nextruntime < time() - $GRACE_TIME```

In case of a problem mails are sent to defined addresses (adjust them in the script) by the shell mail
command (make sure it is working!).
You can adjust the $GRACE-TIME variable directly in the script. 

Add the script to the crontab of your webserver user. An example:

```50 23 * * *     /usr/bin/php -f /var/www/html/moodle/local/categorybackup/cron_task_supervisor.php >/dev/null```

## cli/delete_empty_courses.php
This script deletes empty courses with a non-empty idnumber. Please pay attention to our definition of an empty course:
* The count of modules is <= 1. The news forum is created, when the course is opened first. So a count of 0 means no visits at all and 1 at least one visit.

That's it! So courses with changed topic descriptions, posts in the news forums or fancy block ARE EMPTY if there no other activities in the course.

We used this script to delete "empty courses" in past terms. To get these courses you can set a filter. With the line

```const PATTERN = 'SoSe 2014';```

only courses with "SoSe 2014" in the shortname will be deleted.

You (de)activate the deletion with the line

``const DELETION_ACTIVE = false;``

false: preview mode. No courses will be deleted 

true: kill them all! There will be no further warning! :skull:
