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
	<!-- tpl-Base-List-Field-Date -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=$SEARCH_INFO['searchValue']}
	{else}
		{assign var=SEARCH_VALUES value=''}
	{/if}
	<div class="picklistSearchField u-min-w-150pxr">
		<input name="{$FIELD_MODEL->getName()}" class="listSearchContributor dateRangeField form-control datepicker"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModule()->getName())}"
			type="text" value="{$SEARCH_VALUES}" data-date-format="{$dateFormat}" data-calendar-type="range"
			data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}"
				data-module-name="{$FIELD_MODEL->getModuleName()}"
				{/if} autocomplete="off" {if !$FIELD_MODEL->isActiveSearchView()}disabled{/if} />
		</div>
		<!-- /tpl-Base-List-Field-Date -->
	{/strip}
