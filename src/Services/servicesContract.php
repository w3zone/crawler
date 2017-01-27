<?php
namespace w3zone\Crawler\Services;

interface ServicesInterface
{
    /*
    * initialize crawler service options
    * @param array $arguments
    *
    * return self
    */
    public function initialize($arguments, $settings);

    /*
    * run service
    *
    * return array
    */
    public function run();
}
