<?php
namespace App\Model;

class Filter
{
    private string $key;
    private string $operator;
    private string $value;

    public function __construct(string $key, string $operator, string $value)
    {
        $this->key = $key;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}