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

    private array   $products   = [];
    private int     $totalPages = 1;
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

        // Get all data
        $page = 0;
        do {
            $url  = sprintf('%s%s', self::URL, $page);
            $data = $this->getDataFromUrl($url);
            if (0 === $page) {
                printf("Found %d pages of products%s", $this->totalPages, PHP_EOL);
            } else {
                echo '.';
            }
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
        $header_arr = [
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0',
            'Accept-Language: en-US,en;q=0.5',
            'Connection: keep-alive',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_arr);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);

        $json = json_decode($data, true);

        if (isset($json['message']) && $json['message'] != "Error performing search") {
            return false;
        }

        // Update total pages
        if (isset($json['numPages'])) {
            $this->totalPages = $json['numPages'];
        } else {
            printf("Missing total page count from response!%s", PHP_EOL);

            return false;
        }

        return $data;
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
            ];
        }

        $this->products = array_merge($products, $this->products);
    }
}
