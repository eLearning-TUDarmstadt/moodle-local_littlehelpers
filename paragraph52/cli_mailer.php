<?php
define ( 'CLI_SCRIPT', 1 );
require_once '../../../config.php';
global $CFG;
require_once $CFG->dirroot . '/local/littlehelpers/paragraph52/lib.php';


$replyTo = "moodle@tu-darmstadt.de";
$subject = "Betreff";

$text = "
Sehr geehrte/r ###FIRSTNAME### ###LASTNAME###,<br>
<br>
###COURSES###
<br>
Mit freundlichen Grüßen
<br>		
Ihr Moodle-Team		
";

$cl = new CourseList();

$cl->sendMails($replyTo, $subject, $text);