# Google Site Creator (Moodle block)

<p align="center">
  <img src="pix/oca_logo.png" alt="Open College of the Arts (OCA) logo" width="120" height="120">
</p>

Block that lets course users create a Google Site from a Google Drive template (file copy), choose who can access it, and store a listing record in Moodle.

**Author:** Paul Vincent  
**Developed by:** [Open College of the Arts (OCA)](https://www.oca.ac.uk/)  
**Component:** `block_google_site_creator`  
**Requires:** Moodle 4.0 or later (tested with currently maintained branches such as 4.5)  
**License:** GNU GPL v3 or later

## Requirements

- Moodle 4.0+
- A Google Cloud project with the **Google Drive API** enabled
- A Google service account JSON key (and, for ownership transfer / acting as users, Google Workspace **domain-wide delegation**)
- Optional: Google Groups if you want group-based sharing in addition to Moodle enrolment-based sharing

This plugin does **not** require Composer or other PHP dependency managers. It talks to the Drive API using Moodle’s `\curl` helper and OpenSSL JWT signing.

## Installation

1. Place the plugin folder at `blocks/google_site_creator/` (folder name must be `google_site_creator`).
2. Visit **Site administration → Notifications** to install the block and create its database tables.
3. Purge caches if you update language strings or code on an existing site.

## Configuration

### Site administration

**Site administration → Plugins → Blocks → Google Site Creator**

| Setting | Purpose |
|---------|---------|
| **Default Google Site Template ID** | Drive file ID of the template site used when a course has no course-specific template |
| **Service account JSON** | Prefer a **path** to the JSON key under `$CFG->dataroot` (or absolute). Pasting JSON works but stores a secret in the database |
| **Delegated Workspace user** | Optional email to impersonate via domain-wide delegation |
| **Allowed email domains for ownership** | Optional comma-separated domains eligible for automatic ownership transfer. Empty = any valid user email |
| **Tutor Google Group email** | Optional. Enables the “Tutor Google Group” visibility option |
| **Organisation Google Group email** | Optional. Enables the “Organisation Google Group” visibility option |

Google Group settings are **optional**. Institutions that share only via Moodle enrolments can leave them empty.

### Course settings (teachers)

Add the block to a course, then use **Course settings** in the block footer (capability **Manage course Google Site settings**):

| Setting | Purpose |
|---------|---------|
| **Course Site Template ID** | Optional Drive template for this course |
| **Course Google Group** | Optional Google Group email for this course. Enables the “Course Google Group” visibility option |

## Visibility and sharing

When a user creates or edits a site they choose a **visibility** mode. Sharing is applied as Google Drive permissions (no notification emails).

### Moodle-based (always available)

These share the Drive file with enrolled users’ **email addresses** (`type=user`):

| Option | Recipients |
|--------|------------|
| **Course teachers (Moodle)** | Active enrolled users with `moodle/course:manageactivities` |
| **Course participants (Moodle)** | All active enrolled users with a valid email |

Default for new sites: **Course teachers (Moodle)**.

Large courses issue one Drive API call per recipient and may be slower or hit Google quotas.

### Google Groups-based (optional)

These options appear only when the corresponding Google Group email is configured:

| Option | Recipients |
|--------|------------|
| **Tutor Google Group** | Site admin tutor group email (`type=group`, writer) |
| **Course Google Group** | Course setting group email (`type=group`, writer) |
| **Organisation Google Group** | Site admin organisation group email (`type=group`, reader) |

If your institution does not use Google Groups, leave the group settings empty and use Moodle-based options only.

## Web services

Two external functions are available for REST/admin integration:

1. **`block_google_site_creator_get_site_details`** — parameters: `siteid`  
2. **`block_google_site_creator_create_site`** — parameters: `courseid`, `userid`, `title`, optional `templateid`, `visibility`  
   Visibility values: `course_teachers`, `course_participants`, `tutor_group`, `course_group`, `organisation_group`  
   Legacy aliases still accepted: `tutors`, `unit_group`, `all_oca`

Enable via **Site administration → Plugins → Web services**, add the functions to a service, and create a token for a user with `block/google_site_creator:create_for_others`.

## Optional discovery helpers

Third-party plugins may call:

- `block_google_site_creator_get_sites_for_index($limit)` — sites with organisation-group visibility  
- `block_google_site_creator_build_cards_for_grid($sites)` — card objects for listing UIs  

There is **no hard dependency** on any other contributed plugin.

## Privacy

The plugin stores site metadata in Moodle and sends titles, descriptions, and share recipient emails to Google when creating or updating Drive permissions. See the in-plugin Privacy API implementation for details shown to site administrators.

## Support

- Issue tracker: use the public GitHub issues for this repository (recommended name: `moodle-block_google_site_creator`)
- Contributions welcome under GPL v3+

## Credits

Developed for the Open College of the Arts (OCA) by Paul Vincent. The OCA logo in `pix/` identifies the developing organisation and is used as the plugin icon in Moodle’s plugin overview.
