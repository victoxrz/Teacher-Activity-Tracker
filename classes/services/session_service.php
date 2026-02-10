<?php

namespace local_teacher_activities\services;

use local_teacher_activities\forms\activity_form;
use stdClass;

class session_service
{
    private const SESSION_KEY = 'teacher_activity_data';

    /**
     * Initialize session data if not exists
     */
    public static function init()
    {
        global $SESSION;
        if (!isset($SESSION->{self::SESSION_KEY})) {
            $SESSION->{self::SESSION_KEY} = [];
        }
    }

    /**
     * Get all session data
     */
    public static function get_all(): array
    {
        global $SESSION;
        self::init();
        return $SESSION->{self::SESSION_KEY};
    }

    /**
     * Get data for a specific step
     */
    public static function get_activity(string $sectionkey, string $activitykey): object|null
    {
        global $SESSION;
        self::init();
        return $SESSION->{self::SESSION_KEY}[$sectionkey][$activitykey] ?? null;
    }

    /**
     * Set all session data
     */
    public static function set_all(array $data)
    {
        global $SESSION;
        self::init();
        $SESSION->{self::SESSION_KEY} = $data;
    }

    /**
     * Set data for a specific step
     */
    public static function set_activity(string $sectionkey, string $activitykey, stdClass|null $data)
    {
        global $SESSION;
        self::init();
        if (!empty((array) $data))
            $SESSION->{self::SESSION_KEY}[$sectionkey][$activitykey] = $data;
    }

    /**
     * Get repeat count for a specific step
     */
    public static function get_item_count(string $sectionkey, string $activitykey): int
    {
        $step_data = self::get_activity($sectionkey, $activitykey);
        return $step_data->{activity_form::REPEAT_HIDDEN_NAME} ?? 0;
    }

    public static function get_submission_id(): int
    {
        $data = self::get_all();
        return $data['personal_data'][0]->id ?? 0;
    }

    /**
     * Clear all session data
     */
    public static function clear()
    {
        global $SESSION;
        unset($SESSION->{self::SESSION_KEY});
    }

    /**
     * Check if step has data
     */
    public static function has_activity(string $sectionkey, string $activitykey): bool
    {
        $data = self::get_all();
        return isset($data[$sectionkey][$activitykey]);
    }
}