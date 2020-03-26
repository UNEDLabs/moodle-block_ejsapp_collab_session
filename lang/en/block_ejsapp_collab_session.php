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
 * English strings
 *
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
//block_ejsapp_collab_session.php:
$string['pluginname'] = 'EJSApp collab session';
$string['block_title'] = 'EJSApp Collab Session';
$string['start_collaborative_session:view'] = 'View Block for Collaborative Sessions';
$string['goToMasSessBut'] = 'Go to my active session';
$string['goToStudSessBut'] = 'List my active invitations';
$string['closeMasSessBut'] = 'Close my active session';
$string['closeStudSessBut'] = 'Leave this session';
$string['createBut'] = 'Create collaborative session';
$string['navBarCollaborativeSession'] = 'Collaborative session';

//close_collab_session.php:
$string['close1'] = 'The collaborative session with lab ';
$string['close2'] = ' has been closed.';
$string['goodbyeStudent'] = 'You have left the collaborative session.';
$string['backToCourse'] = 'Go back to the course';

//join_collab_session.php:
$string['navBarNonMasterUser'] = 'Accepting invitation';
$string['selectInvitation'] = 'Select one of the following invitations:';
$string['invitationMsg1'] = ' has invited you to a collaborative session with this lab: ';
$string['acceptInvitation'] = 'Accept invitation';
$string['cantJoinSessionErr2'] = 'Error: Right now you have no active invitations to any collaborative session.';

//send_messages.php:
$string['invitationMsg2'] = 'Join this session.';

//invite_participants.php:
$string['cantJoinSessionErr1'] = 'Error: You are already participating in a collaborative session. Close your active collaborative session before creating a new one.';
$string['inviteParticipants'] = 'Invite participants';
$string['navBarShowParticipants'] = 'Selecting participants';
$string['selectLabBut'] = 'Select a laboratory';

//generate_embedding_code.php and many others:
$string['pageTitle'] = 'Lab Collaborative Session';

//settings.php:
$string['default_options_config'] = 'Configure the default (not using ENLARGE) communication options for collab sessions';
$string['collaborative_port'] = 'Port for collaborative sessions';
$string['collaborative_port_description'] = 'Port used to establish communication for the collaborative sessions when ENLARGE is not used';
$string['enlarge_header_config'] = 'Configure ENLARGE settings for all the block instances';
$string['using_enlarge'] = 'Allow using ENLARGE in collaborative sessions?';
$string['using_enlarge_help'] = 'This option enables to use ENLARGE for routing connections in collaborative sessions.';

//edit_form.php:
$string['enlarge_header'] = 'Configure ENLARGE for this block instance';
$string['use_enlarge'] = 'Use ENLARGE in collaborative sessions?';
$string['enlarge_instance'] = "ENLARGE server for the collab sessions";
$string['enlarge_instance_help'] = "The order corresponds to the one used for the values in the myFrontier_IP variable configured at the Remlab Manager block settings page";

//Capabilities
$string['ejsapp_collab_session:addinstance'] = 'Add a new EJSApp block for collaborative sessions';
$string['ejsapp_collab_session:myaddinstance'] = 'Add a new EJSApp block for collaborative sessions to My home';

//Privacy
$string['privacy:metadata'] = 'The EJSApp Collab Session block only configures collab sessions between users.';