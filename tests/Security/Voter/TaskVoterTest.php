<?php

namespace App\Tests\Security\Voter;

use App\Entity\Task;
use App\Security\Voter\TaskVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;

class TaskVoterTest extends TestCase
{
    public function testVoteOnAttributeFalse()
    {
        $tokenInterface = $this->getMockBuilder(TokenInterface::class)->disableOriginalConstructor()->getMock();
        $security = $this->getMockBuilder(Security::class)->disableOriginalConstructor()->getMock();
        $voter = new TaskVoter($security);
        $this->assertEquals(0, $voter->vote($tokenInterface, (new Task()), ["WRONG_ATTRIBUTE"]));
    }
}