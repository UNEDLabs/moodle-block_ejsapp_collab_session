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
$string['goToStudSessBut'] = 'List all my active invitations';
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
$string['default_options_config'] = 'Configure the default (not using Sarlab) communication options for collab sessions';
$string['collaborative_port'] = 'Port for collaborative sessions';
$string['collaborative_port_description'] = 'Port used to establish communication for the collaborative sessions when Sarlab is not used';
$string['sarlab_header_config'] = 'Configure Sarlab settings for all the block instances';
$string['using_sarlab'] = 'Allow using Sarlab in collaborative sessions?';
$string['using_sarlab_help'] = 'This option enables to use Sarlab for routing connections in collaborative sessions.';
$string['sarlab_IP'] = "Name and IP address of the Sarlab server(s)";
$string['sarlab_IP_description'] = "If you want to use Sarlab to establish the communication in the collaborative sessions, you need to provide the IP address of the server that runs the Sarlab system you want to use. Otherwise, this value will not be used, so you can leave the default value. If you have more than one Sarlab server (for example, one at 127.0.0.1 and a second one at 127.0.0.2), insert the IP addresses separated by semicolons: 127.0.0.1;127.0.0.2. Additionally, you can provide a name in order to identify each Sarlab server: 'Sarlab Madrid'127.0.0.1;'Sarlab Huelva'127.0.0.2";
$string['sarlab_port'] = "Sarlab communication port(s)";
$string['sarlab_port_description'] = "If you want to use Sarlab to establish the communication in the collaborative sessions, you need to provide a valid port for establishing the communications with the Sarlab server. Otherwise, this value will not be used, so you can leave the default value. If you have more than one Sarlab server (for example, one using port 443 and a second one also using port 443), insert the values separated by semicolons: 443;443";

//edit_form.php:
$string['sarlab_header'] = 'Configure Sarlab for this block instance';
$string['use_sarlab'] = 'Use Sarlab in collaborative sessions?';

//Capabilities
$string['ejsapp_collab_session:addinstance'] = 'Add a new EJSApp block for collaborative sessions';
$string['ejsapp_collab_session:myaddinstance'] = 'Add a new EJSApp block for collaborative sessions to My home';