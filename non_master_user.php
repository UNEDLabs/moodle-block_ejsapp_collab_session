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
 * Init file for an invited user
 *
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');
require_login();
global $CFG, $DB;
require_once('manage_collaborative_db.php');
require_once("$CFG->libdir/formslib.php");

$page_caller = get_string('navBarNonMasterUser', 'block_ejsapp_collab_session');
require('init_page.php');

create_non_existing_tables();

if (is_the_user_participating_in_any_session()) {
	echo $OUTPUT->heading(get_string('cantJoinSessionErr1', 'block_ejsapp_collab_session'));
} elseif ( !has_the_user_been_invited_to_any_session() ) {
	echo $OUTPUT->heading(get_string('cantJoinSessionErr2', 'block_ejsapp_collab_session'));
} else {
	echo $OUTPUT->heading(get_string('selectInvitation', 'block_ejsapp_collab_session'));
	$my_invitations = get_sessions_where_i_am_invited();
	echo '<form action="' . "{$CFG->wwwroot}/mod/ejsapp/view.php" . '" method="get">';
	$i = 1;
	foreach ($my_invitations as $master_user=>$ejsapp) {
		$ejsapp_name = get_ejsapp_name($master_user);
		$session = get_collaborative_session_id($master_user);
		if ($i == 1) {
			echo '<input type=radio name="colsession" value="' . $session .
			'" checked>' . get_user_name($master_user) . get_string('invitationMsg1', 'block_ejsapp_collab_session') . "$ejsapp_name" .
			'<br>';
		} else {
			echo '<input type=radio name="colsession" value="' . $session .
				'">' . get_user_name($master_user) . get_string('invitationMsg1', 'block_ejsapp_collab_session') . "$ejsapp_name" . '<br>';
		}
		$i++;
	}

    echo '<br><p align="center"><input type=submit value=' . '"' . get_string('acceptInvitation', 'block_ejsapp_collab_session') . '"' . '></p> </form>';
} // if (is_the_user_participating_in_any_session())

echo $OUTPUT->footer();

?>