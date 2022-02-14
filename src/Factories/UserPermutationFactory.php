<?php

namespace Railroad\Crux\Factories;

use Railroad\Crux\UserPermutations\UserPermutation;
use \Railroad\Usora\Entities\User;

class UserAccountDetailsPagePermutationFactory
{
    /** @var UserPermutation */
    private $permutation;

    public function __contruct(User $user)
    {
        // todo: determine and created instance
        $this->permutation = '?????????';
    }

    public function getPermutation(): UserPermutation
    {
        return $this->permutation;
    }
}