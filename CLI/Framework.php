<?php

namespace mvodanovic\WebFW\CLI;

use mvodanovic\WebFW\Core\Classes\ClassHelper;
use mvodanovic\WebFW\Core\Config;
use mvodanovic\WebFW\Core\Exception;
use mvodanovic\WebFW\Dev\Profiler;
use ReflectionClass;

final class Framework
{
    public static function start()
    {
        Profiler::getInstance()->addMoment('Framework start');

        Config::init();

        Profiler::getInstance()->addMoment('After configuration load');

        $command = ArgumentProcessor::getInstance()->getCommand();
        if ($command === null) {
            throw new Exception('No command given');
        }

        static::runCommand($command);

        Profiler::getInstance()->outputToCLI();
    }

    private static function runCommand($command)
    {
        $classHelper = new ClassHelper();
        $isCommandFound = false;
        foreach ($classHelper->getClasses(Script::className(), false) as $scriptName) {
            $isCommandFound = true;
            $obj = new ReflectionClass($scriptName);
            if ($obj->getconstant("COMMAND") === $command) {
                Profiler::getInstance()->addMoment('After script detection');

                /** @var Script $script */
                $script = new $scriptName();

                Profiler::getInstance()->addMoment('After script construction');

                $returnValue = $script->execute();

                Profiler::getInstance()->addMoment('After script execution');

                echo $returnValue;
                break;

            }

        }

        if (!$isCommandFound) {
            throw new Exception('Command "' . $command . '" not found');
        }
    }
}
