<?php
/**
 * Entry point for the framework.
 *
 * @package mvodanovic\WebFW
 */

use mvodanovic\WebFW\Bootstrap;
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
Bootstrap::init();

try {
    Framework::start();
} catch (WebFWException $e) {
    $e->ErrorMessage();
} catch (Exception $e) {
    $e = new WebFWException($e->getMessage(), $e->getCode(), $e);
    $e->ErrorMessage();
}
