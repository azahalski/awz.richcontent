<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Awz\RichContent\Helper;
Loc::loadMessages(__FILE__);
global $APPLICATION;
$module_id = "awz.richcontent";
$MODULE_RIGHT = $APPLICATION->GetGroupRight($module_id);
$zr = "";
if (! ($MODULE_RIGHT >= "R"))
$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
$APPLICATION->SetTitle(Loc::getMessage('AWZ_RICHCONTENT_OPT_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(!Loader::includeModule($module_id)) return;

Asset::getInstance()->addJs("/bitrix/js/".$module_id."/sett.js");
Extension::load("ui.progressbar");
Extension::load("ui.buttons");
Extension::load("ui.forms");

$ibList = [];
$propertyList = [];
if(Loader::includeModule('iblock')){
    $iblockRes = \Bitrix\Iblock\IblockTable::getList(['select'=>["ID","NAME","IBLOCK_TYPE_ID"]]);
    while($data = $iblockRes->fetch()){
        $ibList[$data['ID']] = '['.$data['IBLOCK_TYPE_ID'].']['.$data['ID'].'] - '.$data['NAME'];
        $propertyList[$data['ID']] = [];
    }
}
$maxLine = round(count($ibList)/2);
if($maxLine>50) $maxLine = round(count($ibList)/4);
if($maxLine>20) $maxLine = 20;
$ibOpts = [
    Helper::TYPE_PREVIEW_TEXT => Loc::getMessage('AWZ_RICHCONTENT_OPT_IBLOCK_SETT_2'),
    Helper::TYPE_DETAIL_TEXT => Loc::getMessage('AWZ_RICHCONTENT_OPT_IBLOCK_SETT_1'),
    Helper::TYPE_ALL_TEXT => Loc::getMessage('AWZ_RICHCONTENT_OPT_IBLOCK_SETT_3')
];
$request = Application::getInstance()->getContext()->getRequest();

$checkIb = [];
if ($_SERVER["REQUEST_METHOD"] == "POST" && $MODULE_RIGHT == "W" && strlen($_REQUEST["Update"]) > 0 && check_bitrix_sessid())
{
    $IBLOCK_IDS = $request->get('IBLOCK_IDS');
    foreach($IBLOCK_IDS as $iblockId){
        $iblockId = intval($iblockId);
        if($iblockId && isset($ibList[$iblockId])){
            $checkIb[] = $iblockId;
            $key = 'IBLOCK_SETT_'.$iblockId;
            $key2 = 'IBLOCK_PROP_'.$iblockId;
            $propVal = $request->get($key2);
            if($propVal == Helper::AWZ_PROP_SYSTEM){
                $propVal = Helper::getSystemProp($iblockId, true);
            }
            if($request->get($key)){
                Option::set($module_id, $key, $request->get($key).','.$propVal, "");
            }else{
                Option::delete($module_id, ['name'=>$key]);
            }
        }
    }

    Option::set($module_id, "IBLOCK_IDS", implode(",", $checkIb), "");
}
$checkIb = explode(',',Option::get($module_id, "IBLOCK_IDS", "",""));
if(Loader::includeModule('iblock') && !empty($checkIb)){
    $propRes = \Bitrix\Iblock\PropertyTable::getList([
        'select'=>['ID','NAME','CODE','IBLOCK_ID'],
        'filter'=>[
            '=IBLOCK_ID'=>$checkIb,
            '=PROPERTY_TYPE'=>'S',
            '=MULTIPLE'=>'N'
        ]
    ]);
    $checkSystem = [];
    while($data = $propRes->fetch()){
        if(!isset($checkSystem[$data['IBLOCK_ID']])) $checkSystem[$data['IBLOCK_ID']] = false;
        if($data['CODE'] == Helper::AWZ_PROP_SYSTEM){
            $checkSystem[$data['IBLOCK_ID']] = true;
        }
        $propertyList[$data['IBLOCK_ID']][$data['ID']] = '['.$data['CODE'].'] - '.$data['NAME'];
    }
    foreach($checkSystem as $ib=>$checkRes){
        if($checkRes === false){
            $propertyList[$ib][Helper::AWZ_PROP_SYSTEM] = '['.Helper::AWZ_PROP_SYSTEM.'] - '.Loc::getMessage('AWZ_RICHCONTENT_OPT_NEW_PROP');
        }
    }
}
$aTabs = array();

$aTabs[] = array(
    "DIV" => "edit1",
    "TAB" => Loc::getMessage('AWZ_RICHCONTENT_OPT_SECT1'),
    "ICON" => "vote_settings",
    "TITLE" => Loc::getMessage('AWZ_RICHCONTENT_OPT_SECT1')
);

$aTabs[] = array(
    "DIV" => "edit2",
    "TAB" => Loc::getMessage('AWZ_RICHCONTENT_OPT_SECT2'),
    "ICON" => "vote_settings",
    "TITLE" => Loc::getMessage('AWZ_RICHCONTENT_OPT_SECT2')
);

$aTabs[] = array(
    "DIV" => "edit3",
    "TAB" => Loc::getMessage('AWZ_RICHCONTENT_OPT_SECT3'),
    "ICON" => "vote_settings",
    "TITLE" => Loc::getMessage('AWZ_RICHCONTENT_OPT_SECT3')
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>
    <form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($module_id)?>&lang=<?=LANGUAGE_ID?>&mid_menu=1" id="FORMACTION">
        <?
        $tabControl->BeginNextTab();
        ?>
        <?if(Loader::includeModule('iblock')){
            ?>
            <tr>
                <td style="max-width:300px;"><?=Loc::getMessage('AWZ_RICHCONTENT_OPT_GEN')?></td>
                <td>
                    <input type="hidden" id="max_step_time" name="max_step_time" value="3"/>
                    <input type="hidden" id="awz_cnt" name="awz_cnt" value="0"/>
                    <input type="hidden" id="awz_last" name="awz_last" value="0"/>
                    <input type="hidden" id="awz_cnt_all" name="awz_cnt_all" value="0"/>
                    <select style="width:240px;" name="IBLOCK_ID" id="IBLOCK_ID" onchange="window.awz_sett.loadProperties(this, 'awz-richgen-prop');">
                        <option value="">-</option>
                        <?
                        foreach($ibList as $v=>$name){
                            ?>
                            <option value="<?=$v?>"><?=$name?></option>
                            <?
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="max-width:300px;"><?=Loc::getMessage('AWZ_RICHCONTENT_OPT_IBLOCK_SETT')?></td>
                <td>
                    <select style="width:240px;" name="IBLOCK_SETT" id="IBLOCK_SETT">
                        <option value="">-</option>
                        <?
                        foreach($ibOpts as $v=>$name){
                            ?>
                            <option value="<?=$v?>"><?=$name?></option>
                            <?
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="max-width:300px;"><?=Loc::getMessage('AWZ_RICHCONTENT_OPT_IBLOCK_SETT_CODE')?></td>
                <td id="awz-richgen-prop">

                </td>
            </tr>
            <tr>
                <td colspan="2" id="awz-richgen-prop-progress" style="text-align: center;">

                </td>
            </tr>

        <?}?>
        <?
        $tabControl->BeginNextTab();
        ?>
        <?if(Loader::includeModule('iblock')){
            ?>
            <tr>
                <td style="max-width:300px;"><?=Loc::getMessage('AWZ_RICHCONTENT_OPT_IBLOCK_IDS')?></td>
                <td>
                    <select style="width:240px;" name="IBLOCK_IDS[]" id="IBLOCK_IDS" size="<?=$maxLine?>" multiple="multiple">
                    <?
                    foreach($ibList as $v=>$name){
                        ?>
                        <option value="<?=$v?>"<?=(in_array($v,$checkIb)?' selected="selected"':'')?>><?=$name?></option>
                        <?
                    }
                    ?>
                    </select>
                </td>
            </tr>
        <?}?>
        <?foreach($checkIb as $iblockId){
            if(!trim($iblockId)) continue;
            ?>
            <?
            $valIbAr = explode(',',Option::get($module_id, "IBLOCK_SETT_".$iblockId, "",""));
            if(empty($valIbAr) || !is_array($valIbAr)){
                $valIbAr = ['',''];
            }
            if(count($valIbAr)!=2) $valIbAr = ['',''];
            ?>
            <tr class="heading">
                <td colspan="2">
                    <?=$ibList[$iblockId]?>
                </td>
            </tr>
            <tr>
                <td style="max-width:300px;"><?=Loc::getMessage('AWZ_RICHCONTENT_OPT_IBLOCK_SETT')?></td>
                <td>
                    <select style="width:240px;" name="IBLOCK_SETT_<?=$iblockId?>" id="IBLOCK_SETT_<?=$iblockId?>">
                        <option value="">-</option>
                        <?
                        foreach($ibOpts as $v=>$name){
                            ?>
                            <option value="<?=$v?>"<?=($v == $valIbAr[0]?' selected="selected"':'')?>><?=$name?></option>
                            <?
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td style="max-width:300px;"><?=Loc::getMessage('AWZ_RICHCONTENT_OPT_IBLOCK_SETT_CODE')?></td>
                <td>
                    <select style="width:240px;" name="IBLOCK_PROP_<?=$iblockId?>" id="IBLOCK_PROP_<?=$iblockId?>">
                        <option value="">-</option>
                        <?
                        if(isset($propertyList[$iblockId])){
                            foreach($propertyList[$iblockId] as $v=>$name){
                                ?>
                                <option value="<?=$v?>"<?=($v == $valIbAr[1]?' selected="selected"':'')?>><?=$name?></option>
                                <?
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
        <?}?>
        <?
        $tabControl->BeginNextTab();
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
        ?>
        <?
        $tabControl->Buttons();
        ?>
        <input <?if ($MODULE_RIGHT<"W") echo "disabled" ?> type="submit" class="adm-btn-green" name="Update" value="<?=Loc::getMessage('AWZ_RICHCONTENT_OPT_BTN_SAVE')?>" />
        <input type="hidden" name="Update" value="Y" />
        <?$tabControl->End();?>
    </form>
    <script>
        setTimeout(function(){
            window.awz_sett['loc'] = {
                'btn-start':'<?=Loc::getMessage('AWZ_RICHCONTENT_OPT_GEN_BTN')?>',
                'progress':'<?=Loc::getMessage('AWZ_RICHCONTENT_OPT_GEN_PROGRESS')?>',
                'finish':'<?=Loc::getMessage('AWZ_RICHCONTENT_OPT_GEN_FINISH')?>',
            };
        },0);
    </script>
    <?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");