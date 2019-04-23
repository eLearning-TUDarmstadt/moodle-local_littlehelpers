<?php

/**
    csv-reports for student activities in courses grouped by days
	args - course ids space seperated
 **/

define ( 'CLI_SCRIPT', 1 );

$courseids = array_slice( $argv, 1 );

require_once '../../../config.php';
require_once('../../../lib/csvlib.class.php');
global $CFG, $DB;

$csvDir = __DIR__.'/csv';
$fields = ['activity', 'date', 'views', 'students'];
$context_course = CONTEXT_COURSE;
$context_module = CONTEXT_MODULE;
$student_role = 5;


// use $days to limit the reports to the last x days
$days = 178; 
$date = date_create();
date_sub($date, date_interval_create_from_date_string("$days days"));
$timestamp = date_timestamp_get($date);

$DB->set_debug(true);

if (!file_exists($csvDir)) {
    mkdir($csvDir, 0775, true);
}

foreach($courseids as $courseid) {

    if(is_numeric($courseid) && $context = context_course::instance($courseid)) {

        $students = get_role_users(5 , $context);

        $ids = implode(',', array_keys($students));

        $sql = "SELECT CONCAT(l.contextinstanceid,'-',to_timestamp(timecreated)::date) as key, l.contextinstanceid as cmid, to_timestamp(timecreated)::date as daydate, COUNT('x') AS numviews, COUNT(DISTINCT l.userid) AS distinctusers
                FROM    mdl_logstore_standard_log l,
                        mdl_role_assignments ra,
                        mdl_context ctx
                WHERE	l.courseid = ctx.instanceid
                AND	    ctx.id = ra.contextid
                AND 	ctx.instanceid=$courseid
                AND     ctx.contextlevel=$context_course
                AND 	l.anonymous = 0
                AND     ra.roleid=$student_role
		AND 	l.crud = 'r'
                AND 	l.contextlevel = $context_module             
                AND 	l.userid=ra.userid
		AND	timecreated > $timestamp
        	GROUP BY l.contextinstanceid, daydate
		ORDER By l.contextinstanceid";

        $result = $DB->get_records_sql($sql);
        
	$modinfo = get_fast_modinfo($courseid);
        $data = array();
	foreach($result as $r) {
	    $r->cmid = $modinfo->cms[$r->cmid]->name;
	    unset($r->key);
            array_push($data, (array) $r);
        }

        array_unshift($data,$fields);
	
        $csv = csv_export_writer::print_array($data, 'semicolon', '"', true);
	file_put_contents("$csvDir/student_course_activities_by_day_$courseid.csv", $csv);

    } else {
        print "a course with id $courseid does not exists\n";
    }

}



