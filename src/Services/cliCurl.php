<?php
namespace w3zone\Crawler\Services;

class cliCurl implements ServicesInterface
{
    private $statement = [];
    private $headers = [];
    private $cookies = [];
    private $dumpedHeaders = null;

    /*
    * {@inheritDoc}
    *
    * throw \Exception
    */
    public function initialize($arguments, $settings)
    {
        $this->statement = [];
        if ($arguments['method'] == 'post') {
            $this->statement[] = '-X POST --data "' . http_build_query($arguments['data']) . '"';
        } else {
            $this->statement[] = '-X GET';
        }

        if (isset($arguments['referer'])) {
            $this->statement[] = '-e "' . $arguments['referer'] . '"';
        }

        if (true === $arguments['dumpHeaders']) {
            $this->statement[] = '-D -';
        }

        if (isset($arguments['json'])) {
            $this->headers[] = 'Accept: application/json';
            $this->headers[] = 'Content-type: application/json';
        }

        if (isset($arguments['xml'])) {
            $this->headers[] = 'Accept: text/xml';
            $this->headers[] = 'Content-type: text/xml';
        }

        if (isset($arguments['headers'])) {
            $this->headers = $arguments['headers'];
        }

        if (isset($arguments['cookies'])) {
            if ($arguments['cookies']['mode'] == 'r') {
                $this->cookies[] = '-b "' . $arguments['cookies']['file'] . '"';
            } else if ($arguments['cookies']['mode'] == 'w') {
                $this->cookies[] = '-c "' . $arguments['cookies']['file'] . '"';
            } else if ($arguments['cookies']['mode'] == 'r+w' || $arguments['cookies']['mode'] == 'w+r') {
                $this->cookies[] = '-b "' . $arguments['cookies']['file'] . '"';
                $this->cookies[] = '-c "' . $arguments['cookies']['file'] . '"';
            }
            $this->statement[] = ' ' . implode(' ', $this->cookies) . ' ';
        }

        if (count($this->headers) > 0) {
            $this->statement[] = '-H "' . implode('" -H "', $this->headers) . '"';
        }

        if (isset($arguments['proxy'])) {
            $proxy = $arguments['proxy'];
            $this->statement[] = '-x "' . $proxy['ip'] . '" ' . ($proxy['type'] != 'http' ? '--' . $proxy['type'] : '');
        }

        if (isset($arguments['initialize'])) {
            throw new \Exception('you can\'t re-initialize options in cliCurl service');
        }

        $this->statement[] = '-H "Expect:"';

        $this->statement[] = '"' . $arguments['url'] . '"';

        return $this;
    }

    /*
    * {@inheritDoc}
    */
    public function run()
    {
        $result = [];
        $statement = implode(' ', $this->statement);

        $response = shell_exec('curl -s --compressed ' . $statement);

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

    private function explainHeaders($headers)
    {
        preg_match('#HTTP\/1\.1 ([0-9]+) (.*?)#U', $headers, $statusCode);

        if ($statusCode[1] >= 400) {
            return ['error' => 'Error (' . $statusCode[1] . ') in your cURL request : ' . $statusCode[2]];
        }

        return ['statusCode' => $statusCode[1], 'cookies' => $this->getCookiesFromHeaders($headers)];
    }

    private function getCookiesFromHeaders($headers)
    {
        preg_match_all('#Set-Cookie:[\s]([^;]+)#i', $headers, $cookies);
        return implode(';', $cookies[1]);
    }
}
