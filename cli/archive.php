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
 * Export custom language strings to zip files.
 *
 * @package    tool_course_archiver
 * @copyright  2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/clilib.php");

$usage = <<<EOF
"Backup courses to a target folder and optionally delete the course after backup.

Options:
-c, --category          Category ID to archive all courses inside that category.
-d, --delete            Whether to delete the course after backup, default: false.
-h, --help              Print out this help message.
-r, --recursive         Whether to include courses in subcategories when a category is specified, default: false.
-t, --target            Target directory to store the backup files, default: $CFG->dataroot/course_archiver
-x, --course            Archive a specific course by its ID.

Examples:
Archive a single course:
\$ sudo -u www-data /usr/bin/php public/admin/tool/course_archiver/cli/archive.php -x=34

Export all course within a single category:
\$ sudo -u www-data /usr/bin/php public/admin/tool/course_archiver/cli/archive.php -c=12

Export all course within a category and its subcategories, and delete the courses after backup:
\$ sudo -u www-data /usr/bin/php public/admin/tool/course_archiver/cli/archive.php -c=12 -r -d

EOF;

$target = "$CFG->dataroot/course_archiver/";
$delete = false;
$recursive = false;

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    [
        'target' => false,
        'help' => false,
        'delete' => false,
        'recursive' => false,
        'category' => 0,
        'course' => 0,
    ],
    [
        'h' => 'help',
        'c' => 'category',
        'd' => 'delete',
        'r' => 'recursive',
        't' => 'target',
        'x' => 'course'
    ]
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_error($usage, 0);
}
// Target dir set by option or default.
if ($options['target']) {
    $target = rtrim($options['target'], '/') . '/';
}
// Ensure target directory exists.
if (!check_dir_exists($target)) {
    cli_error(get_string('targetnotfound', 'tool_course_archiver', ['target' => $target]));
}

if (empty($options['category']) && empty($options['course'])) {
    cli_error(get_string('missingcategoryorcourse', 'tool_course_archiver'));
}
if (!empty($options['category']) && !empty($options['course'])) {
    cli_error(get_string('onlycategoryorcourse', 'tool_course_archiver'));
}

if ($options['delete']) {
    $delete = true;
}
if ($options['recursive']) {
    $recursive = true;
}

if ($options['category']) {
    $archiver = new \tool_course_archiver\category(
        id: (int)$options['category'],
        archivepath: $target,
        delete: $delete,
        recursive: $recursive
    );
} else {
    $archiver = new \tool_course_archiver\course(
        id: (int)$options['course'],
        archivepath: $target,
        delete: $delete
    );
}

$archiver->archive();
echo PHP_EOL;
// Ask for confirmation via cli input.
$yes = strtolower(substr(get_string('yes'), 0, 1));
$no = strtolower(substr(get_string('no'), 0, 1));
$input = cli_input(
    get_string('confirmcontinue', 'tool_course_archiver') . ' (' . $yes . '/' . strtoupper($no) . ')',
    $no,
    [$yes, strtoupper($yes), $no, strtoupper($no)]
);
if (strtolower($input) !== $yes) {
    exit(0);
}
// Continue with archiving after confirmation.
$archiver->archive(confirm: true);
