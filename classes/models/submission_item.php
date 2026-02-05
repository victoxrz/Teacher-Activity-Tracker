<?php

namespace local_teacher_activities\models;

class submission_item
{
    public int $id;
    public int $submission_id;
    public string $section;
    public string $item_type;
    public string $details;
    public float $user_score;
    public float $evaluation_score;
    public string|null $item_link;
    public int|null $item_file;
}