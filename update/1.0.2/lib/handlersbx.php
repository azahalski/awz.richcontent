<?php

namespace Awz\RichContent;

use Bitrix\Main\Config\Option;

class HandlersBx {

    public static function OnAfterIBlockElementAdd($arFields){
        return self::OnAfterIBlockElementUpdate($arFields);
    }
    public static function OnAfterIBlockElementUpdate($arFields){
        if($arFields['IBLOCK_ID'] && $arFields['ID']){
            $checkIb = explode(',',Option::get("awz.richcontent", "IBLOCK_IDS", "",""));
            if(is_array($checkIb) && in_array($arFields['IBLOCK_ID'], $checkIb)){
                $setProp = false;
                $propId = 0;
                $sett = explode(',',Option::get('awz.richcontent', "IBLOCK_SETT_".$arFields['IBLOCK_ID'], "",""));

                if(is_array($sett) && isset($sett[1])){
                    $propId = $sett[1];
                }
                if(!$propId) return;
                if(
                    isset($arFields['DETAIL_TEXT_TYPE']) && isset($arFields['PREVIEW_TEXT_TYPE']) &&
                    isset($arFields['DETAIL_TEXT']) && isset($arFields['PREVIEW_TEXT'])
                ){
                    $txt = Helper::generateFromElementData($arFields);
                    $setProp = true;
                }elseif($arFields['ID']){
                    $txt = Helper::generateFromElementId($arFields['ID']);
                    $setProp = true;
                }
                if($setProp){
                    \CIBlockElement::SetPropertyValues($arFields['ID'], $arFields['IBLOCK_ID'], $txt, $propId);
                }
            }
        }
    }

}