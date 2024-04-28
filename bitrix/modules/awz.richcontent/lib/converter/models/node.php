<?php

namespace Awz\RichContent\Converter\Models;

class Node
{
    protected string $nodeType;

    protected ArrayInterface $data;

    /** @var Node[] */
    protected ?array $content = null;

    /**
     * Node constructor.
     * @param string         $nodeType
     * @param ArrayInterface $data
     * @param Node[]|null    $content
     */
    public function __construct(string $nodeType, ArrayInterface $data, ?array $content) {
        $this->nodeType = $nodeType;
        $this->data     = $data;
        $this->content  = $content;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        $parse = [
            'nodeType' => $this->nodeType,
            'data'     => $this->data->toArray(),
            'content'  => [],
        ];
        if (!empty($this->content)) {
            foreach ($this->content as $item) {
                if ($item instanceof Node) {
                    $parse['content'][] = $item->toArray();
                } else {
                    $parse['content'][] = $item;
                }
            }
        }
        return $parse;
    }
}
