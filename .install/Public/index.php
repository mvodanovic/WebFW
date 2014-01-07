<?php
/**
 * Entry point for the framework.
 *
 * @package WebFW\Framework\Core
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
    \WebFW\Framework\Core\Framework::start();
} catch (\WebFW\Framework\Core\Exception $e) {
    $e->ErrorMessage();
} catch (Exception $e) {
    $e = new \WebFW\Framework\Core\Exception($e->getMessage(), $e->getCode(), $e);
    $e->ErrorMessage();
}
