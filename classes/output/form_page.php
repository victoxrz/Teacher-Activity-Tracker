<?php

namespace local_teacher_activities\output;
use local_teacher_activities\services\session_service;


class form_page implements \renderable, \templatable
{
    protected $toc_map;
    protected $content_html;

    public function __construct($total_steps, $current_step, $sid, $content_html)
    {
        $this->content_html = $content_html;

        $local_step = 0;
        foreach ($total_steps as $section_key => $activities) {
            $activity_list = [];
            foreach ($activities['activity_keys'] as $act_key) {
                $title = ($section_key === 'personal_data')
                    ? get_string('general_data', 'local_teacher_activities')
                    : get_string($act_key, 'local_teacher_activities');

                $activity_list[] = [
                    'title' => $title,
                    'url' => (new \moodle_url(
                        '/local/teacher_activities/activity/form.php',
                        ['step' => $local_step, 'sid' => $sid]
                    ))->out(false),
                    'is_active' => $local_step == $current_step,
                    'is_completed' => session_service::has_activity($section_key, $act_key),
                ];
                $local_step++;
            }

            $this->toc_map[] = [
                'title' => get_string($section_key, 'local_teacher_activities'),
                'activities' => $activity_list,
            ];
        }
    }

    public function export_for_template(\core\output\renderer_base $output)
    {
        return [
            'toc' => $this->toc_map,
            'content_html' => $this->content_html,
        ];
    }
}