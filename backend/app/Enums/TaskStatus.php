<?php

namespace app\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';

    public static function transitions(): array
    {
        return [
            self::TODO => [self::IN_PROGRESS->value],
            self::IN_PROGRESS => [self::REVIEW->value],
            self::REVIEW => [self::DONE->value, self::IN_PROGRESS->value],
            self::DONE => [],
        ];
    }
}
