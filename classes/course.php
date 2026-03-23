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

namespace tool_course_archiver;

/**
 * Class for archive a course.
 *
 * @package     tool_course_archiver
 * @copyright   2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {
    /** @var int The course ID. */
    public int $id;
    /** @var bool Whether to delete the course after backup. */
    public bool $delete;
    /** @var string The path to store the backup files. */
    public string $archivepath;
    /** @var string|null The shortname of the course, used for confirmation message. */
    protected ?string $shortname = null;

    /**
     * Constructor for the course archiver.
     *
     * @param int $id The course ID.
     * @param string $archivepath The path to store the backup files.
     * @param bool $delete Whether to delete the course after backup.
     */
    public function __construct(int $id, string $archivepath, bool $delete) {
        $this->id = $id;
        $this->archivepath = $archivepath;
        $this->delete = $delete;
    }

    /**
     * Archive the course by backing it up and optionally deleting it.
     * If not confirmed yet, it will print out a confirmation message with the course name and ID.
     *
     * @param bool $confirm Whether to ask for confirmation before archiving.
     * @return void
     */
    public function archive(bool $confirm = false): void {
        if ($confirm) {
            echo get_string('backupcourse', 'tool_course_archiver', [
                'name' => $this->get_course_shortname(),
                'id' => $this->id,
                'path' => $this->archivepath
            ]) . PHP_EOL;
            $this->exec(
                'backup.php',
                "--courseid={$this->id} --destination={$this->archivepath}",
                'backupdfailed'
            );
            if ($this->delete) {
                echo get_string('deletecourse', 'tool_course_archiver', [
                    'name' => $this->get_course_shortname(),
                    'id' => $this->id,
                    'path' => $this->archivepath
                ]) . PHP_EOL;
                $this->exec(
                    'delete_course.php',
                    "--courseid={$this->id} --disablerecyclebin --non-interactive",
                    'deletefailed'
                );
            }
            return;
        }
        echo $this->get_confirmation() . PHP_EOL;
    }

    /**
     * Execute backup or delete command.
     * @param string $script
     * @param string $artgs
     * @param string $err
     */
    protected function exec(string $script, string $args, string $err): void {
        global $CFG;

        $php = PHP_BINARY;
        $script = realpath("$CFG->dirroot/../admin/cli/$script");
        if (DIRECTORY_SEPARATOR !== '/') {
            $script = str_replace('/', DIRECTORY_SEPARATOR, $script);
        }
        $cmd = escapeshellcmd("$php $script $args");

        exec($cmd, $output, $returnvar);
        if ($returnvar !== 0) {
            throw new \moodle_exception($err, 'tool_course_archiver', '', implode("\n", $output));
        }
    }

    /**
     * Get the shortname of the course for confirmation message.
     *
     * @return string The shortname of the course.
     * @throws \moodle_exception If the course is not found.
     */
    protected function get_course_shortname(): string {
        global $DB;
        if ($this->shortname === null) {
            $this->shortname = $DB->get_field('course', 'shortname', ['id' => $this->id]);
            if (!$this->shortname) {
                throw new \moodle_exception('coursenotfound', 'tool_course_archiver', '', $this->id);
            }
        }
        return $this->shortname;
    }   

    /**
     * Get the confirmation message for archiving the course.
     *
     * @return string The confirmation message.
     */
    public function get_confirmation(): string {
        return get_string(
            'confirmarchivecourse',
            'tool_course_archiver',
            [
                'course' => $this->get_course_shortname(),
                'id' => $this->id
            ]
        );
    }
}
