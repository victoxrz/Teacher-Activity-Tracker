<?php

use local_teacher_activities\services\session_service;
use local_teacher_activities\services\template_service;
use local_teacher_activities\services\api;
use moodle_url;

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require_login();

// echo '<pre>';
// print_r(session_service::get_all());
// echo '</pre>';
// api::update_submission(api::get_submission(1));

$url_step = optional_param('step', 0, PARAM_INT);
$sid = optional_param('sid', 0, PARAM_INT);


$total_steps = [];
$total_steps['personal_data'] = [
    'totalcount' => 1,
    'activity_keys' => [0 => 0], // Dummy key for personal data section
];

foreach (template_service::get_activities() as $section_key => $activities) {
    $total_steps[$section_key] = [
        'totalcount' => count($activities),
        'activity_keys' => array_keys($activities),
    ];
}

// Validate current step
$total_step_count = array_sum(array_column($total_steps, 'totalcount'));
if ($url_step < 0 || $url_step >= $total_step_count) {
    $params = ['step' => 0, 'sid' => $sid];
    redirect(new moodle_url('/local/teacher_activities/activity/form.php', $params));
}
$current_step = $url_step;
if (session_service::get_submission_id() !== $sid) {
    session_service::clear(); // Clear any existing session data for a fresh start
    session_service::set_all(api::get_submission($sid));
}

foreach ($total_steps as $sec_key => $data) {
    if ($current_step < $data['totalcount']) {
        $section_key = $sec_key;
        $activity_key = $data['activity_keys'][$current_step];
        break;
    }
    $current_step -= $data['totalcount'];
}
$is_last_step = $url_step == $total_step_count - 1;

// Set up the page
$params = ['step' => $url_step, 'sid' => $sid];
$PAGE->set_url(new moodle_url('/local/teacher_activities/activity/form.php', $params));
$PAGE->set_context(\context_system::instance());

$action_string = $sid ? 'Editare' : 'Înregistrare';
$PAGE->set_title("$action_string activitate didactică");
$PAGE->set_heading(get_string($section_key, 'local_teacher_activities'));

// Create appropriate form based on section
$mform = $section_key === 'personal_data' ?
    new \local_teacher_activities\forms\general_form($PAGE->url->out(false)) :
    new \local_teacher_activities\forms\activity_form(
        $PAGE->url->out(false),
        [
            'activity' => template_service::get_activity($section_key, $activity_key),
            'is_last_step' => $is_last_step,
            'step' => $current_step + 1, // For display purposes, steps are 1-based
            'item_count' => session_service::get_item_count($section_key, $activity_key),
        ]
    );

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Cancel element will be interpreted as back button
    session_service::set_activity($section_key, $activity_key, $mform->get_submitted_data());

    if ($url_step > 0)
        redirect(new moodle_url($PAGE->url, ['step' => $url_step - 1, 'sid' => $sid]));
} else if ($fromform = $mform->get_data()) {

    session_service::set_activity($section_key, $activity_key, $fromform);

    if (!$is_last_step)
        redirect(new moodle_url($PAGE->url, ['step' => $url_step + 1, 'sid' => $sid]));

    // Save/Update
    $result_string = $sid ? 'Actualizare' : 'Înregistrare';
    api::save_submission(session_service::get_all());
    session_service::clear();

    redirect(
        new moodle_url('/local/teacher_activities/activity/index.php'),
        "$result_string cu succes!",
        3,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.

    if (session_service::has_activity($section_key, $activity_key))
        $mform->set_data(session_service::get_activity($section_key, $activity_key));

    echo $OUTPUT->header();

    echo $OUTPUT->render(new \local_teacher_activities\output\form_page(
        $total_steps,
        $url_step,
        $sid,
        $mform->render()
    ));

    echo $OUTPUT->footer();
}