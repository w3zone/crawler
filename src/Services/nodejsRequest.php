<?php
namespace w3zone\Crawler\Services;

class nodejsRequest implements ServicesInterface
{

    /*
    * {@inheritDoc}
    */
    public function initialize($arguments, $settings)
    {
        if (isset($arguments['initialize'])) {
            foreach ($arguments['initialize'] as $key => $argument) {
                $arguments[$key] = $argument;
            }
        }
        $this->arguments = json_encode($arguments);
        $this->settings = $settings;
        return $this;
    }

    private function getNodePath($nodePath)
    {
        return ($nodePath == 'default' ? 'nodejs' : $nodePath);
    }

    /*
    * {@inheritDoc}
    */
    public function run()
    {
        $response = shell_exec($this->getNodePath($this->settings['NODE_PATH']) . ' vendor/w3zone/crawler/bin/nodejs/App.js \'' . ($this->arguments) . '\'');
        $response = json_decode($response, true);
        $response['cookies'] = (isset($response['cookies']) ? $this->getCookiesFromHeaders($response['cookies']) : '');
        return $response;
    }

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
