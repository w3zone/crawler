<?php
namespace w3zone\Crawler\Services;

class nodejsRequest implements ServicesInterface
{

    /**
    * array of request options
    *
    * @var array
    */
    private $options = [];

    /**
    * default settings from the Crawler
    *
    * @var array
    */
    private $settings = [];

    /*
    * {@inheritDoc}
    */
    public function initialize($options, $settings)
    {
        if (isset($options['initialize'])) {
            foreach ($options['initialize'] as $key => $option) {
                $options[$key] = $option;
            }
        }
        $this->options = json_encode($options);
        $this->settings = $settings;
        return $this;
    }

    /**
    * get the node path , by default the node path is nodejs
    *
    * @param string $nodePath
    * @return string
    **/
    private function getNodePath($nodePath)
    {
        return ($nodePath == 'default' ? 'nodejs' : $nodePath);
    }

    /*
    * {@inheritDoc}
    */
    public function run()
    {
        $response = shell_exec($this->getNodePath($this->settings['NODE_PATH']) . ' vendor/w3zone/crawler/bin/nodejs/App.js \'' . ($this->options) . '\'');
        $response = json_decode($response, true);
        $response['cookies'] = (isset($response['cookies']) ? $this->getCookiesFromHeaders($response['cookies']) : '');
        return $response;
    }

    /**
    * Extracting cookies from header string
    *
    * @param string $headers
    * @return string
    */
    private function getCookiesFromHeaders($headers)
    {
        $cookies = [];
        foreach ($headers as $key => $header) {
            preg_match('#([^;]+)\;.*?#', $header, $match);
            $cookies[] = $match[1];
        }
        return implode(';', $cookies);
    }
}
