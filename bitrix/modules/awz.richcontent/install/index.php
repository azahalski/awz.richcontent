<?php
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class awz_richcontent extends CModule
{
	var $MODULE_ID = "awz.richcontent";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

    public function __construct()
	{
        $arModuleVersion = [];

        include(__DIR__.'/version.php');

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("AWZ_RICHCONTENT_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("AWZ_RICHCONTENT_MODULE_DESCRIPTION");
		$this->PARTNER_NAME = Loc::getMessage("AWZ_PARTNER_NAME");
		$this->PARTNER_URI = "https://zahalski.dev/";

		return true;
	}

	function DoInstall()
	{
        $this->InstallEvents();
        $this->InstallFiles();
        ModuleManager::registerModule($this->MODULE_ID);
        return true;
	}

	function DoUninstall()
	{
        $this->UnInstallEvents();
        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
	}

    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible("iblock", "OnAfterIBlockElementAdd",
            $this->MODULE_ID, '\Awz\RichContent\HandlersBx', 'OnAfterIBlockElementAdd'
        );
        $eventManager->registerEventHandlerCompatible("iblock", "OnAfterIBlockElementUpdate",
            $this->MODULE_ID, '\Awz\RichContent\HandlersBx', 'OnAfterIBlockElementUpdate'
        );
        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementAdd",
            $this->MODULE_ID, '\Awz\RichContent\HandlersBx', 'OnAfterIBlockElementAdd'
        );
        $eventManager->unRegisterEventHandler("iblock", "OnAfterIBlockElementUpdate",
            $this->MODULE_ID, '\Awz\RichContent\HandlersBx', 'OnAfterIBlockElementUpdate'
        );
        return true;
    }
	
	function InstallDB() {
		return true;
	}

    function UnInstallDB()
    {
        return true;
    }

    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID);
        return true;
    }

}