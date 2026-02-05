<?php

namespace local_teacher_activities\forms;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class general_form extends \moodleform
{
    // TODO: Add validation rules as needed
    public function definition()
    {
        global $USER;

        $mform = $this->_form;

        // Last name input
        $mform->addElement('text', 'lastname', 'Nume');
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname', 'Last name is required', 'required', null, 'client');
        if (isset($USER->lastname))
            $mform->setDefault('lastname', $USER->lastname);

        // First name input
        $mform->addElement('text', 'firstname', 'Prenume');
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname', 'First name is required', 'required', null, 'client');
        if (isset($USER->firstname))
            $mform->setDefault('firstname', $USER->firstname);

        // Email input
        $mform->addElement('text', 'email', 'Adresa de email');
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', 'Email is required', 'required', null, 'client');
        if (isset($USER->email))
            $mform->setDefault('email', $USER->email);

        // Occupation input
        $mform->addElement('text', 'position_title', 'Funcția');
        $mform->setType('position_title', PARAM_TEXT);
        $mform->addRule('position_title', 'Occupation is required', 'required', null, 'client');

        // Submit button (Next)
        $this->add_action_buttons(false, 'Next >');
    }

    // public function validation($data, $files)
    // {
    // }
}
