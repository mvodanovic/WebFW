<?php
/**
 * Entry point for the framework.
 *
 * @package WebFW\Core
 */

session_start();
ob_start();

if (!file_exists('../webfw/config/bootstrap.inc.php')) {
   echo 'WebFW: FATAL ERROR: Bootstrap function unreachable: ' . realpath('../webfw/config/bootstrap.inc.php');
   die;
}

require_once('../webfw/config/bootstrap.inc.php');

\WebFW\Config\Bootstrap();

try {
    \WebFW\Core\Framework::Start();
} catch (\WebFW\Core\Exception $e) {
    $e->ErrorMessage();
} catch (Exception $e) {
    $e = new \WebFW\Core\Exception($e->getMessage(), $e->getCode(), $e);
    $e->ErrorMessage();
}

