<?php
namespace ext\test;

class ARTestHelper
{
    private $test;
    private $db;
    private $command;
    private $commandBuilder;

    public function setUp(\CTestCase $test)
    {
        $this->test = $test;

        $this->db = $this->test->getMockBuilder('\CDbConnection')->getMock();

        $schema = $this->test->getMockBuilder('\CMysqlSchema')
            ->setMethods(['getCommandBuilder'])
            ->setConstructorArgs([$this->db])
            ->getMock();

        $this->commandBuilder = $this->test->getMockBuilder('\CMysqlCommandBuilder')
            ->setConstructorArgs([$schema])
            ->setMethods([
                'createUpdateCommand',
            ])
            ->getMock();

        $this->command = $this->test->getMockBuilder('\CDbCommand')
            ->disableOriginalConstructor()
            ->getMock();

        $this->db
            ->method('getSchema')
            ->willReturn($schema);

        $schema
            ->method('getCommandBuilder')
            ->willReturn($this->commandBuilder);

        $this->db
            ->method('createCommand')
            ->willReturn($this->command);

        \CActiveRecord::$db = $this->db;
    }

    public function tearDown()
    {
        \CActiveRecord::$db = \Yii::app()->getDb();
    }

    public function willFind(array $attributes)
    {
        $this->command
            ->method('queryRow')
            ->willReturn($attributes);
    }

    public function willSave(array $attributes)
    {
        $this->commandBuilder
            ->expects($this->test->once())
            ->method('createUpdateCommand')
            ->with($this->test->anything(), $this->test->callback(function ($data) use ($attributes) {

                foreach ($attributes as $key => $value) {
                    if (!isset($data[$key])) {
                        return false;
                    }

                    if ($data[$key] !== $value) {
                        return false;
                    }
                }

                return true;
            }), $this->test->anything())
            ->willReturn($this->command)
            ;

        $this->command
            ->expects($this->test->once())
            ->method('execute');
    }
}
