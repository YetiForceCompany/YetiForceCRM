{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
<div id="toggleButton" class="toggleButton" title="{vtranslate('LBL_LEFT_PANEL_SHOW_HIDE', 'Vtiger')}">
	<i id="tButtonImage" class="{if $LEFTPANELHIDE neq '1'}icon-chevron-left{else}icon-chevron-right{/if}"></i>
</div>&nbsp
<div style="padding-left: 15px;">
    <table style=" width:90%;margin-left: 5% " cellpadding="10" class="searchUIBasic well">
        <tr>
            <td class="font-x-large" align="left" colspan="2">
                <strong>{'LBL_IMPORT_SCHEDULED'|@vtranslate:$MODULE}</strong>
            </td>
        </tr>
        {if $ERROR_MESSAGE neq ''}
            <tr>
                <td class="style1" align="left" colspan="2">
                    {$ERROR_MESSAGE}
                </td>
            </tr>
        {/if}
        <tr>
            <td colspan="2" valign="top">
                <table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents">
                    <tr>
                        <td>{'LBL_SCHEDULED_IMPORT_DETAILS'|@vtranslate:$MODULE}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="right" colspan="2">
                <a type="button" name="cancel" value="{'LBL_CANCEL_IMPORT'|@vtranslate:$MODULE}" class="crmButton small delete"
                   onclick="location.href = 'index.php?module={$FOR_MODULE}&view=Import&mode=cancelImport&import_id={$IMPORT_ID}'">{'LBL_CANCEL_IMPORT'|@vtranslate:$MODULE}</a>
                {include file='Import_Done_Buttons.tpl'|@vtemplate_path:'Import'}
            </td>
        </tr>
    </table>
</div>
{/strip}