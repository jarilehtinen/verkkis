<?php

namespace Verkkokauppa;

use Verkkokauppa\Data;
use Verkkokauppa\SavedSearches;
use Verkkokauppa\Search;

class Outlet
{
    private $red = "\e[0;31m";
    private $red_bold = "\e[1;31m";
    private $green = "\e[0;32m";
    private $green_bold = "\e[1;32m";
    private $yellow = "\e[0;33m";
    private $yellow_bold = "\e[1;33m";
    private $cyan = "\e[0;36m";
    private $cyan_bold = "\e[1;36m";
    private $white_bold = "\e[1;37m";
    private $reset = "\e[0m";

    /**
     * Run command
     *
     * @param   array    $args  Arguments
     * @return  boolean
     */
    public function runCommand($args)
    {
        $command = $args[1];

        // Update
        if ($command == 'update' || $command == 'u') {
            $data = new Data();
            $data->updateData();
            return;
        }

        // Save
        if ($command == 'save' || $command == 's' || $command == 'a' || $command == 'add') {
            $save = new SavedSearches();
            $save->saveSearch($args);
            return;
        }

        // Remove/delete
        if ($command == 'remove' || $command == 'delete' || $command == 'r' || $command == 'd') {
            $save = new SavedSearches();
            $save->removeSearch($args);
            return;
        }

        // List
        if ($command == 'list' || $command == 'l' || $command == 'ls') {
            $save = new SavedSearches();
            $save->listSavedSearches($args);
            return;
        }

        // Help
        if ($command == '--help' || $command == '--h' || $command == 'help') {
            $this->help();
        }

        // Search
        $search = new Search();
        array_shift($args);
        $search->search($args);
    }

    /**
     * Run default command
     */
    public function runDefaultCommand()
    {
        $save = new SavedSearches();
        $saved = $save->getSavedSearches();

        if (!$saved) {
            echo $this->yellow;
            echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
            echo "│ No saved searches. You can save searches with: verkkis save [search string] │\n";
            echo "└─────────────────────────────────────────────────────────────────────────────┘\n";
            echo $this->reset;
            return;
        }

        foreach ($saved as $args) {
            echo $this->green_bold;
            echo 'Searching for "' . implode(' ', $args) . '"...' . "\n";
            echo $this->reset;

            $search = new Search();
            $search->search($args, 3);
        }
    }

    /**
     * Help
     */
    private function help()
    {
        echo 'Usage: verkkis [-h | --help] <command> [<args>]' . "\n\n";
        echo 'Available commands:' . "\n";
        echo '  update         Update data from Verkkokauppa.com Outlet' . "\n";
        echo '  save [args]    Save search' . "\n";
        echo '  list           List saved searches' . "\n";
        echo '  remove [<id>]  Remove saved search' . "\n";
        echo '  help           Show help' . "\n\n";
        echo 'Running without any commands will run saved searches.';
    }
}
