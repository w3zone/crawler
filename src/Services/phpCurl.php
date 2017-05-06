<?php
namespace w3zone\Crawler\Services;

class phpCurl implements ServicesInterface
{
    private $curlHandler;

    /**
    * array of curl options
    *
    * @var array
    */
    private $options = [];

    /**
    * set curl cookies options
    *
    * @param array $cookie
    * @return void
    */
    public function cookieHandler($cookie)
    {
        $cookieFile = $cookie['file'];
        if ($cookie['mode'] == 'r') {
            $this->prepareOption(CURLOPT_COOKIE, $cookieFile);
        } else if ($cookie['mode'] == 'r+w' || $cookie['mode'] == 'w+r') {
            $this->prepareOption(CURLOPT_COOKIE, $cookieFile);
            $this->prepareOption(CURLOPT_COOKIEFILE, $cookieFile);
            $this->prepareOption(CURLOPT_COOKIEJAR, $cookieFile);
        } else if ($cookie['mode'] == 'w') {
            $this->prepareOption(CURLOPT_COOKIEFILE, $cookieFile);
            $this->prepareOption(CURLOPT_COOKIEJAR, $cookieFile);
        }
    }

    /**
    * set curl proxy options
    *
    * @param array $proxy
    * @return void
    */
    public function proxyHandler($proxy)
    {
        $this->prepreOption(CURLOPT_PROXY, $proxy['ip']);
        $this->prepreOption(CURLOPT_PROXYTYPE, constant('CURLOPT_' . strtoupper($proxy['type'])));
    }

    /**
    * check if the curlopt is a constant
    *
    * @param mixed $argument
    * @return string
    */
    private function constantify($argument)
    {
        return (is_string($argument) ? constant($argument) : $argument);
    }

    /**
    * prepare curl options
    *
    * @param mixed $key
    * @param string $value
    * @return void
    */
    private function prepareOption($key, $value)
    {
        $key = $this->constantify($key);
        $this->options[$key] = $value;
    }

    /**
    * set array of curl options
    *
    * @param array $options
    * @return void
    */
    private function setOptions($options)
    {
        return curl_setopt_array($this->curlHandler, $options);
    }

    /*
    * {@inheritDoc}
    */
    public function initialize($arguments, $settings)
    {
        $this->curlHandler = curl_init();

        $this->prepareOption(CURLOPT_URL, $arguments['url']);
        $this->prepareOption(CURLOPT_RETURNTRANSFER, true);
        $this->prepareOption(CURLOPT_FAILONERROR, true);
        $this->prepareOption(CURLOPT_ENCODING, 'gzip,deflate');
        $this->prepareOption(CURLOPT_SSL_VERIFYPEER, 0);
        $this->prepareOption(CURLOPT_SSL_VERIFYHOST, 0);

        $this->prepareOption(CURLOPT_HEADER, $arguments['dumpHeaders']);
        $this->prepareOption(CURLOPT_USERAGENT, $arguments['userAgent']);

        if (isset($arguments['referer'])) {
            $this->prepareOption(CURLOPT_REFERER, $arguments['referer']);
        }

        $data = (isset($arguments['data']) ? http_build_query($arguments['data']) : null);

        if (isset($arguments['json'])) {
            $arguments['headers'] = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ];
        }

        if (isset($arguments['xml'])) {
            $arguments['headers'] = [
                'Content-Type: text/xml'
            ];
        }

        if (isset($arguments['headers'])) {
            $this->prepareOption(CURLOPT_HTTPHEADER, $arguments['headers']);
        }

        if (isset($arguments['cookies'])) {
            $this->cookieHandler($arguments['cookies']);
        }

        if (isset($arguments['proxy'])) {
            $this->proxyHandler($arguments['proxy']);
        }

        if ($arguments['method'] == 'post') {
            // print_r($arguments);exit;
            $this->prepareOption(CURLOPT_POST, true);
            $this->prepareOption(CURLOPT_POSTFIELDS, $data); // TODO
        }

        if (isset($arguments['initialize'])) {
            foreach ($arguments['initialize'] as $key => $value) {
                $this->prepareOption($key, $value);
            }
        }

        $this->setOptions($this->options);
        return $this;
    }

    /*
    * {@inheritDoc}
    */
    public function run()
    {
        $result = [];
        $response = curl_exec($this->curlHandler);

        if ($error = curl_error($this->curlHandler)) {
            return ['error' => curl_error($this->curlHandler)];
        }

        $headerSize = curl_getinfo($this->curlHandler, CURLINFO_HEADER_SIZE);
        $result['statusCode'] = curl_getinfo($this->curlHandler, CURLINFO_HTTP_CODE);
        $result['headers'] = substr($response, 0, $headerSize);
        $result['cookies'] = $this->getCookiesFromHeaders($result['headers']);
        $result['body'] = substr($response, $headerSize);
        curl_close($this->curlHandler);

        return $result;
    }

    /**
    * Extracting cookies from header string
    *
    * @param string $headers
    * @return string
    */
    private function getCookiesFromHeaders($headers)
    {
        preg_match_all('#Set-Cookie:[\s]([^;]+)#i', $headers, $cookies);
        return implode(';', $cookies[1]);
    }
}
