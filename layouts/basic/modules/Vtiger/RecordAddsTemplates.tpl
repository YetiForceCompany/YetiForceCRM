{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-RecordAddsTemplates -->
<div class="modal-body js-modal-body" data-js="container">
	{foreach from=$MODULE_FORM item=FORM_MODULE_NAME}
		<form class="form-horizontal js-record-template" data-js="container">
			<input type="hidden" name="module" value="{$FORM_MODULE_NAME}"/>
			<input type="hidden" name="recordAddsType" value="{$RECORD_TEMPLATE}"/>
			{include file=\App\Layout::getTemplatePath('EditBlocks.tpl') RECORD_STRUCTURE=$RECORD_STRUCTURE[$FORM_MODULE_NAME]}
		</form>
	{/foreach}
</div>
<!-- /tpl-Base-RecordAddsTemplates -->
{/strip}
