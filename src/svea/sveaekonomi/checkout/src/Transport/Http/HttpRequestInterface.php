<?php

namespace Svea\Checkout\Transport\Http;

interface HttpRequestInterface
{
    public function setOption($name, $value);

    public function init();

    public function execute();

    public function getInfo($name);

    public function getError();

    public function getErrorNumber();

    public function close();
}
