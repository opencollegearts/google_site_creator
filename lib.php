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
 * Library functions for block_google_site_creator.
 *
 * @package   block_google_site_creator
 * @copyright 2026 Paul Vincent
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Visibility: share with course teachers via Moodle enrolment emails.
 */
const BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_TEACHERS = 'course_teachers';

/**
 * Visibility: share with all course participants via Moodle enrolment emails.
 */
const BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_PARTICIPANTS = 'course_participants';

/**
 * Visibility: share with configured tutor Google Group.
 */
const BLOCK_GOOGLE_SITE_CREATOR_VIS_TUTOR_GROUP = 'tutor_group';

/**
 * Visibility: share with course-level Google Group.
 */
const BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_GROUP = 'course_group';

/**
 * Visibility: share with organisation-wide Google Group.
 */
const BLOCK_GOOGLE_SITE_CREATOR_VIS_ORGANISATION_GROUP = 'organisation_group';

/**
 * Legacy visibility values kept for migration/aliases.
 */
const BLOCK_GOOGLE_SITE_CREATOR_VIS_LEGACY_TUTORS = 'tutors';
const BLOCK_GOOGLE_SITE_CREATOR_VIS_LEGACY_UNIT_GROUP = 'unit_group';
const BLOCK_GOOGLE_SITE_CREATOR_VIS_LEGACY_ALL_OCA = 'all_oca';

/**
 * Get the Google Site template ID for a course (course-level or site default).
 *
 * @param int $courseid
 * @return string|null Template ID or null if not configured
 */
function block_google_site_creator_get_template_id(int $courseid): ?string {
    global $DB;
    $course = $DB->get_record('block_google_site_creator_course', ['courseid' => $courseid], 'template_id');
    if ($course && !empty(trim($course->template_id ?? ''))) {
        return trim($course->template_id);
    }
    $default = get_config('block_google_site_creator', 'defaulttemplate');
    return ($default !== false && trim($default) !== '') ? trim($default) : null;
}

/**
 * Get the course Google Group email (optional).
 *
 * @param int $courseid
 * @return string|null
 */
function block_google_site_creator_get_unit_group(int $courseid): ?string {
    global $DB;
    $course = $DB->get_record('block_google_site_creator_course', ['courseid' => $courseid], 'unit_group_email');
    if ($course && !empty(trim($course->unit_group_email ?? ''))) {
        return trim($course->unit_group_email);
    }
    return null;
}

/**
 * Get the configured tutor Google Group email, or null if not set.
 *
 * @return string|null
 */
function block_google_site_creator_get_tutor_group_email(): ?string {
    $email = get_config('block_google_site_creator', 'tutorgroupemail');
    if ($email === false) {
        return null;
    }
    $email = trim((string)$email);
    return $email !== '' ? $email : null;
}

/**
 * Get the configured organisation-wide Google Group email, or null if not set.
 *
 * @return string|null
 */
function block_google_site_creator_get_organisation_group_email(): ?string {
    $email = get_config('block_google_site_creator', 'organisationgroupemail');
    if ($email === false) {
        return null;
    }
    $email = trim((string)$email);
    return $email !== '' ? $email : null;
}

/**
 * Normalise a legacy or current visibility value to a current constant.
 *
 * @param string $visibility
 * @param int $courseid Used when mapping legacy tutors with no tutor group configured
 * @return string
 */
function block_google_site_creator_normalise_visibility(string $visibility, int $courseid = 0): string {
    switch ($visibility) {
        case BLOCK_GOOGLE_SITE_CREATOR_VIS_LEGACY_TUTORS:
            return block_google_site_creator_get_tutor_group_email()
                ? BLOCK_GOOGLE_SITE_CREATOR_VIS_TUTOR_GROUP
                : BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_TEACHERS;
        case BLOCK_GOOGLE_SITE_CREATOR_VIS_LEGACY_UNIT_GROUP:
            return BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_GROUP;
        case BLOCK_GOOGLE_SITE_CREATOR_VIS_LEGACY_ALL_OCA:
            return BLOCK_GOOGLE_SITE_CREATOR_VIS_ORGANISATION_GROUP;
        default:
            return $visibility;
    }
}

/**
 * Whether a visibility option is currently available for a course.
 *
 * @param string $visibility
 * @param int $courseid
 * @return bool
 */
function block_google_site_creator_is_visibility_available(string $visibility, int $courseid): bool {
    $visibility = block_google_site_creator_normalise_visibility($visibility, $courseid);
    $options = block_google_site_creator_get_visibility_options($courseid);
    return array_key_exists($visibility, $options);
}

/**
 * Build visibility select options for create/edit forms.
 *
 * Moodle enrolment-based options are always available. Google Group options
 * appear only when the corresponding group email is configured.
 *
 * @param int $courseid
 * @return array<string,string> value => label
 */
function block_google_site_creator_get_visibility_options(int $courseid): array {
    $options = [
        BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_TEACHERS =>
            get_string('visibility_course_teachers', 'block_google_site_creator'),
        BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_PARTICIPANTS =>
            get_string('visibility_course_participants', 'block_google_site_creator'),
    ];

    if (block_google_site_creator_get_tutor_group_email()) {
        $options[BLOCK_GOOGLE_SITE_CREATOR_VIS_TUTOR_GROUP] =
            get_string('visibility_tutor_group', 'block_google_site_creator');
    }
    if (block_google_site_creator_get_unit_group($courseid)) {
        $options[BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_GROUP] =
            get_string('visibility_course_group', 'block_google_site_creator');
    }
    if (block_google_site_creator_get_organisation_group_email()) {
        $options[BLOCK_GOOGLE_SITE_CREATOR_VIS_ORGANISATION_GROUP] =
            get_string('visibility_organisation_group', 'block_google_site_creator');
    }

    return $options;
}

/**
 * Collect emails from enrolled users, optionally limited by capability.
 *
 * @param int $courseid
 * @param string $capability Empty string for all participants
 * @param int|null $excludeuserid Optional user id to skip (e.g. site creator)
 * @return string[] Lowercased unique emails
 */
function block_google_site_creator_get_enrolment_emails(
    int $courseid,
    string $capability = '',
    ?int $excludeuserid = null
): array {
    $context = context_course::instance($courseid);
    $users = get_enrolled_users($context, $capability, 0, 'u.id, u.email', null, 0, 0, true);
    $emails = [];
    foreach ($users as $user) {
        if ($excludeuserid !== null && (int)$user->id === $excludeuserid) {
            continue;
        }
        $email = trim((string)($user->email ?? ''));
        if ($email === '' || !validate_email($email)) {
            continue;
        }
        $emails[strtolower($email)] = $email;
    }
    return array_values($emails);
}

/**
 * Resolve Drive share targets for a visibility setting.
 *
 * @param string $visibility
 * @param int $courseid
 * @param int|null $excludeuserid Skip this user for Moodle-based shares
 * @return array<int,array{email:string,type:string,role:string}>
 */
function block_google_site_creator_resolve_share_targets(
    string $visibility,
    int $courseid,
    ?int $excludeuserid = null
): array {
    $visibility = block_google_site_creator_normalise_visibility($visibility, $courseid);
    $targets = [];

    if ($visibility === BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_TEACHERS) {
        foreach (block_google_site_creator_get_enrolment_emails(
            $courseid,
            'moodle/course:manageactivities',
            $excludeuserid
        ) as $email) {
            $targets[] = ['email' => $email, 'type' => 'user', 'role' => 'writer'];
        }
        return $targets;
    }

    if ($visibility === BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_PARTICIPANTS) {
        foreach (block_google_site_creator_get_enrolment_emails($courseid, '', $excludeuserid) as $email) {
            $targets[] = ['email' => $email, 'type' => 'user', 'role' => 'writer'];
        }
        return $targets;
    }

    if ($visibility === BLOCK_GOOGLE_SITE_CREATOR_VIS_TUTOR_GROUP) {
        $email = block_google_site_creator_get_tutor_group_email();
        if ($email) {
            $targets[] = ['email' => $email, 'type' => 'group', 'role' => 'writer'];
        }
        return $targets;
    }

    if ($visibility === BLOCK_GOOGLE_SITE_CREATOR_VIS_COURSE_GROUP) {
        $email = block_google_site_creator_get_unit_group($courseid);
        if ($email) {
            $targets[] = ['email' => $email, 'type' => 'group', 'role' => 'writer'];
        }
        return $targets;
    }

    if ($visibility === BLOCK_GOOGLE_SITE_CREATOR_VIS_ORGANISATION_GROUP) {
        $email = block_google_site_creator_get_organisation_group_email();
        if ($email) {
            $targets[] = ['email' => $email, 'type' => 'group', 'role' => 'reader'];
        }
        return $targets;
    }

    return $targets;
}

/**
 * Emails this plugin considers "managed" when syncing visibility (safe to remove).
 *
 * @param int $courseid
 * @return array<string,true> Lowercased email => true
 */
function block_google_site_creator_get_managed_share_emails(int $courseid): array {
    $known = [];
    foreach ([
        block_google_site_creator_get_tutor_group_email(),
        block_google_site_creator_get_organisation_group_email(),
        block_google_site_creator_get_unit_group($courseid),
    ] as $email) {
        if ($email) {
            $known[strtolower($email)] = true;
        }
    }
    // Include all course participant emails so Moodle-based shares can be revoked on change.
    foreach (block_google_site_creator_get_enrolment_emails($courseid) as $email) {
        $known[strtolower($email)] = true;
    }
    return $known;
}

/**
 * Get all Google Sites with organisation-group visibility (optional discovery hook).
 *
 * @param int $limit
 * @return stdClass[]
 */
function block_google_site_creator_get_sites_for_index(int $limit = 100): array {
    global $DB;
    return $DB->get_records(
        'block_google_site_creator_sites',
        ['visibility' => BLOCK_GOOGLE_SITE_CREATOR_VIS_ORGANISATION_GROUP],
        'timecreated DESC',
        '*',
        0,
        $limit
    );
}

/**
 * Build card objects for Google Sites for use with discovery/listing templates.
 *
 * @param stdClass[] $sites
 * @return stdClass[]
 */
function block_google_site_creator_build_cards_for_grid(array $sites): array {
    global $OUTPUT;
    $cards = [];
    foreach ($sites as $site) {
        $user = \core_user::get_user($site->userid, '*', MUST_EXIST);
        $card = new stdClass();
        $card->id = $site->id;
        $card->title = format_string($site->title);
        $card->summary = !empty($site->description)
            ? shorten_text(strip_tags((string)$site->description), 200)
            : get_string('pluginname', 'block_google_site_creator');
        $card->date = userdate($site->timecreated);
        $card->url = $site->url;
        $card->userpicture = $OUTPUT->user_picture($user, ['size' => 48, 'link' => false]);
        $card->fullname = fullname($user);
        $card->status = get_string('pluginname', 'block_google_site_creator');
        $card->bannerurl = block_google_site_creator_get_site_banner_url($site);
        $card->sorttime = $site->timecreated;
        $cards[] = $card;
    }
    return $cards;
}

/**
 * Get service account JSON (path or inline). Returns decoded array or null.
 *
 * @return array|null
 */
function block_google_site_creator_get_service_account_json(): ?array {
    $config = get_config('block_google_site_creator', 'serviceaccountjson');
    if (empty($config)) {
        return null;
    }
    $config = trim($config);
    if (strpos($config, '{') === 0) {
        $decoded = json_decode($config, true);
        return is_array($decoded) ? $decoded : null;
    }
    global $CFG;
    $path = $config;
    if (!str_starts_with($path, '/')) {
        $path = $CFG->dataroot . '/' . $path;
    }
    if (!is_readable($path)) {
        return null;
    }
    $content = file_get_contents($path);
    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : null;
}

/**
 * Get delegated Workspace user email (domain-wide delegation subject), if configured.
 *
 * @return string|null
 */
function block_google_site_creator_get_delegated_user(): ?string {
    $email = get_config('block_google_site_creator', 'delegateduser');
    if ($email === false) {
        return null;
    }
    $email = trim((string)$email);
    return $email !== '' ? $email : null;
}

/**
 * Check whether a Moodle user email is eligible for ownership transfer.
 *
 * Empty allowedemaildomains means any non-empty valid email is eligible.
 *
 * @param string|null $email
 * @return bool
 */
function block_google_site_creator_can_transfer_to_user_email(?string $email): bool {
    $email = trim((string)$email);
    if ($email === '' || !validate_email($email)) {
        return false;
    }
    $domains = get_config('block_google_site_creator', 'allowedemaildomains');
    if ($domains === false || trim((string)$domains) === '') {
        return true;
    }
    $parts = explode('@', $email);
    if (count($parts) !== 2) {
        return false;
    }
    $userdomain = strtolower($parts[1]);
    foreach (preg_split('/\s*,\s*/', (string)$domains) as $domain) {
        $domain = strtolower(trim($domain));
        if ($domain !== '' && $domain === $userdomain) {
            return true;
        }
    }
    return false;
}

/**
 * Get a user's created Google Site for a course.
 *
 * @param int $userid
 * @param int $courseid
 * @return stdClass|null
 */
function block_google_site_creator_get_user_site_for_course(int $userid, int $courseid): ?stdClass {
    global $DB;
    $record = $DB->get_record(
        'block_google_site_creator_sites',
        ['userid' => $userid, 'courseid' => $courseid],
        '*',
        IGNORE_MULTIPLE
    );
    return $record ?: null;
}

/**
 * Apply visibility share targets, removing previously managed permissions first.
 *
 * @param \block_google_site_creator\drive_service $drive
 * @param string $fileid
 * @param int $courseid
 * @param string $visibility
 * @param int|null $excludeuserid
 * @return bool True if all target grants succeeded
 */
function block_google_site_creator_sync_visibility_permission(
    \block_google_site_creator\drive_service $drive,
    string $fileid,
    int $courseid,
    string $visibility,
    ?int $excludeuserid = null
): bool {
    $knownemails = block_google_site_creator_get_managed_share_emails($courseid);

    $permissions = $drive->list_permissions($fileid);
    foreach ($permissions as $permission) {
        $email = strtolower(trim((string)($permission['emailAddress'] ?? '')));
        $permissionid = (string)($permission['id'] ?? '');
        if ($email !== '' && $permissionid !== '' && isset($knownemails[$email])) {
            try {
                $drive->delete_permission($fileid, $permissionid);
            } catch (Throwable $e) {
                debugging('Failed to delete Drive permission: ' . $e->getMessage(), DEBUG_DEVELOPER);
            }
        }
    }

    $targets = block_google_site_creator_resolve_share_targets($visibility, $courseid, $excludeuserid);
    $allok = true;
    foreach ($targets as $target) {
        try {
            $drive->add_permission(
                $fileid,
                $target['email'],
                $target['role'],
                $target['type']
            );
        } catch (Throwable $e) {
            $allok = false;
            debugging(
                'Failed to add Drive permission for ' . $target['email'] . ': ' . $e->getMessage(),
                DEBUG_DEVELOPER
            );
        }
    }
    return $allok;
}

/**
 * Save site banner image from draft area.
 *
 * @param context_course $context
 * @param int $siteid
 * @param int $draftitemid
 * @return void
 */
function block_google_site_creator_save_site_banner(context_course $context, int $siteid, int $draftitemid): void {
    file_save_draft_area_files(
        $draftitemid,
        $context->id,
        'block_google_site_creator',
        'sitebanner',
        $siteid,
        [
            'subdirs' => 0,
            'maxfiles' => 1,
            'accepted_types' => ['image'],
        ]
    );
}

/**
 * Get banner image URL for a created Google Site record.
 *
 * @param stdClass $site
 * @return string|null
 */
function block_google_site_creator_get_site_banner_url(stdClass $site): ?string {
    $context = context_course::instance((int)$site->courseid);
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'block_google_site_creator', 'sitebanner', (int)$site->id, 'filename', false);
    if (!$files) {
        return null;
    }
    $file = reset($files);
    return moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename()
    )->out(false);
}

/**
 * File serving for Google Site Creator banner images.
 */
function block_google_site_creator_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel !== CONTEXT_COURSE) {
        return false;
    }
    if ($filearea !== 'sitebanner') {
        return false;
    }
    require_login($course, false);
    $canaccess = is_enrolled($context, null, '', true)
        || has_capability('block/google_site_creator:create', $context)
        || has_capability('block/google_site_creator:managecourse', $context)
        || has_capability('moodle/course:view', $context);
    if (!$canaccess) {
        return false;
    }
    $itemid = (int)array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_google_site_creator', 'sitebanner', $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
