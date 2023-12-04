<?php

namespace Verkkokauppa;

class SavedSearches
{
    private Storage $storage;

    /**
     * Validates the existence of the data path
     *
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * List saved searches
     */
    public function listSavedSearches(): void
    {
        try {
            $savedData = $this->storage->getSavedSearches();
        } catch (\Exception $e) {
            printf('Error when listing saved searches: %s', $e->getMessage());
            exit;
        }

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

        try {
            $this->storage->addSavedSearch($searchStrings);
        } catch (\Exception $e) {
            printf('Error while saving searches: %s', $e->getMessage());
            exit;
        }
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

        try {
            $this->storage->removeSavedSearch($searchId);
        } catch (\Exception $e) {
            printf('Error while removing saved search: %s', $e->getMessage());
            exit;
        }
    }
}
