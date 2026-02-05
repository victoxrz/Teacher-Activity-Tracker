<?php

namespace local_teacher_activities\models;

class submission
{
    public int $id;
    public int $user_id;
    public int $template_id;
    public string $first_name;
    public string $last_name;
    public string $position_title;
    public string $email;
    public int $time_created;
    public int $time_modified;
}