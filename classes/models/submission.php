<?php

namespace local_teacher_activities\models;

class submission extends \core\persistent
{
    // there might be anonymous submissions
    // public int $userid;
    // TODO: add later, after versioning is implemented
    // public int $template_id;

    const TABLE = 'local_teacher_submissions';

    protected static function define_properties()
    {
        global $USER;

        return [
            'id' => [
                'default' => 0,
                'type' => PARAM_INT,
            ],
            'firstname' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'lastname' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'positiontitle' => [
                'type' => PARAM_TEXT,
                'null' => NULL_NOT_ALLOWED,
            ],
            'email' => [
                'type' => PARAM_EMAIL,
                'null' => NULL_NOT_ALLOWED,
            ],
        ];
    }

    public function to_defined_record()
    {
        $data = new \stdClass();
        // TODO: alternatives? use properties_filter?
        $properties = array_intersect_key(
            self::define_properties(),
            self::properties_definition()
        );
        foreach ($properties as $property => $definition) {
            $data->$property = $this->raw_get($property);
        }
        return $data;
    }
}