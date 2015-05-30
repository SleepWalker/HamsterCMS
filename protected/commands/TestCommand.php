<?php

class TestCommand extends \CConsoleCommand
{
    public function actionIndex($moduleId)
    {
        $path = \Yii::getPathOfAlias('application.modules.' . $moduleId) . '/tests';

        $this->runUnitTests($path);
    }

    private function runUnitTests($path)
    {
        $args = [
            '--configuration ' . \Yii::getPathOfAlias('application.tests.phpunit') . '.xml',
            "--colors=always",
            "'$path'",
        ];

        $output = $this->runComposerBinary('phpunit', $args);

        echo "\n\n" . implode("\n", $output) . "\n\n";
    }

    private function runComposerBinary($binName, $args)
    {
        if (is_array($args)) {
            $args = implode(' ', $args);
        }

        \Yii::setPathOfAlias('composer', \Yii::getPathOfAlias('application.vendor.composer'));

        $command = \Yii::getPathOfAlias("composer.vendor.bin.$binName") . " $args";

        exec($command, $output);

        return $output;
    }
}
