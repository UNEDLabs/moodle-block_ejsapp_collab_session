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
 
function print_me_into_navbar($page_caller = null){
	global $PAGE;
	$PAGE->navbar->add(get_string('navBarCollaborativeSession', 'block_ejsapp_collab_session'));
	if ($page_caller) {
		$PAGE->navbar->add($page_caller);
	}
}

global $page_caller;

$courseid = required_param('courseid', PARAM_RAW);
$contextid = required_param('contextid', PARAM_INT);
$context = get_context_instance_by_id($contextid, MUST_EXIST);
$title = get_string('pageTitle', 'block_ejsapp_collab_session');

if ($context->contextlevel != CONTEXT_COURSE) {
	$context->contextlevel = CONTEXT_COURSE;
}

$course = $DB->get_record('course', array('id'=>$context->instanceid), '*', MUST_EXIST);

require_login($course);

$PAGE->set_context($context);
$PAGE->set_url('/blocks/ejsapp_collab_session/init_page.php');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->set_pagelayout('incourse');
print_me_into_navbar($page_caller);

echo $OUTPUT->header();

?>