<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Block instance edit form.
 *
 * @package   block_google_site_creator
 * @copyright 2026 Paul Vincent
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/edit_form.php');

class block_google_site_creator_edit_form extends block_edit_form {
    /**
     * Add per-instance settings shown in "Configure block" form.
     *
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition($mform): void {
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        $mform->addElement(
            'text',
            'config_buttonlabel',
            get_string('instancebuttonlabel', 'block_google_site_creator')
        );
        $mform->setType('config_buttonlabel', PARAM_TEXT);
        $mform->setDefault('config_buttonlabel', get_string('creategooglesite', 'block_google_site_creator'));
        $mform->addHelpButton('config_buttonlabel', 'instancebuttonlabel', 'block_google_site_creator');
    }
}
