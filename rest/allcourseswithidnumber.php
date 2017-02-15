<?php

// Needed for
// https://github.com/eLearning-TUDarmstadt/tucanveranstaltungswatcher

require_once '../../../config.php';
require_capability('local/littlehelpers:view', context_system::instance());
global $CFG, $DB;

require_once $CFG->dirroot . '/local/littlehelpers/lib.php';

$sql = "
		SELECT 
			c.id,c.shortname,c.fullname,c.idnumber,
			(SELECT COUNT(id) FROM {course_modules} WHERE course=c.id GROUP BY course) as anzahlAktivitaeten,
			(SELECT COUNT(id) FROM {forum_discussions} WHERE course=c.id GROUP BY course) as anzahlForeneintraege,
			(SELECT name FROM {course_categories} WHERE id=cat.parent) as semester,
			cat.name as fb 
		FROM 
			{course} c, {course_categories} cat
		WHERE 
			c.idnumber != '' AND
			cat.id = c.category
		";

$result = $DB->get_records_sql($sql);

echo json_encode($result, JSON_PRETTY_PRINT);
