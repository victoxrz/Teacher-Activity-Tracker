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
    public static function get_activities(): object
    {
        $cache = self::get_cache();
        $activities = $cache->get(self::CACHE_KEY);

        if ($activities === false) {
            $json_path = __DIR__ . '/../../templates/activities.json';
            $activities = json_decode(file_get_contents($json_path));
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

    public static function get_section_key(int $index): string
    {
        $sections = array_keys((array) self::get_activities());
        return $sections[$index] ?? '';
    }

    public static function get_section(int $index): array
    {
        $activities = self::get_activities();
        $section_keys = array_keys((array) $activities);
        $section_key = $section_keys[$index] ?? '';
        return $activities->{$section_key} ?? [];
    }

    public static function get_section_by_key(string $section_key): array
    {
        $activities = self::get_activities();
        return $activities->{$section_key} ?? [];
    }

    public static function get_activity(int $section_index, int $activity_index): ?object
    {
        $section = self::get_section($section_index);
        return $section[$activity_index] ?? null;
    }
}