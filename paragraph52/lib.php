<?php
class CourseList {
	private $courses = null;
	public $roles = [];
	
	
	function __construct() {
		global $DB;
		$this->loadCourses ();
		$this->roles = (array) $DB->get_records_list('role', 'archetype', array('editingteacher','teacher'), 'id, name, shortname');
		//$this->roles = $DB->get_records('role', array('archetype' => 'editingteacher'), '', 'id, name, shortname');
		//CourseList::printer ( $this->courses );
	}
	
	public function getPersonsToNotify() {
		global $DB;
		$sql = "SELECT 
					ra.id,
					u.id as userid,
					u.email,
					u.firstname,
					u.lastname,
					c.id as courseid,
					c.shortname,
					(SELECT name FROM {course_categories} WHERE id = cat.parent) as semester,
					cat.name as fb
				FROM 
					{user} u,
					{role_assignments} ra,
					{context} con,
					{course} c,
					{course_categories} cat,
					{role} r,
					{paragraph52} p
				WHERE
					ra.userid = u.id
					AND u.email != ''
					AND ra.roleid IN (SELECT id FROM {role} WHERE archetype = 'editingteacher')
					AND ra.contextid = con.id
					AND con.contextlevel = 50
					AND con.instanceid = c.id
					AND r.id = ra.roleid
					AND c.id = p.course
					AND p.clean = 0
					AND c.category = cat.id
					AND (SELECT cc.name FROM {course_categories} cc WHERE cc.id = cat.parent) IN ('SoSe 2015', 'WiSe 2015/16', 'SoSe 2016', 'WiSe 2016/17', 'Semesterübergreifende Kurse')
					";
		$entries = $DB->get_records_sql($sql);
		
		$persons = array();
		foreach ($entries as $entry) {
			$c = new stdClass();
			$c->id = $entry->courseid;
			$c->shortname = $entry->shortname;
			$c->semester = $entry->semester;
			$c->fb = $entry->fb;
			
			if(isset($persons[$entry->userid])) {
				$persons[$entry->userid]->courses[$c->id] = $c;
			} else {
				$p = new stdClass();
				$p->userid = $entry->userid;
				$p->firstname = $entry->firstname;
				$p->lastname = $entry->lastname;
				$p->email = $entry->email;
				
				
				$p->courses = array($c->id => $c);
				$persons[$entry->userid] = $p;				
			}
		}
		return $persons;
	}
	
	public function formatCourses($courses) {
		global $CFG;
		$return = "";
		
		// Absteigende Reihenfolge nach Kursids
		krsort($courses);
		foreach ($courses as $c) {
			$return .= "<a target='_blank' href='".$CFG->wwwroot."/course/view.php?id=".$c->id."'>".$c->semester." / ".$c->fb." / ".$c->shortname."</a><br>";
		}
		
		return $return;
	}
	
	public function sendMails($replyTo, $subject, $text) {
		$persons = $this->getPersonsToNotify();
		
		
		foreach ($persons as $p) {
			$t = $text;
			$t = str_replace("###FIRSTNAME###", $p->firstname, $t);
			$t = str_replace("###LASTNAME###", $p->lastname, $t);
			$t = str_replace("###COURSES###", $this->formatCourses($p->courses), $t);
			
			$this->sendMail($p->userid, $subject, $t, '', $replyTo);
		}
		
		echo "Mails an " . count($persons) . " Personen verschickt!";
	}
	
	public function sendMail($toUserId, $subject, $content, $smallContent = "", $replyTo) {
		global $USER;
		
		$message = new \core\message\message();
		$message->component = 'local_littlehelpers';
		$message->name = 'paragraph52notification';
		$message->userfrom = $USER;
		$message->userto = $toUserId;
		$message->subject = $subject;
		$message->fullmessage = $content;
		$message->fullmessageformat = FORMAT_MARKDOWN;
		$message->fullmessagehtml = $content;
		$message->smallmessage = $smallContent;
		$message->notification = '0';
		//$message->contexturl = 'http://GalaxyFarFarAway.com';
		//$message->contexturlname = 'Context name';
		$message->replyto = $replyTo;
		//$content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
		//$message->set_additional_content('email', $content);
		
		$messageid = message_send($message);
		echo "MessageId: " . $messageid . "<br>";
	}
	
	private function isTeacherInCourseContext($contextid) {
		global $USER;
		$isTeacher = false;
		foreach ($this->roles as $roleid => $role) {
			if(user_has_role_assignment($USER->id, $roleid, $contextid)) {
				$isTeacher = true;
				break;
			}
		}
		return $isTeacher;
	}
	
	public function markCourseAsClean($courseid) {
		$context = context_course::instance($courseid);
		
		// Is teacher in course?
		if($this->isTeacherInCourseContext($context->id)) {
			global $DB, $USER;
			$c = $DB->get_record('paragraph52', array('course' => $courseid), '*', MUST_EXIST);
			$c->userid = $USER->id;
			$c->timemodified = time();
			$c->clean = 1;
			$DB->update_record('paragraph52', $c);
		} 
		// Is teacher in course?
		else {
			echo "Not a teacher!";
		}
	}
		
	public function allCoursesAsArray() {
		global $DB;
		$sql = "SELECT
					p.id,
					c.id as courseid,
					c.fullname,
					c.shortname,
					ccat.name AS fb,
					(SELECT cc.name FROM {course_categories} cc WHERE cc.id = ccat.parent) AS semester,
					p.clean,
					p.userid,
					(SELECT u.firstname FROM {user} u WHERE u.id = p.userid) as modifier_firstname,
					(SELECT u.lastname FROM {user} u WHERE u.id = p.userid) as modifier_lastname,
					p.timemodified
				FROM
					{course} c,
					{course_categories} ccat,
					{paragraph52} p
				WHERE
					p.course = c.id AND
					ccat.id = c.category AND
					(SELECT cc.name FROM {course_categories} cc WHERE cc.id = ccat.parent) IN ('SoSe 2015', 'WiSe 2015/16', 'SoSe 2016', 'WiSe 2016/17', 'Semesterübergreifende Kurse')
								";
		$courses =  $DB->get_records_sql($sql);
		
		
		$result = [];
		$result[] = array("#", "Semester", "FB", "Kurs", "Sauber", "Markiert von", "Datum");
		foreach ($courses as $c) {
			$result[] = array($c->id, $c->semester, $c->fb, $c->shortname, $c->clean, $c->modifier_firstname . ' ' . $c->modifier_lastname, userdate($c->timemodified));
		}
		return json_encode($result);
	}
	
	public function getCoursesWithRoleTeacher() {
		require_once '../../../config.php';
		global $USER, $DB;
		
		if(!isloggedin()) {
			throw new require_login_exception("Please login first");
		}
		
		$usersCourses = enrol_get_all_users_courses($USER->id);
		
		$courseids = [];
		foreach($usersCourses as $id => $course) {
			$isTeacher = false;
			foreach ($this->roles as $roleid => $role) {
				if(user_has_role_assignment($USER->id, $roleid, $course->ctxid)) {
					$isTeacher = true;
					break;
				}
			}
			
			if($isTeacher) {
				$courseids[] = $id;
			}
		}
		
		if(empty($courseids)) {
			return [];
		}
		
		$sql = "SELECT
					p.id,
					c.id as courseid,
					c.fullname,
					c.shortname,
					ccat.name AS fb,
					(SELECT cc.name FROM {course_categories} cc WHERE cc.id = ccat.parent) AS semester,
					p.clean,
					p.userid,
					(SELECT u.firstname FROM {user} u WHERE u.id = p.userid) as modifier_firstname,
					(SELECT u.lastname FROM {user} u WHERE u.id = p.userid) as modifier_lastname,
					p.timemodified
				FROM 
					{course} c,
					{course_categories} ccat,
					{paragraph52} p
				WHERE 
					p.course IN (" . implode(",", $courseids) . ") AND
					p.course = c.id AND
					ccat.id = c.category AND
					(SELECT cc.name FROM {course_categories} cc WHERE cc.id = ccat.parent) IN ('SoSe 2015', 'WiSe 2015/16', 'SoSe 2016', 'WiSe 2016/17', 'Semesterübergreifende Kurse')
								";
		return $DB->get_records_sql($sql);
	}
	
	public function loadCourses() {
		global $DB;
		
		$allCourses = $DB->get_records('course', null, '', 'id');
		$paragraphCourses = $DB->get_records ( 'paragraph52' );
		
		foreach ( $allCourses as $id => $course ) {
			// Is course already registered in table paragraph52?
			$registeredCourse = null;
			foreach ( $paragraphCourses as $c ) {
				if ($c->course == $id) {
					$registeredCourse = $c;
					break;
				}
			}
			
			// Insert record
			if (! $registeredCourse) {
				$registeredCourse = new stdClass ();
				$registeredCourse->course = $course->id;
				$registeredCourse->clean = 0;
				$registeredCourse->userid = NULL;
				$registeredCourse->timemodified = NULL;
				$i = $DB->insert_record ( 'paragraph52', $registeredCourse );
				$paragraphCourses [$i] = $registeredCourse;
			}
		}
		
		$this->courses = $paragraphCourses;
	}
	public static function printer($v) {
		echo "<pre>" . print_r ( $v, true ) . "</pre>";
	}
}

$cl = new CourseList ();