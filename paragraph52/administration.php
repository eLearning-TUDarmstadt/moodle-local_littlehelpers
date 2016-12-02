<?php
require_once '../../../config.php';
require $CFG->dirroot . '/local/littlehelpers/paragraph52/lib.php';

global $OUTPUT, $PAGE, $CFG;
// Only course creators and managers
require_capability ( 'local/littlehelpers:view', context_system::instance () );

$PAGE->set_context ( context_system::instance () );
$PAGE->set_url ( '/local/littlehelpers/paragraph52/administration.php' );
$PAGE->set_pagelayout ( 'base' );
$PAGE->set_title ( 'Überprüfung §52a UrhG - Administration' );


$replyTo = optional_param('replyto', '', PARAM_RAW);
$subject = optional_param('subject', '', PARAM_RAW);
$text = optional_param('text', '', PARAM_RAW);
$submit = optional_param('send', '', PARAM_RAW);

$cl = new CourseList ();
echo $OUTPUT->header ();

// Mails verschicken
if($submit) {
	$cl->sendMails($replyTo, $subject, $text);
}


echo '<a href="#course_overview"><input type="button" value="Zur Kursübersicht"></a>';

// $cl->sendMail();

echo '<h1>Benachrichtigungen verschicken</h1>
		<form method="post">
			Antwort an:<br>
			<input type="text" name="replyto" value="moodle@tu-darmstadt.de"><br>
			Betreff:<br>
			<input type="text" name="subject" value="Betreff"><br>
			Text mit möglichen Platzhaltern:
			<ul>
				<li>###FIRSTNAME###</li>
				<li>###LASTNAME###</li>
				<li>###COURSES###</li>
			</ul>
			<textarea name="text" rows="10" cols="100">
Sehr geehrte/r ###FIRSTNAME### ###LASTNAME###,<br>
<br>
###COURSES###
<br>
Mit freundlichen Grüßen
<br>		
Ihr Moodle-Team
			</textarea><br><br>
			<input type="submit" name="send" value="Send">
			<input type="reset" value="Reset">
		</form>';

$persons = $cl->getPersonsToNotify ();

echo '<h1>Benachrichtigungen gehen an ' . count($persons) . ' Lehrende/Assistenten</h1>';
echo '<p>Gelistet werden Lehrende/Assistenten die mindestens einen ungeprüften Kurs haben. Unter "Courses" stehen die ungeprüften Kurse.</p>';

$notificationsTable = '<table class="table table-condensed">
		<thead>
			<tr>
				<th>#</th>
				<th>' . get_string ( 'firstname' ) . '</th>
				<th>' . get_string ( 'lastname' ) . '</th>
				<th>' . get_string ( 'email' ) . '</th>
				<th>' . get_string ( 'courses' ) . '</th>
			</tr>
		</thead>
		<tbody>';
foreach ( $persons as $p ) {
	$notificationsTable .= '<tr>
			<td>' . $p->userid . '</td>
			<td>' . $p->firstname . '</td>
			<td>' . $p->lastname . '</td>
			<td>' . $p->email . '</td>
			<td>' . $cl->formatCourses($p->courses) . '</td>
	</tr>';
}
$notificationsTable .= '</tbody></table>';

function cleanCourses($row) {
	return $row[4] == 1;
}

$allCoursesArrayAsJson = $cl->allCoursesAsArray();

$allCoursesArray = json_decode($allCoursesArrayAsJson);
$numOfCourses = count($allCoursesArray);
$numOfCleanCourses = count(array_filter($allCoursesArray, "cleanCourses"));
$percOfCleanCourses = number_format((float) ($numOfCleanCourses * 100) / $numOfCourses, 2, '.', ''); 


echo $notificationsTable;

echo '<a name="course_overview"><h1>Kursübersicht</h1></a>';
echo 'Anzahl Kurse: ' . $numOfCourses . '<br>';
echo 'davon gepr&uuml;ft: ' . $numOfCleanCourses . '<br>';
echo 'in Prozent: ' . $percOfCleanCourses . ' %<br>';
echo '
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">

      // Load the Visualization API and the controls package.
      google.charts.load("current", {"packages":["corechart", "controls"]});

      // Set a callback to run when the Google Visualization API is loaded.
      google.charts.setOnLoadCallback(drawDashboard);

      // Callback that creates and populates a data table,
      // instantiates a dashboard, a range slider and a pie chart,
      // passes in the data and draws it.
      function drawDashboard() {

        // Create our data table.
        var data = google.visualization.arrayToDataTable(' . $allCoursesArrayAsJson . ');

        // Create a dashboard.
        var dashboard = new google.visualization.Dashboard(
            document.getElementById("dashboard_div"));

        var filter_semester = new google.visualization.ControlWrapper({
		          "controlType": "CategoryFilter",
		          "containerId": "semesterfilter_div",
		          "options": {
		            "filterColumnLabel": "Semester"
		          }
		        });
       
        var filter_fb = new google.visualization.ControlWrapper({
		          "controlType": "CategoryFilter",
		          "containerId": "fbfilter_div",
		          "options": {
		            "filterColumnLabel": "FB"
		          }
		        });
        		
        var filter_clean = new google.visualization.ControlWrapper({
		          "controlType": "CategoryFilter",
		          "containerId": "cleanfilter_div",
		          "options": {
		            "filterColumnLabel": "Geprüft"
		          }
		        });

        var table = new google.visualization.ChartWrapper({
			        chartType: "Table",
			        containerId: "table_div",
			        options: {
			            showRowNumber: false,
			            width: "100%",
			            //page: "enable",
			            //pageSize: 25,
			            allowHtml: true
			                    //sortColumn: 0,
			                    //sortAscending: false
			        },
                    /*
			        view: {
			            // 0: instance
			            // 1: section name
			            // 2: localised activity type
			            // 3: activity name
			            // 4: mod - moodle internal mod name, for example forum, chat, assign, choice
			            // 5: course module id (cm)
			            // 6: visible (1 || 0)
			            columns: [0,1,2,3,4,5,6,7]
			        }*/
			    });
        		
        // Establish dependencies, declaring that "filter" drives "pieChart",
        // so that the pie chart will only display entries that are let through
        // given the chosen slider range.
        dashboard.bind([filter_semester, filter_fb, filter_clean], table);

        // Draw the dashboard.
        dashboard.draw(data);
      }
    </script>
		<div id="dashboard_div">
	      <!--Divs that will hold each control and chart-->
	      <div id="semesterfilter_div"></div>
	      <div id="fbfilter_div"></div>
	      <div id="cleanfilter_div"></div>
          <div id="table_div"></div>
	    </div>
		';

echo $OUTPUT->footer ();
