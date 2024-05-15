<?php
namespace Awz\RichContent;

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Text\HtmlConverter;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\IO\File;
use Bitrix\Main\Web;
use Bitrix\Main\File\Image;

Loc::loadMessages(__FILE__);

class Helper {

    const ALLOW_IMAGE = 4;

    const IMAGE_MAXW = 200;
    const IMAGE_MAXH = 200;

    const TYPE_PREVIEW_TEXT = 'PREVIEW_TEXT';
    const TYPE_DETAIL_TEXT = 'DETAIL_TEXT';
    const TYPE_ALL_TEXT = 'ALL_TEXT';
    const AWZ_PROP_SYSTEM = 'AWZ_RICHCONTENT_SYSTEM';

    public static $propIdsCache = [];

    public static function getImageRich($row){
        $json = [];
        if($row['nodeType'] == 'image'){
            $file = new File(Application::getDocumentRoot().$row['data']['src']);
            if(!$file->isExists()) return $json;
            if(!Web\MimeType::isImage($file->getContentType())) return $json;
            $image = new Image($file->getPath());
            $info = $image->getInfo();
            if($info->getWidth() < self::IMAGE_MAXW) return $json;
            if($info->getHeight() < self::IMAGE_MAXH) return $json;
            $json = [
                'widgetName'=>"raShowcase",
                'type'=>'roll',
                'blocks'=>[
                    [
                        'imgLink'=>'',
                        'img'=>[
                            'src'=>'https://'.$_SERVER['HTTP_HOST'].$row['data']['src'],
                            'srcMobile'=>'https://'.$_SERVER['HTTP_HOST'].$row['data']['src'],
                            'alt'=>''
                        ]
                    ]
                ]
            ];
        }elseif(!empty($row['content'])){
            foreach($row['content'] as $tmprow){
                $json = self::getImageRich($tmprow);
                if(!empty($json)) return $json;
            }
        }
        return $json;
    }

    public static function createList($row, $lists = []){
        if(empty($row['content'])) return $lists;
        //print_r($row['content']);
        foreach($row['content'] as $rowList){
            if($rowList['nodeType'] == 'list-item' && !empty($rowList['content'])){
                $tmp = [];
                foreach($rowList['content'] as $tmprow){
                    if($tmprow['nodeType'] == 'paragraph'){
                        foreach($tmprow['content'] as $tmp3){
                            $tmpv = Converter\Tool::DOMinnerHTML($tmp3);
                            if(trim($tmpv)) $tmp[] = trim($tmpv);
                        }
                    }else{
                        $tmpv = Converter\Tool::DOMinnerHTML($tmprow);
                        if(trim($tmpv)) $tmp[] = trim($tmpv);
                    }
                }
                if(!empty($tmp)){
                    $lists[] = [
                        'text'=> [
                            'size'=>"size2",
                            "align"=> "left",
                            "color"=> "color1",
                            'content'=>$tmp
                        ]
                    ];
                }
            }else{
                $lists = self::createList($rowList, $lists);
            }
        }
        return $lists;
    }

    public static function getRichText($desc, $mask = null){
        if($mask && !($mask instanceof Right\bMask)) $mask = null;
        if(!$mask) $mask = new Right\bMask();
        $desc = str_replace('<noindex>','',$desc);
        $desc = str_replace('<\/noindex>','',$desc);
        $desc = str_replace('&nbsp;',' ',$desc);

        $sanitizer = new \CBXSanitizer;
        $sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
        $sanitizer->DelTags(['a','i','noindex']);
        $desc = $sanitizer->sanitizeHtml($desc);
        if(!$desc) return "";

        $content = Converter\Tool::parse($desc);

        $json = ['content'=>[],"version"=> 0.3];
        foreach($content['content'] as $row){
            $checkImage = [];
            if($mask->check(self::ALLOW_IMAGE)){
                $checkImage = self::getImageRich($row);
            }
            if(!empty($checkImage)){
                $json['content'][] = $checkImage;
            }elseif($row['nodeType'] == 'text' && isset($row['value']) && trim($row['value'])){
                $json['content'][] = [
                    'widgetName'=>"raTextBlock",
                    'text'=>[
                        "size"=> "size2",
                        "color"=>"color1",
                        "content"=>[trim($row['value'])]
                    ]
                ];
            }elseif($row['nodeType'] == 'paragraph'){
                $bold = false;
                $tmp = [];
                $clistAll = [];
                foreach($row['content'] as $tmprow){
                    $clist = self::createList($tmprow);
                    if(!empty($clist)){

                        if(!empty($tmp)){
                            $json['content'][] = [
                                'widgetName'=>"raTextBlock",
                                'text'=>[
                                    "size"=> $bold ? "size4" : "size2",
                                    "color"=>"color1",
                                    "content"=>$tmp
                                ]
                            ];
                            $tmp = [];
                        }

                        $json['content'][] = [
                            'widgetName'=>"list",
                            "theme"=> "bullet",
                            "blocks"=>$clist
                        ];
                    }elseif($tmprow['nodeType'] == 'text' && trim($tmprow['value'])){
                        $tmp[] = trim($tmprow['value']);
                        if(isset($tmprow['marks'][0]['type']) && $tmprow['marks'][0]['type'] == 'bold'){
                            $bold = true;
                        }
                    }else{
                        $tmpv = Converter\Tool::DOMinnerHTML($tmprow);
                        if(trim($tmpv)) $tmp[] = trim($tmpv);
                    }
                }
                if(!empty($tmp)){
                    $json['content'][] = [
                        'widgetName'=>"raTextBlock",
                        'text'=>[
                            "size"=> $bold ? "size4" : "size2",
                            "color"=>"color1",
                            "content"=>$tmp
                        ]
                    ];
                }
            }
            if($row['nodeType'] == 'heading-5' || $row['nodeType'] == 'heading-4'){
                $json['content'][] = [
                    'widgetName'=>"raTextBlock",
                    'text'=>[
                        "size"=>"size4",
                        "color"=>"color1",
                        "content"=>[Converter\Tool::DOMinnerHTML($row)]
                    ]
                ];
            }
            if($row['nodeType'] == 'heading-3'){
                $json['content'][] = [
                    'widgetName'=>"raTextBlock",
                    'text'=>[
                        "size"=>"size5",
                        "color"=>"color1",
                        "content"=>[Converter\Tool::DOMinnerHTML($row)]
                    ]
                ];
            }
            if($row['nodeType'] == 'heading-2'){
                $json['content'][] = [
                    'widgetName'=>"raTextBlock",
                    'text'=>[
                        "size"=>"size6",
                        "color"=>"color1",
                        "content"=>[Converter\Tool::DOMinnerHTML($row)]
                    ]
                ];
            }
            if($row['nodeType'] == 'unordered-list'){
                $clist = self::createList($row);
                if(!empty($clist)){
                    $json['content'][] = [
                        'widgetName'=>"list",
                        "theme"=> "bullet",
                        "blocks"=>$clist
                    ];
                }
            }

        }

        if(Application::isUtfMode()){
            return Json::encode($json, JSON_UNESCAPED_UNICODE );
        }else{
            return Json::encode($json);
        }

    }

    public static function generateFromElementId(int $elId, string $type = ''){
        if(Loader::includeModule('iblock')){
            $data = ElementTable::getList([
                'select'=>['ID','IBLOCK_ID','PREVIEW_TEXT','DETAIL_TEXT','PREVIEW_TEXT_TYPE','DETAIL_TEXT_TYPE'],
                'filter'=>['ID'=>$elId]
            ])->fetch();
            if($data) return self::generateFromElementData($data, $type);
        }
        return "";
    }

    public static function generateFromElementData(array $data, string $type = ''){
        if(!$data['IBLOCK_ID']){
            throw new ArgumentNullException('IBLOCK_ID');
        }
        if(!$data['PREVIEW_TEXT'] && !$data['DETAIL_TEXT']) return '';
        if($data['PREVIEW_TEXT'] && !isset($data['PREVIEW_TEXT_TYPE'])) return '';
        if($data['DETAIL_TEXT'] && !isset($data['DETAIL_TEXT_TYPE'])) return '';
        if(!$type){
            $sett = explode(',',Option::get('awz.richcontent', "IBLOCK_SETT_".$data['IBLOCK_ID'], "",""));
            if(is_array($sett)){
                $type = $sett[0];
            }
        }
        if(!$type) return '';
        //$htmlConverter = new HtmlConverter();

        if($data['PREVIEW_TEXT_TYPE'] === HtmlConverter::TEXT){
            $data['PREVIEW_TEXT'] = '<div>'.\TxtToHTML($data['PREVIEW_TEXT']).'</div>';
        }
        if($data['DETAIL_TEXT_TYPE'] === HtmlConverter::TEXT){
            $data['DETAIL_TEXT'] = '<div>'.\TxtToHTML($data['DETAIL_TEXT']).'</div>';
        }
        $text = '';
        if($type == self::TYPE_PREVIEW_TEXT){
            $text = $data['PREVIEW_TEXT'];
        }elseif($type == self::TYPE_DETAIL_TEXT){
            $text = $data['DETAIL_TEXT'];
        }elseif($type == self::TYPE_ALL_TEXT){
            $text = $data['PREVIEW_TEXT'].$data['DETAIL_TEXT'];
        }
        return self::getRichText($text);
    }

    public static function getSystemProp(int $iblockId, bool $createNew = false, string $code = self::AWZ_PROP_SYSTEM): int
    {
        if(isset(self::$propIdsCache[$iblockId])) return self::$propIdsCache[$iblockId];
        if(Loader::includeModule('iblock')){
            $r = \Bitrix\Iblock\PropertyTable::getList([
                'select'=>['ID'],
                'filter'=>['=IBLOCK_ID'=>$iblockId, '=CODE'=>$code]
            ]);
            if($propData = $r->fetch()){
                self::$propIdsCache[$iblockId] = (int) $propData['ID'];
                return self::$propIdsCache[$iblockId];
            }
            if($createNew){
                $r = \Bitrix\Iblock\PropertyTable::add([
                    'IBLOCK_ID'=>$iblockId,
                    'NAME'=>Loc::getMessage('AWZ_RICHCONTENT_LIB_HELPER_PROPNAME'),
                    'CODE'=>$code,
                    'ACTIVE'=>'Y',
                    'PROPERTY_TYPE'=>\Bitrix\Iblock\PropertyTable::TYPE_STRING,
                ]);
                if($r->isSuccess()){
                    self::$propIdsCache[$iblockId] = (int) $r->getId();
                    return self::$propIdsCache[$iblockId];
                }
            }
        }
        return 0;
    }

}