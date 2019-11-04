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
 * Minimalistic edit form
 *
 * @package    block
 * @subpackage ejsapp_collab_session
 * @copyright  2012 Luis de la Torre, Ruben Heradio and Carlos Jara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_ejsapp_collab_session_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        if (get_config('block_ejsapp_collab_session', 'Use_enlarge') == 1) {
            $mform->addElement('header', 'enlarge_header', get_string('enlarge_header', 'block_ejsapp_collab_session'));

            $mform->addElement('selectyesno', 'config_use_enlarge', get_string('use_enlarge', 'block_ejsapp_collab_session'));
            $mform->setDefault('config_use_enlarge', '0');
            $mform->setType('config_use_enlarge', PARAM_INT);

            $list_enlarge_IPs = explode(";", get_config('block_ejsapp_collab_session', 'Collab_enlarge_IP'));
            if(is_array($list_enlarge_IPs)) $enlarge_IP = $list_enlarge_IPs[0];
            else  $enlarge_IP = get_config('block_ejsapp_collab_session', 'Collab_enlarge_IP');
            $init_pos = strpos($enlarge_IP, "'");
            $end_pos = strrpos($enlarge_IP, "'");
            if(($init_pos === false) || ($init_pos === $end_pos)) {
                $enlarge_instance_options = array('ENLARGE server 1');
            } else {
                $enlarge_instance_options = array(substr($enlarge_IP,$init_pos+1,$end_pos-$init_pos-1));
            }
            for ($i = 1; $i < count($list_enlarge_IPs); $i++) {
                $enlarge_instance_options_temp = $list_enlarge_IPs[$i];
                $init_pos = strpos($enlarge_instance_options_temp, "'");
                $end_pos = strrpos($enlarge_instance_options_temp, "'");
                if(($init_pos === false) || ($init_pos === $end_pos)) {
                    array_push($enlarge_instance_options, 'ENLARGE server ' . ($i+1));
                } else {
                    array_push($enlarge_instance_options, substr($enlarge_instance_options_temp,$init_pos+1,$end_pos-$init_pos-1));
                }
            }

            $mform->addElement('select', 'config_enlarge_instance', get_string('enlarge_instance', 'block_ejsapp_collab_session'), $enlarge_instance_options);
            $mform->disabledIf('config_enlarge_instance', 'config_use_enlarge', 'eq', 0);
            $mform->setDefault('config_enlarge_instance', '0');
        }

    }

}