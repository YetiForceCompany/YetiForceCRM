{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
   * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
{strip}
{* To check current user has permission to make outbound call. If so, make all the detail view phone fields as links to call *}
{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
{if $FIELD_VALUE}
	{assign var=PERMISSION value=Vtiger_Mobile_Model::checkPermissionForOutgoingCall()}
	{if $PERMISSION}
		{assign var=PHONE_FIELD_VALUE value=$FIELD_VALUE}
		{assign var=PHONE_NUMBER value=$PHONE_FIELD_VALUE|regex_replace:"/[-()\s]/":""}
		{assign var=CALLTOUSERS value=Vtiger_Mobile_Model::getPrivilegesUsers()}
		<a class="phoneField" data-phoneNumber="{$PHONE_NUMBER}" record="{$RECORD->getId()}" onclick="Vtiger_Mobile_Js.registerOutboundCall('{$PHONE_NUMBER}',{$RECORD->getId()})">{$FIELD_MODEL->get('fieldvalue')}</a>
		{if $CALLTOUSERS}
			<a class="btn btn-xs btn-default btnNoFastEdit" onclick="Vtiger_Mobile_Js.registerOutboundCallToUser(this,'{$PHONE_NUMBER}',{$RECORD->getId()})" data-placement="right" data-original-title="{vtranslate('LBL_SELECT_USER_TO_CALL',$MODULE)}" data-content='
			<select class="select sesectedUser" name="sesectedUser">
				{foreach from=$CALLTOUSERS item=item key=key}
					<option value="{$key}">{$item}</option>
				{/foreach}
			</select><br /><a class="btn btn-success popoverCallOK">{vtranslate('LBL_BTN_CALL',$MODULE)}</a>   <a class="btn btn-inverse popoverCallCancel">{vtranslate('LBL_CANCEL',$MODULE)}</a>
			' data-trigger="manual"><i class="glyphicon glyphicon-user"></i></a>
		{/if}
	{else}
		{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD)}
	{/if}
{/if}
{/strip}
