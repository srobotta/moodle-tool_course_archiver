# Plugin Course Archiver

This plugin provides a simple cli script that allows to trigger course archives
manually.

## Usage

On your moodle server in your moodle root directory run:

```
php public/admin/tool/course_archiver/cli/archive.php -x=<COURSEID> -d
```

This creates a backup of the course and then deletes it from your Moodle.
Course backups are written into `moodle-data/course_archiver/`.

You may also archive courses within a category and their sub categories:

```
php public/admin/tool/course_archiver/cli/archive.php -c=<CATEGORYID> -d -r
```

Before the action is executed, a confirmation dialogue is shown that lists
all affected courses.

## Options

The folling options can be set:

* `-x` or `--course` with the course ID.
* `-c` or `--category` with the category ID.
* `-d` or `--delete` when set, delete the courses, default is no delete.
* `-r` or `--recursive` when set, traverse the sub categories of a category,
  default is no traverse into subcategories. This has no effect when a course ID
  is used.
* `-t` or `--target`  to define another directory where the course backup files
  are written to. Default is `moodle-data/course_archiver/`.


