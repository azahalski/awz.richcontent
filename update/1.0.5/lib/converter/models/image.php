<?php

namespace Awz\RichContent\Converter\Models;


class Image implements ArrayInterface
{
    protected string $uri;

    /**
     * Unit constructor.
     * @param string $src
     */
    public function __construct(string $src) {
        $this->src = $src;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return [
            'src' => $this->src
        ];
    }
}
