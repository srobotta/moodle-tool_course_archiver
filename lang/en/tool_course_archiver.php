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
 * Strings for the tool_installaddon component.
 *
 * @package     tool_course_archiver
 * @category    string
 * @copyright   2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['backupcourse'] = 'Backup course {$a->name} (ID: {$a->id}) to {$a->path}';
$string['backupfailed'] = 'Course backup failed: {$a}';
$string['confirmarchivecategory'] = 'Archive all courses in category "{$a}":';
$string['confirmarchivecourse'] = 'Archive course "{$a->course}" (ID: {$a->id}):';
$string['confirmcontinue'] = 'Are you sure you want to continue?';
$string['coursenotfound'] = 'Course with ID {$a} not found.';
$string['deletecourse'] = 'Delete course {$a->name} (ID: {$a->id})';
$string['deletefailed'] = 'Course deletion failed: {$a}';
$string['targetnotfound'] = 'The target directory {$a} does not exist and could not be created.';
$string['missingcategoryorcourse'] = 'You must specify either a category ID or a course ID to archive.';
$string['onlycategoryorcourse'] = 'You cannot specify both a category ID and a course ID to archive. Please choose one of them.';
$string['pluginname'] = 'CLI Course archiver';
$string['privacy:metadata'] = 'The CLI Course archiver plugin does not store any personal data.';