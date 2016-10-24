<?php
/**
 * Dieses Skript versteckt alle Aktivitäten in allen Kursen, die in der 
 * Tabelle paragraph52 unter clean eine 0 haben
 * 
 * Hinweis: Um Schaden abzuwenden
 */

$REALLY_HIDE_ACTIVITIES = true;;

if(!$REALLY_HIDE_ACTIVITIES) {
	echo "Da in dieser Datei die Variable REALLY_HIDE_ACTIVITIES nicht auf true steht \n";
	echo "mache ich gaaaaaar nichts!\n";
	die;
}

define ( 'CLI_SCRIPT', 1 );
require_once '../../../config.php';
global $CFG;
require_once $CFG->dirroot . '/course/lib.php';

function hideCm($cmid, $i, $sum) {
	$cm     = get_coursemodule_from_id('', $cmid, 0, true, MUST_EXIST);
	$modcontext = context_module::instance($cm->id);
	
	echo "[" . $i . "/" . $sum . "]\t" . "Kurs: " . $cm->course . "\t\tTyp: " . $cm->modname . "\t\t\t" . $cm->name . "\n"; 
	
	set_coursemodule_visible($cm->id, 0);
	\core\event\course_module_updated::create_from_cm($cm, $modcontext)->trigger();
}

$cmids = $DB->get_records_sql("SELECT cm.id FROM {course_modules} cm, {paragraph52} p
WHERE
	cm.course = p.course
	AND p.clean = 0
	AND cm.visible = 1");


$sum = count($cmids);
echo "Verstecke " . $sum . " Aktivitäten:\n";

$i = 1;
foreach ($cmids as $cm) {
	hideCm($cm->id, $i, $sum);
	$i++;
}

?>