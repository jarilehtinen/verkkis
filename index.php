<?php

const PATH    = __DIR__;
const DATADIR = '.data';

require_once('lib/Outlet.php');
require_once('lib/Data.php');
require_once('lib/SavedSearches.php');
require_once('lib/Search.php');
require_once('lib/Storage.php');

use Verkkokauppa\Outlet;

$dataPath = sprintf('%s%s%s%s', PATH, DIRECTORY_SEPARATOR, DATADIR, DIRECTORY_SEPARATOR);

$outlet = new Outlet($dataPath);

// Run command
if (isset($argv[1])) {
    $outlet->runCommand($argv);
    exit;
}

$outlet->runDefaultCommand();
