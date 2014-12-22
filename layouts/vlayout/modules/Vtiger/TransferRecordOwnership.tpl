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
    <div id="transferOwnershipContainer" class='modelContainer'>
        <div class="modal-header contentsBackground">
            <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
            <h3 id="massEditHeader">{vtranslate('LBL_TRANSFER_OWNERSHIP', $MODULE)}</h3>
        </div>
        <form class="form-horizontal" id="changeOwner" name="changeOwner" method="post" action="index.php">
            <div class="modal-body tabbable">
                <div class="control-group">
                    <div class="control-label" style="width: 50;">{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}</div>
                    <div class="controls">
                        <select class="select2-container columnsSelect" id="related_modules" data-validation-engine="validate[required]" data-placeholder="{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}" multiple="" name="related_modules[]" style="width: 350px;">
                            {foreach item=RELATED_MODULE from=$RELATED_MODULES}
                                {if !in_array($RELATED_MODULE->get('relatedModuleName'), $SKIP_MODULES)}
                                    <option value="{$RELATED_MODULE->get('relatedModuleName')}">{vtranslate($RELATED_MODULE->get('relatedModuleName'), $RELATED_MODULE->get('relatedModuleName'))}</option>
                                {/if}
                            {/foreach}
                        </select>
                    </div></br>
                </div>
                <div class="control-group">
                    <div class="control-label">{vtranslate('LBL_ASSIGNED_TO', $MODULE)}</div>
                    <div class="controls">
                        {assign var=ALL_ACTIVEUSER_LIST value=$USER_MODEL->getAccessibleUsers()}
                        {assign var=ALL_ACTIVEGROUP_LIST value=$USER_MODEL->getAccessibleGroups()}
                        {assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
                        {assign var=ACCESSIBLE_USER_LIST value=$USER_MODEL->getAccessibleUsersForModule($MODULE)}
                        {assign var=ACCESSIBLE_GROUP_LIST value=$USER_MODEL->getAccessibleGroupForModule($MODULE)}

                        <select class="chzn-select" data-validation-engine="validate[ required]" name="transferOwnerId" id="transferOwnerId">
                            <optgroup label="{vtranslate('LBL_USERS')}">
                                {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
                                    <option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}' {if $FIELD_VALUE eq $OWNER_ID} selected {/if}
                                {if array_key_exists($OWNER_ID, $ACCESSIBLE_USER_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if}
                                data-userId="{$CURRENT_USER_ID}">
                                {$OWNER_NAME}
                            </option>
                        {/foreach}
                    </optgroup>
                    <optgroup label="{vtranslate('LBL_GROUPS')}">
                        {foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
                            <option value="{$OWNER_ID}" data-picklistvalue= '{$OWNER_NAME}'
                        {if array_key_exists($OWNER_ID, $ACCESSIBLE_GROUP_LIST)} data-recordaccess=true {else} data-recordaccess=false {/if} >
                        {$OWNER_NAME}
                    </option>
                {/foreach}
            </optgroup>
        </select>
    </div>
</div>
</div>
{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
</form>
</div>
{/strip}
