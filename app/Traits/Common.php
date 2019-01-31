<?php

namespace App\Traits;

use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;

trait Common
{
    private function config()
    {
        $timeout = 60;

        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(
            function ($retries) {
                return $retries < 5;
            },
            function ($retries) {
                return pow(2, $retries - 1);
            }
        ));
        $config = [
            "timeout" => $timeout,
            "headers" => [
                "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
            ],
            "handler" => $stack,
        ];
        return $config;
    }
}
