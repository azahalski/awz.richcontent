<?php
namespace Awz\RichContent;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Text\HtmlConverter;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Iblock\ElementTable;

class Helper {

    const TYPE_PREVIEW_TEXT = 'PREVIEW_TEXT';
    const TYPE_DETAIL_TEXT = 'DETAIL_TEXT';
    const TYPE_ALL_TEXT = 'ALL_TEXT';

    public static function getImageRich($row){
        $json = [];
        if($row['nodeType'] == 'image'){
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

    public static function getRichText($desc){
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
            //echo'<pre>';print_r($row);echo'</pre>';
            $checkImage = self::getImageRich($row);
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

        return Json::encode($json, JSON_UNESCAPED_UNICODE );

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

}