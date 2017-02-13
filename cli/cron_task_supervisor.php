<?php
//
// Configuration
//

// Task is broken, if: mdl_task_scheduled.nextruntime < current time - GRACE_TIME
// enter time in seconds
$GRACE_TIME = 0;
$MAIL_TO = [
				"steffen.pegenau@gmail.com",
			];
$SUBJECT = "Moodle cron task problem"; 
$DATETIME = "d.m.Y H:i:s";
// </config>


//
// Includes
//
define ( 'CLI_SCRIPT', 1 );
require_once '../../../config.php';
require_once($CFG->libdir . '/adminlib.php');

$pluginman = core_plugin_manager::instance();
$enabled_plugins = $pluginman->get_plugins();

//
// Code
//

// get potential lame tasks
global $DB;
$current_time = time() - $GRACE_TIME;
$sql = "SELECT * FROM {task_scheduled} WHERE disabled = 0 AND nextruntime < " . $current_time;
$results = $DB->get_records_sql($sql);

// sort out tasks with disabled plugins
$lame_tasks = array();
foreach ($results as $r) {
	if($r->component != 'moodle') {
		list($type, $name) = core_component::normalize_component($r->component);
		$enabled_plugins = $pluginman->get_enabled_plugins($type);
		if(isset($enabled_plugins[$name])) {
			$lame_tasks[$r->component] = $r;
		}
	}
}

// are there any broken tasks?
if(count($lame_tasks) == 0) {
	echo "Everything is fine. Exit. :)\n";
	exit();
}

// compose message
$msg = "Got problems with the cron tasks on " . $CFG->wwwroot . ": \n";
foreach ($lame_tasks as $r) {
	$msg .= " * " . $r->classname 
	. " with settings: minute=".$r->minute 
	. " hour=" . $r->hour 
	. " day=".$r->day 
	. " month=".$r->month
	. " dayofweek=" . $r->dayofweek
	. "\n";
	
	$msg .= "\t Last run: " . date($DATETIME, $r->lastruntime)
		. "\t Planned next run was: " . date($DATETIME, $r->nextruntime) . "\n";
}
$msg .= "\n" . count($lame_tasks) . " tasks did not run\n"; 
$msg .= "See " . $CFG->wwwroot . "/admin/tool/task/scheduledtasks.php \n"; 


// Send Mails
$cmd = "echo '" . $msg . "' | mail -s '" . $SUBJECT . "' ";
foreach($MAIL_TO as $address) {
	echo "Sending mail to " . $address . "...";
	$ret;
	$out;
	exec($cmd . "'" . $address . "'", $out, $ret);
	if($ret !== 0) {
		echo "Mail liefert Fehlercode: " . $ret;
		foreach ($out as $line) {
			echo $line . "\n";
		}
	} else {
		"OK!\n";
	}
	echo "\n";
}
