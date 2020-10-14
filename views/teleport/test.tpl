<?php

require_once(__DIR__."/../composer/vendor/autoload.php");
require_once (__DIR__."/../quantum/kernel/quantum.php");


use PHPUnit\Framework\TestCase;


class {$test_name} extends TestCase
{
    public function testSomething()
    {
        $req = new \Quantum\CurlRequest("https://google.com");

        $this->assertInstanceOf(\Quantum\CurlRequest::class, $req);

}


}
