<?php

namespace Verkkokauppa;

use Exception;

class Storage
{
    private const DATA_FILENAME         = 'data.json';
    private const PREVIOUS_DATA_FILENAME         = 'data-previous.json';
    private const SEARCHES_FILENAME     = 'saved-searches.json';
    private const LAST_UPDATED_FILENAME = 'last-updated.json';

    private string $path;

    public function __construct($path)
    {
        $this->path = $path;

        if (!is_dir($this->path)) {
            mkdir($this->path);
        }
    }

    /**
     * Get data
     *
     * @throws Exception
     */
    public function getData(): array
    {
        $fullPath = sprintf('%s%s', $this->path, self::DATA_FILENAME);

        if (!file_exists($fullPath)) {
            file_put_contents($fullPath, '[]');
        }

        $data = file_get_contents($fullPath);

        if (false === $data) {
            throw new Exception('Error reading data from disk!');
        }

        $json = json_decode($data, true);

        if (false === $json) {
            throw new Exception('Invalid JSON');
        }

        return $json;
    }

    /**
     * Get previous data
     *
     * @throws Exception
     */
    public function getPreviousData(): array
    {
        $fullPath = sprintf('%s%s', $this->path, self::PREVIOUS_DATA_FILENAME);

        if (!file_exists($fullPath)) {
            return false;
        }

        $data = file_get_contents($fullPath);

        if (false === $data) {
            throw new Exception('Error reading data from disk!');
        }

        $json = json_decode($data, true);

        if (false === $json) {
            throw new Exception('Invalid JSON');
        }

        return $json;
    }

    /**
     * @throws Exception
     */
    public function resetData(): void
    {
        // Store previous data before saving new data
        $this->storePreviousData();

        $this->saveData([]);
        $this->resetLastUpdated();
    }

    /**
     * @throws Exception
     */
    public function saveData(array $data): void
    {
        // Data path
        $fullPath = sprintf('%s%s', $this->path, self::DATA_FILENAME);

        $json = json_encode($data);

        if (false === $json) {
            throw new Exception('Error while converting to JSON');
        }

        $result   = file_put_contents($fullPath, $json);

        if (false === $result) {
            throw new Exception('Could not save data on disk!');
        }
    }

    /**
     * Store previous data
     */
    private function storePreviousData(): void
    {
        // Check if data file exists
        $dataPath = sprintf('%s%s', $this->path, self::DATA_FILENAME);

        if (!file_exists($dataPath)) {
            return;
        }

        // Copy data file to previous data file
        $previousDataPath = sprintf('%s%s', $this->path, self::PREVIOUS_DATA_FILENAME);

        copy($dataPath, $previousDataPath);
    }

    /**
     * Get saved searches
     *
     * @throws Exception
     */
    public function getSavedSearches(): array
    {
        $savedData = [];
        $fullPath  = sprintf('%s%s', $this->path, self::SEARCHES_FILENAME);

        if (file_exists($fullPath)) {
            $savedSearches = file_get_contents($fullPath);

            if (false === $savedSearches) {
                throw new Exception('Error reading data from disk!');
            }

            $savedData = json_decode($savedSearches, true);

            if (null === $savedData || is_bool($savedData)) {
                throw new Exception('Data file is not valid JSON!');
            }
        }

        return $savedData;
    }

    /**
     * @throws Exception
     */
    public function addSavedSearch(array $add): void
    {
        // Get saved searches
        $savedData = $this->getSavedSearches();

        // Append to saved searches
        $savedData = array_merge($savedData, $add);

        // Save to JSON
        $this->saveSavedSearches($savedData);
    }

    /**
     * @throws Exception
     */
    public function removeSavedSearch(int $index): void
    {
        $searches = $this->getSavedSearches();

        if ([] === $searches) {
            throw new Exception('No saved searches');
        }

        if (!isset($searches[$index - 1])) {
            throw new Exception('No such search ID');
        }

        unset($searches[$index - 1]);
        $this->saveSavedSearches($searches);
    }

    /**
     * @throws Exception
     */
    private function saveSavedSearches(array $data): void
    {
        $fullPath = sprintf('%s%s', $this->path, self::SEARCHES_FILENAME);

        // Make sure array keys don't get stored
        $data = array_values($data);

        // Encode data
        $json = json_encode($data);

        if (false === $json) {
            throw new Exception('Data cannot be encoded as JSON');
        }

        $result = file_put_contents($fullPath, json_encode($data));

        if (false === $result) {
            throw new Exception('Unable to write to disk!');
        }
    }

    /**
     * @throws Exception
     */
    public function getLastUpdated(): string|false
    {
        $fullPath = sprintf('%s%s', $this->path, self::LAST_UPDATED_FILENAME);

        if (!file_exists($fullPath)) {
            return false;
        }

        $data = file_get_contents($fullPath);

        if (false === $data) {
            throw new Exception('Error while reading last update date from disk!');
        }

        $json = json_decode($data, true);

        if (!isset($json['date'])) {
            throw new Exception('Last update date is corrupted!');
        }

        return $json['date'];
    }

    /**
     * @throws Exception
     */
    public function updateLastUpdated(): void
    {
        $fullPath = sprintf('%s%s', $this->path, self::LAST_UPDATED_FILENAME);

        $result = file_put_contents($fullPath, json_encode(['date' => date('Y-m-d H:i:s')]));

        if (!$result) {
            throw new Exception('Could not write last update timestamp to disk!');
        }
    }

    /**
     * @throws Exception
     */
    private function resetLastUpdated(): void
    {
        $fullPath = sprintf('%s%s', $this->path, self::LAST_UPDATED_FILENAME);

        if (!file_exists($fullPath)) {
            return;
        }

        $result = unlink($fullPath);

        if (!$result) {
            throw new Exception('Error while deleting last updated timestamp file!');
        }
    }

    public function isInitialized(): bool
    {
        return file_exists(sprintf('%s%s', $this->path, self::DATA_FILENAME));
    }
}
