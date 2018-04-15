<?php
class block_assignments_overview extends block_base {
    
    public function init() {
        $this->blockname = get_class($this);
	    $this->title = get_string('assignments_overview', $this->blockname);
	}

    function get_required_javascript() {
       $this->page->requires->js_call_amd('block_assignments_overview/assignments_overviewblock', 'init');
    }

	public function get_content() {
        global $DB, $USER;

        if ($this->content !== null) {
	      return $this->content;
	    }

	    $this->content =  new stdClass;
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
            $sql = 'SELECT * FROM {assign} WHERE course '.$insql;
            $assignments = $DB->get_records_sql($sql, $inparams);

            $sql = 'SELECT * FROM {assign_grades} WHERE userid = ?';
            $assignmentgrades = $DB->get_records_sql($sql, array($USER->id));

            $sql = 'SELECT * FROM {assign_submission} WHERE userid = ?';
            $assignmentsubmissions = $DB->get_records_sql($sql, array($USER->id));

        } else {
            $this->content->text = get_string("nocourses",$this->blockname);
            return $this->content;
        }

        if (empty($assignments)) {
            $this->content->text = get_string("noassignments",$this->blockname);
            return $this->content;
        }
        else {
            $this->content->text = html_writer::tag('b', get_string('mycourses', $this->blockname, $USER->firstname));

            $renderer = $this->page->get_renderer($this->blockname);
            $this->content->text .= $renderer->assignments_tree($courses, $assignments, $assignmentgrades, $assignmentsubmissions);
            return $this->content;
        }
	}
}
