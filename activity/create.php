<?php

use local_teacher_activities\services\session_service;
use local_teacher_activities\services\template_service;
use moodle_url;

require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/lib/validateurlsyntax.php');

require_login();
// print session data for debugging
// echo '<pre>';
// echo '<br><br><br>';
// print_r($SESSION->teacher_activity_data);
// echo '</pre>';

/** @var int */
$url_step = optional_param('step', 0, PARAM_INT);
/**
 * @var array{string: int} $total_steps
 */
$total_steps = [];

$section_keys = array_keys((array) template_service::get_activities());
foreach ($section_keys as $section_key) {
    $total_steps[0] = 1; // Personal data section has only 1 step
    $total_steps[$section_key] = count(template_service::get_section_by_key($section_key));
}

// Validate current step
if ($url_step < 0 || $url_step >= array_sum($total_steps)) {
    redirect(new moodle_url('/local/teacher_activities/activity/create.php', ['step' => 0]));
}

$current_section_key = '';
$total_steps_in_section = 0;
$current_step = $url_step;

if ($current_step === 0) {
    $current_section_key = 'personal_data';
    $total_steps_in_section = 1;
} else {
    foreach ($total_steps as $section_key => $steps_in_section) {
        if ($current_step < $steps_in_section) {
            $current_section_key = $section_key;
            $total_steps_in_section = $steps_in_section;
            break;
        }
        $current_step -= $steps_in_section;
    }
}
$is_final_step = $url_step == array_sum($total_steps) - 1;

// Set up the page
$PAGE->set_url(new moodle_url('/local/teacher_activities/activity/create.php', ['step' => $url_step]));
$PAGE->set_context(\core\context\system::instance());
$PAGE->set_title('Înregistrare activitate didactică');
$PAGE->set_heading(get_string($current_section_key, 'local_teacher_activities'));

// Create appropriate form based on section
$mform = $current_section_key === 'personal_data' ?
    new \local_teacher_activities\forms\general_form($PAGE->url->out(false)) :
    new \local_teacher_activities\forms\activity_form(
        $PAGE->url->out(false),
        [
            'activity' => template_service::get_section_by_key($current_section_key)[$current_step],
            'is_last_step' => $is_final_step,
            'step' => $current_step + 1,
            'repeat_count' => session_service::get_repeat_count($current_section_key, $current_step),
        ]
    );

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Cancel element will be interpreted as back button
    session_service::set_step($current_section_key, $current_step, $mform->get_submitted_data());

    if ($url_step > 0)
        redirect(new moodle_url($PAGE->url, ['step' => $url_step - 1]));
} else if ($fromform = $mform->get_data()) {
    // When the form is submitted, and the data is successfully validated,
    // the `get_data()` function will return the data posted in the form.

    session_service::set_step($current_section_key, $current_step, $fromform);

    if (!$is_final_step)
        redirect(new moodle_url($PAGE->url, ['step' => $url_step + 1]));

    \local_teacher_activities\services\submission_service::save_submission(session_service::get_all());

    redirect(
        new moodle_url('/my/'),
        'Activitatea a fost înregistrată cu succes!',
        3,
        \core\output\notification::NOTIFY_SUCCESS
    );
} else {
    // This branch is executed if the form is submitted but the data doesn't
    // validate and the form should be redisplayed or on the first display of the form.

    if (session_service::has_step($current_section_key, $current_step))
        $mform->set_data(session_service::get_step($current_section_key, $current_step));

    echo $OUTPUT->header();
    $mform->display();
}

echo $OUTPUT->footer();