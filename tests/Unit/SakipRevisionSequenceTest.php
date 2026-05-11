<?php

namespace Tests\Unit;

use App\Support\SakipRevisionSequence;
use PHPUnit\Framework\TestCase;

class SakipRevisionSequenceTest extends TestCase
{
    public function test_next_revision_starts_from_one_when_history_is_empty(): void
    {
        $this->assertSame(1, SakipRevisionSequence::next(null));
        $this->assertSame(1, SakipRevisionSequence::next(0));
    }

    public function test_next_revision_increments_existing_maximum_revision(): void
    {
        $this->assertSame(2, SakipRevisionSequence::next(1));
        $this->assertSame(4, SakipRevisionSequence::next(3));
    }
}
