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
 * Assignments overview block caps.
 *
 * @package   block_assignments_overview
 * @copyright 2018 Tanmay Gatle Manasi Ladkat
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../config.php");
require_login();

/**
 * Assignments overview block class
 *
 * Used to produce list of assignments as submitted, not submitted or graded.
 *
 * @package   block_assignments_overview
 * @copyright 2018 Tanmay Gatle Manasi Ladkat
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_assignments_overview extends block_base
{

    /**
     * Set the initial properties for the block
     */
    public function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('assignments_overview', $this->blockname);
    }

    /**
     * Gets Javascript that may be required for navigation
     */
    public function get_required_javascript() {
        $this->page->requires->js_call_amd('block_assignments_overview/assignments_overviewblock', 'init');
    }

    /**
     * Gets the content for this block by grabbing it from $this->page
     *
     * @return object $this->content
     */
    public function get_content() {
        global $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        if ($USER->id == null) {
            return $this->content;
        }

        $courses = enrol_get_my_courses();
        if (count($courses) > 0) {
            $courseids = array();
            foreach ($courses as $course) {
                array_push($courseids, $course->id);
            }
            list($insql, $inparams) = $DB->get_in_or_equal($courseids);
            $sql = 'SELECT * FROM {assign} WHERE course ' . $insql;
            $assignments = $DB->get_records_sql($sql, $inparams);

            $sql = 'SELECT * FROM {assign_grades} WHERE userid = ?';
            $assignmentgrades = $DB->get_records_sql($sql, array($USER->id));

            $sql = 'SELECT * FROM {assign_submission} WHERE userid = ?';
            $assignmentsubmissions = $DB->get_records_sql($sql, array($USER->id));

        } else {
            $this->content->text = get_string("nocourses", $this->blockname);
            return $this->content;
        }

        if (empty($assignments)) {
            $this->content->text = get_string("noassignments", $this->blockname);
            return $this->content;
        } else {
            $this->content->text = html_writer::tag('b', get_string('mycourses', $this->blockname, $USER->firstname));

            $renderer = $this->page->get_renderer($this->blockname);
            $this->content->text .= $renderer->assignments_tree($courses, $assignments, $assignmentgrades, $assignmentsubmissions);
            return $this->content;
        }
    }
}
