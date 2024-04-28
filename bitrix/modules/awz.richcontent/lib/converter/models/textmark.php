<?php

namespace Awz\RichContent\Converter\Models;

class TextMark implements ArrayInterface
{
    protected string $type;

    /**
     * TextMark constructor.
     *
     * @param string $type
     */
    public function __construct(string $type) {
        $this->type = $type;
    }

    /**
     * @return string[]
     */
    public function toArray(): array {
        return [
            'type' => $this->type
        ];
    }
}
