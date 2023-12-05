<?php

namespace Verkkokauppa;

use Exception;

/**
 * Manages the data retrieval from Verkkokauppa
 */
class Data
{
    /** @var string API URL for fetching the outlet data */
    private const URL = "https://web-api.service.verkkokauppa.com/search?private=true&sort=releaseDate%3Adesc&lang=fi&context=customer_returns_page&pageNo=";
    private const LOADING_BAR_LENGTH = 30;
    private const HEADER = [
        'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0',
        'Accept-Language: en-US,en;q=0.5',
        'Connection: keep-alive',
    ];

    private array $products = [];
    private int $totalPages = 1;
    private Storage $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Get products
     */
    public function getProducts(): array
    {
        try {
            return $this->storage->getData();
        } catch (Exception $exception) {
            printf('Error while reading data from disk! %s', $exception->getMessage());
            exit;
        }
    }

    /**
     * Update data
     */
    public function updateData(): void
    {
        try {
            $this->storage->resetData();
        } catch (Exception $e) {
            printf('Error while resetting data: %s', $e->getMessage());
            exit();
        }

        printf("Updating data...%s", PHP_EOL);

        // Get total pages
        $url  = sprintf('%s%s', self::URL, 0);
        $this->totalPages = $this->getTotalPagesFromUrl($url);

        if (!$this->totalPages) {
            printf("Could not get total page count!%s", PHP_EOL);
            exit;
        }

        // Get all data
        $page = 0;

        do {
            $url  = sprintf('%s%s', self::URL, $page);
            $data = $this->getDataFromUrl($url);

            // Loading indicator: 419/459 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░]  91%
            $percent = round($page / $this->totalPages * 100);

            $loaded = round(($percent / 100) * self::LOADING_BAR_LENGTH);
            $loading_bar_loaded = str_repeat("▓", $loaded);

            $remaining = self::LOADING_BAR_LENGTH - mb_strlen($loading_bar_loaded);
            $loading_bar_remaining = $remaining > 0 ? str_repeat("░", $remaining) : '';

            $total_pages_str_length = strlen($this->totalPages);

            printf(
                "\r%{$total_pages_str_length}u/%u [%s%s] %u%%",
                $page,
                $this->totalPages,
                $loading_bar_loaded,
                $loading_bar_remaining,
                $percent
            );

            $page++;

            if (!$data) {
                break;
            }

            $this->getProductsFromData($data);
            usleep(200000);
        } while ($page <= $this->totalPages);

        try {
            $this->storage->saveData($this->products);
        } catch (Exception $e) {
            printf('Error while saving data: %s', $e->getMessage());
            exit;
        }

        try {
            $this->storage->updateLastUpdated();
        } catch (Exception $e) {
            printf('Error while updating timestamp: %s', $e->getMessage());
            exit;
        }

        printf("%sUpdate complete%s", PHP_EOL, PHP_EOL);
    }

    /**
     * Get data from URL
     */
    private function getDataFromUrl(string $url): bool|string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::HEADER);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);
        $json = json_decode($data, true);

        if (isset($json['message']) && $json['message'] != "Error performing search") {
            return false;
        }

        return $json;
    }

    /**
     * Get total pages from URL
     */
    private function getTotalPagesFromUrl(string $url): bool|string
    {
        $json = $this->getDataFromUrl($url);

        if (!$json) {
            return false;
        }

        // Return total number of pages
        if (isset($json['numPages'])) {
            return $json['numPages'];
        }

        printf("Missing total page count from response!%s", PHP_EOL);

        return false;
    }

    /**
     * Get products from data
     */
    private function getProductsFromData(string $data): void
    {
        $data = json_decode($data, true);
        $data = $data['products'];

        $products = [];

        foreach ($data as $product) {
            $products[] = [
                'id'    => $product['customerReturnsInfo']['id'],
                'name'  => $product['name'],
                'price' => $product['customerReturnsInfo']['price_with_tax'],
                'originalPrice' => isset($product['price']['current']) ? $product['price']['current'] : null,
                'condition' => $product['customerReturnsInfo']['condition']
            ];
        }

        $this->products = array_merge($products, $this->products);
    }
}
