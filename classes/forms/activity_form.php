<?php

namespace local_teacher_activities\forms;

use html_writer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class activity_form extends \moodleform
{
    // Prefixes for different element types
    public const PREFIX_ITEMTYPE = 'itemtype-';
    public const PREFIX_VAR = 'var-';
    public const PREFIX_DETAIL = 'detail-';


    // Names for repeat elements
    public const REPEAT_HIDDEN_NAME = 'item_count';
    private const DELETE_BUTTON_NAME = 'remove_item';
    private const SUBMIT_BUTTON_NAME = 'submitbutton';
    private const BACK_BUTTON_NAME = 'backbutton';

    protected static $fieldstoremove = [self::SUBMIT_BUTTON_NAME, self::BACK_BUTTON_NAME];

    // Store hidden elements with their conditions to manage conditional visibility
    protected $_hidden_elements = [];

    public static function get_filemanager_options()
    {
        return ['subdirs' => 0, 'maxfiles' => 10];
    }

    /**
     * Recursively define activity elements
     * 
     * @param array $activity The activity object
     * @param array $repeatarray Reference to the repeat elements array
     * @param array $repeatoptions Reference to the repeat options array
     * @param array{element:string, value:string}|null $parent The parent element details ['element' => string, 'value' => string], if any
     * @return void
     */
    private function define_activity($activity, &$repeatarray, &$repeatoptions, $parent = null)
    {
        $mform = $this->_form;

        if (isset($activity['child_activities']) && isset($activity['child_activities']['items'])) {
            // 1. Publication Type
            $puboptions = ['' => get_string('choosedots')];
            foreach ($activity['child_activities']['items'] as $child) {
                $puboptions[$child['key']] = get_string($child['key'], 'local_teacher_activities');
            }

            $elementname = self::PREFIX_ITEMTYPE . "{$activity['key']}";
            $repeatarray[] = $mform->createElement(
                'select',
                $elementname,
                get_string($activity['child_activities']['key'], 'local_teacher_activities'),
                $puboptions
            );

            // If this element has a parent, hide it unless the parent has the correct value
            if ($parent) {
                $repeatoptions[$elementname]['hideif'] = [$parent['element'], 'neq', $parent['value']];
                $this->_hidden_elements[$elementname] = [$parent['element'], $parent['value']];
            }

            foreach ($activity['child_activities']['items'] as $child) {
                $this->define_activity(
                    $child,
                    $repeatarray,
                    $repeatoptions,
                    ['element' => $elementname, 'value' => $child['key']]
                );
            }
        }

        // 2. Variables for each activity
        if (isset($activity['score']['vars']))
            foreach ($activity['score']['vars'] as $varkey => $varlabel) {
                $var_elementname = self::PREFIX_VAR . "{$varkey}-{$activity['key']}";
                $repeatarray[] = $mform->createElement(
                    'float',
                    $var_elementname,
                    get_string($varkey, 'local_teacher_activities')
                );

                if ($parent)
                    $repeatoptions[$var_elementname]['hideif'] = [$parent['element'], 'neq', $parent['value']];
            }

        // 3. Details for each activity
        if (isset($activity['details']))
            foreach ($activity['details'] as $detailkey => $detaillabel) {
                $detail_elementname = self::PREFIX_DETAIL . "{$detailkey}-{$activity['key']}";
                $repeatarray[] = $mform->createElement(
                    'textarea',
                    $detail_elementname,
                    get_string($detailkey, 'local_teacher_activities')
                );
                $repeatoptions[$detail_elementname]['type'] = PARAM_RAW_TRIMMED;
            }
    }

    public function definition()
    {
        $mform = $this->_form;

        // Custom data
        $customdata = $this->_customdata;
        $activity = $customdata['activity'];
        $is_last_step = $customdata['is_last_step'] ?? false;
        $item_count = $customdata['item_count'] ?? 0;
        $step = $customdata['step'] ?? 0;

        // Title
        $mform->addElement('html', "<h3>$step. " . get_string($activity['key'], 'local_teacher_activities') . "</h3>");

        $repeatarray = [];
        $repeatoptions = [];

        $repeatarray[] = $mform->createElement('hidden', 'id');
        $repeatoptions['id']['type'] = PARAM_INT;

        // Card opening HTML
        $repeatarray[] = $mform->createElement(
            'html',
            html_writer::start_div('card mb-3') .
            html_writer::start_div('card-header bg-primary text-white') .
            html_writer::tag('h5', 'Item', ['class' => 'card-title mb-0']) .
            html_writer::end_div() .
            html_writer::start_div('card-body')
        );

        // 1. Activity Type Selection
        $this->define_activity($activity, $repeatarray, $repeatoptions);

        // TODO: use CSS styling for making use of the full width
        // 3. URL Link
        $repeatarray[] = $mform->createElement(
            'url',
            'itemlink',
            get_string('section_item_link', 'local_teacher_activities'),
            ['size' => 100],
            ['usefilepicker' => false],
        );
        $repeatoptions['itemlink']['type'] = PARAM_RAW_TRIMMED;

        // 4. File Upload
        $repeatarray[] = $mform->createElement(
            'filemanager',
            'itemfile',
            get_string('section_item_file', 'local_teacher_activities'),
            null,
            self::get_filemanager_options()
        );
        $repeatoptions['itemfile']['type'] = PARAM_FILE;

        // 5. Remove button
        $repeatarray[] = $mform->createElement(
            'submit',
            'remove_item',
            get_string('section_item_removebutton', 'local_teacher_activities'),
            [],
            false
        );

        // Card closing HTML
        $repeatarray[] = $mform->createElement(
            'html',
            html_writer::end_div() . // card-body
            html_writer::end_div()   // card
        );

        $this->repeat_elements(
            $repeatarray,
            $item_count,
            $repeatoptions,
            self::REPEAT_HIDDEN_NAME,
            'add_item',
            1,
            get_string('section_item_addbutton', 'local_teacher_activities'),
            true,
            self::DELETE_BUTTON_NAME,
        );

        // Submit buttons
        $buttonarray = [];
        $buttonarray[] = $mform->createElement(
            'cancel',
            'backbutton',
            get_string('section_backbutton', 'local_teacher_activities')
        );
        $buttonarray[] = $mform->createElement(
            'submit',
            'submitbutton',
            $is_last_step ?
            get_string('section_submitbutton', 'local_teacher_activities')
            : get_string('section_nextbutton', 'local_teacher_activities')
        );

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        foreach ($data as $key => $value) {

            // 1. Check only item type fields
            // require if visible
            if (str_starts_with($key, self::PREFIX_ITEMTYPE)) {
                foreach ($value as $idx => $val) {
                    $isvisible = true;
                    if (isset($this->_hidden_elements[$key])) {
                        $parentinfo = $this->_hidden_elements[$key];
                        $parentfield = $parentinfo[0];
                        $parentvalue = $parentinfo[1];

                        if (isset($data[$parentfield]) && $data[$parentfield][$idx] !== $parentvalue) {
                            $isvisible = false;
                        }
                    }

                    if ($isvisible) {
                        $registry =& \HTML_QuickForm_RuleRegistry::singleton();
                        $req = $registry->getRule('required');

                        if (!$req->validate($val)) {
                            $errors["{$key}[$idx]"] = get_string('err_required', 'form');
                        }
                    }
                }
            }

            // 2. Check only variable fields
            if (str_starts_with($key, self::PREFIX_VAR)) {
                foreach ($value as $idx => $val) {
                    if ($val <= 0) {
                        $errors["{$key}[$idx]"] = get_string('err_positive_number', 'local_teacher_activities');
                    }
                }
            }

            // 3. Check only item link fields
            if (str_ends_with($key, 'itemlink')) {
                foreach ($value as $idx => $val) {
                    if (!empty($val)) {
                        $isvalid = clean_param($val, PARAM_URL);
                        if (!$isvalid) {
                            $errors["{$key}[$idx]"] = get_string('err_url', 'local_teacher_activities');
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Process form data to handle removed items and reindex arrays
     * 
     * @param \stdClass|null $formdata The form data object
     */
    private function hard_remove($formdata)
    {
        if (isset($formdata->{self::DELETE_BUTTON_NAME . '-hidden'})) {
            $removed = \count($formdata->{self::DELETE_BUTTON_NAME . '-hidden'});
            $formdata->{self::REPEAT_HIDDEN_NAME} -= $removed; // Adjust repeat count if items were removed

            $keys = array_keys(get_object_vars($formdata));
            foreach ($keys as $key) {
                if (\is_array($formdata->{$key})) {
                    $formdata->{$key} = array_values($formdata->{$key});
                }
            }
        }

        if (isset($formdata->{self::REPEAT_HIDDEN_NAME}) && $formdata->{self::REPEAT_HIDDEN_NAME} === 0) {
            unset($formdata->{self::REPEAT_HIDDEN_NAME});
        }
        unset($formdata->{self::DELETE_BUTTON_NAME . '-hidden'});
    }

    public function get_data()
    {
        $data = parent::get_data();

        if (\is_object($data)) {
            foreach (static::$fieldstoremove as $field) {
                unset($data->{$field});
            }
        }

        $this->hard_remove($data);
        return $data;
    }

    public function get_submitted_data()
    {
        $data = parent::get_submitted_data();

        if (\is_object($data)) {
            foreach (static::$fieldstoremove as $field) {
                unset($data->{$field});
            }
        }

        $this->hard_remove($data);
        return $data;
    }

}