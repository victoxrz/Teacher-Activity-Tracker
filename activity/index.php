<?php

use local_teacher_activities\models\submission;
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require_login();

$PAGE->set_url(new moodle_url('/local/teacher_activities/activity/index.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Activități Didactice');
$PAGE->set_heading('Activități Didactice');

echo $OUTPUT->header();
// Add a prominent button to create new activity
echo html_writer::start_div('mb-3');
echo $OUTPUT->single_button(
    new moodle_url('/local/teacher_activities/activity/form.php'),
    'Adaugă Activitate Nouă',
    'get',
    ['class' => 'btn-primary btn-lg']
);
echo html_writer::end_div();

// Query submissions, ordered by latest
$submissions = submission::get_records([], 'timecreated', 'DESC');

if (empty($submissions)) {
    echo html_writer::tag('p', 'Nu există activități înregistrate.', ['class' => 'alert alert-info']);
} else {
    foreach ($submissions as $sub) {
        $editurl = new moodle_url('/local/teacher_activities/activity/form.php', ['sid' => $sub->get('id')]);

        echo html_writer::start_div('card mb-3');
        echo html_writer::start_div('card-header bg-primary text-white');
        echo html_writer::tag(
            'h5',
            "Activitate #{$sub->get('id')}",
            ['class' => 'card-title mb-0']
        );
        echo html_writer::end_div();

        echo html_writer::start_div('card-body');

        // User details
        echo html_writer::tag(
            'p',
            '<strong>Nume:</strong> ' . s($sub->get('firstname') . ' ' . $sub->get('lastname')),
            ['class' => 'mb-1']
        );
        echo html_writer::tag(
            'p',
            '<strong>Email:</strong> ' . s($sub->get('email')),
            ['class' => 'mb-1']
        );
        echo html_writer::tag(
            'p',
            '<strong>Funcție:</strong> ' . s($sub->get('positiontitle')),
            ['class' => 'mb-1']
        );
        echo html_writer::tag(
            'p',
            '<strong>Data trimiterii:</strong> ' . userdate($sub->get('timecreated'), '%d %B %Y, %H:%M'),
            ['class' => 'mb-3']
        );

        // Edit button
        echo html_writer::link($editurl, 'Editează', ['class' => 'btn btn-secondary']);

        echo html_writer::end_div(); // card-body
        echo html_writer::end_div(); // card
    }
}

echo $OUTPUT->footer();
