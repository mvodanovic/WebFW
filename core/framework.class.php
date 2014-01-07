<?php

namespace WebFW\Core;

use WebFW\Cache\Cache;
use WebFW\Core\Classes\GeneralHelper;
use WebFW\Core\Exceptions\NotFoundException;
use WebFW\Dev\Classes\DevHelper;
use WebFW\Dev\Controller as DevController;
use WebFW\Dev\InfoBox;
use WebFW\Dev\Profiler;

/**
 * Class Framework
 *
 * Class used for executing core framework operations.
 *
 * @package WebFW\Core
 */
final class Framework
{
    /**
     * Starts the framework core.
     *
     * @throws Exception When an error occurs
     * @internal
     */
    public static function start()
    {
        Profiler::getInstance()->addMoment('Framework start');

        Config::init();

        Profiler::getInstance()->addMoment('After configuration load');

        Request::handleIncomingRedirection();

        if (Config::get('Debug', 'errorReporting') !== null) {
            error_reporting(Config::get('Debug', 'errorReporting'));
        }

        if (Config::get('Debug', 'displayErrors') !== null) {
            ini_set('display_errors', Config::get('Debug', 'displayErrors'));
        }

        if (Config::get('General', 'routerClass') !== null) {
            Router::setClass(Config::get('General', 'routerClass'));
        }

        /** @var Controller $ctl */
        $ctl = Request::getInstance()->ctl;
        if ($ctl === null || $ctl === '') {
            $ctl = Config::get('General', 'defaultController');
        }
        if ($ctl === null || $ctl === '') {
            require_once \WebFW\Core\FW_PATH . '/core/templates/helloworld.template.php';
            return;
        }

        if (!is_subclass_of($ctl, Controller::className())) {
            throw new NotFoundException($ctl . ' is not an instance of ' . Controller::className());
        }

        Profiler::getInstance()->addMoment('After controller detection');

        if (DevHelper::isDevRequest()) {
            DevHelper::requestAuthentication(DevController::REALM_MESSAGE);
        }

        $cacheKey = null;
        $controllerOutput = null;
        $controller = null;

        if ($ctl::isCacheEnabled()) {
            $cacheKey = $ctl::className() . serialize(Request::getInstance()->getValues());
            if (Cache::getInstance()->exists($cacheKey)) {
                $controllerOutput = Cache::getInstance()->get($cacheKey);
                Profiler::getInstance()->addMoment('After reading controller output from cache');
            }
        }

        if ($controllerOutput === null) {
            /** @var Controller $controller */
            $controller = $ctl::getInstance();

            Profiler::getInstance()->addMoment('After controller construction');

            $controller->executeAction();

            Profiler::getInstance()->addMoment('After controller action execution');

            $controller->processOutput();
            $controllerOutput = $controller->getOutput();

            Profiler::getInstance()->addMoment('After controller output processing');

            if ($ctl::isCacheEnabled()) {
                Cache::getInstance()->set($cacheKey, $controllerOutput, $ctl::getCacheExpirationTime());
            }
        }

        if (DevHelper::isDevRequest() && is_subclass_of($ctl, HTMLController::className())) {
            $infobox = new InfoBox();
            if ($controller === null) {
                $infobox->setTitle($ctl);
            } else {
                $infobox->setTitle($ctl . '->' . $controller->getAction() . '()');
            }
            if ($cacheKey !== null) {
                $infobox->addData('Cache key', $cacheKey);
            }
            if ($ctl::isCacheEnabled()) {
                $infobox->addData('Cache duration', $ctl::getCacheExpirationTime());
            }
            $infobox->addData('Request', Request::getInstance());
            $controllerOutput = preg_replace('#<body([^>]*)>#', '<body$1>' . $infobox->parse(), $controllerOutput, 1);

            $infobox = new InfoBox();
            $infobox->setTitle('Profiler');
            $infobox->setContent(Profiler::getInstance()->getHTMLOutput());
            $controllerOutput = preg_replace('#</body>#', $infobox->parse() . '</body>', $controllerOutput, 1);
        }

        echo $controllerOutput;
    }

    /**
     * Runs a component and returns it's output.
     * $ownerObject can be another Component or the Controller.
     * Component with the $ownerObject set can access its public properties and methods.
     *
     * @param string $name Name of the component class
     * @param string|null $params Parameters passed to the component
     * @param Component|null $ownerObject Owner, or creator of the component
     * @return string Component's output
     * @throws Exception If the component doesn't exist or isn't an instance of Component
     */
    public static function runComponent($name, $params = null, Component $ownerObject = null)
    {
        /** @var Component $name */
        if (!is_subclass_of($name, Component::className())) {
            throw new Exception($name . ' is not an instance of ' . Component::className());
        }

        $cacheKey = null;
        $componentOutput = null;

        if ($name::isCacheEnabled()) {
            $cacheKey = $name . serialize($params);
            if (Cache::getInstance()->exists($cacheKey)) {
                $componentOutput = Cache::getInstance()->get($cacheKey);
            }
        }

        if ($componentOutput === null) {
            Profiler::getInstance()->addMoment('CMP: ' . $name . ' - before construction');

            /** @var $component Component */
            $component = new $name($params, $ownerObject);

            Profiler::getInstance()->addMoment('CMP: ' . $name . ' - after construction');

            $componentOutput = $component->run();

            Profiler::getInstance()->addMoment('CMP: ' . $name . ' - after execution');

            if ($name::isCacheEnabled()) {
                Cache::getInstance()->set($cacheKey, $componentOutput, $name::getCacheExpirationTime());
            }
        }

        if (DevHelper::isDevRequest()) {
            $infobox = new InfoBox();
            $infobox->setTitle('CMP: ' . $name);
            $infobox->setContent($componentOutput);
            $infobox->addData('Params', GeneralHelper::toString($params));
            if ($ownerObject !== null) {
                $infobox->addData('Owner', $ownerObject::className());
            }
            if ($cacheKey !== null) {
                $infobox->addData('Cache key', $cacheKey);
            }
            if ($name::isCacheEnabled()) {
                $infobox->addData('Cache duration', $name::getCacheExpirationTime());
            }
            $componentOutput = $infobox->parse();
        }

        return $componentOutput;
    }

    /**
     * Class cannot be instantiated.
     */
    private function __construct() {}

    /**
     * Class cannot be cloned.
     */
    private function __clone() {}
}
