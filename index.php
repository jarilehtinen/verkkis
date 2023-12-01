<?php

define('PATH', __DIR__);

require_once('lib/Outlet.php');
require_once('lib/Data.php');
require_once('lib/SavedSearches.php');
require_once('lib/Search.php');

$outlet = new Verkkokauppa\Outlet();

// Run command
if (isset($argv[1])) {
    $outlet->runCommand($argv);
    exit;
}

$outlet->runDefaultCommand();
