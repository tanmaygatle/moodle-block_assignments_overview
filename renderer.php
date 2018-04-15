<?php
class block_assignments_overview_renderer extends plugin_renderer_base
{
    public function assignments_tree($courses,$assignments, $assignmentgrades, $assignmentsubmissions)
    {
        global $CFG;
        $content = html_writer::start_tag('ul', array('id' => 'mycoursestree', 'class' => 'tree courses_ul', 'role' => 'tree'));

        list($assignment_icon_html,
            $graded_assignment_icon_html,
            $notsubmitted_assignment_icon_html,
            $notgraded_assignment_icon_html,
            $graded_course_icon_html,
            $notsubmitted_course_icon_html,
            $notgraded_course_icon_html) = $this->get_icons();


        foreach ($courses as $course) {

            $assignment_list_for_a_course = '';
            $assignment_submission_status_count = array(0,0,0); //0: graded, 1: submitted but not graded, 2: not submitted

            foreach ($assignments as $assignment) {
                if ($assignment->course === $course->id) {
                    $isgraded = false;
                    $issubmitted = false;

                    foreach ($assignmentgrades as $assignmentgrade) {
                        if ($assignmentgrade->assignment === $assignment->id) {
                            $submitstatus = html_writer::tag('li', get_string("status", "block_assignments_overview") . get_string("graded", "block_assignments_overview"), array('role' => "treeitem", 'tabindex' => '-1'));
                            $grade = html_writer::tag('li', get_string('gradedlabel', "block_assignments_overview"). $assignmentgrade->grade, array('role' => "treeitem", 'tabindex' => '-1'));
                            $assignmentdetails = html_writer::tag('ul', $submitstatus . $grade, array('role' => 'group', 'class' => 'assignment_details_ul'));
                            $isgraded = true;
                            $assignment_submission_status_count[0]++;
                            break;
                        }
                    }

                    if(!$isgraded) {
                        foreach ($assignmentsubmissions as $assignmentsubmission) {
                            if ($assignmentsubmission->assignment === $assignment->id) {
                                if($assignmentsubmission->status == "submitted") {
                                    $submitstatus = html_writer::tag('li', get_string("status", "block_assignments_overview") . get_string("submitted", "block_assignments_overview"), array('role' => 'treeitem', 'tabindex' => '-1'));
                                    $duedate = html_writer::tag('li', get_string("duedate", "block_assignments_overview") .'<br>'. userdate($assignment->duedate), array('role' => 'treeitem', 'tabindex' => '-1'));
                                    $assignmentsubmitteddate = html_writer::tag('li', get_string("assignmentsubmitteddate", "block_assignments_overview") .'<br>'. userdate($assignmentsubmission->timemodified), array('role' => 'treeitem', 'tabindex' => '-1'));
                                    $assignmentdetails = html_writer::tag('ul', $submitstatus . $assignmentsubmitteddate . $duedate, array('role' => 'group', 'class' => 'assignment_details_ul'));
                                    $issubmitted = true;
                                    $assignment_submission_status_count[1]++;
                                }
                                break;
                            }
                        }
                    }

                    if(!$isgraded && !$issubmitted) {
                        $submitstatus = html_writer::tag('li', get_string("status", "block_assignments_overview").get_string("new", "block_assignments_overview"), array('role' => 'treeitem', 'tabindex' => '-1'));
                        $duedate = html_writer::tag('li', get_string("duedate", "block_assignments_overview").'<br>'.userdate($assignment->duedate), array('role' => 'treeitem', 'tabindex' => '-1'));
                        $time = time();
                        if (($assignment->duedate - $time) <= 0) {
                            $due = get_string('assignmentisdue', 'block_assignments_overview');
                        } else {
                            $due = format_time($assignment->duedate - $time);
                        }
                        $timeremaining = html_writer::tag('li', get_string("timeremaining", "block_assignments_overview").'<br>'.$due, array('role' => 'treeitem', 'tabindex' => '-1'));
                        $assignmentdetails = html_writer::tag('ul', $submitstatus.$duedate.$timeremaining, array('role' => 'group', 'class' => 'assignment_details_ul'));
                        $assignment_submission_status_count[2]++;
                    }

                    $href = ($this->get_course_modules($course))[$course->id][$assignment->id]->out();
                    $assignment_link = html_writer::tag('a', $assignment_icon_html.$assignment->name, array('href' => $href));

                    if($isgraded)
                        $assignment_status_icon_html = $graded_assignment_icon_html;
                    else if($issubmitted)
                        $assignment_status_icon_html = $notgraded_assignment_icon_html;
                    else
                        $assignment_status_icon_html = $notsubmitted_assignment_icon_html;

                    $assignment_list_for_a_course .= html_writer::tag('li', $assignment_link . $assignment_status_icon_html . $assignmentdetails, array('role' => "treeitem", 'aria-expanded' => "false", 'tabindex' => '-1'));
                }
            }

            if (array_sum($assignment_submission_status_count) === 0)
                break;

            $content .= html_writer::start_tag('li', array('class' => 'tree-parent', 'role' => 'treeitem', 'aria-expanded' => 'false', 'tabindex' => '-1'));
            $content .= html_writer::tag('a', $course->fullname, array('href' => $CFG->wwwroot . '/course/view.php?id=' . $course->id));

            if($assignment_submission_status_count[0] !== 0 && $assignment_submission_status_count[1] === 0 && $assignment_submission_status_count[2] === 0)
                $content .= $graded_course_icon_html;
            else if($assignment_submission_status_count[0] === 0 && $assignment_submission_status_count[1] !== 0 && $assignment_submission_status_count[2] === 0)
                $content .= $notgraded_course_icon_html;
            else
                $content .= $notsubmitted_course_icon_html;

            $content .= html_writer::start_tag('ul', array('role' => 'group', 'class' => 'assignments_ul'));
            $content .= html_writer::tag('p', '[T:'.array_sum($assignment_submission_status_count).',G:'.$assignment_submission_status_count[0].',S:'.$assignment_submission_status_count[1].',N:'.$assignment_submission_status_count[2].']', array('class' => 'assignments_quick_count'));
            $content .= $assignment_list_for_a_course;
            $content .= html_writer::end_tag('ul');
            $content .= html_writer::end_tag('li');
        }

        $content .= html_writer::end_tag('ul');
        return $content;
    }

    function get_icons() {
        $assignment_icon = new pix_icon('icon', get_string('assignment', 'block_assignments_overview'), 'mod_assignment');
        $assignment_icon_html = $this->output->render($assignment_icon);

        $graded_assignment_icon = new pix_icon('greensquare',get_string('graded', 'block_assignments_overview'),'block_assignments_overview', array('class' => 'assignment_status_img'));
        $graded_assignment_icon_html = $this->output->render($graded_assignment_icon);

        $notsubmitted_assignment_icon = new pix_icon('redsquare',get_string('new', 'block_assignments_overview'),'block_assignments_overview', array('class' => 'assignment_status_img'));
        $notsubmitted_assignment_icon_html = $this->output->render($notsubmitted_assignment_icon);

        $notgraded_assignment_icon = new pix_icon('yellowsquare',get_string('submitted', 'block_assignments_overview'),'block_assignments_overview', array('class' => 'assignment_status_img'));
        $notgraded_assignment_icon_html = $this->output->render($notgraded_assignment_icon);

        $graded_course_icon = new pix_icon('greentick', get_string('graded', 'block_assignments_overview'),'block_assignments_overview', array('class' => 'course_status_img'));
        $graded_course_icon_html = $this->output->render($graded_course_icon);

        $notgraded_course_icon = new pix_icon('yellowtick', get_string('submitted', 'block_assignments_overview'),'block_assignments_overview', array('class' => 'course_status_img'));
        $notgraded_course_icon_html = $this->output->render($notgraded_course_icon);

        $notsubmitted_course_icon = new pix_icon('redexclamation', get_string('new', 'block_assignments_overview'),'block_assignments_overview', array('class' => 'course_status_img'));
        $notsubmitted_course_icon_html = $this->output->render($notsubmitted_course_icon);

        return array($assignment_icon_html,
            $graded_assignment_icon_html,
            $notsubmitted_assignment_icon_html,
            $notgraded_assignment_icon_html,
            $graded_course_icon_html,
            $notsubmitted_course_icon_html,
            $notgraded_course_icon_html);
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