<?php

namespace local_teacher_activities\services;

use local_teacher_activities\forms\activity_form;

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
    public static function get_step(string $section, int $step): object|null
    {
        global $SESSION;
        self::init();
        return $SESSION->{self::SESSION_KEY}[$section][$step] ?? null;
    }

    /**
     * Set data for a specific step
     */
    public static function set_step(string $section, int $step, $data)
    {
        global $SESSION;
        self::init();
        $SESSION->{self::SESSION_KEY}[$section][$step] = $data;
    }

    /**
     * Get repeat count for a specific step
     */
    public static function get_repeat_count(string $section, int $step): int
    {
        $step_data = self::get_step($section, $step);
        return $step_data->{activity_form::REPEAT_HIDDEN_NAME} ?? 0;
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
    public static function has_step(string $section, int $step)
    {
        $data = self::get_all();
        return isset($data[$section][$step]);
    }
}