<?php

define ( 'CLI_SCRIPT', 1 );
require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

global $CFG;
global $DB;

print("start search for all students \n");

$dbUsers = $DB->get_records('user', array('deleted' => '0', 'suspended' => '0'), null, 'username, id');

$ldap = ldap_connect("ldaps://ldap.hrz.tu-darmstadt.de:636");
$bind = ldap_bind($ldap);
$basedn = "ou=STUD,o=TU";
$attributes = array('cn');
$filter = '(cn=*)';
$search = ldap_search($ldap, $basedn, $filter, $attributes) or die("Error in search Query: " . ldap_error($ldap));
$result = ldap_get_entries($ldap, $search);

$ldapUsers = array_map(function($r) { return $r['cn'][0]; }, $result);
$users = array_keys($dbUsers);
$intersection = array_intersect($ldapUsers, $users);

$userIds = array_map(function($tuid) use ($dbUsers) { return $dbUsers[$tuid]; }, $intersection);

foreach(array_slice($userIds, 0, 2) as $key => $value) {
	$message = createMessage($value, 'Testnachricht', 'Das ist ein Test');
	$DB->insert_record('message', $message);
}

?>

