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
 * File that sends an invitation to join the collaborative session by (1) email and (2) moodle message
 *
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');
require_login();
global $CFG, $DB;
require_once($CFG->dirroot.'/message/lib.php');

require_once('manage_collaborative_db.php');

global $CFG, $DB, $PAGE, $OUTPUT, $USER, $SESSION;

$mycourseid = required_param('courseid', PARAM_RAW);

$collaborative_session_id = get_collaborative_session_id($USER->id);

$send = true;
$preview = false;
$edit = false;
$format = FORMAT_PLAIN;

$messagebody = get_string('invitationMsg2', 'block_ejsapp_collab_session');

// <\getting the message body>

$url = new moodle_url('/blocks/ejsapp_collab_session/send_messages.php', array('id'=>$mycourseid));
$url->param('messagebody', $messagebody);
$url->param('format', $format);

$systemcontext = context_system::instance();   // SYSTEM context
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);

if (!$course = $DB->get_record('course', array('id'=>$mycourseid))) {
  print_error('invalidcourseid');
}

require_login();

$coursecontext = context_course::instance($mycourseid); // Course context

$SESSION->emailto = array();
$SESSION->emailto[$mycourseid] = array();
$SESSION->emailselect[$mycourseid] = array('messagebody' => $messagebody);
$messagebody = $SESSION->emailselect[$mycourseid]['messagebody'];

$count = 0;

foreach ($_POST as $k => $v) {
	if (preg_match('/^(user|teacher)(\d+)$/',$k,$m)) {
    if (!array_key_exists($m[2],$SESSION->emailto[$mycourseid])) {
      if ($user = $DB->get_record_select('user', "id = ?", array($m[2]), 'id,firstname,lastname,idnumber,email,mailformat,lastaccess, lang')) {
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

  foreach ($SESSION->emailto[$mycourseid] as $user) {
  	$good = $good && @message_post_message($USER,$user,$messagebody,$format);
		insert_collaborative_invitation($user->id, $collaborative_session_id);
  }
  $session_director = $DB->get_record('ejsapp_collab_sessions',array('master_user'=>$USER->id));
  $session_id = $session_director->id;
$redirection = <<<EOD
<center>
<script>
	location.href = '{$CFG->wwwroot}/mod/ejsapp/view.php?colsession=$session_id';
</script>
</center>
EOD;
  echo $redirection;

  if (!empty($CFG->noemailever)) {
    $CFG->noemailever = $temp_cfg;
  }
} else {
  echo $OUTPUT->notification(get_string('nousersyet'));
}

echo $OUTPUT->footer();