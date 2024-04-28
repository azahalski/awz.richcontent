<?php

namespace Awz\RichContent\Converter\Models;

class TextNode
{
    protected string $nodeType;
    protected string $value;

    protected ArrayInterface $data;
    /** @var TextMark[] */
    protected ?array $marks = null;

    /**
     * TextNode constructor.
     * @param string          $nodeType
     * @param string          $value
     * @param ArrayInterface  $data
     * @param TextMark[]|null $marks
     */
    public function __construct(string $nodeType, string $value, ArrayInterface $data, ?array $marks) {
        $this->nodeType = $nodeType;
        $this->value    = $value;
        $this->data     = $data;
        $this->marks    = $marks;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        $parse = [
            'nodeType' => $this->nodeType,
            'value'    => $this->value,
            'data'     => $this->data->toArray(),
            'marks'    => [],
        ];

        if (!is_null($this->marks)) {
            foreach ($this->marks as $item) {
                $parse['marks'][] = $item->toArray();
            }
        }
        return $parse;
    }
}
