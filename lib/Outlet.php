<?php

namespace Verkkokauppa;

require_once('Color.php');

class Outlet
{
    private string $dataPath;

    public function __construct(string $dataPath)
    {
        $this->dataPath = $dataPath;
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath);
        }
    }

    /**
     * Run command
     */
    public function runCommand(array $args): void
    {
        $command = $args[1];
        $params = array_slice($args, 2);

        switch ($command) {
            case 'update':
            case 'u':
                $data = new Data($this->dataPath);
                $data->updateData();
                break;

            case 'save':
            case 's':
            case 'add':
            case 'a':
                $save = new SavedSearches($this->dataPath);
                $save->saveSearch($params);
                break;

            case 'remove':
            case 'r':
            case 'delete':
            case 'd':
                $save = new SavedSearches($this->dataPath);
                $save->removeSearch($params[0]);
                break;

            case 'list':
            case 'l':
            case 'ls':
                $save = new SavedSearches($this->dataPath);
                $save->listSavedSearches();
                break;

            case 'help':
            case '--help':
            case '-h':
                $this->help();
                break;

            default:
                $search = new Search($this->dataPath);
                $search->search($params);
        }
    }

    /**
     * Run default command
     */
    public function runDefaultCommand(): void
    {
        $save  = new SavedSearches($this->dataPath);
        $saved = $save->getSavedSearches();

        if (!$saved) {
            echo Color::YELLOW;
            echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
            echo "│ No saved searches. You can save searches with: verkkis save [search string] │\n";
            echo "└─────────────────────────────────────────────────────────────────────────────┘\n";
            echo Color::RESET;

            return;
        }

        foreach ($saved as $save) {
            sprintf("%sSearching for %s...%s%s", Color::GREEN_BOLD, $save, Color::RESET, PHP_EOL);

            $search = new Search($this->dataPath);
            $search->search([$save], 3);
        }
    }

    /**
     * Help
     */
    private function help(): void
    {
        echo 'Usage: verkkis [-h | --help] <command> [<args>]' . PHP_EOL . PHP_EOL;
        echo 'Available commands:' . PHP_EOL;
        echo '  update         Update data from Verkkokauppa.com Outlet' . PHP_EOL;
        echo '  save [args]    Save search' . PHP_EOL;
        echo '  list           List saved searches' . PHP_EOL;
        echo '  remove [<id>]  Remove saved search' . PHP_EOL;
        echo '  help           Show help' . PHP_EOL . PHP_EOL;
        echo 'Running without any commands will run saved searches.' . PHP_EOL . PHP_EOL;
    }
}
