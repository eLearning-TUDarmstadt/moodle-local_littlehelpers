<?php
require_once '../../../config.php';
require $CFG->dirroot . '/local/littlehelpers/paragraph52/lib.php';

global $OUTPUT, $PAGE, $CFG;

$PAGE->set_context ( context_system::instance () );
$PAGE->set_url ( '/local/littlehelpers/paragraph52/teacher.php' );
$PAGE->set_pagelayout ( 'base' );
$PAGE->set_title ( 'Überprüfung §52a UrhG' );

$cl = new CourseList ();
$courses = $cl->getCoursesWithRoleTeacher ();

// Mark course as clean
$courseid = optional_param ( 'courseIsClean', 0, PARAM_INT ); // Course Module ID
if ($courseid) {
	$cl->markCourseAsClean ( $courseid );
	redirect($CFG->wwwroot."/local/littlehelpers/paragraph52/teacher.php");
}

// Count courses
$cleanCourses = 0;
$dirtyCourses = 0;
$totalCourses = count ( $courses );
foreach ( $courses as $c ) {
	($c->clean == 1) ? $cleanCourses ++ : $dirtyCourses ++;
}

echo $OUTPUT->header ();

echo "<h1>Ihre Kurse:</h1>";
echo "<h4>in die Sie als Lehrende/r oder Assistent/in eingetragen sind</h4>";

if ($cleanCourses == $totalCourses) {
	echo '<div class="alert alert-success">
			  Alle Kurse wurden bearbeitet. Nichts weiter zu tun.
			</div>';
} else {
	echo '<div class="alert alert-error">
			  <h4>Unbearbeitete Kurse</h4>
				Es gibt noch Kurse, die von Ihnen nicht als "sauber" markiert wurden. Bitte entfernen Sie ggf. geschütztes Material und
				markieren Sie anschließend den jeweiligen Kurs.
			</div>';
}

// $cl::printer($courses);
if (empty ( $courses )) {
	echo '<div class="alert alert-info">
			  <b>Keine Kurse gefunden!</b>
			</div>';
} else {
	$table = '<div class="container"><table class="table table-condensed">
				<tr>
					<th>#</th>
					<th>Semester</th>
					<th>FB</th>
					<th>Kurs</th>
					<th>Status</th>
					<th>Markiert von</th>
					<th>Datum</th>
				</tr>
			';
	
	foreach ( $courses as $c ) {
		if ($c->clean == "1") {
			$table .= '<tr class="success">';
		} else {
			$table .= '<tr class="alert-error">';
		}
		
		$table .= '<td>' . $c->courseid . '</id>
					<td>' . $c->semester . '</td>
					<td>' . $c->fb . '</td>
					<td>' . $c->shortname . '</td>';
		if ($c->clean == "1") {
			$table .= '<td>' . $c->clean . '</td>
					<td>' . $c->modifier_firstname . ' ' . $c->modifier_lastname . '</td>
					<td>' . userdate ( $c->timemodified ) . '</td>';
		} else {
			$table .= '<td colspan="3">
					<form action="teacher.php" method="POST">
					<input type="hidden" name="courseIsClean" value="' . $c->courseid . '">
					<input type="submit" class="btn" value="Kurs markieren">
					</form></td>';
		}
		
		$table .= '</tr>';
	}
	
	$table .= '<table></div>';
	echo $table;
}

echo $OUTPUT->footer ();
