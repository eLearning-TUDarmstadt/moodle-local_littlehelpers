<?php
define ( 'CLI_SCRIPT', 1 );
require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');
global $CFG;
require_once (__DIR__.'/lib.php');

$replyTo = "moodle@tu-darmstadt.de";
$subject = "Erinnerung: Neuregelung zu digitalen Texten in der Lehre";

$text = "
Sehr geehrte/r ###FIRSTNAME### ###LASTNAME###
<br><br>
die bisher durch den &sect;52a UrhG (&bdquo;Öffentliche Zugänglichmachung für Unterricht und Forschung&rdquo;) erm&ouml;glichte digitale Bereitstellung von Text-Ausz&uuml;gen 
aus urheberrechtlich geschützten Quellen, für welche die TU Darmstadt, die ULB oder die Lehrenden selbst keine Lizenz haben, endet am 31.12.2016. 
<br><br>
Darunter fallen typischerweise einzelne Buchkapitel und Artikel aus wissenschaftlichen Zeitschriften sowie je nach Fach auch bestimmte Prim&auml;rquellen. Nicht darunter fallen Abbildungen etc. in Vorlesungsmanuskripten und Folienpr&auml;sentationen. Nicht betroffen sind Werke, die keine Texte sind wie Bilder, Filmausschnitte, Ausschnitte aus Musikaufnahmen oder Partituren.
<br><br> 
<b>Bitte beachten Sie </b> das für Moodle vorgesehene Verfahren zur Pr&uuml;fung entsprechender Materialien: <br>
http://www.e-learning.tu-darmstadt.de/52a
<br><br>
Ihre folgenden Kurse (SoSe 2015 - WiSe 2016/17 + semester&uuml;bergreifend) sind noch zu prüfen:
<br><br>
###COURSES###
<br><br> 
Bei Fragen können Sie sich an e-learning@tu-darmstadt.de wenden. 
<br><br> 
Mit freundlichen Gr&uuml;&szlig;en<br>
Ihre E-Learning Arbeitsgruppe";

$cl = new CourseList();

$cl->sendMails($replyTo, $subject, $text);