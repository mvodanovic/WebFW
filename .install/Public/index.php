<?php
/**
 * Entry point for the framework.
 *
 * @package mvodanovic\WebFW
 */

use mvodanovic\WebFW\Bootstrap;
use mvodanovic\WebFW\BootstrapException;
use mvodanovic\WebFW\Core\Framework;
use mvodanovic\WebFW\Core\Exception as WebFWException;

session_start();
ob_start();

if (!file_exists('../.Bootstrap.php')) {
    header('Content-type: text/plain; charset=UTF-8');
    echo 'WebFW Fatal Error: Bootstrap unreachable: ', realpath('..'), DIRECTORY_SEPARATOR, '.Bootstrap.php';
    die;
}

require_once('../.Bootstrap.php');

try {
    Bootstrap::init();
    Framework::start();
} catch (WebFWException $e) {
    $e->errorMessage();
} catch (BootstrapException $e) {
    if (class_exists('mvodanovic\WebFW\Core\Exception', false)) {
        $e = new WebFWException($e->getMessage(), $e->getCode(), $e);
        $e->errorMessage();
    } else {
        $e->errorMessage();
    }
} catch (Exception $e) {
    $e = new WebFWException($e->getMessage(), $e->getCode(), $e);
    $e->errorMessage();
}
