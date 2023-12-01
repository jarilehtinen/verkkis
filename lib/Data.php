<?php

namespace Verkkokauppa;

class Data
{
    private $url = 'https://web-api.service.verkkokauppa.com/search?private=true&pageNo=%page%&sort=releaseDate%3Adesc&lang=fi&context=customer_returns_page';
    private $products = [];
    private $totalPages = 10;

    /**
     * Get data
     */
    public function getData()
    {
        $path = PATH . '/data/data.json';

        if (!file_exists($path)) {
            return false;
        }

        return file_get_contents($path);
    }

    /**
     * Get products
     */
    public function getProducts()
    {
        $data = $this->getData();
        return json_decode($data, true);
    }

    /**
     * Update data
     */
    public function updateData()
    {
        $page = 0;

        $this->resetData();

        // Get all data
        for ($page = 0; $page < 200; $page++) {
            if ($page > $this->totalPages) {
                break;
            }

            $url = str_replace('%page%', $page, $this->url);

            if ($data = $this->getDataFromUrl($url)) {
                echo ".";
                $this->getProductsFromData($data);
                usleep(200000);
            } else {
                break;
            }
        }

        $this->storeData();
    }

    /**
     * Get data from URL
     */
    private function getDataFromUrl($url)
    {
        $header_arr = array(
            '0' => 'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0',
            '1' => 'Accept-Language: en-US,en;q=0.5',
            '2' => 'Connection: keep-alive',
        );

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
        }

        return $data;
    }

    /**
     * Get products from data
     */
    private function getProductsFromData($data)
    {
        $data = json_decode($data, true);
        $data = $data['products'];

        $products = [];

        foreach ($data as $product) {
            $products[] = array(
                'id' => $product['customerReturnsInfo']['id'],
                'name' => $product['name'],
                'price' =>  $product['customerReturnsInfo']['price_with_tax']
            );
        }

        $this->products = array_merge($products, $this->products);
    }

    /**
     * Reset data
     */
    private function resetData()
    {
        $path = PATH . '/data/data.json';

        if (!file_exists($path)) {
            return false;
        }

        file_put_contents($path, "");
    }

    /**
     * Store data
     */
    private function storeData()
    {
        // Attempt to create data directory
        if (!file_exists(PATH . '/data')) {
            mkdir(PATH . '/data');
        }

        // Store data
        $path = PATH . '/data/data.json';
        file_put_contents($path, json_encode($this->products));

        // Store last updated date
        $path = PATH . '/data/last-updated.json';
        file_put_contents($path, json_encode(array('date' => date('Y-m-d H:i:s'))));

        return true;
    }

    /**
     * Last updated
     */
    public function lastUpdated()
    {
        $path = PATH . '/data/last-updated.json';

        if (!file_exists($path)) {
            return false;
        }

        $date = json_decode(file_get_contents($path), true);

        return $date['date'];
    }
}
