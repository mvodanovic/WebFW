#!/usr/bin/env php
<?php

use mvodanovic\WebFW\Bootstrap;
use mvodanovic\WebFW\CLI\Framework;
use mvodanovic\WebFW\CLI\Writer\String;
use mvodanovic\WebFW\CLI\Writer\Style;
use mvodanovic\WebFW\CLI\Writer\Writer;

chdir(realpath(dirname(__FILE__)));

include '../../../../.Bootstrap.php';

try {
    Bootstrap::init();
    Framework::start();
} catch (Exception $e) {
    $styleDefault = (new Style())->setReset();
    $styleEmphasis = (new Style())->setColor(Style::VT_RED)->setBold();
    $writer = Writer::getInstance();

    $writer->clear()->add($styleDefault);
    $writer->add((new String('FATAL(' . $e->getCode() . '): '))->setStyle($styleEmphasis));
    $writer->addLine($e->getMessage());
    $writer->write(STDERR);

    $exitCode = $e->getCode();
    if (!is_int($exitCode)) {
        $exitCode = 1;
    }
    exit($exitCode);
}

exit(0);
