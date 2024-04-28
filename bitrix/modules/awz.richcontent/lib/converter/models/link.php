<?php

namespace Awz\RichContent\Converter\Models;

class Link implements ArrayInterface
{
    protected string $uri;

    /**
     * Unit constructor.
     * @param string $uri
     */
    public function __construct(string $uri) {
        $this->uri = $uri;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return [
            'uri' => $this->uri
        ];
    }
}
