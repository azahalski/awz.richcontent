<?php
namespace Awz\RichContent\Api\Scopes;

use Awz\RichContent\Api\Type\Parameters as ParametersType;

class Parameters extends ParametersType {

    public function __construct(array $params = array())
    {
        parent::__construct($params);
    }

}