<?php
namespace w3zone\Crawler\Services;

class cliCurl implements ServicesInterface
{
    /**
    * array of curl request options
    *
    * @var array
    */
    private $options = [];

    private $headers = [];
    private $cookies = [];

    /*
    * {@inheritDoc}
    *
    * throw \Exception
    */
    public function initialize($options, $settings)
    {
        $this->options = [];
        if ($options['method'] == 'post') {
            $this->options[] = '-X POST --data "' . http_build_query($options['data']) . '"';
        } else {
            $this->options[] = '-X GET';
        }

        if (isset($options['referer'])) {
            $this->options[] = '-e "' . $options['referer'] . '"';
        }

        if (true === $options['dumpHeaders']) {
            $this->options[] = '-D -';
        }

        if (isset($options['json'])) {
            $this->headers[] = 'Accept: application/json';
            $this->headers[] = 'Content-type: application/json';
        }

        if (isset($options['xml'])) {
            $this->headers[] = 'Accept: text/xml';
            $this->headers[] = 'Content-type: text/xml';
        }

        if (isset($options['headers'])) {
            $this->headers = $options['headers'];
        }

        if (isset($options['cookies'])) {
            if ($options['cookies']['mode'] == 'r') {
                $this->cookies[] = '-b "' . $options['cookies']['file'] . '"';
            } else if ($options['cookies']['mode'] == 'w') {
                $this->cookies[] = '-c "' . $options['cookies']['file'] . '"';
            } else if ($options['cookies']['mode'] == 'r+w' || $options['cookies']['mode'] == 'w+r') {
                $this->cookies[] = '-b "' . $options['cookies']['file'] . '"';
                $this->cookies[] = '-c "' . $options['cookies']['file'] . '"';
            }
            $this->options[] = ' ' . implode(' ', $this->cookies) . ' ';
        }

        if (count($this->headers) > 0) {
            $this->options[] = '-H "' . implode('" -H "', $this->headers) . '"';
        }

        if (isset($options['proxy'])) {
            $proxy = $options['proxy'];
            $this->options[] = '-x "' . $proxy['ip'] . '" ' . ($proxy['type'] != 'http' ? '--' . $proxy['type'] : '');
        }

        if (isset($options['initialize'])) {
            throw new \Exception('you can\'t re-initialize options in cliCurl service');
        }

        $this->options[] = '-H "Expect:"';

        $this->options[] = '"' . $options['url'] . '"';

        return $this;
    }

    /*
    * {@inheritDoc}
    */
    public function run()
    {
        $result = [];
        $options = implode(' ', $this->options);

        $response = shell_exec('curl -s --compressed ' . $options);

        list($headers, $body) = explode("\r\n\r\n", $response, 2);

        $explainHeaders = $this->explainHeaders($headers);

        if (isset($explainHeaders['error'])) {
            return ['error' => $explainHeaders['error']];
        }

        $result['statusCode'] = $explainHeaders['statusCode'];
        $result['headers'] = $headers;
        $result['cookies'] = $explainHeaders['cookies'];
        $result['body'] = $body;

        return $result;
    }

    /**
    * Explain response header
    *
    * @param string $headers
    * @return array
    */
    private function explainHeaders($headers)
    {
        preg_match('#HTTP\/1\.1 ([0-9]+) (.*?)#U', $headers, $statusCode);

        if ($statusCode[1] >= 400) {
            return ['error' => 'Error (' . $statusCode[1] . ') in your cURL request : ' . $statusCode[2]];
        }

        return ['statusCode' => $statusCode[1], 'cookies' => $this->getCookiesFromHeaders($headers)];
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
