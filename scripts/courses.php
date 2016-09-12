<?php

require_once '../../../config.php';
require_capability('local/littlehelpers:view', context_system::instance());
global $CFG, $DB;

require_once $CFG->dirroot . '/local/littlehelpers/lib.php';

// Bootstrap 
echo '
<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
';


function sql_user_enrolments() {
	global $DB;
	$roles = $DB->get_records('role', null, '', 'id,shortname');	
	//echo "<pre>" . print_r($roles, true) . "</pre>";
	$statements = array();
	foreach($roles as $id => $role){
		$statements[] = "(SELECT COUNT(ue.id) FROM {user_enrolments} ue, {enrol} e 
				WHERE 
					e.id = ue.enrolid AND
					e.courseid = c.id AND
					e.roleid = ".$id."
				) AS role_" . $role->shortname;
	}
	//echo "<pre>" . print_r($statements, true) . "</pre>";
	return implode(',', $statements);
}

function sql_activities() {
	global $DB;
	$modules = $DB->get_records('modules', null, '', 'id,name');
	//echo "<pre>" . print_r($roles, true) . "</pre>";
	$statements = array();
	foreach($modules as $id => $mod){
		$statements[] = " (SELECT COUNT(cm.id) 
				FROM {course_modules} cm
				WHERE
					cm.course = c.id AND
					cm.module = ".$id."
				GROUP BY
					cm.course, cm.module
				) AS mod_" . $mod->name;
	}
	//echo "<pre>" . print_r($statements, true) . "</pre>";
	return implode(',', $statements);
}

//echo sql_activities();

$sql = "SELECT 
	c.id,
	(SELECT name FROM {course_categories} WHERE id = ccat.parent) as semester,
	ccat.name AS fb,
	c.shortname,
	c.fullname,
	c.visible,
	(SELECT COUNT(ue.id) FROM {user_enrolments} ue, {enrol} e WHERE ue.enrolid = e.id AND e.courseid = c.id) as enrolments,".
	sql_user_enrolments().",".
	sql_activities()."
FROM
	{course} c,
	{course_categories} ccat
WHERE c.category = ccat.id";

//$DB->set_debug(true);
$courses = $DB->get_records_sql($sql);

$out = get_table($courses);

echo $out;
