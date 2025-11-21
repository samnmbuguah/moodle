<?php
// Parent portal block providing a dashboard view for parents/guardians.

defined('MOODLE_INTERNAL') || die();

class block_parent_portal extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_parent_portal');
    }

    public function applicable_formats() {
        return ['my' => true]; // Parent dashboard on My home page.
    }

    public function has_config() {
        return false;
    }

    public function get_content() {
        global $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        // Determine any mentees (linked students) for this user via role assignments.
        $userfieldsapi = \core_user\fields::for_name();
        $userfieldssql = $userfieldsapi->get_sql('u', false, '', '', false);
        [$usersort] = users_order_by_sql('u', null, $this->context, $userfieldssql->mappings);

        $sql = "SELECT u.id, {$userfieldssql->selects}
                  FROM {role_assignments} ra
                  JOIN {context} c ON ra.contextid = c.id
                  JOIN {user} u ON c.instanceid = u.id
                 WHERE ra.userid = ?
                   AND c.contextlevel = ?
              ORDER BY $usersort";

        $mentees = $DB->get_records_sql($sql, [$USER->id, CONTEXT_USER]);

        $html = html_writer::start_div('parent-portal-block');

        $html .= html_writer::tag('h3', get_string('section_overview', 'block_parent_portal'));
        $html .= html_writer::tag('p', get_string('section_overview_desc', 'block_parent_portal'));

        if (empty($mentees)) {
            $html .= html_writer::tag('p', get_string('nochildren', 'block_parent_portal'));
        } else {
            $html .= html_writer::start_div('parent-portal-children');
            $html .= html_writer::tag('h4', get_string('children_header', 'block_parent_portal'));
            $html .= html_writer::start_tag('ul', ['class' => 'parent-portal-children-list']);

            foreach ($mentees as $child) {
                $childname = fullname($child);
                $profileurl = \core\user::get_profile_url($child);
                $gradesurl = new moodle_url('/grade/report/overview/index.php', [
                    'id' => SITEID,
                    'userid' => $child->id,
                ]);

                $links = [];
                $links[] = html_writer::link($profileurl, get_string('link_profile', 'block_parent_portal'));
                $links[] = html_writer::link($gradesurl, get_string('link_grades', 'block_parent_portal'));
                $linkshtml = implode(' | ', $links);

                $itemcontent = html_writer::span($childname, 'parent-portal-child-name') . ' - ' .
                    html_writer::span($linkshtml, 'parent-portal-child-links');

                $html .= html_writer::tag('li', $itemcontent);
            }

            $html .= html_writer::end_tag('ul');
            $html .= html_writer::end_div();
        }

        $html .= html_writer::start_div('parent-portal-section performance');
        $html .= html_writer::tag('h4', get_string('section_performance', 'block_parent_portal'));
        $html .= html_writer::tag('p', get_string('section_performance_placeholder', 'block_parent_portal'));
        $html .= html_writer::end_div();

        $html .= html_writer::start_div('parent-portal-section attendance');
        $html .= html_writer::tag('h4', get_string('section_attendance', 'block_parent_portal'));
        $html .= html_writer::tag('p', get_string('section_attendance_placeholder', 'block_parent_portal'));
        $html .= html_writer::end_div();

        $html .= html_writer::start_div('parent-portal-section fees');
        $html .= html_writer::tag('h4', get_string('section_fees', 'block_parent_portal'));
        $html .= html_writer::tag('p', get_string('section_fees_placeholder', 'block_parent_portal'));
        $html .= html_writer::end_div();

        $html .= html_writer::end_div();

        $this->content->text = $html;
        $this->content->footer = '';

        return $this->content;
    }
}
