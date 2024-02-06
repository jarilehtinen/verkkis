<?php

namespace Verkkokauppa;

class NewProducts extends Search
{
    private int $defaultLimit = 10;

    /**
     * List products
     *
     * @param int   $indent Indent count on output
     */
    public function listProducts(array $params, int $indent = 0): void
    {
        $dataClass = new Data($this->storage);

        // Check when data was last updated
        $this->lastUpdatedWarning();

        // Get products
        $products = $dataClass->getProducts();

        if (!$products) {
            $this->noProductsFound($indent);
            return;
        }

        // Sort products in reverse order
        arsort($products);

        // Get previous products and store them in a class variable
        $this->previousProducts = $dataClass->getPreviousProducts();

        echo PHP_EOL;

        $i = 0;

        $limit = isset($params['limit']) && is_numeric($params['limit'])
            ? $params['limit']
            : $this->defaultLimit;

        foreach ($products as $product) {
            $previousProduct = $this->getPreviousProduct($product['id']);
            $this->printProductInfo($product, $previousProduct, $indent);
            $i++;

            if ($i >= $limit) {
                break;
            }
        }
    }
}
