<?php

namespace Verkkokauppa;

class SavedSearches
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
     * Get saved searches
     */
    public function getSavedSearches()
    {
        $saved_data = [];
        $path = PATH . '/data/saved-searches.json';

        if (file_exists($path)) {
            $saved_searches = file_get_contents($path);
            $saved_data = json_decode($saved_searches, true);
        }

        return $saved_data;
    }

    /**
     * List saved searches
     */
    public function listSavedSearches($args)
    {
        $saved_data = $this->getSavedSearches();

        $i = 1;

        foreach ($saved_data as $saved_search) {
            echo $this->yellow . '[' . $i . '] ' . $this->reset;
            echo implode(' ', $saved_search) . "\n";
            $i++;
        }
    }

    /**
     * Save search
     */
    public function saveSearch($args)
    {
        array_shift($args);
        array_shift($args);

        if (!$args) {
            echo 'No search string given.' . "\n";
            echo 'Usage: verkkis save <search string>' . "\n";
            return false;
        }

        // Get saved searches
        $saved_data = $this->getSavedSearches();

        // Append to saved searches
        $saved_data[] = $args;

        // Save to JSON
        $this->saveToJSON($saved_data);
    }

    /**
     * Save to JSON
     */
    private function saveToJSON($data)
    {
        $path = PATH . '/data/saved-searches.json';
        file_put_contents($path, json_encode($data));
    }

    /**
     * Remove search
     */
    public function removeSearch($args)
    {
        if (!isset($args[2]) || !is_numeric($args[2])) {
            echo 'No ID given.' . "\n";
            echo 'Get search ID by running: verkkis list' . "\n";
            echo 'Then run: verkkis remove <id>' . "\n";
            return false;
        }

        $id = $args[2];

        // Get saved searches
        $saved_data = $this->getSavedSearches();

        if (!$saved_data) {
            echo 'No saved search data found. Nothing to remove.' . "\n";
            return false;
        }

        // Remove search string from array
        unset($saved_data[$id - 1]);

        // Save to JSON
        $this->saveToJSON($saved_data);
    }
}
