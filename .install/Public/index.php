<?php
/**
 * Entry point for the framework.
 *
 * @package WebFW\Core
 */

session_start();
ob_start();

if (!file_exists('../webfw/core/bootstrap.inc.php')) {
    header('Content-type: text/plain; charset=UTF-8');
    echo 'WebFW Fatal Error: Bootstrap unreachable: ' . realpath('..') . '/webfw/core/bootstrap.inc.php';
    die;
}

require_once('../webfw/core/bootstrap.inc.php');

try {
    \WebFW\Core\Framework::start();
} catch (\WebFW\Core\Exception $e) {
    $e->ErrorMessage();
} catch (Exception $e) {
    $e = new \WebFW\Core\Exception($e->getMessage(), $e->getCode(), $e);
    $e->ErrorMessage();
}
