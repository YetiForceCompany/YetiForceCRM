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
	{assign var=FIELD_INFO value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=FIELD_VALUE value=$SEARCH_INFO['searchValue']}
	{else}
		{assign var=FIELD_VALUE value=""}
	{/if}
	{assign var="TIME_FORMAT" value=$USER_MODEL->get('hour_format')}
	<div class="tpl-List-Field-Time picklistSearchField">
		<input type="text" data-format="{$TIME_FORMAT}" class="form-control clockPicker listSearchContributor"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}" value="{$FIELD_VALUE}"
			name="{$FIELD_MODEL->getFieldName()}" data-fieldinfo='{$FIELD_INFO}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
				{/if} autocomplete="off" {if !$FIELD_MODEL->isActiveSearchView()}disabled{/if} />
		</div>
	{/strip}
