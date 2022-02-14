<?php

use Railroad\Crux\Tests\TestCase;

class UnitTest extends TestCase
{
    private $permutations = [
        // case 1
        [
            'foo' => 'bar',
            'bar' => 'baz',
        ],
        // case 2
        [
            'foo' => 'bar',
            'bar' => 'baz',
        ],
        // case 2
        [
            'foo' => 'bar',
            'bar' => 'baz',
        ],
        // case 2
        [
            'foo' => 'bar',
            'bar' => 'baz',
        ],
        // case 2
        [
            'foo' => 'bar',
            'bar' => 'baz',
        ],
    ];

    public function test_that_true_is_true()
    {
        $this->assertTrue(true);
    }

    public function test_active_membership_monthly()
    {
        $this->markTestIncomplete('To do');
    }

    public function test_active_membership_annual()
    {
        $this->markTestIncomplete('To do');
    }

    public function test_active_membership_every_2_months()
    {
        $this->markTestIncomplete('To do');
    }

    public function test_active_membership_2_months()
    {
        $this->markTestIncomplete('To do');
    }

    public function test_active_membership_3_months()
    {
        $this->markTestIncomplete('To do');
    }

    public function test_active_membership_6_months()
    {
        $this->markTestIncomplete('To do');
    }
}
