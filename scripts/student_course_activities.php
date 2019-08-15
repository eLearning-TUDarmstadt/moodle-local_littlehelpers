<?php

/**
    csv-reports for student activities in courses
    args - course ids space seperated
 **/

define ( 'CLI_SCRIPT', 1 );

//$courseids = array_slice( $argv, 1 );

$arguments = array_slice( $argv, 1 );

$courseids = explode(',', explode('ids=',$arguments[0])[1]);
$startdate = explode('startdate=',$arguments[1])[1];
$enddate = explode('enddate=',$arguments[2])[1];

if(empty($courseids)){
    exit("Please provide a comma separated list of course ids.");
}

foreach($courseids as $courseid) {
    if(!is_numeric($courseid)){
        exit("only numbers are allowed as ids");
    }
}


if(!$startdate = strtotime($startdate)){
    exit("Wrong Date format");
}

$sql_with_startdate = "AND     timecreated > $startdate  ";

if($startdate == "") {
    print("The whole log is used"."\n");
    $sql_with_startdate = "";
}

require_once '../../../config.php';
require_once('../../../lib/csvlib.class.php');
global $CFG, $DB;

$csvDir = __DIR__.'/csv';
$fields = ['activity', 'views', 'students', 'lastaccess'];
$context_course = CONTEXT_COURSE;
$context_module = CONTEXT_MODULE;
$student_role = 5;


if (!file_exists($csvDir)) {
    mkdir($csvDir, 0775, true);
}

foreach($courseids as $courseid) {

    if($context = context_course::instance($courseid)) {

        $students = get_role_users(5 , $context);

        $ids = implode(',', array_keys($students));

        $sql = "SELECT l.contextinstanceid as cmid, COUNT('x') AS numviews, COUNT(DISTINCT l.userid) AS distinctusers, MAX(l.timecreated) AS lasttime
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
                ". $sql_with_startdate .
	            "GROUP BY l.contextinstanceid";

        $result = $DB->get_records_sql($sql);
        $modinfo = get_fast_modinfo($courseid);
        $data = array();
	foreach($result as $r) {
	    $r->cmid = $modinfo->cms[$r->cmid]->name;
            array_push($data, (array) $r);
        }

        array_unshift($data, $fields);
	
        $csv = csv_export_writer::print_array($data, 'semicolon', '"', true);
	file_put_contents("$csvDir/student_course_activities_$courseid.csv", $csv);

    } else {
        print "a course with id $courseid does not exists\n";
    }

}
