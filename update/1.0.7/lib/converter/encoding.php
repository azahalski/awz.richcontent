<?php

namespace Awz\RichContent\Converter;

use Bitrix\Main\Text\Encoding as bxEncoding;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;

class Encoding extends bxEncoding{

    public static $defaultCharset = null;

    public static function convertEntities($html){
        return self::convert($html,  self::getCurrentEncoding(), 'HTML-ENTITIES');
    }
	
	protected static function getCurrentEncoding(): string
    {
        $currentCharset = null;

        $context = Application::getInstance()->getContext();
        if ($context != null)
        {
            $culture = $context->getCulture();
            if ($culture != null)
            {
                $currentCharset = $culture->getCharset();
            }
        }

        if ($currentCharset == null)
        {
            $currentCharset = Configuration::getValue("default_charset");
        }

        if ($currentCharset == null)
        {
            $currentCharset = "Windows-1251";
        }

        return $currentCharset;
    }

}