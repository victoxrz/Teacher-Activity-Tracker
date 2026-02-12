<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');

require_login();

$PAGE->set_url(new moodle_url('/local/teacher_activities/activity/index.php'));
$PAGE->set_context(\context_system::instance());
$PAGE->set_title('Activități Didactice');
$PAGE->set_heading('Activități Didactice');

echo $OUTPUT->header();
echo $OUTPUT->heading('Listă Activități');

$submissions = $DB->get_records('local_teacher_submissions');

$table = new html_table();
$table->head = ['Prenume', 'Nume', 'Funcție', 'Acțiuni'];

foreach ($submissions as $sub) {
    $editurl = new moodle_url('/local/teacher_activities/activity/form.php', ['sid' => $sub->id]);
    $items_link = new moodle_url('/local/teacher_activities/activity/view.php', ['sid' => $sub->id]);

    $table->data[] = [
        s($sub->firstname),
        s($sub->lastname),
        s($sub->positiontitle),
        html_writer::link($editurl, 'Editează')
    ];
}

echo html_writer::table($table);

echo $OUTPUT->single_button(new moodle_url('/local/teacher_activities/activity/form.php'), 'Adaugă Activitate Nouă');

echo $OUTPUT->footer();
