<?php
/**
 * Entry point for the framework.
 *
 * @package mvodanovic\WebFW
 */

session_start();
ob_start();

if (!file_exists('../WebFW/Framework/Core/__bootstrap.php')) {
    header('Content-type: text/plain; charset=UTF-8');
    echo 'WebFW Fatal Error: Bootstrap unreachable: ',
        realpath('..'), DIRECTORY_SEPARATOR,
        'WebFW', DIRECTORY_SEPARATOR,
        'Framework', DIRECTORY_SEPARATOR,
        'Core', DIRECTORY_SEPARATOR,
        '__bootstrap.php';
    die;
}

require_once('../WebFW/Framework/Core/__bootstrap.php');

try {
    \mvodanovic\WebFW\Core\Framework::start();
} catch (\mvodanovic\WebFW\Core\Exception $e) {
    $e->ErrorMessage();
} catch (Exception $e) {
    $e = new \mvodanovic\WebFW\Core\Exception($e->getMessage(), $e->getCode(), $e);
    $e->ErrorMessage();
}
