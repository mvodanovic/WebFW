<?php

namespace mvodanovic\WebFW\CLI;

use mvodanovic\WebFW\Core\Classes\BaseClass;

class ArgumentProcessor extends BaseClass
{
    protected static $instance = null;

    protected $command = null;
    protected $flags = [];
    protected $args = [];
    protected $kwargs = [];

    /**
     * @return ArgumentProcessor
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    protected function __construct()
    {
        global $argv;

        array_shift($argv);
        $this->command = array_shift($argv);

        while (($argument = array_shift($argv)) !== null) {
            if ($argument[0] === '-') {
                if (mb_strlen($argument) >= 2) {
                    if ($argument[1] !== '-') {
                        for ($i = 1; $i < mb_strlen($argument); $i++) {
                            $this->flags[$argument[$i]] = true;
                        }
                        continue;
                    } else {
                        $pieces = explode('=', substr($argument, 2));
                        if (count($pieces) === 2) {
                            $this->kwargs[$pieces[0]] = $pieces[1];
                            continue;
                        }
                    }
                }
            }

            $this->args[] = $argument;
        }
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function __get($key)
    {
        if (is_string($key) && array_key_exists($key, $this->kwargs)) {
            return $this->kwargs[$key];
        } elseif (is_int($key) && array_key_exists($key, $this->args)) {
            return $this->args[$key];
        }

        return null;
    }

    public function isFlagSet($flag)
    {
        return array_key_exists($flag, $this->flags);
    }

    public function getFlags()
    {
        return array_keys($this->flags);
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getKwargs()
    {
        return $this->kwargs;
    }

    private function __clone() {}
}
