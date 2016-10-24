<?php

$textForLabel = '<h1>Hello World!</h1>';

define ( 'CLI_SCRIPT', 1 );
require_once '../../../config.php';
global $CFG, $DB, $USER;
$USER->id=2;

require_once $CFG->dirroot . '/course/modlib.php';
require_once $CFG->dirroot . '/mod/label/lib.php';
require_once $CFG->dirroot . '/mod/label/mod_form.php';

$moduleid = $DB->get_field('modules', 'id', array('name' => 'label'));

function getData($course, $moduleid) {
	$data = new stdClass();
	$data->section          = 0;  // The section number itself - relative!!! (section column in course_sections)
	$data->visible          = 1;
	$data->course           = $course->id;
	$data->module           = $moduleid;
	$data->modulename       = 'label';
	$data->groupmode        = $course->groupmode;
	$data->groupingid       = $course->defaultgroupingid;
	$data->id               = '';
	$data->instance         = '';
	$data->coursemodule     = '';
	$data->add              = 'label';
	$data->return           = 0; //must be false if this is an add, go back to course view on cancel
	$data->sr               = 0;
	
	return $data;
}

function getLabelData($courseid, $moduleid, $textForLabel) {
	/*
	 [introeditor] => Array
	 (
	 [text] => bghjfttfdgd
	 [format] => 1
	 [itemid] => 200195313
	 )
	
	 [visible] => 1
	 [availabilityconditionsjson] => {"op":"&","c":[],"showc":[]}
	 [tags] =>
	 [course] => 2
	 [coursemodule] => 0
	 [section] => 1
	 [module] => 18
	 [modulename] => label
	 [instance] => 0
	 [add] => label
	 [update] => 0
	 [return] => 0
	 [sr] => 0
	 [competency_rule] => 0
	 [submitbutton2] => Save and return to course
	 */
	
	$fromform = new stdClass();
	$fromform->introeditor = array('text' => $textForLabel, 'format' => 1, 'itemid' => 0);
	$fromform->visible = 1;
	$fromform->availabilityconditionsjson = '{"op":"&","c":[],"showc":[]}';
	$fromform->course = $courseid;
	$fromform->coursemodule = 0;
	$fromform->section = 0;
	$fromform->module = $moduleid;
	$fromform->modulename = 'label';
	$fromform->instance = 0;
	$fromform->add = 'label';
	$fromform->update = 0;
	$fromform->return = 0;
	$fromform->sr = 0;
	$fromform->competency_rule = 0;
	$fromform->submitbutton2 = "Save and return to course";
	return $fromform;
}

function addLabelToCourse($course, $moduleid, $textForLabel) {
	$fromform = getLabelData($course->id, $moduleid, $textForLabel);
	
	$data = getData($course, $moduleid);
	
	$cm = null;
	$mform = new mod_label_mod_form($data, 0, $cm, $course);
	$mform->set_data($data);
	
	return add_moduleinfo($fromform, $course, $mform);
}


$sql = "SELECT c.* FROM mdl_course c, mdl_paragraph52 p 
WHERE
	c.id = p.course
	AND p.clean = 0
	AND c.id != 1";
$courses = $DB->get_records_sql($sql);

$sum = COUNT($courses);
echo $sum . " Kurse gefunden\n";

$i = 1;
foreach ($courses as $c) {
	echo "[" . $i . "/" . $sum . "]\t#" . $c->id . "\t" . $c->shortname . "\n";
	addLabelToCourse($c, $moduleid, $textForLabel);
	$i++;
}
