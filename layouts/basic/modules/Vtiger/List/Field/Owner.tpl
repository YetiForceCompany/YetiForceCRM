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
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->getName()}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUE value=explode('##', $SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUE value=[]}
	{/if}
	{assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUE)}
	{if !AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
		{if !empty($VIEWID) && AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST')}
			{assign var=USERS_GROUP_LIST value=\App\Fields\Owner::getInstance($MODULE)->getUsersAndGroupForModuleList($VIEWID)}
			{assign var=ALL_ACTIVEUSER_LIST value=$USERS_GROUP_LIST['users']}
			{assign var=ALL_ACTIVEGROUP_LIST value=$USERS_GROUP_LIST['group']}
		{else}
			{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance($MODULE)->getAccessibleUsers()}
			{if $ASSIGNED_USER_ID neq 'modifiedby'}
				{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance($MODULE)->getAccessibleGroups()}
			{else}
				{assign var=ALL_ACTIVEGROUP_LIST value=[]}
			{/if}
		{/if}
	{/if}
	<div class="tpl-List-Field-Owner picklistSearchField">
		<select class="select2noactive listSearchContributor form-control {$ASSIGNED_USER_ID}"
				title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" name="{$ASSIGNED_USER_ID}"
				multiple="multiple"
				{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
					data-ajax-search="1" data-ajax-url="index.php?module={$MODULE}&action=Fields&mode=getOwners&fieldName={$ASSIGNED_USER_ID}" data-minimum-input="{AppConfig::performance('OWNER_MINIMUM_INPUT_LENGTH')}"{' '}
				{/if}
				data-fieldinfo='{$FIELD_INFO|escape}'
				{if !empty($FIELD_MODEL->get('source_field_name'))}
					data-source-field-name="{$FIELD_MODEL->get('source_field_name')}"
					data-module-name="{$FIELD_MODEL->getModuleName()}"
				{/if}
				>
			{if AppConfig::performance('SEARCH_OWNERS_BY_AJAX')}
				{foreach from=$SEARCH_VALUES item=OWNER_ID}
					<option value="{$OWNER_ID}" selected>{\App\Fields\Owner::getLabel($OWNER_ID)}</option>
				{/foreach}
			{else}
				{if count($ALL_ACTIVEUSER_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_USERS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
							<option value="{$OWNER_ID}"
									data-picklistvalue="{$OWNER_NAME}" {if in_array(trim(App\Purifier::decodeHtml($OWNER_NAME)),$SEARCH_VALUES) || in_array($OWNER_ID, $SEARCH_VALUES)} selected {/if}
									data-userId="{$OWNER_ID}">
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
				{if count($ALL_ACTIVEGROUP_LIST) gt 0}
					<optgroup label="{\App\Language::translate('LBL_GROUPS')}">
						{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
							<option value="{$OWNER_ID}"
									data-picklistvalue="{$OWNER_NAME}" {if in_array(trim(App\Purifier::decodeHtml($OWNER_NAME)),$SEARCH_VALUES) || in_array($OWNER_ID, $SEARCH_VALUES)} selected {/if} >
								{$OWNER_NAME}
							</option>
						{/foreach}
					</optgroup>
				{/if}
			{/if}
		</select>
	</div>
{/strip}
