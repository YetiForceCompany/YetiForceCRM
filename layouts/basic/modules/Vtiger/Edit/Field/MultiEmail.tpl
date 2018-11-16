{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var="FIELD_INFO" value=\App\Purifier::encodeHtml(\App\Json::encode($FIELD_MODEL->getFieldInfo()))}
	{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'),$RECORD)}
	{if !empty($FIELD_MODEL->get('fieldvalue'))}
		{assign var=NOT_DISPLAY_LIST_VALUES value=$FIELD_VALUE}
	{else}
		{assign var=NOT_DISPLAY_LIST_VALUES value=[['e'=>'']]}
	{/if}
	<div class="tpl-Base-Edit-Field-MultiEmail d-flex align-items-center js-multi-email">
		<input name="{$FIELD_MODEL->getFieldName()}" value="{if $FIELD_MODEL->get('fieldvalue')}{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}{/if}" type="hidden" class="js-hidden-email" data-js="value"/>
		<button type="button" class="btn btn-outline-success border mr-2 mb-2 h-100 js-add-item"
				data-js="click">
			<span class="fas fa-plus" title="{\App\Language::translate('LBL_ADD', $MODULE)}"></span>
		</button>
		<div class="form-inline">
			{counter start=0 skip=1 print=false}
			{foreach item=ITEM from=$NOT_DISPLAY_LIST_VALUES}
				{include file=\App\Layout::getTemplatePath('Edit/Field/MultiEmailValue.tpl', $MODULE)}
			{/foreach}
		</div>
	</div>
{/strip}
