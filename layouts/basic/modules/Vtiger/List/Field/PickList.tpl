{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<!-- tpl-Base-List-Field-PickList -->
	{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues(true)}
	{assign var=FIELD_INFO value=\App\Json::encode($FIELD_MODEL->getFieldInfo())}
	{if isset($SEARCH_INFO['searchValue'])}
		{assign var=SEARCH_VALUES value=explode('##', \App\Purifier::decodeHtml($SEARCH_INFO['searchValue']))}
	{else}
		{assign var=SEARCH_VALUES value=[]}
	{/if}
	<div class="picklistSearchField input-group {if isset($CLASS_SIZE)}{$CLASS_SIZE}{/if} u-min-w-150pxr">
		<select class="select2 listSearchContributor form-control" name="{$FIELD_MODEL->getName()}" multiple="multiple"
			{if !$FIELD_MODEL->isActiveSearchView()}disabled="disabled" data-placeholder=" " {/if}
			title="{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $FIELD_MODEL->getModule()->getName())}"
			data-fieldinfo='{$FIELD_INFO|escape}'
			{if !empty($FIELD_MODEL->get('source_field_name'))}
				data-source-field-name="{$FIELD_MODEL->get('source_field_name')}" data-module-name="{$FIELD_MODEL->getModuleName()}"
			{/if}>
			{foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_VALUES}
				<option value="{\App\Purifier::encodeHtml($PICKLIST_KEY)}" {if in_array($PICKLIST_KEY,$SEARCH_VALUES) && ($PICKLIST_KEY neq "") } selected{/if}>
					{\App\Purifier::encodeHtml($PICKLIST_LABEL)}
				</option>
			{/foreach}
		</select>
	</div>
	<!-- /tpl-Base-List-Field-PickList -->
{/strip}
