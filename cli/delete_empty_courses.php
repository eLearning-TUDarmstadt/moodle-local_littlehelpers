<?php

// Aufruf im Moodle Hauptverzeichnis:
// sudo -u www_mdl php local/littlehelpers/cli/delete_empty_courses.php


define ( 'CLI_SCRIPT', 1 );
require_once 'config.php';
class deleter {	
	
	/*
	 * AB HIER EINSTELLEN
	 */
	
	// false: Nichts wird gelöscht
	const DELETION_ACTIVE = false;
	
	// Es werden nur Kurse selektiert, bei denen das Pattern im Kurznamen enthalten ist
	const PATTERN = 'SoSe 2014';
	
	/*
	 * ENDE EINSTELLUNGEN
	 */
	
	private $list = null;
	function __construct() {
		global $DB;
		
		$sql = "SELECT 
					c.id,
					c.shortname,
					(SELECT COUNT(cm.id) FROM {course_modules} cm WHERE course=c.id) AS anzahl
				FROM {course} c  WHERE c.idnumber != '' AND c.shortname LIKE '%" . self::PATTERN . "%'
					GROUP BY c.id, c.shortname HAVING (SELECT COUNT(cm.id) FROM {course_modules} cm WHERE course=c.id) <= 1 ORDER BY c.id DESC";
		
		$this->list = $DB->get_records_sql($sql);
		
		
		if(self::DELETION_ACTIVE && self::PATTERN != '') {
			require_once 'lib/moodlelib.php';
			//  löschen
			foreach ($this->list as $id => $course) {
				echo "Loesche " . $course->shortname . " (" . $course->anzahl . " Aktivitaeten)...";
				try {
					$noError = delete_course($id);
					if(!$noError) {
						echo "FEHLER!\n";
					} else {
						echo "ok!\n";
					}
				} catch (Exception $e) {
					echo "FEHLER!\n";
					echo $e;
				}
			}
		} else {
			$this->printList();
		}
		echo "Done!\n";
	}
	
	private function printList() {
		$output = "";
		$output .= "#\tAnz. Aktivitaeten\tKurzname\n";
		foreach ($this->list as $id => $course) {
			$output .= $id . "\t" . $course->anzahl . "\t" . $course->shortname . "\n";
		}
		$output .= "\n\n";
		$output .= count($this->list) . " Kurse gefunden.\n";
		echo $output;
	}
}

$d = new deleter();
