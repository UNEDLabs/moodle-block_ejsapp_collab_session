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
 
//block_start_collaborative_session.php:
$string['pluginname'] = 'EJSApp collab session';
$string['block_title'] = 'Collab Session';
$string['start_collaborative_session:view'] = 'View Block for Collaborative Sessions';
$string['goToMasSessBut'] = 'Go to my active session';
$string['goToStudSessBut'] = 'Participate as an invited student';
$string['closeMasSessBut'] = 'Close my active session'; //Also in generate_applet_embedding_code.php
$string['closeStudSessBut'] = 'Leave my active session'; //Also in generate_applet_embedding_code.php
$string['createBut'] = 'Create collaborative session';
$string['navBarCollaborativeSession'] = 'Collaborative session';

//close_collaborative_session.php:
$string['close1'] = 'The collaborative session with lab ';
$string['close2'] = ' has been closed.';
$string['goodbyeStudent'] = 'You have left the collaborative session.';
$string['backToCourse'] = 'Go back to the course';

//master_user.php:
$string['navBarMasterUser'] = 'Selecting lab';
$string['cantJoinSessionErr1'] = 'Error: You are already participating in a collaborative session. Close your active collaborative session before creating a new one.';
$string['selectLabBut'] = 'Select laboratory';
$string['selectColLab'] = 'Select one of the following synchronous collaborative labs:';

//non_master_user.php:
$string['navBarNonMasterUser'] = 'Accepting invitation';
$string['selectInvitation'] = 'Select one of the following invitations:';
$string['invitationMsg1'] = ' has invited you to the following lab: ';
$string['acceptInvitation'] = 'Accept invitation';
$string['cantJoinSessionErr2'] = 'Error: Right now you have no active invitations to any collaborative session.';

//Send_messages.php:
$string['invitationMsg2'] = 'Invited you to a collaborative session.';

//Show_participants.php:
$string['inviteParticipants'] = 'Invite participants';
$string['navBarShowParticipants'] = 'Selecting participants';

//generate_applet_embedding_code.php and many others:
$string['pageTitle']='Lab Collaborative Session';

?>