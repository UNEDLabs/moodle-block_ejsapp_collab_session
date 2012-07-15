<?php

// This file is part of the Moodle block "EJSApp Collab Session"
//
// EJSApp Collab Session is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// EJSApp Collab Session is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// The GNU General Public License is available on <http://www.gnu.org/licenses/>
//
// EJSApp Collab Session has been developed by:
//  - Luis de la Torre (1): ldelatorre@dia.uned.es
//	- Ruben Heradio (1): rheradio@issi.uned.es
//  - Carlos Jara (2): carlos.jara@ua.es
//
//  (1): Computer Science and Automatic Control, Spanish Open University (UNED),
//       Madrid, Spain
//  (2): Physics, Systems Engineering and Signal Theory Department, University
//       of Alicante, Spain


/**
 *    
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
function is_the_user_participating_in_any_session(){

	global $CFG, $USER, $DB, $PAGE, $OUTPUT;

	$sql = "select  *
		from {$CFG->prefix}collaborative_users
		where id = {$USER->id}
	";

	$records = $DB->get_records_sql($sql);
	$collaborative_session_where_user_participates = null;
	foreach ($records as $collaborative_user){
		$collaborative_session_where_user_participates =
			$collaborative_user->collaborative_session_where_user_participates;
	}

	return !( (count($records) == 0) or
		($collaborative_session_where_user_participates == null) );

} //is_the_user_participating_in_any_session

function has_the_user_been_invited_to_any_session(){
	global $USER, $DB, $CFG;

	$sql = "select  *
		from {$CFG->prefix}collaborative_invitations
		where invited_user = {$USER->id}
	";

	$records = $DB->get_records_sql($sql);

	return !(count($records) == 0);
} //has_the_user_been_invited_to_any_session

function does_the_table_exists($table_name) {
	global $DB;
	$sql = "show tables like \"$table_name\"";
	$records = $DB->get_records_sql($sql);
	return count($records) > 0;
}

function create_non_existing_tables(){
	global $CFG;

$tables["{$CFG->prefix}collaborative_sessions"] = "create table {$CFG->prefix}collaborative_sessions (
  id serial not null,
  port int not null,
  course int not null,
  ejsapp varchar(1000) not null,
  master_user int not null,
  primary key(id)
)";

$tables["{$CFG->prefix}collaborative_users"] = "create table {$CFG->prefix}collaborative_users (
  id int not null,
  ip varchar(100),
  collaborative_session_where_user_participates int not null,
  primary key(id)
)";

$tables["{$CFG->prefix}collaborative_invitations"] = "create table {$CFG->prefix}collaborative_invitations (
  id serial not null,
  invited_user int not null,
  collaborative_session int not null,
  primary key(id)
)";
	// store the non-existing tables into $non_initialized_tables
	$non_initialized_tables = array();
	$i = 0;
	foreach ($tables as $table_name => $sql){
		if (!does_the_table_exists($table_name)) {
			$non_initialized_tables[$i] = $table_name;
			$i++;
		}
	} //foreach

	// create the non-existing tables
	if (count($non_initialized_tables) > 0) {
		mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die(mysql_error());
		mysql_select_db($CFG->dbname) or die(mysql_error());
		foreach ($non_initialized_tables as $table_name) {
			mysql_query($tables[$table_name]) or die(mysql_error());
		}
	} //if
} //create_non_existing_tables

function insert_collaborative_session($port, $ejsapp, $master_user, $ip, $course){
	global $CFG, $DB;

	// if the session exists do nothing
	if (is_the_user_participating_in_any_session()) {
		return;
	}

	mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die(mysql_error());
	mysql_select_db($CFG->dbname) or die(mysql_error());

	$sql = "insert into {$CFG->prefix}collaborative_sessions (port, ejsapp, master_user, course)
	values  (\"$port\", \"$ejsapp\", \"$master_user\", \"$course\")";

	mysql_query($sql) or die(mysql_error());

	$session_id = null;
	$sql = "select * from {$CFG->prefix}collaborative_sessions where master_user=\"$master_user\"";
	$records = $DB->get_records_sql($sql);
	foreach ($records as $record) {
		$session_id = $record->id;
	}
	insert_collaborative_user($master_user, $ip, $session_id);
} //insert_collaborative_session

function insert_collaborative_user($id, $ip=null, $collaborative_session){
	global $CFG, $DB;

	// if the user has already been inserted do nothing
	if (is_the_user_participating_in_any_session()) {
		return;
	}

	mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die(mysql_error());
	mysql_select_db($CFG->dbname) or die(mysql_error());
	$sql = "select * from {$CFG->prefix}collaborative_users where id ='$id'";
	$records = $DB->get_records_sql($sql);
	if (count($records) == 0) {
		if ($ip) {
			$sql = "insert into {$CFG->prefix}collaborative_users (id, ip, collaborative_session_where_user_participates)
			values  ($id, \"$ip\", $collaborative_session)";
		} else {
			$sql = "insert into {$CFG->prefix}collaborative_users (id, collaborative_session_where_user_participates)
			values  ($id, $collaborative_session)";
		}
		mysql_query($sql) or die(mysql_error());
	}
} //insert_collaborative_user

function get_collaborative_session_id($master_user){
	global $DB, $CFG;
	$session_id = null;
	$sql = "select * from {$CFG->prefix}collaborative_sessions where master_user='$master_user'";
	$records = $DB->get_records_sql($sql);
	foreach ($records as $record) {
		$session_id = $record->id;
	}
	return $session_id;
} //get_collaborative_session_id

function get_my_collaborative_session(){
	global $DB, $USER, $CFG;
	$session_id = null;
	$sql = "select * from {$CFG->prefix}collaborative_users where id='{$USER->id}'";
	$records = $DB->get_records_sql($sql);
	foreach ($records as $record) {
		$session_id = $record->collaborative_session_where_user_participates;
	}
	return $session_id;
}

function insert_collaborative_invitation($invited_user, $collaborative_session){
	global $CFG,$DB;

	// <if the invitation exists do noting>
	$sql = "select  *
		from {$CFG->prefix}collaborative_invitations
		where invited_user = '$invited_user' and collaborative_session = '$collaborative_session'
	";
	$records = $DB->get_records_sql($sql);
	if ((count($records) > 0)) {
		return;
	}
	// <\if the invitation exists do noting>

	mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die(mysql_error());
	mysql_select_db($CFG->dbname) or die(mysql_error());
	$sql = "insert into {$CFG->prefix}collaborative_invitations (invited_user, collaborative_session)
			values  ($invited_user, $collaborative_session)";
	mysql_query($sql) or die(mysql_error());
} //insert_collaborative_invitation

function delete_me_as_collaborative_user(){
	global $CFG, $USER;
	mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die(mysql_error());
	mysql_select_db($CFG->dbname) or die(mysql_error());
	$sql = "delete from {$CFG->prefix}collaborative_users where id='{$USER->id}'";
	mysql_query($sql) or die(mysql_error());
} //delete_me_as_collaborative_user

function delete_collaborative_session($master_user){
	global $CFG;
	mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die(mysql_error());
	mysql_select_db($CFG->dbname) or die(mysql_error());
	$session = get_collaborative_session_id($master_user);
	$sql = "delete from {$CFG->prefix}collaborative_sessions where id = \"$session\"";
	mysql_query($sql) or die(mysql_error());
	$sql = "delete from {$CFG->prefix}collaborative_users where collaborative_session_where_user_participates = \"$session\"";
	mysql_query($sql) or die(mysql_error());
	$sql = "delete from {$CFG->prefix}collaborative_invitations where collaborative_session = \"$session\"";
	mysql_query($sql) or die(mysql_error());
} //delete_collaborative_session

function get_ejsapp($master_user){
	global $DB, $CFG;
	$ejsapp = null;
	$sql = "select ejsapp from {$CFG->prefix}collaborative_sessions where master_user = '$master_user'";
	$records = $DB->get_records_sql($sql);
	foreach ($records as $record) {
		$ejsapp = $record->ejsapp;
	}
	return $ejsapp;
} //get_ejsapp

function get_ejsapp_name($master_user){
	global $DB, $CFG;
	$ejsapp_id = null;
	$sql = "select ejsapp from {$CFG->prefix}collaborative_sessions where master_user = '$master_user'";
	$records = $DB->get_records_sql($sql);
	foreach ($records as $record) {
		$ejsapp_id = $record->ejsapp;
	}
	$sql = "select name from {$CFG->prefix}ejsapp where id = '$ejsapp_id'";
	$records = $DB->get_records_sql($sql);
	$ejsapp_name = null;
	foreach ($records as $record) {
		$ejsapp_name = $record->name;
	}
	return $ejsapp_name;
} //get_ejsapp_name

function get_ejsapp_object($session){
	global $DB, $CFG;
	$ejsapp_id = null;
	$sql = "select ejsapp from {$CFG->prefix}collaborative_sessions where id = '$session'";
	$records = $DB->get_records_sql($sql);
	foreach ($records as $record) {
		$ejsapp_id = $record->ejsapp;
	}
	$sql = "select * from {$CFG->prefix}ejsapp where id = '$ejsapp_id'";
	$records = $DB->get_records_sql($sql);
	$ejsapp = null;
	foreach ($records as $record) {
		$ejsapp = $record;
	}
	return $ejsapp;
} //get_ejsapp_object

function get_master_user_object($session){
	global $DB, $CFG;
	$sql = "select master_user from {$CFG->prefix}collaborative_sessions where id = '$session'";
	$records = $DB->get_records_sql($sql);
	$master_user_id = null;
	foreach ($records as $record) {
		$master_user_id = $record->master_user;
	}
	$sql = "select * from {$CFG->prefix}collaborative_users where id = '$master_user_id'";
	$records = $DB->get_records_sql($sql);
	$master_user = null;
	foreach ($records as $record) {
		$master_user = $record;
	}
	return $master_user;
} //get_master_user_object

function get_sessions_where_i_am_invited(){
	global $DB, $CFG, $USER;

	$sql = "select * from {$CFG->prefix}collaborative_invitations where invited_user = '{$USER->id}'";
	$records = $DB->get_records_sql($sql);
	$sessions = array();
	foreach ($records as $record) {
		$session = $record->collaborative_session;
		$sql = "select * from {$CFG->prefix}collaborative_sessions where id = '$session'";
		$new_record = $DB->get_record_sql($sql);
		$sessions[$new_record->master_user] = $new_record->ejsapp;
	}
	return $sessions;

} //get_sessions_where_i_am_invited

function get_user_name($user){
	global $DB;
	$user_name = null;
	$record = $DB->get_record('user', array('id'=>$user));
	return "{$record->firstname} {$record->lastname}";
} //get_user_name

function delete_non_master_user_from_collaborative_users(){
	global $CFG, $USER;
	mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die(mysql_error());
	mysql_select_db($CFG->dbname) or die(mysql_error());
	$sql = "delete from {$CFG->prefix}collaborative_users where id='{$USER->id}'";
	mysql_query($sql) or die(mysql_error());
} //delete_non_master_user_from_collaborative_users

function get_all_collaborative_lab_names($course) {
	global $DB, $CFG;
	$sql = "select name, id from {$CFG->prefix}ejsapp where is_collaborative='1' and course='$course'";
	if ($course == '*') {
		$sql = "select name, id from {$CFG->prefix}ejsapp where is_collaborative='1'";
	}
	$records = $DB->get_records_sql($sql);
	return $records;
} //get_all_collaborative_lab_names

function get_port($session_id){
	global $DB, $CFG;
	$sql = "select * from {$CFG->prefix}collaborative_sessions where id = '$session_id'";
	$records = $DB->get_records_sql($sql);
	$session = null;
	foreach ($records as $record) {
		$session = $record;
	}
	return $session->port;
} //get_port

function get_course($session_id){
	global $DB, $CFG;
	$sql = "select * from {$CFG->prefix}collaborative_sessions where id = '$session_id'";
	$records = $DB->get_records_sql($sql);
	$session = null;
	foreach ($records as $record) {
		$session = $record;
	}
	return $session->course;
} //get_course

function get_course_name($course_id){
	global $DB;
	$user_name = null;
	$record = $DB->get_record('course', array('id'=>$course_id));
	return $record->fullname;
} //get_user_name

function am_i_master_user(){
	global $DB, $USER, $CFG;
	$user_name = null;

	$sql = "select * from {$CFG->prefix}collaborative_sessions where master_user='{$USER->id}'";
	$records = $DB->get_records_sql($sql);
	return (count($records) > 0);
} //is_master_user
?>