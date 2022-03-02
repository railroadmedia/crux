<?php

namespace Railroad\Crux\UserPermutations;

class StudentWithoutMembershipAccess extends UserPermutation
{
    public function hasMembershipAccess()
    {
        return false;
    }
}