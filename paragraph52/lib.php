<?php
class CourseList {
	private $courses = null;
	public $roles = [];
	const FILE = "/courses_paragraph_52.json";
	function __construct() {
		global $DB;
		$this->loadCourses ();
		$this->roles = $DB->get_records('role', array('archetype' => 'editingteacher'), '', 'id, name, shortname');
		//CourseList::printer ( $this->courses );
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
					ccat.id = c.category
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