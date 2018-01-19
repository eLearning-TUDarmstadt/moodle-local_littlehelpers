<?php

defined('MOODLE_INTERNAL') || die ();


class BatchMailer {
    
    private $batchSize = 100;
    private $eventType = 'batchmailingnotification';
    private $component = 'local_littlehelpers';
    
    public static function sendNextBatch() {
        
        global $DB;
        
        $conditions = array('eventtype' => $this->eventType, 'component' => $this->component);
        $messages = $DB->get_records('message', $conditions, 'timecreated', '*', 0, $this->batchSize);
        
        foreach($messages as $key => $message) {
            $this->sendMessageMock($message);
        }
        
    }
    
    private function sendMessageMock($message) {
        
        global $DB;
        
        if(isset($message->id)) {
            $conditions = array('id' => $message->id, 'eventtype' => $this->eventType, 'component' => $this->component);
            $DB->delete_records('message', $conditions);
        }
    }
    
    public static function createMessage($user, $subject, $content) {

    	global $USER;
    
    	$message = new stdClass();
    	$message->useridfrom        = $USER->id;
    	$message->useridto          = $user->id;
    	$message->subject           = $subject;
    	$message->fullmessage       = $content;
    	$message->fullmessageformat = FORMAT_MARKDOWN;
    	$message->fullmessagehtml   = $content;
    	$message->smallmessage      = "";
    	$message->notification      = '0';
    	$message->eventtype         = $this->eventType;
    	$message->component         = $this->component;
    	$message->timecreated		= time();
    	$message->contexturl 		= 'https://moodle.tu-darmstadt.de/local/littlehelpers/batchmailing';
    	$message->contexturlname 	= 'Batchmailing';
    	
    	return $message;
    }
    
    
 }