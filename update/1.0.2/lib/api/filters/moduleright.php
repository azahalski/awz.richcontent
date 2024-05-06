<?php

namespace Awz\RichContent\Api\Filters;

use Awz\RichContent\Api\Scopes\Scope;
use Awz\RichContent\Api\Scopes\BaseFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class ModuleRight extends BaseFilter {

    protected $currentModule;
    /**
     * ModuleRight constructor.
     *
     * @param string $scopeCode
     * @param string $moduleId
     */
    public function __construct(string $scopeCode, string $moduleId="", string $right="W", array $requireScopes = []){
        $tmp = mb_strtolower(__NAMESPACE__);
        $tmp = str_replace('\\api\\filters','',$tmp);
        $tmp = str_replace('\\','.',$tmp);
        $this->currentModule = $tmp;
        if(!$moduleId) $moduleId = $tmp;
        parent::__construct(['module_id'=>$moduleId,'right'=>$right], [], Scope::createFromCode($scopeCode), $requireScopes);
    }

    public function onBeforeAction(Event $event)
    {
        if(!$this->checkRequire()){
            return null;
        }
        $this->disableScope();

        $module_id = $this->getParams()->get('module_id');
        if(!$module_id){
            throw new ArgumentNullException('module_id');
        }
        if(Loader::includeModule($module_id)){
            global $APPLICATION;
            $MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
            if (($MODULE_RIGHT >= $this->getParams()->get('right'))){
                $this->enableScope();
            }
        }
        return new EventResult(EventResult::SUCCESS, null, $this->currentModule, $this);
    }

}