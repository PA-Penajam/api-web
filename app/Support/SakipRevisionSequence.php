<?php

namespace App\Support;

class SakipRevisionSequence
{
    public static function next(?int $currentMax): int
    {
        return max(0, $currentMax ?? 0) + 1;
    }
}
