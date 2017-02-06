<?php
//
// Configuration
//

// Task is broken, if: mdl_task_scheduled.nextruntime < current time - GRACE_TIME
// enter time in seconds
$GRACE_TIME = 0;
$MAIL_TO = "steffen.pegenau@gmail.com";
$SUBJECT = "Moodle cron task problem"; 
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


// are there any?
if(count($lame_tasks) == 0) {
	echo "Everything is fine. Exit. :)";
	exit();
}

// send mail
$msg = "Got problems with the cron tasks on " . $CFG->wwwroot . ":";
foreach ($lame_tasks as $r) {
	$msg .= " * " . $r->classname 
	. " with settings: minute=".$r->minute 
	. " hour=" . $r->hour 
	. " day=".$r->day 
	. " month=".$r->month
	. " dayofweek=" . $r->dayofweek;
}
$msg .= count($lame_tasks) . " tasks did not run";
print_r(exec('echo ' . $msg . ' | mail -s ' . $SUBJECT . ' ' . $MAIL_TO)); 
// | mail -s "' .. '" ' . $MAIL_TO .'" <<EOF\n ' . $msg . "\n EOF"); 

//print_r($results);