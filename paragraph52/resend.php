<?php

/**
 * in case of errors while sending 
 * 
 * @var unknown
 */

define ( 'CLI_SCRIPT', 1 );
require(__DIR__.'/../../../config.php');
global $CFG;
require_once($CFG->libdir.'/clilib.php');
global $DB;


// fetch all unread messages with context Paragraph52
$result = $DB->get_records_sql('SELECT * FROM {message} WHERE contexturlname=?', array('Paragraph52'));

// create new from old message
function createMessage($object) {
	global $USER;
	
	$message = new \core\message\message();
	$message->component = 'local_littlehelpers';
	$message->name = 'paragraph52notification';
	$message->userfrom = $USER;
	
	// Get user
	$message->userto = core_user::get_user($object->useridto);
	
	// force email sending
	$message->userto->emailstop = 0;
	
	$message->subject = $object->subject;
	$message->fullmessage = $object->fullmessage;
	$message->fullmessageformat = FORMAT_MARKDOWN;
	$message->fullmessagehtml = $object->fullmessagehtml;
	$message->smallmessage = $object->smallmessage;
	$message->notification = '0';
	
	$message->contexturl = 'https://moodle.tu-darmstadt.de/local/littlehelpers/paragraph52';
	$message->contexturlname = 'Paragraph52';
	$message->replyto = "moodle@tu-darmstadt.de";

	return $message;
}

$ids = array();
$index = 0;
$length = count($result);

// iterate all messages from database
foreach($result as $key=>$m) {
	
	// store id of old message
	array_push($ids, $m->id);
	
	// create new message with old content
	$message = createMessage($m);
	
	// try to send the new message
	try {
		
		// message sending implicates storing new message in table {message}
		$messageid = message_send($message);
		
		// log old and new message id
		printf("send successfull message with oldid %s and newid %s\n", $key, $messageid);
		
		// log counter
		printf("message %s of %s\n", $index, $length);
		
	} catch(Exception $e) {
		printf("error message with id %s \n", $key);
	}
	$index++;
}

// delete all old messages
list($insql, $inparams) = $DB->get_in_or_equal($ids);
$DB->delete_records_select('message', 'id '.$insql, $inparams);

?>