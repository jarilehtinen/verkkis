<?php

namespace Verkkokauppa;

use Verkkokauppa\Data;

class Search
{
    private $view_url = 'https://www.verkkokauppa.com/fi/outlet/yksittaiskappaleet/';
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
     * Search
     */
    public function search($args, $indent = 0)
    {
        $dataClass = new Data();

        // Check when data was last updated
        $this->lastUpdatedWarning();

        // Get products
        $products = $dataClass->getProducts();

        if (!$products) {
            return false;
        }

        // Search string
        $search_string = $this->getSearchStringFromArgs($args);

        $top_results = [];
        $medium_results = [];
        $low_results = [];

        foreach ($products as $product) {
            $name = strtolower($product['name']);

            $string_found = 0;

            foreach ($search_string as $string) {
                if (strstr($name, $string)) {
                    $string_found++;
                }
            }

            if ($string_found > 2) {
                $top_results[] = $product;
            } elseif ($string_found > 1) {
                $medium_results[] = $product;
            } elseif ($string_found > 0) {
                $low_results[] = $product;
            }
        }

        if (count($top_results) > 0) {
            $results = $top_results;
        } else {
            $results = array_merge($low_results, $medium_results, $top_results);
        }

        if (!$results) {
            $this->noProductsFound($indent);
        }

        echo "\n";

        foreach ($results as $product) {
            $this->printProductInfo($product, $indent);
        }
    }

    /**
     * Get search string from args
     */
    private function getSearchStringFromArgs($args)
    {
        $search_string = [];

        foreach ($args as $arg) {
            $search_string[] = strtolower($arg);
        }

        return $search_string;
    }

    /**
     * Print product info
     */
    private function printProductInfo($product, $indent = 0)
    {
        // Name
        echo $this->yellow_bold;
        echo str_pad(' ', $indent) . $product['name'];
        echo $this->reset;
        echo " ";

        // Price
        echo $this->white_bold;
        echo $product['price'] . " €\n";
        echo $this->reset;

        // Link
        echo $this->cyan;
        echo str_pad(' ', $indent) . $this->view_url . $product['id'];
        echo $this->reset;

        echo "\n\n";
    }

    /**
     * No products found
     */
    private function noProductsFound($indent)
    {
        echo "\n" . str_pad(' ', $indent) . "No results.\n";
    }

    /**
     * Last updated warning
     */
    private function lastUpdatedWarning()
    {
        $dataClass = new Data();

        $date = $dataClass->lastUpdated();

        if (!$date) {
            return false;
        }

        if (strtotime($date) < strtotime('-1 day')) {
            echo $this->red;
            echo "┌───────────────────────────────────────────────────────────────────────┐\n";
            echo "│ Production information updated over 24 hours ago. Run: verkkis update │\n";
            echo "└───────────────────────────────────────────────────────────────────────┘\n";
            echo $this->reset;
        }
    }
}
