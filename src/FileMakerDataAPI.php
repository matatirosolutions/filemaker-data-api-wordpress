<?php
/**
 * Created by PhpStorm.
 * User: stevewinter
 * Date: 31/05/2018
 * Time: 14:25
 */

namespace FMDataAPI;

use \WP_Http;
use \Exception;

class FileMakerDataAPI
{

    /** @var Settings */
    private $settings;

    /** @var string */
    private $baseURI;

    /** @var string */
    private $token;

    private $cache = [];

    private $retried = false;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
        $this->setBaseURL($settings->getServer(), $settings->getDatabase());
    }

    /**
     * @param $layout
     * @param bool|object $class
     * 
     * @return array
     * @throws Exception
     */
    public function findAll($layout)
    {
        $this->setOrFetchToken();

        // by default API only returns 100 records at a time, so we need to keep getting records till we run out
        $offset = 1;
        $retrieved = 100;
        $results = [];
        
        while($retrieved == 100) {
            $uri = $this->baseURI . sprintf('layouts/%s/records?_offset=%s', $layout, $offset);
            $records = $this->performFMRequest('GET', $uri, []);
            $retrieved = count($records);
            $offset += 100;

            $results = array_merge($results, $records);
        }

        return $results;
    }

    /**
     * @param $layout
     * @param $query
     *
     * @return array|mixed
     * @throws Exception
     */
    public function findOneBy($layout, $query)
    {
        $records = $this->find($layout, $query);

        if(empty($records)) {
            return [];
        }

        return $records[0];
    }

    /**
     * @param $layout
     * @param $query
     * @param bool $class
     *
     * @return array
     *
     * @throws Exception
     */
    public function find($layout, $query)
    {
        $queryHash = md5(
            serialize($query)
        );
        if(array_key_exists($queryHash, $this->cache)) {
            return $this->cache[$queryHash];
        }

        $this->setOrFetchToken();
        $body = json_encode([
            'query' => [$query]
        ]);

        $uri = $this->baseURI . sprintf('layouts/%s/_find', $layout);
        $records = $this->performFMRequest("POST", $uri, ['body' => $body]);

        $this->cache[$queryHash] = $records;

        return $records;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return array
     * @throws Exception
     */
    private function performFMRequest($method, $uri, $options)
    {
        $params = [
            'method' => $method,
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $this->token),
                'Content-Type' => 'application/json'
            ]
        ];

        $request = new WP_Http();
        $response = $request->request($uri, array_merge($params, $options));

        if($response) {
            $responseArray = json_decode($response['body'], true);
            $responseCode = $responseArray['messages'][0]['code'];

            switch($responseCode){
                case 0:
                    return $this->flattenRecords($responseArray['response']['data']);
                case 401:
                    return [];
                case 952:
                    if(!$this->retried) {
                        $this->retried = true;
                        $this->fetchToken();
                        $this->performFMRequest($method, $uri, $options);
                    }
                    break;
            }

            throw new Exception($responseArray['messages'][0]['message'], $responseArray['messages'][0]['code']);
        }

        throw new Exception('No response received from FileMaker are you sure the settings are correct?');
    }

    private function flattenRecords(array $records) {
        $resp = [];
        foreach($records as $record) {
            $resp[] = array_merge([
                'portalData' => $record['portalData'],
                'recordId' => $record['recordId'],
                'modId' => $record['modId'],
            ], $record['fieldData']);
        }

        return $resp;
    }

    private function setBaseURL($host, $database)
    {
        $this->baseURI =
            ('http' == substr($host, 4) ? $host : 'https://' . $host) .
            ('/' == substr($host, -1) ? '' : '/') .
            'fmi/data/v1/databases/' .
            $database . '/';
    }

    /**
     * @return string
     * @throws Exception
     */
    private function setOrFetchToken()
    {
        if(!empty($_SESSION['fm-data-api-token'])) {
            return $this->token = $_SESSION['fm-data-api-token'];
        }

        return $this->fetchToken();
    }

    /**
     * @return string
     * @throws Exception
     */
    public function fetchToken()
    {
        $headers = [
            'Authorization' => 'Basic '.base64_encode("{$this->settings->getUsername()}:{$this->settings->getPassword()}"),
            'Content-Type' => 'application/json'
        ];
        $request = new WP_Http();
        $response = $request->request($this->baseURI . 'sessions', [
            'method' => 'POST',
            'headers' => $headers
        ]);

        if(is_a($response, 'WP_Error')) {
            throw new Exception(sprintf(': %s',  $response->get_error_message()));
        }

        if($response) {
            $responseObj = json_decode($response['body'], false);
            $responseCode = $responseObj->messages[0]->code;

            if ($responseCode == '0') {
                $this->token = $responseObj->response->token;
                $_SESSION['fm-data-api-token'] = $this->token;

                return $this->token;
            }

            throw new Exception($responseObj->messages[0]->message, $responseObj->messages[0]->code);
        }

        throw new Exception('No response received from FileMaker are you sure the settings are correct?');
    }

}