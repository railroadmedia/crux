<?php

namespace Railroad\Crux\UserPermutations;

class CancelledMemberWithAccessRemaining extends UserPermutation
{
    /**
     * @return bool
     */
    public function showCancelledNotice(): bool
    {
        return true;
    }
}