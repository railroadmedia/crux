<?php

namespace Railroad\Crux\UserPermutations;

class StudentWithoutMembershipAccess extends UserPermutation
{
    public function hasMembership()
    {
        return false;
    }
}