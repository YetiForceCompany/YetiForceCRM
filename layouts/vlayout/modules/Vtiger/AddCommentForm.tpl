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

{* Change to this also refer: RecentComments.tpl *}
{assign var="COMMENT_TEXTAREA_DEFAULT_ROWS" value="2"}

<div id="addCommentContainer" class='modelContainer'>
	<div class="modal-header contentsBackground">
        <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
		<h3>{vtranslate('LBL_ADDING_COMMENT', $MODULE)}</h3>
	</div>
	<form class="form-horizontal" id="massSave" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
		<input type="hidden" name="action" value="MassSaveAjax" />
		<input type="hidden" name="viewname" value="{$CVID}" />
		<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
		<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
        <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
        <input type="hidden" name="operator" value="{$OPERATOR}" />
        <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
        <input type="hidden" name="search_params" value='{ZEND_JSON::encode($SEARCH_PARAMS)}' />

		<div class="modal-body tabbable">
			<textarea class="input-xxlarge" name="commentcontent" id="commentcontent" rows="{$COMMENT_TEXTAREA_DEFAULT_ROWS}" placeholder="{vtranslate('LBL_WRITE_YOUR_COMMENT_HERE', $MODULE)}..."></textarea>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
</div>
{/strip}