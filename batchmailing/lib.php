<?php

defined('MOODLE_INTERNAL') || die ();


class BatchMailer {
    
    private static $batchSize = 100;
    private static $eventType = 'batchmailingnotification';
    private static $component = 'local_littlehelpers';
    
    public static function sendNextBatch() {
        
        global $DB;
        
        $conditions = array('eventtype' => self::$eventType, 'component' => self::$component);
        $messages = $DB->get_records('message', $conditions, 'timecreated', '*', 0, self::$batchSize);
        
        foreach($messages as $key => $message) {
            self::sendMessageMock($message);
        }
        
    }
    
    private function sendMessageMock($message) {
        
        global $DB;
        
        if(isset($message->id)) {
            $conditions = array('id' => $message->id, 'eventtype' => self::$eventType, 'component' => self::$component);
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
    	$message->eventtype         = self::$eventType;
    	$message->component         = self::$component;
    	$message->timecreated		= time();
    	$message->contexturl 		= 'https://moodle.tu-darmstadt.de/local/littlehelpers/batchmailing';
    	$message->contexturlname 	= 'Batchmailing';
    	
    	return $message;
    }
    
    
 }