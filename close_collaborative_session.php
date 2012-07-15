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
 
require_once('../../config.php');
require_login();
require_once('manage_collaborative_db.php');

global $CFG;

$session = required_param('session', PARAM_INT);

require('init_page.php');

$ejsapp = get_ejsapp_object($session);
$master_user = get_master_user_object($session);
$course_id = get_course($master_user->collaborative_session_where_user_participates);
$course_url = $CFG->wwwroot . "/course/view.php?id=$course_id";

if (am_i_master_user()) {
	delete_collaborative_session($USER->id);
	echo "<center>".get_string('close1', 'block_ejsapp_collab_session')."{$ejsapp->name}".get_string('close2', 'block_ejsapp_collab_session')."</center>";
} else {
	delete_me_as_collaborative_user();
	echo "<center>".get_string('goodbyeStudent', 'block_ejsapp_collab_session')."</center>";
}

if (!empty($course_id)) {
  $backToCourse = get_string('backToCourse', 'block_ejsapp_collab_session');
  $button = <<<EOD
	<center>
	<form>
	<input type="button" value="$backToCourse" onClick="window.location.href = '$course_url'">
	</form>
	</center>
EOD;
	echo $button;
}

echo $OUTPUT->footer();

?>