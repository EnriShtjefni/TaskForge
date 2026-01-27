<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';

    public static function transitions(): array
    {
        return [
            self::TODO->value        => [self::IN_PROGRESS->value],
            self::IN_PROGRESS->value => [self::REVIEW->value],
            self::REVIEW->value      => [self::DONE->value, self::IN_PROGRESS->value],
            self::DONE->value        => [],
        ];
    }
}
