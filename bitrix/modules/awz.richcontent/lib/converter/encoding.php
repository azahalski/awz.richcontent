<?php

namespace Awz\RichContent\Converter;

use Bitrix\Main\Text\Encoding as bxEncoding;

class Encoding extends bxEncoding{

    public static $defaultCharset = null;

    public static function convertEntities($html){
        return self::convert($html,  self::getCurrentEncoding(), 'HTML-ENTITIES');
    }

}