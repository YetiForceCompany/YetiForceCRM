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
    <div class='modelContainer'>
	<div class="modal-header contentsBackground">
	    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	    <h3>
            {if $RECORD}
                {vtranslate('LBL_EDIT_BOOKMARK', $MODULE)}
            {else}
                {vtranslate('LBL_ADD_NEW_BOOKMARK', $MODULE)}
            {/if}
        </h3>
	</div>
    <form class="form-horizontal" id="saveBookmark" method="post" action="index.php">
        <input type="hidden" name="record" value="{$RECORD}" />
        <input type="hidden" name="module" value="{$MODULE}" />
        <input type="hidden" name="action" value="SaveAjax" />
        <div class="modal-body tabbable">
            <div class="control-label">
                <label>
                    <span class="redColor">*</span>&nbsp;{vtranslate('LBL_BOOKMARK_NAME',$MODULE)}
                </label>
            </div>
            <div class="controls">
                <input type="text" name="bookmarkName" id="bookmarkName"{if $RECORD} value="{$BOOKMARK_NAME}" {/if} placeholder="{vtranslate('LBL_ENTER_BOOKMARK_NAME', $MODULE)}" />
            </div>
            <br>
            <div class="control-label">
                <label>
                    <span class="redColor">*</span>&nbsp;{vtranslate('LBL_BOOKMARK_URL',$MODULE)}
                </label>
            </div>
            <div class="controls">
                <input type="text" name="bookmarkUrl" id="bookmarkUrl"{if $RECORD} value="{$BOOKMARK_URL}" {/if} placeholder="{vtranslate('LBL_ENTER_URL', $MODULE)}" />
            </div>
        </div>
        {include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
    </form>
    </div>
{/strip}