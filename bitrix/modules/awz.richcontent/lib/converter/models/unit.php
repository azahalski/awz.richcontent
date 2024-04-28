<?php

namespace Awz\RichContent\Converter\Models;

class Unit implements ArrayInterface
{
    protected array $data = [];

    /**
     * Unit constructor.
     * @param array $data
     */
    public function __construct(array $data = []) {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return $this->data;
    }
}
