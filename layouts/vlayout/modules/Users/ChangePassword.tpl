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
    <div id="massEditContainer" class='modelContainer'>
        <div class="modal-header contentsBackground">
            <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
            <h3 id="massEditHeader">{vtranslate('LBL_CHANGE_PASSWORD', $MODULE)}</h3>
        </div>
        <form class="form-horizontal" id="changePassword" name="changePassword" method="post" action="index.php">
            <input type="hidden" name="module" value="{$MODULE}" />
            <input type="hidden" name="userid" value="{$USERID}" />
            <div name='massEditContent'>
                <div class="modal-body">

                    <div class="control-group">
                        {if !$CURRENT_USER_MODEL->isAdminUser()}
                            <label class="control-label">{vtranslate('LBL_OLD_PASSWORD', $MODULE)}</label>
                            <div class="controls">
                                <input type="password" name="old_password" data-validation-engine="validate[required]"/>
                            </div>
                        {/if}
                    </div>

                    <div class="control-group">
                        <label class="control-label">{vtranslate('LBL_NEW_PASSWORD', $MODULE)}</label>
                        <div class="controls">
                            <input type="password" name="new_password" data-validation-engine="validate[required]"/>
                        </div>
                    </div>

                    <div class="control-group">
                        <label class="control-label">{vtranslate('LBL_CONFIRM_PASSWORD', $MODULE)}</label>
                        <div class="controls">
                            <input type="password" name="confirm_password" data-validation-engine="validate[required]"/>
                        </div>
                    </div>

                </div>
            </div>
    {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
</form>
</div>
{/strip}
