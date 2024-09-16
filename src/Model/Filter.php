<?php
namespace App\Model;

class Filter
{
    public function __construct(
        public string $key, 
        public string $operator, 
        public int|string $value)
    {}
}