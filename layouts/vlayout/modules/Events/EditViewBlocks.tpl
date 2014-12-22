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
    {include file="EditViewBlocks.tpl"|@vtemplate_path:'Vtiger'}
    <input type="hidden" name="userChangedEndDateTime" value="{$USER_CHANGED_END_DATE_TIME}" />
    <table class="table table-bordered blockContainer showInlineTable">
        <tr>
            <th class="blockHeader" colspan="4">{vtranslate('LBL_INVITE_USER_BLOCK', $MODULE)}</th>
        </tr>
        <tr>
            <td class="fieldLabel">
                <label class="muted pull-right marginRight10px">
                    {vtranslate('LBL_INVITE_USERS', $MODULE)}
                </label>
            </td>
            <td class="fieldValue">
                <select id="selectedUsers" class="select2" multiple name="selectedusers[]" style="width:200px;">
                    {foreach key=USER_ID item=USER_NAME from=$ACCESSIBLE_USERS}
                        {if $USER_ID eq $CURRENT_USER->getId()}
                            {continue}
                        {/if}
                        <option value="{$USER_ID}" {if in_array($USER_ID,$INVITIES_SELECTED)}selected{/if}>
                            {$USER_NAME}
                        </option>
                    {/foreach}
                    <select>
                        </td>
                        </tr>
                        </table>
                        <br>
                    {/strip}