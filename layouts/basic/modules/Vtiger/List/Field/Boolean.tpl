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
	<!-- tpl-Base-List-Field-Boolean -->
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=$SEARCH_INFO['searchValue']}
	{else}
		{assign var=SEARCH_VALUES value=''}
	{/if}
	<div class="boolenSearchField u-min-w-150pxr">
		<select name="{$FIELD_MODEL->getName()}" class="select2noactive select2 listSearchContributor"
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE)}"
			data-fieldinfo='{$FIELD_INFO|escape}' data-allow-clear="true"
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if}
			{if !$FIELD_MODEL->isActiveSearchView()}disabled{/if}>
			<option value="">{\App\Language::translate('LBL_SELECT_OPTION','Vtiger')}</option>
			<option value="1" {if $SEARCH_VALUES eq 1} selected{/if}>{\App\Language::translate('LBL_YES',$MODULE)}</option>
			<option value="0" {if $SEARCH_VALUES eq '0'} selected{/if}>{\App\Language::translate('LBL_NO',$MODULE)}</option>
		</select>
	</div>
	<!-- /tpl-Base-List-Field-Boolean -->
{/strip}
