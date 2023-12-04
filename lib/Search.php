<?php

namespace Verkkokauppa;

class Search
{
    /** @var string URL prefix to use when generating view links */
    private const VIEW_URL = 'https://www.verkkokauppa.com/fi/outlet/yksittaiskappaleet/';
    private string $dataPath;

    /**
     * Sets the data path.
     *
     * This class doesn't directly manipulate any data so we don't need to check for existence.
     *
     * @param string $dataPath
     */
    public function __construct(string $dataPath)
    {
        $this->dataPath = $dataPath;
    }

    /**
     * Search
     *
     * @param array $params Search parameters
     * @param int   $indent Indent count on output
     */
    public function search(array $params, int $indent = 0): void
    {
        $dataClass = new Data($this->dataPath);

        // Check when data was last updated
        $this->lastUpdatedWarning();

        // Get products
        $products = $dataClass->getProducts();

        if (!$products) {
            return;
        }

        // Search string
        $searchString = $this->getSearchStringArrayFromParams($params);

        $topResults    = [];
        $mediumResults = [];
        $lowResults    = [];

        foreach ($products as $product) {
            $name = strtolower($product['name']);

            $stringFound = 0;

            foreach ($searchString as $string) {
                if (strstr($name, $string)) {
                    $stringFound++;
                }
            }

            if ($stringFound > 2) {
                $topResults[] = $product;
            } else {
                if ($stringFound > 1) {
                    $mediumResults[] = $product;
                } else {
                    if ($stringFound > 0) {
                        $lowResults[] = $product;
                    }
                }
            }
        }

        if (count($topResults) > 0) {
            $results = $topResults;
        } else {
            $results = array_merge($lowResults, $mediumResults, $topResults);
        }

        if (!$results) {
            $this->noProductsFound($indent);
        }

        echo PHP_EOL;

        foreach ($results as $product) {
            $this->printProductInfo($product, $indent);
        }
    }

    /**
     * Get search string array from args
     */
    private function getSearchStringArrayFromParams(array $params): array
    {
        $searchString = [];

        foreach ($params as $param) {
            $searchString[] = strtolower($param);
        }

        return $searchString;
    }

    /**
     * Print product info
     */
    private function printProductInfo(array $product, int $indent = 0): void
    {
        $indentString = str_pad(' ', $indent);
        // Name
        printf('%s%s%s%s ', Color::YELLOW, $indentString, $product['name'], Color::RESET);

        // Price
        printf("%s%.2f €%s%s", Color::WHITE_BOLD, $product['price'], Color::RESET, PHP_EOL);

        // Link
        printf('%s%s%s%s%s', Color::CYAN, $indentString, self::VIEW_URL, $product['id'], Color::RESET);

        echo PHP_EOL . PHP_EOL;
    }

    /**
     * No products found
     */
    private function noProductsFound(int $indent): void
    {
        printf("%s%sNo results.%s", PHP_EOL, str_pad(' ', $indent), PHP_EOL);
    }

    /**
     * Last updated warning
     */
    private function lastUpdatedWarning(): void
    {
        $dataClass = new Data($this->dataPath);
        $date      = $dataClass->lastUpdated();

        if (!$date) {
            return;
        }

        if (strtotime($date) < strtotime('-1 day')) {
            echo Color::RED;
            echo "┌───────────────────────────────────────────────────────────────────────┐\n";
            echo "│ Production information updated over 24 hours ago. Run: verkkis update │\n";
            echo "└───────────────────────────────────────────────────────────────────────┘\n";
            echo Color::RESET;
        }
    }
}
