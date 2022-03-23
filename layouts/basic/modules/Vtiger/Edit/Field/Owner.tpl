{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-Edit-Field-Owner -->
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var=SPECIAL_VALIDATOR value=$FIELD_MODEL->getValidator()}
	{if $FIELD_MODEL->getUIType() eq '53'}
		{assign var=ROLE_RECORD_MODEL value=$USER_MODEL->getRoleDetail()}
		{assign var=OWNER_FIELD value=\App\Fields\Owner::getInstance($MODULE_NAME)}
		{assign var=ALL_ACTIVEUSER_LIST value=$OWNER_FIELD->getAccessibleUsers('',$FIELD_MODEL->getFieldDataType())}
		{assign var=ALL_ACTIVEGROUP_LIST value=$OWNER_FIELD->getAccessibleGroups('',$FIELD_MODEL->getFieldDataType(), true)}
		{assign var=FIELD_NAME value=$FIELD_MODEL->getName()}
		{assign var=CURRENT_USER_ID value=$USER_MODEL->get('id')}
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
		{if $FIELD_VALUE eq '' && isset($VIEW) && $VIEW neq 'MassEdit'}
			{assign var=FIELD_VALUE value=$CURRENT_USER_ID}
		{/if}
		{assign var=SHOW_FAVORITE_OWNERS value=App\Config::module('Users','FAVORITE_OWNERS') && $CURRENT_USER_ID === \App\User::getCurrentUserRealId()}
		{assign var=FAVORITE_OWNERS value=[]}
		{function OPTGRUOP BLOCK_NAME='' OWNERS=[] ACTIVE='inactive'}
			{if $OWNERS}
				<optgroup label="{\App\Language::translate($BLOCK_NAME)}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$OWNERS}
						<option value="{$OWNER_ID}"
							data-picklistvalue="{$OWNER_NAME}" {if $FIELD_VALUE eq $OWNER_ID} selected="selected" {/if}
							data-userId="{$CURRENT_USER_ID}"
							{if $SHOW_FAVORITE_OWNERS}
								data-url="index.php?module={$MODULE_NAME}&action=Fields&mode=changeFavoriteOwner&fieldName={$FIELD_NAME}&owner={$OWNER_ID}" data-state="{$ACTIVE}" data-icon-active="fas fa-star" data-icon-inactive="far fa-star" data-template="<span class='c-option-template--state-icons'>{$OWNER_NAME}<span class='js-select-option-actions o-filter-actions noWrap float-right'><span data-js='click|class:icons' class='mx-1 js-select-option-event{if $ACTIVE == 'active'} fas fa-star{else} far fa-star{/if}'></span></span></span>"
							{/if}>
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
			{/if}
		{/function}
		<div class="w-100">
			<select class="select2 form-control {$FIELD_NAME}" tabindex="{$FIELD_MODEL->getTabIndex()}" title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
				data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
				data-name="{$FIELD_NAME}" name="{$FIELD_NAME}" data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{\App\Purifier::encodeHtml(\App\Json::encode($SPECIAL_VALIDATOR))}' {/if}
				{if $FIELD_MODEL->isEditableReadOnly()}readonly="readonly" {/if} {if $USER_MODEL->isAdminUser() == false && $ROLE_RECORD_MODEL->get('changeowner') == 0}readonly="readonly" {/if}
				{if App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
					data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName={$FIELD_NAME}" data-minimum-input="{App\Config::performance('OWNER_MINIMUM_INPUT_LENGTH')}"
				{elseif $SHOW_FAVORITE_OWNERS}
					data-select-cb="registerIconsEvents" data-template-result="prependDataTemplate" data-template-selection="prependDataTemplate" data-select-cb="mycb"
				{/if}>
				{if !App\Config::performance('SEARCH_OWNERS_BY_AJAX')}
					{assign var=FOUND_SELECT_VALUE value=isset($ALL_ACTIVEUSER_LIST[$FIELD_VALUE]) || isset($ALL_ACTIVEGROUP_LIST[$FIELD_VALUE])}
					{if isset($VIEW) && $VIEW eq 'MassEdit'}
						<optgroup class="p-0">
							<option value="">{\App\Language::translate('LBL_SELECT_OPTION')}</option>
						</optgroup>
					{/if}
					{if App\Config::module('Users','FAVORITE_OWNERS')}
						{assign var=FAVORITE_OWNERS value=$OWNER_FIELD->getFavorites($FIELD_MODEL->getFieldDataType())}
						{if $FAVORITE_OWNERS}
							{assign var=FAVORITE_OWNERS value=array_intersect_key($ALL_ACTIVEUSER_LIST, $FAVORITE_OWNERS) + array_intersect_key($ALL_ACTIVEGROUP_LIST, $FAVORITE_OWNERS)}
							{assign var=ALL_ACTIVEUSER_LIST value=array_diff_key($ALL_ACTIVEUSER_LIST, $FAVORITE_OWNERS)}
							{assign var=ALL_ACTIVEGROUP_LIST value=array_diff_key($ALL_ACTIVEGROUP_LIST, $FAVORITE_OWNERS)}
							{OPTGRUOP BLOCK_NAME='LBL_FAVORITE_OWNERS' OWNERS=$FAVORITE_OWNERS ACTIVE='active'}
						{/if}
					{/if}
					{OPTGRUOP BLOCK_NAME='LBL_USERS' OWNERS=$ALL_ACTIVEUSER_LIST}
					{OPTGRUOP BLOCK_NAME='LBL_GROUPS' OWNERS=$ALL_ACTIVEGROUP_LIST}
					{if !empty($FIELD_VALUE) && $FOUND_SELECT_VALUE == 0 && !($ROLE_RECORD_MODEL->get('allowassignedrecordsto') == 5 && count($ALL_ACTIVEGROUP_LIST) != 0 && $FIELD_VALUE == '')}
						{assign var=OWNER_NAME value=\App\Fields\Owner::getLabel($FIELD_VALUE)}
						<option value="{$FIELD_VALUE}" data-picklistvalue="{$OWNER_NAME}" selected="selected"
							data-userId="{$CURRENT_USER_ID}">
							{$OWNER_NAME}
						</option>
					{/if}
				{else}
					{if isset($ALL_ACTIVEUSER_LIST[$FIELD_VALUE])}
						{assign var=OWNER_NAME value=$ALL_ACTIVEUSER_LIST[$FIELD_VALUE]}
					{elseif isset($ALL_ACTIVEGROUP_LIST[$FIELD_VALUE])}
						{assign var=OWNER_NAME value=$ALL_ACTIVEGROUP_LIST[$FIELD_VALUE]}
					{else}
						{assign var=OWNER_NAME value=\App\Fields\Owner::getLabel($FIELD_VALUE)}
					{/if}
					<option value="{$FIELD_VALUE}" selected
						data-picklistvalue="{\App\Purifier::encodeHtml($OWNER_NAME)}" selected
						data-userId="{$CURRENT_USER_ID}">
						{\App\Purifier::encodeHtml($OWNER_NAME)}
					</option>
				{/if}
			</select>
		</div>
	{/if}
	<!-- tpl-Base-Edit-Field-Owner -->
{/strip}
