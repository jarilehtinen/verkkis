<?php

namespace Verkkokauppa;

class Search
{
    /** @var string URL prefix to use when generating view links */
    private const VIEW_URL = 'https://www.verkkokauppa.com/fi/outlet/yksittaiskappaleet/';
    private Storage $storage;
    private array $previousProducts = [];

    /**
     * Initialized the storage
     *
     * This class doesn't directly manipulate any data so we don't need to check for existence.
     *
     * @param Storage $storage
     */
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Search
     *
     * @param array $params Search parameters
     * @param int   $indent Indent count on output
     */
    public function search(array $params, int $indent = 0): void
    {
        $dataClass = new Data($this->storage);

        // Check when data was last updated
        $this->lastUpdatedWarning();

        // Get products
        $products = $dataClass->getProducts();

        if (!$products) {
            return;
        }

        // Get previous products and store them in a class variable
        $this->previousProducts = $dataClass->getPreviousProducts();

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
            $previousProduct = $this->getPreviousProduct($product['id']);
            $this->printProductInfo($product, $previousProduct, $indent);
        }
    }

    /**
     * Get previous product
     */
    public function getPreviousProduct(string $id): array|bool
    {
        if (count($this->previousProducts) < 1) {
            return false;
        }

        foreach ($this->previousProducts as $product) {
            if ($product['id'] == $id) {
                return $product;
            }
        }

        return false;
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
    private function printProductInfo(array $product, array|bool $previousProduct, int $indent = 0): void
    {
        $indentString = str_pad(' ', $indent);

        // Name
        printf('%s%s%s%s ', Color::YELLOW, $indentString, $product['name'], Color::RESET);

        // Price
        $originalPrice = '';

        if ($product['originalPrice'] > 0) {
            $originalPrice = sprintf(' (%d €)', round($product['originalPrice']));
        }

        // Previous price
        $previousPrice = 0;

        if (isset($previousProduct['price'])) {
            $previousPrice = round($previousProduct['price']);
        }

        // Price
        $price = round($product['price']);

        if ($previousPrice > 0  && $price > $previousPrice) {
            $price = sprintf('%s▲ %d → %d €%s', Color::RED_BOLD, $price, $previousPrice, COLOR::RESET);
        } elseif ($previousPrice > 0  && $price < $previousPrice) {
            $price = sprintf('%s▼ %d → %d €%s', Color::GREEN_BOLD, $previousPrice, $price, COLOR::RESET);
        } else {
            $price = sprintf('%s%d €%s', Color::WHITE_BOLD, $price, Color::RESET);
        }

        // Print price
        printf("%s%s%s%s", $price, $originalPrice, COLOR::RESET, PHP_EOL);

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
        $date      = $this->storage->getLastUpdated();

        if (!$date) {
            echo Color::RED;
            echo "┌────────────────────────────────────────────────────────────────────┐\n";
            echo "│ Production information has never been updated! Run: verkkis update │\n";
            echo "└────────────────────────────────────────────────────────────────────┘\n";
            echo Color::RESET;
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
