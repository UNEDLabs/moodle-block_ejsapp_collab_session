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
 * Block to manage collaborative sessions among EJS simulations
 *
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('manage_collab_db.php');


/**
 * Class that defines the EJSAppCollabSession block
 */
class block_ejsapp_collab_session extends block_list {

    /**
     * Init function for the EJSAppCollabSession block
     */
    function init() {
      $this->title = get_string('block_title', 'block_ejsapp_collab_session');
    }

    public function specialization() {
        if (empty($this->config->use_sarlab)) $this->use_sarlab = '0';
        else $this->use_sarlab = $this->config->use_sarlab;

        if (empty($this->config->sarlab_instance)) $this->sarlab_instance = '0';
        else $this->sarlab_instance = $this->config->sarlab_instance;
    }

    /**
     * Get content function for the EJSAppCollabSession block
     */
    function get_content() {

        global $CFG, $USER, $DB, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }

        // if there aren't collaborative labs then hide the block
        $courseid = $this->page->course->id;
        $collaborative_lab_records = get_all_collaborative_lab_records($courseid);
        if (count($collaborative_lab_records) == 0) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $currentcontext = $this->page->context;

        if ($courseid == SITEID) {
            if (!has_capability('moodle/site:viewparticipants', context_system::instance())) {
                $this->content = '';
                return $this->content;
            }
        } else {
            if (!has_capability('moodle/course:viewparticipants', $currentcontext)) {
                $this->content = '';
                return $this->content;
            }
        }

        $this->content->items[0] = html_writer::img($CFG->wwwroot.'/blocks/ejsapp_collab_session/pix/icon.gif', "Invite participants to a collaborative session", array('style' => 'width:80%'));

    	if (is_the_user_participating_in_any_session()) {
            $am_i_director= am_i_master_user();
            if ($am_i_director) {
                $session_director = $DB->get_record('ejsapp_collab_sessions',array('master_user'=>$USER->id));
                $session_id = $session_director->id;
            } else {
                $session_invited = $DB->get_record('ejsapp_collab_acceptances',array('accepted_user'=>$USER->id));
                $session_id = $session_invited->collaborative_session;
            }

            $content = $OUTPUT->single_button(new moodle_url('/mod/ejsapp/view.php', array('colsession'=>$session_id)), get_string('goToMasSessBut', 'block_ejsapp_collab_session'), 'get');
            $this->content->items[1] = html_writer::div($content, 'collab_button');

    		if ($am_i_director) {
                $close_button = get_string('closeMasSessBut', 'block_ejsapp_collab_session');
    		} else {
                $close_button = get_string('closeStudSessBut', 'block_ejsapp_collab_session');
    		}

            $content = $OUTPUT->single_button(new moodle_url('/blocks/ejsapp_collab_session/close_collab_session.php', array('session'=>$session_id,'courseid'=>$courseid,'contextid'=>$currentcontext->id)), $close_button, 'get');
            $this->content->items[2] = html_writer::div($content, 'collab_button');
    	} else {
    		$master_user_url = new moodle_url('/blocks/ejsapp_collab_session/invite_participants.php', array('courseid'=>$courseid,'contextid'=>$currentcontext->id));
            $content = $OUTPUT->single_button($master_user_url, get_string('createBut', 'block_ejsapp_collab_session'), 'get');
            $this->content->items[1] = html_writer::div($content, 'collab_button');
            if (has_the_user_been_invited_to_any_session()) {
                $participate_in_session_url = $CFG->wwwroot . "/blocks/ejsapp_collab_session/join_collab_session.php?courseid=$courseid&contextid={$currentcontext->id}";
                $content = $OUTPUT->single_button($participate_in_session_url, get_string('goToStudSessBut', 'block_ejsapp_collab_session'), 'get');
                $this->content->items[2] = html_writer::div($content, 'collab_button');
            }
        }

        return $this->content;
    }

    /**
     * Applicable formats for the EJSAppCollabSession block
     */
    function applicable_formats() {
      return array('all' => true, 'my' => false, 'tag' => false);
    }

    /**
     * Add custom html attributes to aid with theming and styling
     *
     * @return array
     */
    function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' block_'. $this->name(); // Append our class to class attribute
        return $attributes;
    }

    /**
     * Enable global configuration
     */
    function has_config() {return true;}

}