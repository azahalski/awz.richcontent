<?php

namespace Awz\RichContent\Converter\Models;

class Reference implements ArrayInterface
{
    protected string $id;
    protected string $type;
    protected string $linkType;

    /**
     * Reference constructor.
     * @param string $id
     * @param string $type
     * @param string $linkType
     */
    public function __construct(string $id, string $type, string $linkType) {
        $this->id       = $id;
        $this->type     = $type;
        $this->linkType = $linkType;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return [
            'target' => [
                'sys' => [
                    'id'       => $this->id,
                    'type'     => $this->type,
                    'linkType' => $this->linkType,
                ]
            ]
        ];
    }
}
