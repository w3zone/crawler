<?php
namespace w3zone\Crawler;

use w3zone\Crawler\Services\ServicesInterface;

class Crawler
{

    /**
    * @var $settings default settings
    */
    private $settings = [
        'SAFEMOOD' => true,
        'NODE_PATH' => 'default',
    ];

    /**
    * @var $aguments default arguments goes here
    */
    private $arguments = [
        'dumpHeaders' => false,
        'userAgent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0',
    ];

    public function __construct(ServicesInterface $service, $options = [])
    {
        $this->service = $service;
        $this->settings = array_merge($this->settings, $options);
    }

    /**
    * Setting the request to type GET
    *
    * @param mixed $arguments
    * @return $this
    */
    public function get($arguments)
    {
        $this->arguments['method'] = 'get';
        $this->arguments['url'] = (is_array($arguments) ? $arguments['url'] : $arguments);
        $this->arguments['data'] = [];

        return $this;
    }

    /**
    * Setting the request to type POST
    *
    * @param array $arguments
    * @return $this
    */
    public function post($arguments)
    {
        $this->arguments['method'] = 'post';
        $this->arguments['url'] = $arguments['url'];
        $this->arguments['data'] = $arguments['data'];

        return $this;
    }

    /**
    * an easy way to implement JSON request
    *
    * @return $this
    */
    public function json()
    {
        $this->arguments['json'] = true;

        return $this;
    }

    /**
    * an easy way to implement XML request
    *
    * @return $this
    */
    public function xml()
    {
        $this->arguments['xml'] = true;

        return $this;
    }

    /**
    * Adding a referer to the request body
    *
    * @param string the referer url
    * @return $this
    */
    public function referer($referer)
    {
        $this->arguments['referer'] = (is_array($referer) ? $referer['referer'] : $referer);

        return $this;
    }

    /**
    * Attaching array of headers to the request
    *
    * @param array $headers
    * @return $this
    */
    public function headers(array $headers)
    {
        $this->arguments['headers'] = $headers;

        return $this;
    }

    /**
    * Dumps the response header to your outbut
    *
    * @return $this
    */
    public function dumpHeaders()
    {
        $this->arguments['dumpHeaders'] = true;

        return $this;
    }

    /**
    * Setting up request proxy
    *
    * @param array $proxy ip and type
    * @return $this
    */
    public function proxy($proxy)
    {
        $this->arguments['proxy'] = (is_array($proxy) ? $proxy : ['ip' => $proxy, 'type' => 'HTTP']);

        return $this;
    }

    /**
    * Manage the request cookies
    *
    * @param string $file the cookie source you will read from and write to
    * @param string $mode the cookie mode .
    */
    public function cookies($file, $mode = 'r+w')
    {
        $this->arguments['cookies']['mode'] = $mode;
        $this->arguments['cookies']['file'] = $file;

        return $this;
    }

    /**
    * Initialize or Re-Initialize your request
    * using this function requires enabling safemood which will allow you
    * to overwrite the request arguments
    *
    * @param array $aguments
    * @return $this
    */
    public function initialize(array $arguments)
    {
        if ($this->settings['SAFEMOOD']) {
            foreach ($arguments as $key => $argument) {
                $this->arguments['initialize'][$key] = $argument;
            }
        }
        return $this;
    }

    /**
    * Fire the request
    *
    * @throws Exception
    * @return \w3zone\Crawler\Services\ServicesInterface
    */
    public function run()
    {
        try {
            return $this->service->initialize($this->arguments, $this->settings)->run();
        } catch (\Exception $exception) {
            die($exception->getMessage());
        }
    }
}
