{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Modals-RecordAddsTemplates -->
	<div class="modal-body js-modal-body" data-js="container">
		<input type="hidden" name="recordAddsType" value="{$RECORD_TEMPLATE}" />
		{foreach from=$MODULE_FORM item=FORM_MODULE_NAME}
			<form class="form-horizontal js-record-template" data-js="container">
				<input type="hidden" name="module" value="{$FORM_MODULE_NAME}" />
				{include file=\App\Layout::getTemplatePath('EditBlocks.tpl') MODULE_NAME=$FORM_MODULE_NAME RECORD_STRUCTURE=$RECORD_STRUCTURE[$FORM_MODULE_NAME]}
			</form>
		{/foreach}
	</div>
	<!-- /tpl-Base-Modals-RecordAddsTemplates -->
{/strip}
