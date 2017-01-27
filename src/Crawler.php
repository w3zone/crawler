<?php
namespace w3zone\Crawler;

class Crawler
{

    private $settings = [
        'SAFEMOOD' => true,
        'NODE_PATH' => 'default',
    ];

    private static $instance;

    private $flag;

    public function __construct($service, $options = [])
    {
        $this->service = $service;
        $this->settings = array_merge($this->settings, $options);
    }

    private $arguments = [
        'dumpHeaders' => false,
        'userAgent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0',
    ];

    public function get($arguments)
    {
        $this->arguments['method'] = 'get';
        $this->arguments['url'] = (is_array($arguments) ? $arguments['url'] : $arguments);
        $this->arguments['data'] = [];

        return $this;
    }

    public function post($arguments)
    {
        $this->arguments['method'] = 'post';
        $this->arguments['url'] = $arguments['url'];
        $this->arguments['data'] = $arguments['data'];

        return $this;
    }

    public function json()
    {
        $this->arguments['json'] = true;

        return $this;
    }

    public function xml()
    {
        $this->arguments['xml'] = true;

        return $this;
    }

    public function referer($referer)
    {
        $this->arguments['referer'] = (is_array($referer) ? $referer['referer'] : $referer);

        return $this;
    }

    public function headers(array $headers)
    {
        $this->arguments['headers'] = $headers;

        return $this;
    }

    public function dumpHeaders()
    {
        $this->arguments['dumpHeaders'] = true;

        return $this;
    }

    /*
    * TODO
    * add options : proxyheaders - proxyauth - proxyport
    */
    public function proxy($proxy)
    {
        $this->arguments['proxy'] = (is_array($proxy) ? $proxy : ['ip' => $proxy, 'type' => 'HTTP']);

        return $this;
    }

    public function cookies($file, $mode = 'r+w')
    {
        $this->arguments['cookies']['mode'] = $mode;
        $this->arguments['cookies']['file'] = $file;

        return $this;
    }

    public function initialize(array $arguments)
    {
        if ($this->settings['SAFEMOOD']) {
            foreach ($arguments as $key => $argument) {
                $this->arguments['initialize'][$key] = $argument;
            }
        }
        return $this;
    }

    public function run()
    {
        try {
            return $this->service->initialize($this->arguments, $this->settings)->run();
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }
    }
}
