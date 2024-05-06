<?php
namespace Awz\RichContent\Api\Controller;

use Awz\RichContent\Api\Filters\ModuleRight;
use Awz\RichContent\Api\Scopes\Controller;
use Awz\RichContent\Helper;
use Bitrix\Main\Request;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Admin extends Controller
{

    protected function addError(Error $error): Controller
    {

        $this->errorCollection[] = $error;

        return $this;
    }

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function configureActions()
    {
        $config = [
            'propList' => [
                'prefilters' => [
                    new ModuleRight('rm1'),
                    new ModuleRight('rm2','iblock'),
                ]
            ],
            'process' => [
                'prefilters' => [
                    new ModuleRight('rm1'),
                    new ModuleRight('rm2','iblock'),
                ]
            ]
        ];

        return $config;
    }

    public function processAction(int $IBLOCK_ID, string $IBLOCK_PROP, string $IBLOCK_SETT, int $awz_cnt, int $awz_last, int $awz_cnt_all, int $max_step_time=20){
        if(!$this->checkRequire(['rm1','rm2'])){
            $this->addError(new Error('Access denied'));
            return null;
        }

        if($IBLOCK_PROP === Helper::AWZ_PROP_SYSTEM){
            $IBLOCK_PROP = Helper::getSystemProp($IBLOCK_ID, true);
        }

        $maxTime = $max_step_time + time();

        $filter = [
            '=IBLOCK_ID'=>$IBLOCK_ID,
            '>ID'=>$awz_last
        ];
        if(!$awz_cnt_all){
            $rCount = \Bitrix\Iblock\ElementTable::getList([
                'select'=>['COUNT'],
                'runtime'=>[
                    new \Bitrix\Main\ORM\Fields\ExpressionField('COUNT',  'COUNT(*)' )
                ],
                'filter'=>$filter
            ])->fetch();
            $awz_cnt_all = intval($rCount['COUNT']);
        }

        $r = \Bitrix\Iblock\ElementTable::getList([
            'select'=>['ID','IBLOCK_ID','PREVIEW_TEXT','DETAIL_TEXT','PREVIEW_TEXT_TYPE','DETAIL_TEXT_TYPE'],
            'filter'=>$filter,
            'order'=>['ID'=>'ASC']
        ]);
        $lastId = 0;
        while($data = $r->fetch()){
            $lastId = $data['ID'];
            $awz_cnt++;
            $text = Helper::generateFromElementData($data, $IBLOCK_SETT);
            \CIBlockElement::SetPropertyValues($data['ID'], $IBLOCK_ID, $text, $IBLOCK_PROP);
            if($maxTime<time()) break;
        }

        return [
            'awz_cnt_all'=>$awz_cnt_all,
            'awz_cnt'=>$awz_cnt,
            'awz_last'=>$lastId,
            'IBLOCK_ID'=>$IBLOCK_ID,
            'IBLOCK_PROP'=>$IBLOCK_PROP,
            'IBLOCK_SETT'=>$IBLOCK_SETT,
        ];


    }
    public function propListAction(int $iblock_id){
        if(!$this->checkRequire(['rm1','rm2'])){
            $this->addError(new Error('Access denied'));
            return null;
        }
        $propList = [];
        $propRes = \Bitrix\Iblock\PropertyTable::getList([
            'select'=>['ID','NAME','CODE','IBLOCK_ID'],
            'filter'=>[
                '=IBLOCK_ID'=>$iblock_id,
                '=PROPERTY_TYPE'=>'S',
                '=MULTIPLE'=>'N'
            ]
        ]);
        $checkSystem = false;
        while($data = $propRes->fetch()){
            if($data['CODE'] === Helper::AWZ_PROP_SYSTEM) $checkSystem = true;
            $propList[$data['ID']] = '['.$data['CODE'].'] - '.$data['NAME'];
        }
        if(!$checkSystem){
            $propList[Helper::AWZ_PROP_SYSTEM] = '['.Helper::AWZ_PROP_SYSTEM.'] - '.Loc::getMessage('AWZ_RICHCONTENT_LIB_CONTROLLER_ADMIN_PROPNAME');
        }

        return $propList;

    }

}