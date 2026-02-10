<?php

namespace local_teacher_activities\services;

use stdClass;
use local_teacher_activities\forms\activity_form;

class form_helper
{
    public static function get_item_details(stdClass $formdata, int $i)
    {
        $details = new stdClass();
        foreach ($formdata as $key => $value) {
            if (
                str_starts_with($key, activity_form::PREFIX_DETAIL) &&
                \is_array($value) &&
                \array_key_exists($i, $value)
            ) {
                $parts = explode('-', $key, 3);
                $detailkey = $parts[1];
                if ($value[$i] === null || $value[$i] === '') {
                    continue;
                }
                $details->{$detailkey} = $value[$i];
            }
        }
        return $details;
    }

    public static function get_item_vars(stdClass $formdata, int $i)
    {
        $vars = new stdClass();
        foreach ($formdata as $key => $value) {
            if (
                str_starts_with($key, activity_form::PREFIX_VAR) &&
                \is_array($value) &&
                \array_key_exists($i, $value)
            ) {
                $parts = explode('-', $key, 3);
                $varkey = $parts[1];
                $vars->{$varkey} = $value[$i];
            }
        }
        return $vars;
    }

    public static function get_item_types(stdClass $formdata, string $root_item_type, int $i): ?string
    {
        $chain = [];
        $current_field = activity_form::PREFIX_ITEMTYPE . $root_item_type;

        while (isset($formdata->{$current_field}) && isset($formdata->{$current_field}[$i])) {
            $current_value = $formdata->{$current_field}[$i];
            if (empty($current_value)) {
                break;
            }

            $chain[] = $current_value;
            $current_field = activity_form::PREFIX_ITEMTYPE . $current_value;
        }

        return empty($chain) ? null : implode('-', $chain);
    }

}