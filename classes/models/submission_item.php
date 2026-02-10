<?php

namespace local_teacher_activities\models;

class submission_item extends \core\persistent
{
    const TABLE = 'local_teacher_items';

    protected static function define_properties(): array
    {
        // TODO: think about null values for details, vars, activitysubkey and functions from form_helper.
        // ask Maxim about this
        return [
            'sectionkey' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'activitykey' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'activitysubkey' => [
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'details' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'vars' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'evaluationscore' => [
                'type' => PARAM_FLOAT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'urladdress' => [
                // TODO: use PARAM_URL?
                'type' => PARAM_TEXT,
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'submissionid' => [
                'type' => PARAM_INT,
                'null' => NULL_NOT_ALLOWED,
            ],
        ];
    }

    public function is_evaluted(): bool
    {
        return static::get('evaluationscore') !== null;
    }
}