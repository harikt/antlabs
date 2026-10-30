<?php

namespace App\Enum;

enum Status: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';
}
