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
 * Class for archive all courses within a category.
 *
 * @package     tool_course_archiver
 * @copyright   2026 Stephan Robotta <stephan.robotta@bfh.ch>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class category {
    /** @var int The category ID. */
    public int $id;
    /** @var bool Whether to delete the course after backup. */
    public bool $delete;
    /** @var string The path to store the backup files. */
    public string $archivepath;
    /** @var bool Whether to include courses in subcategories. */
    public bool $recursive;
    /** @var array The list of pending categories to process when loading course ids. */
    protected array $pending = [];
    /** @var array The list of courses to archive. */
    protected array $courses = [];

    /**
     * Constructor for the category archiver.
     *
     * @param int $id The category ID.
     * @param string $archivepath The path to store the backup files.
     * @param bool $delete Whether to delete the course after backup.
     * @param bool $recursive Whether to include courses in subcategories.
     */
    public function __construct(int $id, string $archivepath, bool $delete, bool $recursive) {
        $this->id = $id;
        $this->archivepath = $archivepath;
        $this->delete = $delete;
        $this->recursive = $recursive;
        $this->pending[] = $id;
        $this->load_courses();
    }

    /**
     * Load courses from the category and optionally its subcategories.
     *
     * @return void
     */
    protected function load_courses(): void {
        while (!empty($this->pending)) {
            $id = \array_shift($this->pending);
            $courses = \get_courses($id, 'c.id', 'c.id, c.shortname, c.fullname');
            foreach ($courses as $course) {
                if ($course->id == SITEID) {
                    continue;
                }
                $this->courses[$course->id] = $course->fullname . ' (' . $course->shortname . ')';
            }
            if ($this->recursive) {
                $categories = \core_course_category::get($id)->get_children();
                foreach ($categories as $category) {
                    $this->pending[] = $category->id;
                }
            }
        }
    }

    /**
     * Archive the category and its courses.
     * If not confirmed yet, it will print out a confirmation message with a list of courses.
     *
     * @param bool $confirm Whether to ask for confirmation before archiving.
     * @return void
     */
    public function archive(bool $confirm = false): void {
        if ($confirm) {
            foreach (\array_keys($this->courses) as $courseid) {
                $archiver = new course(id: $courseid, archivepath: $this->archivepath, delete: $this->delete);
                $archiver->archive(confirm: true);
            }
            return;
        }
        echo $this->get_confirmation() . PHP_EOL;
    }

    /**
     * Get the confirmation message for archiving the category.
     *
     * @return string The confirmation message.
     */
    public function get_confirmation(): string {
        global $DB;
        $categoryname = $DB->get_record('course_categories', ['id' => $this->id], 'name')->name;
        $text = get_string('confirmarchivecategory', 'tool_course_archiver', $categoryname);
        foreach ($this->courses as $coursename) {
            $text .= "\n - " . $coursename;
        }
        return $text;
    }
}