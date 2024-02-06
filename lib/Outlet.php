<?php

namespace Verkkokauppa;

use Exception;

require_once('Color.php');

class Outlet
{
    private Storage $storage;

    public function __construct(string $dataPath)
    {
        $this->storage = new Storage($dataPath);
    }

    /**
     * Run command
     */
    public function runCommand(array $args): void
    {
        $this->checkInit();

        $command = $args[1];
        $params = array_slice($args, 1);

        switch ($command) {
            case 'update':
            case 'u':
                $data = new Data($this->storage);
                $data->updateData();
                break;

            case 'save':
            case 's':
            case 'add':
            case 'a':
                $save = new SavedSearches($this->storage);
                $save->saveSearch($params);
                break;

            case 'remove':
            case 'r':
            case 'delete':
            case 'd':
                $save = new SavedSearches($this->storage);
                $save->removeSearch($params[1]);
                break;

            case 'list':
            case 'l':
            case 'ls':
                $save = new SavedSearches($this->storage);
                $save->listSavedSearches();
                break;

            case 'help':
            case '--help':
            case '-h':
                $this->help();
                break;

            case 'new':
            case '-new':
            case '-n':
                $newProducts = new NewProducts($this->storage);
                $params = ['limit' => isset($params[1]) ? $params[1] : null];
                $newProducts->listProducts($params);
                break;

            default:
                $search = new Search($this->storage);
                $search->search($params);
        }
    }

    /**
     * Run default command
     */
    public function runDefaultCommand(): void
    {
        $this->checkInit();

        try {
            $saved = $this->storage->getSavedSearches();
        } catch (Exception $e) {
            printf('Error while getting saved searches: %s', $e->getMessage());
            exit;
        }

        if (count($saved) === 0) {
            echo Color::YELLOW;
            echo "┌─────────────────────────────────────────────────────────────────────────────┐\n";
            echo "│ No saved searches. You can save searches with: verkkis save [search string] │\n";
            echo "└─────────────────────────────────────────────────────────────────────────────┘\n";
            echo Color::RESET;

            return;
        }

        foreach ($saved as $save) {
            printf("%sSearching for %s...%s%s", Color::GREEN_BOLD, $save, Color::RESET, PHP_EOL);

            $search = new Search($this->storage);
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
        echo '  new            Show recently added products' . PHP_EOL;
        echo '  help           Show help' . PHP_EOL . PHP_EOL;
        echo 'Running without any commands will run saved searches.' . PHP_EOL . PHP_EOL;
    }

    private function checkInit(): void
    {
        if (!$this->storage->isInitialized()) {
            printf("Data has not been initialized, running initial update...%s", PHP_EOL);
            $data = new Data($this->storage);
            $data->updateData();
        }
    }
}
