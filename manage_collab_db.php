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
 * File that centralizes all DB management
 *
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * returns true if the user is participating in at least one collaborative session
 */
function is_the_user_participating_in_any_session(){
	global $USER, $DB;

	$invited = $DB->record_exists('ejsapp_collab_acceptances', array('accepted_user'=>$USER->id));
    $master = $DB->record_exists('ejsapp_collab_sessions', array('master_user'=>$USER->id));

    return ($invited || $master);
} //is_the_user_participating_in_any_session

/**
 * returns true if the user has been invited to in at least one collaborative session
 */
function has_the_user_been_invited_to_any_session(){
	global $USER, $DB;

    return $DB->record_exists('ejsapp_collab_invitations', array('invited_user'=>$USER->id));
} //has_the_user_been_invited_to_any_session


/**
 * creates a new collaborative session
 *
 * @param int $localport connection port of the master user
 * @param int $ejsapp id of the EJS simulation to be shared
 * @param int $master_user id of the master user
 * @param int $ip connection ip of the session director
 * @param int $sarlabport connection port of the sarlab server
 * @param int $using_sarlab whether sarlab is used or not
 * @param int $course id of the course that includes the collaborative EJS simulation
 */
function insert_collaborative_session($localport, $ejsapp, $master_user, $ip, $sarlabport, $using_sarlab, $course){
	global $DB;

	// if the session exists do nothing
	if (is_the_user_participating_in_any_session()) {
		return;
	}

    $collab_session = new stdClass();
    $collab_session->ip = $ip;
    $collab_session->localport = $localport;
    $collab_session->using_sarlab = $using_sarlab;
    $collab_session->sarlabport = $sarlabport;
    $collab_session->ejsapp = $ejsapp;
    $collab_session->master_user = $master_user;
    $collab_session->course = $course;
    $DB->insert_record('ejsapp_collab_sessions', $collab_session);
} //insert_collaborative_session


/**
 * includes a user into a new collaborative session
 *
 * @param int $collaborative_session id of the collaborative session
 */
function insert_collaborative_user($collaborative_session){
	global $USER, $DB;

	// if the user has already been inserted do nothing
	if (is_the_user_participating_in_any_session()) {
		return;
	}
    $collab_acceptances = new stdClass();
    $collab_acceptances->accepted_user = $USER->id;
    $collab_acceptances->collaborative_session = $collaborative_session;
    $DB->insert_record('ejsapp_collab_acceptances', $collab_acceptances);
} //insert_collaborative_user


/**
 * returns the id of the collaborative session directed by a given master user
 *
 * @param int $master_user id of the master user
 * @return int record id
 */
function get_collaborative_session_id($master_user){
	global $DB;

	$record = $DB->get_record('ejsapp_collab_sessions', array('master_user'=>$master_user));

    if (isset($record->id))	return $record->id;
    else return null;
} //get_collaborative_session_id


/**
 * creates a collaborative invitation
 *
 * @param int $invited_user id of the invited user
 * @param int $collaborative_session id of the collaborative session
 */
function insert_collaborative_invitation($invited_user, $collaborative_session){
	global $DB;

	// <if the invitation exists do noting>
	$record = $DB->get_record('ejsapp_collab_invitations', array('invited_user'=>$invited_user,'collaborative_session'=>$collaborative_session));
	if (isset($record->invited_user)) {
		return;
	}
	// <\if the invitation exists do noting>

    $collab_invitations = new stdClass();
    $collab_invitations->invited_user = $invited_user;
    $collab_invitations->collaborative_session = $collaborative_session;
    $DB->insert_record('ejsapp_collab_invitations',$collab_invitations);
} //insert_collaborative_invitation


/**
 * drops out a user from the collaborative session
 */
function delete_me_as_collaborative_user(){
	global $DB, $USER;

    $DB->delete_records('ejsapp_collab_acceptances', array('accepted_user'=>$USER->id));
} //delete_me_as_collaborative_user


/**
 * finishes a collaborative session
 *
 * @param int $master_user id of the master user
 */
function delete_collaborative_session($master_user){
	global $DB, $USER;

	$session = get_collaborative_session_id($master_user);
    if ($session) {
        $DB->delete_records('ejsapp_collab_sessions', array('id'=>$session));
        $DB->delete_records('ejsapp_collab_invitations', array('collaborative_session'=>$session));
        $DB->delete_records('ejsapp_collab_acceptances', array('collaborative_session'=>$session));
        $DB->delete_records('remlab_manager_sarlab_keys', array('user'=>$USER->username));
    }
} //delete_collaborative_session


/**
 * returns the id of the EJS simulation shared in a collaborative session
 *
 * @param int $master_user id of the master user
 * @return int ejsappid
 */
function get_ejsapp($master_user){
	global $DB;

	$ejsapp = null;
    $record = $DB->get_record('ejsapp_collab_sessions', array('master_user'=>$master_user));
	if (isset($record)) $ejsapp = $record->ejsapp;

	return $ejsapp;
} //get_ejsapp

/**
 * returns the name of the EJS simulation shared in a collaborative session
 *
 * @param int $master_user id of the master user
 * @return string ejsapp name
 */
function get_ejsapp_name($master_user){
	global $DB;

    $ejsapp_name = null;
    $record = $DB->get_record('ejsapp_collab_sessions', array('master_user'=>$master_user));
    if (isset($record->master_user)) {
        $record2 = $DB->get_record('ejsapp', array('id'=>$record->ejsapp));
        if (isset($record2->id)) $ejsapp_name = $record2->name;
    }

	return $ejsapp_name;
} //get_ejsapp_name

/**
 * returns an object that fully describes the simulation shared in a collaborative session
 *
 * @param int $session id of the collaborative session
 * @return stdClass ejsapp
 */
function get_ejsapp_object($session){
	global $DB;

    $ejsapp = null;
    $record = $DB->get_record('ejsapp_collab_sessions', array('id'=>$session));
    if (isset($record->ejsapp)) {
        $ejsapp = $DB->get_record('ejsapp', array('id'=>$record->ejsapp));
    }

	return $ejsapp;
} //get_ejsapp_object


/**
 * returns an object that fully describes the master user that leads a collaborative session
 *
 * @param int $session id of the collaborative session
 * @return stdClass ejsapp_collab_acceptances
 */
function get_master_user_object($session){
	global $DB;

    $record = $DB->get_record('ejsapp_collab_sessions', array('id'=>$session));
    $record2 = new stdClass();
	 if(isset($record->master_user)) {
         $record2 = $DB->get_record('ejsapp_collab_acceptances', array('id'=>$record->master_user));
     }

	return $record2;
} //get_master_user_object

/**
 * returns a list of all collaborative sessions where a user has been invited
 */
function get_sessions_where_i_am_invited(){
	global $DB, $USER;

    $records = $DB->get_records('ejsapp_collab_invitations', array('invited_user'=>$USER->id));
	$sessions = array();
	foreach ($records as $record) {
		$session = $record->collaborative_session;
		$new_record = $DB->get_record('ejsapp_collab_sessions', array('id'=>$session));
		$sessions[$new_record->master_user] = $new_record->ejsapp;
	}

	return $sessions;
} //get_sessions_where_i_am_invited


/**
 * returns the name of a user
 *
 * @param int $user id of user
 * @return string user name
 */
function get_user_name($user){
	global $DB;

	$record = $DB->get_record('user', array('id'=>$user));

	return "{$record->firstname} {$record->lastname}";
} //get_user_name


/**
 * drops out a non master user from the collaborative session
 */
function delete_non_master_user_from_collaborative_users(){
	global $DB, $USER;

    $DB->delete_records('ejsapp_collab_acceptances', array('id'=>$USER->id));
} //delete_non_master_user_from_collaborative_users


/**
 * returns the names of all collaborative EJS simulations in a given course
 *
 * @param int $course id of the course
 * @return array ejsapp
 */
function get_all_collaborative_lab_records($course) {
	global $DB;

	if ($course == '*') $records = $DB->get_records('ejsapp', array('is_collaborative'=>'1'));
	else $records = $DB->get_records('ejsapp', array('is_collaborative'=>'1','course'=>$course));

	return $records;
} //get_all_collaborative_lab_names

/**
 * returns the connection port that the session director is using in the collaborative session
 *
 * @param int $session id of the collaborative session
 * @return int port
 */
function get_port($session){
	global $DB;

    $record = $DB->get_record('ejsapp_collab_sessions', array('id'=>$session));

	return $record->port;
} //get_port

/**
 * returns the name of a course
 *
 * @param int $course_id id of the course
 */
function get_course_name($course_id){
	global $DB;

	$record = $DB->get_record('course', array('id'=>$course_id));

	return $record->fullname;
} //get_user_name

/**
 * returns true if the caller is the master user of a collaborative session
 */
function am_i_master_user(){
	global $DB, $USER;

	$record = $DB->get_record('ejsapp_collab_sessions', array('master_user'=>$USER->id));

	return (isset($record->master_user));
} //is_master_user