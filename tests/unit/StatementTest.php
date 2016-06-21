<?php

use Doctrine\DBAL\Statement as DcStatement;
use Facile\DoctrineMySQLComeBack\Doctrine\DBAL\Statement;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * Class StatementTest
 */
class StatementTest extends \PHPUnit_Framework_TestCase
{
    public function test_construction()
    {
        $sql = 'SELECT 1';
        $connection = $this->mockBaseConnection($sql);
        $statement = new Statement($sql, $connection->reveal());

        $this->assertInstanceOf('Facile\DoctrineMySQLComeBack\Doctrine\DBAL\Statement', $statement);
    }

    public function test_execute()
    {
        $sql = 'SELECT 1';
        $dcStatement = $this->prophesize(DcStatement::class);
        $dcStatement->execute(["test" => 1])->shouldBeCalledTimes(1)->willReturn(true);

        $connection = $this->mockBaseConnection($sql, $dcStatement->reveal());

        $statement = new Statement($sql, $connection->reveal());
        $this->assertTrue(
            $statement->execute(["test" => 1])
        );
    }

    public function test_execute_gone_away_not_retrayable()
    {
        $sql = 'SELECT 1';
        $dcStatement = $this->prophesize(DcStatement::class);
        $dcStatement->execute(["test" => 1])->willThrow(new \Exception('test'));

        $connection = $this->mockBaseConnection($sql, $dcStatement->reveal());
        $connection->canTryAgain(0)->willReturn(false);
        $connection->close()->shouldNotBeCalled();
        $connection->isRetryableException(Argument::type('\Exception'), $sql)->willReturn(false);

        $statement = new Statement($sql, $connection->reveal());

        $this->setExpectedException('\Exception', 'test');
        $statement->execute(["test" => 1]);
    }

    /**
     * @param             $sql
     * @param DcStatement $statement
     *
     * @return \Facile\DoctrineMySQLComeBack\Doctrine\DBAL\Connection|\Prophecy\Prophecy\ObjectProphecy
     */
    private function mockBaseConnection($sql, $statement = null)
    {
        $connection = $this
            ->prophesize('Facile\DoctrineMySQLComeBack\Doctrine\DBAL\Connection');
        $connection
            ->prepareUnwrapped($sql)
            ->shouldBeCalled()
            ->willReturn($statement);

        return $connection;
    }
}
