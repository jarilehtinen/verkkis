<?php

namespace Verkkokauppa;

/**
 * Manages the data retrieval from Verkkokauppa
 */
class Data
{
    /** @var string API URL for fetching the outlet pages */
    private const URL = "https://web-api.service.verkkokauppa.com/search?private=true&sort=releaseDate%3Adesc&lang=fi&context=customer_returns_page&pageNo=";

    /** @var array Product array */
    private array $products   = [];
    private int   $totalPages = 1;
    private string $dataPath;

    public function __construct(string $dataPath)
    {
        $this->dataPath = $dataPath;

        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath);
        }
    }
    /**
     * Get data
     */
    public function getData(): false|string
    {
        $path = sprintf('%sdata.json', $this->dataPath);

        if (!file_exists($path)) {
            return false;
        }

        return file_get_contents($path);
    }

    /**
     * Get products
     */
    public function getProducts(): array
    {
        $data = $this->getData();

        return json_decode($data, true);
    }

    /**
     * Update data
     */
    public function updateData(): void
    {
        $this->resetData();

        printf("Updating data...%s", PHP_EOL);

        // Get all data
        $page = 1;
        do {
            $url  = sprintf('%s%s', self::URL, $page);
            $data = $this->getDataFromUrl($url);
            if (1 === $page) {
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

        $this->storeData();
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
    private function getProductsFromData($data): void
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

    /**
     * Reset data
     */
    private function resetData(): void
    {
        $path = sprintf('%sdata.json', $this->dataPath);

        if (!file_exists($path)) {
            touch($path);
        }

        file_put_contents($path, "");
    }

    /**
     * Store data
     */
    private function storeData(): void
    {
        // Store data
        $path = sprintf('%sdata.json', $this->dataPath);
        file_put_contents($path, json_encode($this->products));

        // Store last updated date
        $path = sprintf('%slast-updated.json', $this->dataPath);
        file_put_contents($path, json_encode(['date' => date('Y-m-d H:i:s')]));
    }

    /**
     * Last updated
     */
    public function lastUpdated(): false|string
    {
        $path = sprintf('%slast-updated.json', $this->dataPath);

        if (!file_exists($path)) {
            return false;
        }

        $date = json_decode(file_get_contents($path), true);

        return $date['date'];
    }
}
