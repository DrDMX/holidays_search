<?php
namespace App\Services;

class Country {
    protected $code;
    protected $name;

    public function __construct(string $code, string $name) {
        $this->code = $code;
        $this->name = $name;
    }

    public function get_code() {
       return $this->code;
    }

    public function get_name() {
        return $this->name;
    }
}
