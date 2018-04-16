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
include_once("../config.php");
class block_assignments_overview_renderer extends plugin_renderer_base
{
    public function assignments_tree($courses, $assignments, $assignmentgrades, $assignmentsubmissions)
    {
        global $CFG;
        $content = html_writer::start_tag('ul', array('id' => 'mycoursestree', 'class' => 'tree courses_ul', 'role' => 'tree'));

        list($assignmenticonhtml,
            $gradedassignmenticonhtml,
            $notsubmittedassignmenticonhtml,
            $notgradedassignmenticonhtml,
            $gradedcourseiconhtml,
            $notsubmittedcourseiconhtml,
            $notgradedcourseiconhtml) = $this->get_icons();


        foreach ($courses as $course) {

            $assignmentlistforacourse = '';
            $assignmentsubmissionstatuscount = array(0, 0, 0);

            foreach ($assignments as $assignment) {
                if ($assignment->course === $course->id) {
                    $isgraded = false;
                    $issubmitted = false;

                    foreach ($assignmentgrades as $assignmentgrade) {
                        if ($assignmentgrade->assignment === $assignment->id) {
                            $submitstatus = html_writer::tag('li', get_string("status", "block_assignments_overview") . get_string("graded", "block_assignments_overview"),
                                array('role' => "treeitem", 'tabindex' => '-1'));
                            $grade = html_writer::tag('li', get_string('gradedlabel', "block_assignments_overview") . $assignmentgrade->grade,
                                array('role' => "treeitem", 'tabindex' => '-1'));
                            $assignmentdetails = html_writer::tag('ul', $submitstatus . $grade, array('role' => 'group', 'class' => 'assignment_details_ul'));
                            $isgraded = true;
                            $assignmentsubmissionstatuscount[0]++;
                            break;
                        }
                    }

                    if (!$isgraded) {
                        foreach ($assignmentsubmissions as $assignmentsubmission) {
                            if ($assignmentsubmission->assignment === $assignment->id) {
                                if ($assignmentsubmission->status == "submitted") {
                                    $submitstatus = html_writer::tag('li', get_string("status", "block_assignments_overview") . get_string("submitted", "block_assignments_overview"),
                                        array('role' => 'treeitem', 'tabindex' => '-1'));
                                    $duedate = html_writer::tag('li', get_string("duedate", "block_assignments_overview") . '<br>' . userdate($assignment->duedate), array('role' => 'treeitem', 'tabindex' => '-1'));
                                    $assignmentsubmitteddate = html_writer::tag('li', get_string("assignmentsubmitteddate", "block_assignments_overview") . '<br>' . userdate($assignmentsubmission->timemodified),
                                        array('role' => 'treeitem', 'tabindex' => '-1'));
                                    $assignmentdetails = html_writer::tag('ul', $submitstatus . $assignmentsubmitteddate . $duedate, array('role' => 'group', 'class' => 'assignment_details_ul'));
                                    $issubmitted = true;
                                    $assignmentsubmissionstatuscount[1]++;
                                }
                                break;
                            }
                        }
                    }

                    if (!$isgraded && !$issubmitted) {
                        $submitstatus = html_writer::tag('li', get_string("status", "block_assignments_overview") . get_string("new", "block_assignments_overview"),
                            array('role' => 'treeitem', 'tabindex' => '-1'));
                        $duedate = html_writer::tag('li', get_string("duedate", "block_assignments_overview") . '<br>' . userdate($assignment->duedate),
                            array('role' => 'treeitem', 'tabindex' => '-1'));
                        $time = time();
                        if (($assignment->duedate - $time) <= 0) {
                            $due = get_string('assignmentisdue', 'block_assignments_overview');
                        } else {
                            $due = format_time($assignment->duedate - $time);
                        }
                        $timeremaining = html_writer::tag('li', get_string("timeremaining", "block_assignments_overview") . '<br>' . $due,
                            array('role' => 'treeitem', 'tabindex' => '-1'));
                        $assignmentdetails = html_writer::tag('ul', $submitstatus . $duedate . $timeremaining, array('role' => 'group', 'class' => 'assignment_details_ul'));
                        $assignmentsubmissionstatuscount[2]++;
                    }

                    $href = ($this->get_course_modules($course))[$course->id][$assignment->id]->out();
                    $assignmentlink = html_writer::tag('a', $assignmenticonhtml . $assignment->name, array('href' => $href));

                    if ($isgraded)
                        $assignmentstatusiconhtml = $gradedassignmenticonhtml;
                    else if ($issubmitted)
                        $assignmentstatusiconhtml = $notgradedassignmenticonhtml;
                    else
                        $assignmentstatusiconhtml = $notsubmittedassignmenticonhtml;

                    $assignmentlistforacourse .= html_writer::tag('li', $assignmentlink . $assignmentstatusiconhtml . $assignmentdetails,
                        array('role' => "treeitem", 'aria-expanded' => "false", 'tabindex' => '-1'));
                }
            }

            if (array_sum($assignmentsubmissionstatuscount) === 0)
                break;

            $content .= html_writer::start_tag('li', array('class' => 'tree-parent', 'role' => 'treeitem', 'aria-expanded' => 'false', 'tabindex' => '-1'));
            $content .= html_writer::tag('a', $course->fullname, array('href' => $CFG->wwwroot . '/course/view.php?id=' . $course->id));

            if ($assignmentsubmissionstatuscount[0] !== 0 && $assignmentsubmissionstatuscount[1] === 0 && $assignmentsubmissionstatuscount[2] === 0)
                $content .= $gradedcourseiconhtml;
            else if ($assignmentsubmissionstatuscount[0] === 0 && $assignmentsubmissionstatuscount[1] !== 0 && $assignmentsubmissionstatuscount[2] === 0)
                $content .= $notgradedcourseiconhtml;
            else
                $content .= $notsubmittedcourseiconhtml;

            $content .= html_writer::start_tag('ul', array('role' => 'group', 'class' => 'assignments_ul'));
            $content .= html_writer::tag('p', '[T:' . array_sum($assignmentsubmissionstatuscount) . ',G:' .
                $assignmentsubmissionstatuscount[0] . ',S:' . $assignmentsubmissionstatuscount[1] . ',N:' . $assignmentsubmissionstatuscount[2] . ']', array('class' => 'assignments_quick_count'));
            $content .= $assignmentlistforacourse;
            $content .= html_writer::end_tag('ul');
            $content .= html_writer::end_tag('li');
        }

        $content .= html_writer::end_tag('ul');
        return $content;
    }

    function get_icons()
    {
        $assignmenticon = new pix_icon('icon', get_string('assignment', 'block_assignments_overview'), 'mod_assignment');
        $assignmenticonhtml = $this->output->render($assignmenticon);

        $gradedassignmenticon = new pix_icon('greensquare', get_string('graded', 'block_assignments_overview'), 'block_assignments_overview', array('class' => 'assignment_status_img'));
        $gradedassignmenticonhtml = $this->output->render($gradedassignmenticon);

        $notsubmittedassignmenticon = new pix_icon('redsquare', get_string('new', 'block_assignments_overview'), 'block_assignments_overview', array('class' => 'assignment_status_img'));
        $notsubmittedassignmenticonhtml = $this->output->render($notsubmittedassignmenticon);

        $notgradedassignmenticon = new pix_icon('yellowsquare', get_string('submitted', 'block_assignments_overview'), 'block_assignments_overview', array('class' => 'assignment_status_img'));
        $notgradedassignmenticonhtml = $this->output->render($notgradedassignmenticon);

        $gradedcourseicon = new pix_icon('greentick', get_string('graded', 'block_assignments_overview'), 'block_assignments_overview', array('class' => 'course_status_img'));
        $gradedcourseiconhtml = $this->output->render($gradedcourseicon);

        $notgradedcourseicon = new pix_icon('yellowtick', get_string('submitted', 'block_assignments_overview'), 'block_assignments_overview', array('class' => 'course_status_img'));
        $notgradedcourseiconhtml = $this->output->render($notgradedcourseicon);

        $notsubmittedcourseicon = new pix_icon('redexclamation', get_string('new', 'block_assignments_overview'), 'block_assignments_overview', array('class' => 'course_status_img'));
        $notsubmittedcourseiconhtml = $this->output->render($notsubmittedcourseicon);

        return array($assignmenticonhtml,
            $gradedassignmenticonhtml,
            $notsubmittedassignmenticonhtml,
            $notgradedassignmenticonhtml,
            $gradedcourseiconhtml,
            $notsubmittedcourseiconhtml,
            $notgradedcourseiconhtml);
    }

    function get_course_modules($course)
    {
        $assignmentpaths = array();
        $cms = get_fast_modinfo($course)->get_cms();
        foreach ($cms as $cm) {
            if ($cm->is_user_access_restricted_by_capability()) {
                continue;
            }

            if ($cm->modname == 'assign') {
                if (array_key_exists($course->id, $assignmentpaths)) {
                    $assignmentpaths[$course->id] = $assignmentpaths[$course->id] + array($cm->instance => $cm->url);
                } else {
                    $assignmentpaths[$course->id] = array($cm->instance => $cm->url);
                }
            }
        }
        return $assignmentpaths;
    }
}