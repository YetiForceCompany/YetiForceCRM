{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
{if !empty($FIELD_MODEL->get('fieldvalue'))}
	{assign var=FIELD_VALUE value=$FIELD_MODEL->getEditViewDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD)}
{else}
	{assign var=FIELD_VALUE value=[['e' => '', 'o' => 0]]}
{/if}
{assign var=ITEMS_COUNT value=count($FIELD_VALUE)}
<div class="js-multiemail tpl-Base-Edit-Field-MultiEmail">
	<input type="hidden" name="{$FIELD_MODEL->getFieldName()}"
			value="{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}"
			class="js-multiemail-value" />
	<div class="row">
		<div class="js-multiemail-add col-lg-1 btn btn-outline-success border p-1 mb-3"
				title="{\App\Language::translate('LBL_ADD', $MODULE)}">
			<span class="fas fa-plus"></span>
		</div>
		<div class="js-multiemail-items col-lg-11">
			{foreach from=$FIELD_VALUE item=ITEM name=multiemailloop}
				{include file=\App\Layout::getTemplatePath('Edit/Field/MultiEmailValue.tpl', $MODULE) ITEMS_COUNT=$ITEMS_COUNT}
			{/foreach}
		</div>
	</div>
</div>
{/strip}
