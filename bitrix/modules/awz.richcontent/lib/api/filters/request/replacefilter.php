<?php
namespace Awz\RichContent\Api\Filters\Request;

use Bitrix\Main\Type;

class ReplaceFilter implements Type\IRequestFilter
{
    protected $replace_values;
    protected $type;

    public function __construct(array $values, string $type = 'post')
    {
        $this->replace_values = $values;
        $this->type = $type;
    }

    public function filter(array $values)
    {
        if(!isset($values[$this->type])) $values[$this->type] = [];
        foreach($this->replace_values as $code=>$value){
            if($code)
                $values[$this->type][$code] = $value;
        }
        return $values;
    }
}
