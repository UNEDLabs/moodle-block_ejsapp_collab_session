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
 * Page where master user selects the users to invite. Those users receive an invitation
 * with "send_messages.php" to join the collaborative session by (1) email and (2) moodle message
 *
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

global $CFG, $PAGE, $DB, $OUTPUT, $USER;

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');
require_once('manage_collab_db.php');
require_once($CFG->dirroot . '/filter/multilang/filter.php');

define('USER_SMALL_CLASS', 20);   // Below this is considered small
define('USER_LARGE_CLASS', 200);  // Above this is considered large
define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

$courseid = required_param('courseid', PARAM_RAW);
$contextid = required_param('contextid', PARAM_INT);

$labid = optional_param('lab_id', 0, PARAM_RAW);
$page = optional_param('page', 0, PARAM_INT);                       // which page to show
$perpage = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // how many per page
$accesssince = optional_param('accesssince', 0, PARAM_INT);         // filter by last access. -1 = never
$search = optional_param('search', '', PARAM_RAW);                  // make sure it is processed with p() or s() when sending to output!
$roleid = optional_param('roleid', 0, PARAM_INT);                   // optional roleid, 0 means all enrolled users (or all on the frontpage)

$context_course = context_course::instance($courseid);
$title = get_string('pageTitle', 'block_ejsapp_collab_session');
$PAGE->set_context($context_course);
$PAGE->set_title($title);
$PAGE->set_heading($title);

$course = $DB->get_record('course', array('id' => $context_course->instanceid), '*', MUST_EXIST);
require_login($course);

$isfrontpage = ($course->id == SITEID);
if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    $PAGE->navbar->add(get_string('navBarCollaborativeSession', 'block_ejsapp_collab_session'));
    $PAGE->navbar->add(get_string('navBarShowParticipants', 'block_ejsapp_collab_session'));
} else {
    $PAGE->set_pagelayout('incourse');
    $PAGE->navbar->add(get_string('navBarCollaborativeSession', 'block_ejsapp_collab_session'));
    $PAGE->navbar->add(get_string('navBarShowParticipants', 'block_ejsapp_collab_session'));
}

if (is_the_user_participating_in_any_session()) {
    $PAGE->set_url('/blocks/ejsapp_collab_session/invite_participants.php', array('courseid' => $courseid, 'contextid' => $contextid));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('cantJoinSessionErr1', 'block_ejsapp_collab_session'));
} else {

    /**
     * returns the course last access
     *
     * @param int $accesssince
     * @return string course last access
     */
    function get_course_lastaccess_sql($accesssince = '')
    {
        if (empty($accesssince)) {
            return '';
        }
        if ($accesssince == -1) { // never
            return 'ul.timeaccess = 0';
        } else {
            return 'ul.timeaccess != 0 AND ul.timeaccess < ' . $accesssince;
        }
    }

    /**
     * returns the user last access
     *
     * @param int $accesssince
     * @return string user last access
     */
    function get_user_lastaccess_sql($accesssince = '')
    {
        if (empty($accesssince)) {
            return '';
        }
        if ($accesssince == -1) { // never
            return 'u.lastaccess = 0';
        } else {
            return 'u.lastaccess != 0 AND u.lastaccess < ' . $accesssince;
        }
    }

    $lab_records = $DB->get_records('ejsapp', array('course'=>$courseid));
    $lab_records = get_available_collab_lab_records($lab_records);
    $i = 1;
    $multilang = new filter_multilang($context_course, array('filter_multilang_force_old'=>0));
    foreach ($lab_records as $lab_record) {
        $lab_name[$lab_record->id] = $multilang->filter($lab_record->name);
        if ($i == 1 && $labid == 0) {
            $labid = $lab_record->id;
        }
        $i++;
    }

    $practiceintro =  $DB->get_field('block_remlab_manager_exp2prc', 'practiceintro', array('ejsappid' => $labid));

    if (get_config('block_ejsapp_collab_session', 'Use_Enlarge') == 1) {
        $enlarge_collab_instance = $DB->get_field('block_remlab_manager_conf', 'enlargeinstance', array('practiceintro' => $practiceintro));
        $enlarge_collab_ips = explode(";", get_config('block_ejsapp_collab_session', 'Collab_enlarge_IP'));
        $ip = substr($enlarge_collab_ips[$enlarge_collab_instance], strrpos($enlarge_collab_ips[$enlarge_collab_instance], "'"));
        $enlargeport = 443;
        $localport = 8079; //49999 //79
        do {
            $localport++;
            $sql = "SELECT * FROM {ejsapp_collab_sessions} WHERE ip = ' $ip 'AND localport = $localport";
        } while ($DB->get_record_sql($sql));
    } else {
        $enlarge_collab_instance = 0;
        $ip = $_SERVER['REMOTE_ADDR'];
        $localport = get_config('block_ejsapp_collab_session', 'collaborative_port');
        $enlargeport = 0;
    }

    $context = context_course::instance($courseid);

    $PAGE->set_url('/blocks/ejsapp_collab_session/invite_participants.php', array('courseid' => $courseid, 'contextid' => $contextid));

    $systemcontext = context_system::instance();

    $rolenamesurl = new moodle_url("$CFG->wwwroot/blocks/ejsapp_collab_session/invite_participants.php?courseid=$courseid&contextid=$contextid&lab_id=$labid&sifirst=&silast=");

    $allroles = get_all_roles();
    $roles = get_profile_roles($context);
    $allrolenames = array();
    if ($isfrontpage) {
        $rolenames = array(0 => get_string('allsiteusers', 'role'));
    } else {
        $rolenames = array(0 => get_string('allparticipants'));
    }


    foreach ($allroles as $role) {
        $allrolenames[$role->id] = strip_tags(role_get_name($role, $context));   // Used in menus etc later on
        if (isset($roles[$role->id])) {
            $rolenames[$role->id] = $allrolenames[$role->id];
        }
    }

    // make sure other roles may not be selected by any means
    if (empty($rolenames[$roleid])) {
        print_error('noparticipants');
    }

    // no roles to display yet?
    // frontpage course is an exception, on the front page course we should display all users
    if (empty($rolenames) && !$isfrontpage) {
        if (has_capability('moodle/role:assign', $context)) {
            redirect($CFG->wwwroot . '/' . $CFG->admin . '/roles/assign.php?contextid=' . $contextid);
        } else {
            print_error('noparticipants');
        }
    }

    $bulkoperations = true;

    $countries = get_string_manager()->get_list_of_countries();

    $strnever = get_string('never');

    $datestring = new stdClass();
    $datestring->year = get_string('year');
    $datestring->years = get_string('years');
    $datestring->day = get_string('day');
    $datestring->days = get_string('days');
    $datestring->hour = get_string('hour');
    $datestring->hours = get_string('hours');
    $datestring->min = get_string('min');
    $datestring->mins = get_string('mins');
    $datestring->sec = get_string('sec');
    $datestring->secs = get_string('secs');

    /// Check to see if groups are being used in this course
    /// and if so, set $currentgroup to reflect the current group

    $groupmode = groups_get_course_groupmode($course);   // Groups are being used
    $currentgroup = groups_get_course_group($course, true);

    if (!$currentgroup) {      // To make some other functions work better later
        $currentgroup = NULL;
    }

    $isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

    if ($course->id === SITEID) {
        $PAGE->navbar->ignore_active();
    }

    echo $OUTPUT->header();

    echo '<div class="userlist">';

    if ($isseparategroups and (!$currentgroup)) {
        // The user is not in the group so show message and exit
        echo $OUTPUT->heading(get_string("notingroup"));
        echo $OUTPUT->footer();
        exit;
    }


    // Should use this variable so that we don't break stuff every time a variable is added or changed.
    $baseurl = new moodle_url('/blocks/ejsapp_collab_session/invite_participants.php', array(
        'courseid' => $courseid,
        'contextid' => $contextid,
        'roleid' => $roleid,
        'perpage' => $perpage,
        'accesssince' => $accesssince,
        'search' => s($search)));

    /// setting up tags
    if ($course->id == SITEID) {
        $filtertype = 'site';
    } else if ($course->id && !$currentgroup) {
        $filtertype = 'course';
        $filterselect = $course->id;
    } else {
        $filtertype = 'group';
        $filterselect = $currentgroup;
    }

    /// Get the hidden field list
    if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
        $hiddenfields = array();  // teachers and admins are allowed to see everything
    } else {
        $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
    }

    if (isset($hiddenfields['lastaccess'])) {
        // do not allow access since filtering
        $accesssince = 0;
    }

    /// Print settings and things in a table across the top
    $controlstable = new html_table();
    $controlstable->attributes['class'] = 'controls';
    $controlstable->data[] = new html_table_row();

    /// Print my course menus
    if ($mycourses = enrol_get_my_courses()) {
        $courselist = array();
        $popupurl = new moodle_url('/blocks/ejsapp_collab_session/invite_participants.php?courseid='.$courseid.'&contextid='.$contextid. '&lab_id=' . $labid . '&roleid=' . $roleid . '&sifirst=&silast=');
        foreach ($mycourses as $mycourse) {
            $courselist[$mycourse->id] = format_string($mycourse->shortname);
        }
        if (has_capability('moodle/site:viewparticipants', $systemcontext)) {
            unset($courselist[SITEID]);
            $courselist = array(SITEID => format_string($SITE->shortname)) + $courselist;
        }
        $select = new single_select($popupurl, 'id', $courselist, $course->id, array('' => 'choosedots'), 'courseform');
        $select->set_label(get_string('mycourses'));
        $controlstable->data[0]->cells[] = $OUTPUT->render($select);
    } /// Print my course menus

    $controlstable->data[0]->cells[] = groups_print_course_menu($course, $baseurl->out(), true);

    if (!isset($hiddenfields['lastaccess'])) {
        // get minimum lastaccess for this course and display a dropbox to filter by lastaccess going back this far.
        // we need to make it diferently for normal courses and site course
        if (!$isfrontpage) {
            $minlastaccess = $DB->get_field_sql('SELECT min(timeaccess)
                                             FROM {user_lastaccess}
                                             WHERE courseid = ?
                                             AND timeaccess != 0', array($course->id));
            $lastaccess0exists = $DB->record_exists('user_lastaccess', array('courseid' => $course->id, 'timeaccess' => 0));
        } else {
            $minlastaccess = $DB->get_field_sql('SELECT min(lastaccess)
                                             FROM {user}
                                             WHERE lastaccess != 0');
            $lastaccess0exists = $DB->record_exists('user', array('lastaccess' => 0));
        }

        $now = usergetmidnight(time());
        $timeaccess = array();
        $baseurl->remove_params('accesssince');
        $baseurl->remove_params('lab_id');
        $baseurl->param('lab_id', $labid);

        // makes sense for this to go first.
        $timeoptions[0] = get_string('selectperiod');

        // days
        for ($i = 1; $i < 7; $i++) {
            if (strtotime('-' . $i . ' days', $now) >= $minlastaccess) {
                $timeoptions[strtotime('-' . $i . ' days', $now)] = get_string('numdays', 'moodle', $i);
            }
        }
        // weeks
        for ($i = 1; $i < 10; $i++) {
            if (strtotime('-' . $i . ' weeks', $now) >= $minlastaccess) {
                $timeoptions[strtotime('-' . $i . ' weeks', $now)] = get_string('numweeks', 'moodle', $i);
            }
        }
        // months
        for ($i = 2; $i < 12; $i++) {
            if (strtotime('-' . $i . ' months', $now) >= $minlastaccess) {
                $timeoptions[strtotime('-' . $i . ' months', $now)] = get_string('nummonths', 'moodle', $i);
            }
        }
        // try a year
        if (strtotime('-1 year', $now) >= $minlastaccess) {
            $timeoptions[strtotime('-1 year', $now)] = get_string('lastyear');
        }

        if (!empty($lastaccess0exists)) {
            $timeoptions[-1] = get_string('never');
        }

        if (count($timeoptions) > 1) {
            $select = new single_select($baseurl, 'accesssince', $timeoptions, $accesssince, null, 'timeoptions');
            $select->set_label(get_string('usersnoaccesssince'));
            $controlstable->data[0]->cells[] = $OUTPUT->render($select);
        }
    } // if (!isset($hiddenfields['lastaccess']))

    if ($currentgroup and (!$isseparategroups or has_capability('moodle/site:accessallgroups', $context))) {    /// Display info about the group
        if ($group = groups_get_group($currentgroup)) {
            if (!empty($group->description) or (!empty($group->picture) and empty($group->hidepicture))) {
                $groupinfotable = new html_table();
                $groupinfotable->attributes['class'] = 'groupinfobox';
                $picturecell = new html_table_cell();
                $picturecell->attributes['class'] = 'left side picture';
                $picturecell->text = print_group_picture($group, $course->id, true, true, false);

                $contentcell = new html_table_cell();
                $contentcell->attributes['class'] = 'content';

                $contentheading = $group->name;
                if (has_capability('moodle/course:managegroups', $context)) {
                    $aurl = new moodle_url('/group/group.php', array('id' => $group->id, 'courseid' => $group->courseid));
                    $contentheading .= '&nbsp;' . $OUTPUT->action_icon($aurl, new pix_icon('t/edit', get_string('editgroupprofile')));
                }

                $group->description = file_rewrite_pluginfile_urls($group->description, 'pluginfile.php', $contextid, 'group', 'description', $group->id);
                if (!isset($group->descriptionformat)) {
                    $group->descriptionformat = FORMAT_MOODLE;
                }
                $options = array('overflowdiv' => true);
                $contentcell->text = $OUTPUT->heading($contentheading, 3) . format_text($group->description, $group->descriptionformat, $options);
                $groupinfotable->data[] = new html_table_row(array($picturecell, $contentcell));
                echo html_writer::table($groupinfotable);
            }
        }
    }

    /// Define a table showing a list of users in the current role selection
    $tablecolumns = array('userpic', 'fullname');
    $tableheaders = array(get_string('userpic'), get_string('fullnameuser'));
    if (!isset($hiddenfields['city'])) {
        $tablecolumns[] = 'city';
        $tableheaders[] = get_string('city');
    }
    if (!isset($hiddenfields['country'])) {
        $tablecolumns[] = 'country';
        $tableheaders[] = get_string('country');
    }
    if (!isset($hiddenfields['lastaccess'])) {
        $tablecolumns[] = 'lastaccess';
        $tableheaders[] = get_string('lastaccess');
    }

    $tablecolumns[] = 'select';
    $tableheaders[] = get_string('select');

    $table = new flexible_table('user-index-participants-' . $course->id);

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl->out());

    if (!isset($hiddenfields['lastaccess'])) {
        $table->sortable(true, 'lastaccess', SORT_DESC);
    }

    $table->no_sorting('roles');
    $table->no_sorting('groups');
    $table->no_sorting('groupings');
    $table->no_sorting('select');

    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('align','center');
    $table->set_attribute('id', 'participants');
    $table->set_attribute('class', 'generaltable generalbox');

    $table->set_control_variables(array(
                TABLE_VAR_SORT      => 'ssort',
                TABLE_VAR_HIDE      => 'shide',
                TABLE_VAR_SHOW      => 'sshow',
                TABLE_VAR_IFIRST    => 'sifirst',
                TABLE_VAR_ILAST     => 'silast',
                TABLE_VAR_PAGE      => 'spage'
    ));
    $table->setup();

    // we are looking for all users with this role assigned in this context or higher
    $contextlist = $context->get_parent_context_ids(true);

    list($esql, $params) = get_enrolled_sql($context, NULL, $currentgroup, true);

    $joins = array("FROM {user} u");
    $wheres = array();

    if ($isfrontpage) {
        $select = "SELECT u.id, u.username, u.firstname, u.lastname,
                        u.email, u.city, u.country, u.picture,
                        u.lang, u.timezone, u.maildisplay, u.imagealt,
                        u.lastaccess";
        $joins[] = "JOIN ($esql) e ON e.id = u.id"; // everybody on the frontpage usually
        if ($accesssince) {
            $wheres[] = get_user_lastaccess_sql($accesssince);
        }
    } else {
        $select = "SELECT u.id, u.username, u.firstname, u.lastname,
                        u.email, u.city, u.country, u.picture,
                        u.lang, u.timezone, u.maildisplay, u.imagealt,
                        COALESCE(ul.timeaccess, 0) AS lastaccess";
        $joins[] = "JOIN ($esql) e ON e.id = u.id"; // course enrolled users only
        $joins[] = "LEFT JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = :courseid)"; // not everybody accessed course yet
        $params['courseid'] = $course->id;
        if ($accesssince) {
            $wheres[] = get_course_lastaccess_sql($accesssince);
        }
    }

    $joinon = 'u.id';
    $contextlevel = CONTEXT_USER;
    $tablealias = 'ctx';
    $ccselect = ", " . context_helper::get_preload_record_columns_sql($tablealias);
    $ccjoin = "LEFT JOIN {context} $tablealias ON ($tablealias.instanceid = $joinon AND $tablealias.contextlevel = $contextlevel)";
    $select .= $ccselect;
    $joins[] = $ccjoin;

    // limit list to users with some role only
    if ($roleid) {
        $wheres[] = "u.id IN (SELECT userid FROM {role_assignments} WHERE roleid = :roleid AND contextid IN (" . implode(',',$contextlist) . "))";
        $params['roleid'] = $roleid;
    }

    $from = implode("\n", $joins);
    if ($wheres) {
        $where = "WHERE " . implode(" AND ", $wheres);
    } else {
        $where = "";
    }

    $totalcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);

    if (!empty($search)) {
        $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
        $wheres[] = "(" . $DB->sql_like($fullname, ':search1', false, false) .
                    " OR " . $DB->sql_like('email', ':search2', false, false) .
                    " OR " . $DB->sql_like('idnumber', ':search3', false, false) . ") ";
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "%$search%";
    }

    list($twhere, $tparams) = $table->get_sql_where();
    if ($twhere) {
        $wheres[] = $twhere;
        $params = array_merge($params, $tparams);
    }

    $from = implode("\n", $joins);
    if ($wheres) {
        $where = "WHERE " . implode(" AND ", $wheres);
    } else {
        $where = "";
    }

    if ($table->get_sql_sort()) {
        $sort = ' ORDER BY ' . $table->get_sql_sort();
    } else {
        $sort = '';
    }

    $matchcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);

    $table->initialbars(true);
    $table->pagesize($perpage, $matchcount);

    // list of users at the current visible page - paging makes it relatively short
    $userlist = $DB->get_recordset_sql("$select $from $where $sort", $params, $table->get_page_start(), $table->get_page_size());

    /// If there are multiple Roles in the course, then show a drop down menu for switching
    if (count($rolenames) > 1) {
        $contents = html_writer::label(get_string('currentrole', 'role') . '&nbsp;', 'rolesform_jump') .
            $OUTPUT->single_select($rolenamesurl, 'roleid', $rolenames, $roleid, null, 'rolesform');
    } else if (count($rolenames) == 1) {
        // when all users with the same role - print its name
        $rolename = reset($rolenames);
        $contents =  get_string('role') . get_string('labelsep', 'langconfig') . $rolename;
    }
    echo html_writer::div($contents, 'rolesform');

    // <Select rem lab pulldown menu>
    $select = new single_select($baseurl, 'lab_id', $lab_name, $labid, null, 'formatmenu');
    $select->set_label(get_string('selectLabBut', 'block_ejsapp_collab_session'));
    $remlabslistcell = new html_table_cell();
    $remlabslistcell->attributes['class'] = 'right';
    $remlabslistcell->text = $OUTPUT->render($select);
    $controlstable->data[0]->cells[] = $remlabslistcell;

    echo html_writer::table($controlstable);
    // </Select rem lab pulldown menu>

    if ($roleid > 0) {
        $a = new stdClass();
        $a->number = $totalcount;
        $a->role = $rolenames[$roleid];
        $heading = format_string(get_string('xuserswiththerole', 'role', $a));

        if ($currentgroup and $group) {
            $a->group = $group->name;
            $heading .= ' ' . format_string(get_string('ingroup', 'role', $a));
        }

        if ($accesssince) {
            $a->timeperiod = $timeoptions[$accesssince];
            $heading .= ' ' . format_string(get_string('inactiveformorethan', 'role', $a));
        }

        $heading .= ": $a->number";
        if (user_can_assign($context, $roleid)) {
            $text = html_writer::img($OUTPUT->pix_url('i/edit'), '', array('class'=>'icon'));
            $heading .= html_writer::link(new moodle_url('roles/assign.php', array('roleid'=>$roleid, 'contextid'=>$contextid)), $text);
        }
        echo $OUTPUT->heading($heading, 3);

    } else {
        if ($course->id != SITEID && has_capability('moodle/course:enrolreview', $context)) {
            $editlink = $OUTPUT->action_icon(new moodle_url('/enrol/users.php', array('id' => $course->id)), new pix_icon('i/edit', get_string('edit')));
        } else {
            $editlink = '';
        }
        if ($course->id == SITEID and $roleid < 0) {
            $strallparticipants = get_string('allsiteusers', 'role');
        } else {
            $strallparticipants = get_string('allparticipants');
        }
        if ($matchcount < $totalcount) {
            echo $OUTPUT->heading($strallparticipants . get_string('labelsep', 'langconfig') . $matchcount . '/' . $totalcount . $editlink, 3);
        } else {
            echo $OUTPUT->heading($strallparticipants . get_string('labelsep', 'langconfig') . $matchcount . $editlink, 3);
        }
    }

    echo html_writer::start_tag('form', array('action'=>"send_messages.php?courseid=$courseid&contextid=$contextid&labid=$labid&localport=$localport&ip=$ip&enlargeport=$enlargeport&enlarge_collab_conf=$enlarge_collab_conf", 'method'=>'post', 'id'=>'participantsform')) .
         html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey())) . html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'returnto', 'value'=>'s(me())'));

    $countrysort = (strpos($sort, 'country') !== false);
    $timeformat = get_string('strftimedate');

    if ($userlist) {
        $usersprinted = array();
        foreach ($userlist as $user) {
            if ($user->id != $USER->id) { //Prevent the director of the session form also appearing in the list
                if (in_array($user->id, $usersprinted)) { /// Prevent duplicates by r.hidden - MDL-13935
                    continue;
                }
                $usersprinted[] = $user->id; /// Add new user to the array of users printed

                context_helper::preload_from_record($user);

                if ($user->lastaccess) {
                    $lastaccess = format_time(time() - $user->lastaccess, $datestring);
                } else {
                    $lastaccess = $strnever;
                }

                if (empty($user->country)) {
                    $country = '';
                } else {
                    if ($countrysort) {
                        $country = '(' . $user->country . ') ' . $countries[$user->country];
                    } else {
                        $country = $countries[$user->country];
                    }
                }

                $usercontext = context_user::instance($user->id);

                if (!isset($user->firstnamephonetic)) $user->firstnamephonetic = $user->firstname;
                if (!isset($user->lastnamephonetic)) $user->lastnamephonetic = $user->lastname;
                if (!isset($user->middlename)) $user->middlename = '';
                if (!isset($user->alternatename)) $user->alternatename = '';

                if ($piclink = ($USER->id == $user->id || has_capability('moodle/user:viewdetails', $context) || has_capability('moodle/user:viewdetails', $usercontext))) {
                    $profilelink = '<strong><a href="' . $CFG->wwwroot . '/user/view.php?id=' . $user->id . '&amp;course=' . $course->id . '">' . fullname($user) . '</a></strong>';
                } else {
                    $profilelink = '<strong>' . fullname($user) . '</strong>';
                }

                $data = array($OUTPUT->user_picture($user, array('size' => 35, 'courseid' => $course->id)), $profilelink);

                if (!isset($hiddenfields['city'])) {
                    $data[] = $user->city;
                }
                if (!isset($hiddenfields['country'])) {
                    $data[] = $country;
                }
                if (!isset($hiddenfields['lastaccess'])) {
                    $data[] = $lastaccess;
                }

                if (isset($userlist_extra) && isset($userlist_extra[$user->id])) {
                    $ras = $userlist_extra[$user->id]['ra'];
                    $rastring = '';
                    foreach ($ras AS $key => $ra) {
                        $rolename = $allrolenames[$ra['roleid']];
                        if ($ra['ctxlevel'] == CONTEXT_COURSECAT) {
                            $rastring .= $rolename . ' @ ' . '<a href="' . $CFG->wwwroot . '/course/category.php?id=' . $ra['ctxinstanceid'] . '">' . s($ra['ccname']) . '</a>';
                        } elseif ($ra['ctxlevel'] == CONTEXT_SYSTEM) {
                            $rastring .= $rolename . ' - ' . get_string('globalrole', 'role');
                        } else {
                            $rastring .= $rolename;
                        }
                    }
                    $data[] = $rastring;
                    if ($groupmode != 0) {
                        // htmlescape with s() and implode the array
                        $data[] = implode(', ', array_map('s', $userlist_extra[$user->id]['group']));
                        $data[] = implode(', ', array_map('s', $userlist_extra[$user->id]['gping']));
                    }
                }

                $data[] = '<input type="checkbox" class="usercheckbox" name="user' . $user->id . '" />';
                $table->add_data($data);
            }
        } // foreach ($userlist as $user)
    } // if ($userlist)

    $table->finish_html();

    $contents = html_writer::empty_tag('input', array('type'=>'button', 'id'=>'checkall', 'value'=>get_string('selectall'))) .
                html_writer::empty_tag('input', array('type'=>'button', 'id'=>'checknone', 'value'=>get_string('deselectall'))) .
                html_writer::empty_tag('input', array('type'=>'submit', 'id'=>'invite_participants', 'value'=>get_string('inviteParticipants', 'block_ejsapp_collab_session')));
    echo html_writer::empty_tag('br') . html_writer::div($contents,'buttons');

    $module = array('name' => 'core_user', 'fullpath' => '/user/module.js');

    if (has_capability('moodle/site:viewparticipants', $context) && $totalcount > ($perpage * 3)) {
        echo html_writer::start_tag('form', array('action'=>'invite_participants.php', 'class'=>'searchform'));
        $contents = html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'courseid', 'value'=>$courseid)) .
                    html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'contextid', 'value'=>$contextid)) .
                    get_string('search') . ':&nbsp;' . "\n" .
                    html_writer::empty_tag('input', array('type'=>'text', 'name'=>'search', 'value'=>s($search))) . '&nbsp;' .
                    html_writer::empty_tag('input', array('value'=>get_string('search')));
        echo html_writer::div($contents);
        echo html_writer::end_tag('form') . "\n";
    }

    $perpageurl = clone($baseurl);
    $perpageurl->remove_params('lab_id');
    $perpageurl->param('lab_id', $labid);
    $perpageurl->remove_params('perpage');
    if ($perpage == SHOW_ALL_PAGE_SIZE) {
        $perpageurl->param('perpage', DEFAULT_PAGE_SIZE);
        echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showperpage', '', DEFAULT_PAGE_SIZE)), array(), 'showall');
    } else if ($matchcount > 0 && $perpage < $matchcount) {
        $perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
        echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showall', '', $matchcount)), array(), 'showall');
    }

    echo '</div>';  // userlist

    if ($userlist) {
        $userlist->close();
    }

}

echo $OUTPUT->footer();