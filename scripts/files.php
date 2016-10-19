<?php
require_once '../../../config.php';
global $DB;
require_capability ( 'local/littlehelpers:view', context_system::instance () );

$sql_cm_files = "SELECT 
	f.id,
	f.contextid,
	f.component,
	f.filearea,
	f.itemid,
	f.filepath,
	f.filename,
	f.filesize,
	cm.course as courseid,
	(SELECT cc.name FROM mdl_course_categories cc WHERE cc.id = ccat.parent) AS semester,
	ccat.name AS fb,
	c.fullname,
	c.shortname
FROM 
	mdl_files f,
	mdl_context con,
	mdl_course c,
	mdl_course_categories ccat,
	mdl_course_modules cm
WHERE 
	f.filesize != 0 AND
	mimetype NOT IN ('application/vnd.moodle.backup','application/java-archive','audio/mp3','audio/mp4','audio/ogg','audio/wav','audio/x-aiff','audio/x-ms-wma','audio/x-pn-realaudio-plugin','video/mp4','video/mpeg','video/ogg','video/quicktime','video/webm','video/x-flv','video/x-ms-asf','video/x-ms-wm','video/x-ms-wmv') AND 
	con.contextlevel = 70 AND
	f.contextid = con.id AND
	f.component != 'backup' AND
	itemid = 0 AND
	con.instanceid = cm.id AND
	cm.course = c.id AND
	ccat.id = c.category";

$sql_course_files = "SELECT 
	f.id,
	f.contextid,
	f.component,
	f.filearea,
	f.itemid,
	f.filepath,
	f.filename,
	f.filesize,
	con.instanceid AS courseid,
	(SELECT cc.name FROM mdl_course_categories cc WHERE cc.id = ccat.parent) AS semester,
	ccat.name AS fb,
	c.fullname,
	c.shortname
FROM 
	mdl_files f,
	mdl_context con,
	mdl_course c,
	mdl_course_categories ccat
WHERE 
	f.filesize != 0 AND
	mimetype NOT IN ('application/vnd.moodle.backup','application/java-archive','audio/mp3','audio/mp4','audio/ogg','audio/wav','audio/x-aiff','audio/x-ms-wma','audio/x-pn-realaudio-plugin','video/mp4','video/mpeg','video/ogg','video/quicktime','video/webm','video/x-flv','video/x-ms-asf','video/x-ms-wm','video/x-ms-wmv') AND 
	con.contextlevel = 50 AND
	f.contextid = con.id AND
	f.component != 'backup' AND
	itemid = 0 AND
	con.instanceid = c.id AND
	ccat.id = c.category";

//$DB->set_debug(true);
$cm_files = $DB->get_records_sql ( $sql_cm_files );
$course_files = $DB->get_records_sql($sql_course_files);
$files = array_merge($cm_files, $course_files);

$PAGE->set_context ( context_system::instance () );
$PAGE->set_url ( '/local/littlehelpers/paragraph52/teacher.php' );
$PAGE->set_pagelayout ( 'base' );
$PAGE->set_title ( 'Alle Dateien' );

echo $OUTPUT->header ();

$table = '<table class="table">
			<tr>
				<th>#</th>
				<th>Dateiname</th>
				<th>Dateigröße</th>
				<th>Semester</th>
				<th>FB</th>
				<th>#</th>
				<th>Kurs</th>
			</tr>';

foreach ( $files as $f ) {
	$url = moodle_url::make_pluginfile_url($f->contextid, $f->component, $f->filearea, $f->itemid, $f->filepath, $f->filename);
	$table .= '<tr>
				<td>' . $f->id . '</td>
				<td><a href="'.$url.'" target="_blank">' . $f->filename . '</a></td>
				<td>' . $f->filesize . '</td>
				<td>' . $f->semester . '</td>
				<td>' . $f->fb . '</td>
				<td>' . $f->courseid . '</td>
				<td><a href="'.$CFG->wwwroot.'/course/view.php?id='.$f->courseid.'">' . $f->shortname . '</a></td>
				</tr>';
}

$table .= '</table>';
echo $table;
// 

echo $OUTPUT->footer ();