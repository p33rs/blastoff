<?php
namespace p33rs\Blastoff;

class Response {

    public static function success(array $data = []) {
        return [
            'success' => 1,
            'error' => null,
            'data' => $data
        ];
    }

    public static function fail($message) {
        return [
            'success' => 0,
            'error' => $message,
            'data' => []
        ];
    }

}