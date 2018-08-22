{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	<div class="form-inline tpl-Edit-Field-MultiEmail js-multi-email">
		{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
		{if !empty($FIELD_MODEL->get('fieldvalue'))}
			{assign var=NOT_DISPLAY_LIST_VALUES value=$FIELD_VALUE}
		{else}
			{assign var=NOT_DISPLAY_LIST_VALUES value=[]}
		{/if}
		<input type="hidden" name="{$FIELD_MODEL->getFieldName()}" value=""/>
		<button class="btn btn-success mr-2 mb-1 js-add-item" data-js="click" type="button" id="button-addon1">
			<span class="fas fa-plus"></span>
		</button>
		{counter start=0 skip=1 print=false}
		{foreach item=ITEM from=$NOT_DISPLAY_LIST_VALUES}
			{include file=\App\Layout::getTemplatePath('Edit/Field/MultiEmailValue.tpl', 'Vtiger')}
		{/foreach}
	</div>
{/strip}
