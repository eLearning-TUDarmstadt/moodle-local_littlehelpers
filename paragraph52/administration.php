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

echo $OUTPUT->header ();

$cl = new CourseList ();

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
        var data = google.visualization.arrayToDataTable('.$cl->allCoursesAsArray().');

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
		            "filterColumnLabel": "Sauber"
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