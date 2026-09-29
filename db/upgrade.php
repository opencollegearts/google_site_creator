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
 * Upgrade script for block_google_site_creator.
 *
 * @package   block_google_site_creator
 * @copyright 2026 Paul Vincent
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for block_google_site_creator.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_block_google_site_creator_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026031901) {
        $table = new xmldb_table('block_google_site_creator_sites');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'title');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_block_savepoint(true, 2026031901, 'google_site_creator');
    }

    if ($oldversion < 2026031904) {
        // Historical step: previously seeded institution-specific group defaults.
        // Kept for upgrade path continuity; values may be cleared in a later step.
        if (get_config('block_google_site_creator', 'tutorgroupemail') === false) {
            set_config('tutorgroupemail', '', 'block_google_site_creator');
        }
        if (get_config('block_google_site_creator', 'organisationgroupemail') === false) {
            set_config('organisationgroupemail', '', 'block_google_site_creator');
        }
        upgrade_block_savepoint(true, 2026031904, 'google_site_creator');
    }

    if ($oldversion < 2026032900) {
        // Migrate legacy visibility values to portable names.
        $tutorgroup = trim((string)(get_config('block_google_site_creator', 'tutorgroupemail') ?: ''));
        $tutorreplacement = $tutorgroup !== '' ? 'tutor_group' : 'course_teachers';

        $DB->set_field('block_google_site_creator_sites', 'visibility', $tutorreplacement, ['visibility' => 'tutors']);
        $DB->set_field('block_google_site_creator_sites', 'visibility', 'course_group', ['visibility' => 'unit_group']);
        $DB->set_field('block_google_site_creator_sites', 'visibility', 'organisation_group', ['visibility' => 'all_oca']);

        // Drop unused staff group setting if present.
        unset_config('staffgroupemail', 'block_google_site_creator');

        upgrade_block_savepoint(true, 2026032900, 'google_site_creator');
    }

    return true;
}
