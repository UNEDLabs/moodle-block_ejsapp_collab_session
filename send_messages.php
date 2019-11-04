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
 * This script creates the collaborative session with the information configured in invite_participants and
 * sends an invitation to join this collaborative session by (1) email and (2) a moodle message
 *
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');
require_login();

global $CFG, $DB, $PAGE, $OUTPUT, $USER, $SESSION;

require_once($CFG->dirroot.'/message/lib.php');
require_once($CFG->dirroot.'/filter/multilang/filter.php');
require_once('manage_collab_db.php');

$mycourseid = required_param('courseid', PARAM_RAW);
$contextid = required_param('contextid', PARAM_RAW);
$labid = required_param('labid', PARAM_RAW);
$localport = required_param('localport', PARAM_RAW);
$ip = required_param('ip', PARAM_RAW);
$enlargeport = required_param('enlargeport', PARAM_RAW);
$enlarge_collab_conf = required_param('enlarge_collab_conf', PARAM_RAW);

$context = context::instance_by_id($contextid);

// Create the collaborative session
insert_collaborative_session($localport, $labid, $USER->id, $ip, $enlargeport, $enlarge_collab_conf, $mycourseid);

$collaborative_session_id = get_collaborative_session_id($USER->id);

$send = true;
$preview = false;
$edit = false;
$format = FORMAT_PLAIN;

$lab_record = $DB->get_record('ejsapp', array('id'=>$labid));
$multilang = new filter_multilang($context, array('filter_multilang_force_old'=>0));
$lab_name = $multilang->filter($lab_record->name);
$messagebody = get_user_name($USER->id) . get_string('invitationMsg1', 'block_ejsapp_collab_session') . $lab_name . '.';

$url = new moodle_url('/blocks/ejsapp_collab_session/send_messages.php', array('id'=>$mycourseid));
$url->param('messagebody', $messagebody);
$url->param('format', $format);

$PAGE->set_url($url);
$PAGE->set_context(context_course::instance($mycourseid));

if (!$course = $DB->get_record('course', array('id'=>$mycourseid))) {
  print_error('invalidcourseid');
}

$SESSION->emailto = array();
$SESSION->emailto[$mycourseid] = array();
$SESSION->emailselect[$mycourseid] = array('messagebody' => $messagebody);
$messagebody = $SESSION->emailselect[$mycourseid]['messagebody'];

$count = 0;
$user_list = array();

foreach ($_POST as $k => $v) {
	if (preg_match('/^(user|teacher)(\d+)$/',$k,$m)) {
        if (!array_key_exists($m[2],$SESSION->emailto[$mycourseid])) {
            if ($user = $DB->get_record('user', array('id'=>$m[2]))) {
                $user_list[] = $user;
                $SESSION->emailto[$mycourseid][$m[2]] = $user;
                $count++;
            }
        }
    }
}

require('init_page.php');

// if messaging is disabled on site, we can still allow users with capabilities to send emails instead
if (empty($CFG->messaging)) {
    echo $OUTPUT->notification(get_string('messagingdisabled','message'));
}

if (count($SESSION->emailto[$mycourseid])) {
  $good = true;
  if (!empty($CFG->noemailever)) {
        $temp_cfg = $CFG->noemailever;
        $CFG->noemailever = true;
  }

  for ($i = 0; $i < count($SESSION->emailto[$mycourseid]); $i++) {
  	    $good = $good && @message_post_message($USER,$user_list[$i],$messagebody,$format);
		insert_collaborative_invitation($user_list[$i]->id, $collaborative_session_id);
  }
  $session_director = $DB->get_record('ejsapp_collab_sessions',array('master_user'=>$USER->id));
  $session_id = $session_director->id;
$redirection = <<<EOD
<script>
	location.href = '{$CFG->wwwroot}/mod/ejsapp/view.php?colsession=$session_id';
</script>
EOD;
  echo $redirection;

  if (!empty($CFG->noemailever)) {
    $CFG->noemailever = $temp_cfg;
  }
} else {
  echo $OUTPUT->notification(get_string('nousersyet'));
}

echo $OUTPUT->footer();