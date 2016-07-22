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
    {assign var=FIELD_INFO value=\includes\utils\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var=ASSIGNED_USER_ID value=$FIELD_MODEL->get('name')}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUE value=explode(',',$SEARCH_INFO['searchValue'])}
	{else}
		{assign var=SEARCH_VALUE value=[]}
	{/if}
    {assign var=SEARCH_VALUES value=array_map("trim",$SEARCH_VALUE)}

	{if $VIEWID && AppConfig::performance('SEARCH_SHOW_OWNER_ONLY_IN_LIST')}
		{assign var=USERS_GROUP_LIST value=\includes\fields\Owner::getInstance($MODULE)->getUsersAndGroupForModuleList($VIEWID)}
		{assign var=ALL_ACTIVEUSER_LIST value=$USERS_GROUP_LIST['users']}
		{assign var=ALL_ACTIVEGROUP_LIST value=$USERS_GROUP_LIST['group']}
	{else}
		{assign var=ALL_ACTIVEUSER_LIST value=\includes\fields\Owner::getInstance()->getAccessibleUsers()}
		{if $ASSIGNED_USER_ID neq 'modifiedby'}
			{assign var=ALL_ACTIVEGROUP_LIST value=\includes\fields\Owner::getInstance()->getAccessibleGroups()}
		{else}
			{assign var=ALL_ACTIVEGROUP_LIST value=array()}
		{/if}
	{/if}
	<div class="picklistSearchField">
		<select class="select2noactive listSearchContributor form-control {$ASSIGNED_USER_ID}" title="{vtranslate($FIELD_MODEL->get('label'), $MODULE)}"  name="{$ASSIGNED_USER_ID}" multiple data-fieldinfo='{$FIELD_INFO|escape}'>
			{if count($ALL_ACTIVEUSER_LIST) gt 0}
				<optgroup label="{vtranslate('LBL_USERS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
						<option value="{$OWNER_NAME}" data-picklistvalue="{$OWNER_NAME}" {if in_array(trim(decode_html($OWNER_NAME)),$SEARCH_VALUES)} selected {/if} data-userId="{$OWNER_ID}">
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
			{/if}
			{if count($ALL_ACTIVEGROUP_LIST) gt 0}
				<optgroup label="{vtranslate('LBL_GROUPS')}">
					{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEGROUP_LIST}
						<option value="{$OWNER_NAME}" data-picklistvalue="{$OWNER_NAME}" {if in_array(trim($OWNER_NAME),$SEARCH_VALUES)} selected {/if} >
							{$OWNER_NAME}
						</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>
	</div>
{/strip}
