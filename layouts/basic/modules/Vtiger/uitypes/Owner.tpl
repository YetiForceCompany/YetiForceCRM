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
	{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{if $FIELD_MODEL->get('uitype') eq '53'}
		{assign var=ROLE_RECORD_MODEL value=$USER_MODEL->getRoleDetail()}
		{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance($MODULE)->getAccessibleUsers('',$FIELD_MODEL->getFieldDataType())}
		{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance($MODULE)->getAccessibleGroups('',$FIELD_MODEL->getFieldDataType())}
		{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
		{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
		{if $FIELD_VALUE eq '' && $VIEW neq 'MassEdit'}
			{assign var=FIELD_VALUE value=$CURRENT_USER_ID}
		{/if}
		{assign var=FOUND_SELECT_VALUE value=0}
		<select class="select2 form-control {$ASSIGNED_USER_ID}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}" data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-name="{$ASSIGNED_USER_ID}" name="{$ASSIGNED_USER_ID}" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={\App\Json::encode($SPECIAL_VALIDATOR)}{/if} {if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly"{/if} {if $USER_MODEL->isAdminUser() == false && $ROLE_RECORD_MODEL->get('changeowner') == 0}readonly="readonly"{/if} 
				{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')} 
					data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&type=Edit" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
				{/if}>
			{if !AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
				{if $VIEW eq 'MassEdit'}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
				<optgroup label="{vtranslate('LBL_USERS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
						<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" {if $FIELD_VALUE eq $OWNER_ID} selected {assign var=FOUND_SELECT_VALUE value=1}{/if}
								data-userId="{$CURRENT_USER_ID}">
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
				<optgroup label="{vtranslate('LBL_GROUPS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
						<option value="{$OWNER_ID}" data-picklistvalue="{$OWNER_NAME}" {if $FIELD_MODEL->get('fieldvalue') eq $OWNER_ID} selected {assign var=FOUND_SELECT_VALUE value=1}{/if}>
							{vtranslate($OWNER_NAME, $MODULE)}
						</option>
					{/foreach}
				</optgroup>
				{if !empty($FIELD_VALUE) && $FOUND_SELECT_VALUE == 0 && !($ROLE_RECORD_MODEL->get('allowassignedrecordsto') == 5 && count($ALL_ACTIVEGROUP_LIST) != 0 && $FIELD_MODEL->get('fieldvalue') == '')}
					{assign var=OWNER_NAME value=\App\Fields\Owner::getLabel($FIELD_VALUE)}
					<option value="{$FIELD_VALUE}" data-picklistvalue="{$OWNER_NAME}" selected data-userId="{$CURRENT_USER_ID}">
						{$OWNER_NAME}
					</option>
				{/if}
			{else}
				{if isset($ALL_ACTIVEUSER_LIST[$FIELD_VALUE])}
					{assign var=OWNER_NAME value=$ALL_ACTIVEUSER_LIST[$FIELD_VALUE]}
				{elseif isset($ALL_ACTIVEGROUP_LIST[$FIELD_VALUE])}
					{assign var=OWNER_NAME value=$ALL_ACTIVEGROUP_LIST[$FIELD_VALUE]}
				{else}
					{assign var=OWNER_NAME value=vtlib\Functions::getOwnerRecordLabel($FIELD_VALUE)}
				{/if}
				<option value="{$FIELD_VALUE}" selected data-picklistvalue="{$OWNER_NAME}" selected data-userId="{$CURRENT_USER_ID}">
					{$OWNER_NAME}
				</option>
			{/if}
		</select>
	{/if}
{/strip}
