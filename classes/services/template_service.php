<?php

namespace local_teacher_activities\services;

class template_service
{
    private const CACHE_KEY = 'activities_data';

    /**
     * Get cache instance
     */
    private static function get_cache(): \cache
    {
        return \cache::make('local_teacher_activities', 'activities');
    }

    /**
     * Get activities with caching
     */
    public static function get_activities(): array
    {
        $cache = self::get_cache();
        $activities = $cache->get(self::CACHE_KEY);

        if ($activities === false) {
            $json_path = __DIR__ . '/../../templates/activities.json';
            $activities = json_decode(file_get_contents($json_path), true);
            $cache->set(self::CACHE_KEY, $activities);
        }

        return $activities;
    }

    /**
     * Clear cache (useful when JSON is updated)
     */
    public static function clear_cache(): void
    {
        $cache = self::get_cache();
        $cache->delete(self::CACHE_KEY);
    }

    public static function get_section(string $section_key): ?array
    {
        $activities = self::get_activities();
        return $activities[$section_key];
    }

    public static function get_activity(string $section_key, string $activity_key): ?array
    {
        $section = self::get_section($section_key);
        return $section[$activity_key];
    }
}