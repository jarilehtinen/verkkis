<?php

namespace Verkkokauppa;

class SavedSearches
{
    /** @var string Data path */
    private string $dataPath;

    /**
     * Validates the existence of the data path
     *
     * @param string $dataPath
     */
    public function __construct(string $dataPath)
    {
        $this->dataPath = $dataPath;

        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath);
        }
    }

    /**
     * Get saved searches
     *
     * @return array
     */
    public function getSavedSearches(): array
    {
        $saved_data = [];
        $path       = sprintf('%ssaved-searches.json', $this->dataPath);

        if (file_exists($path)) {
            $saved_searches = file_get_contents($path);
            $saved_data     = json_decode($saved_searches, true);
        }

        return $saved_data;
    }

    /**
     * List saved searches
     */
    public function listSavedSearches(): void
    {
        $savedData = $this->getSavedSearches();

        $i = 1;
        foreach ($savedData as $savedSearch) {
            printf("%s[%d]%s %s%s", Color::YELLOW, $i++, Color::RESET, $savedSearch, PHP_EOL);
        }
    }

    /**
     * Save search
     */
    public function saveSearch(array $searchStrings = []): void
    {
        if ([] === $searchStrings) {
            echo 'No search strings given.' . PHP_EOL;
            echo 'Usage: verkkis save <search string> [additional search strings...]' . PHP_EOL;

            return;
        }

        // Get saved searches
        $savedData = $this->getSavedSearches();

        // Append to saved searches
        $savedData = array_merge($savedData, $searchStrings);

        // Save to JSON
        $this->saveToJSON($savedData);
    }

    /**
     * Save to JSON
     */
    private function saveToJSON(array $data): void
    {
        $path = sprintf('%ssaved-searches.json', $this->dataPath);
        file_put_contents($path, json_encode($data));
    }

    /**
     * Remove search
     */
    public function removeSearch(string $searchId = null): void
    {
        if (!is_numeric($searchId)) {
            echo 'No ID given.' . PHP_EOL;
            echo 'Get search ID by running: verkkis list' . PHP_EOL;
            echo 'Then run: verkkis remove <id>' . PHP_EOL;

            return;
        }

        // Get saved searches
        $saved_data = $this->getSavedSearches();

        if (!$saved_data) {
            echo 'No saved search data found. Nothing to remove.' . PHP_EOL;

            return;
        }

        // Remove search string from array
        unset($saved_data[$searchId - 1]);

        // Save to JSON
        $this->saveToJSON($saved_data);
    }
}
