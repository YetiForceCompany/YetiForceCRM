{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Base-Edit-Modal -->
	<form class="form-horizontal validateForm sendByAjax" id="editForm">
		<input type="hidden" name="module" value="{$MODULE_NAME}">
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="action" value="SaveAjax" />
		<input type="hidden" name="mode" value="save" />
		<input type="hidden" id="record" name="record" value="{$RECORD_MODEL->getId()}">
		<div class="modal-body">
			{foreach from=$RECORD_MODEL->getModule()->getFormFields() item=FIELD_INFO key=FIELD_NAME name=fields}
				{assign var=FIELD_MODEL value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME)}
				<div class="form-group row mb-2">
					<label class="col-form-label col-md-4 u-text-small-bold text-right">
						{\App\Language::translate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}
						{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
						{if !empty($FIELD_INFO['tooltip'])}
							<span class="fas fa-info-circle u-cursor-pointer text-primary ml-1 js-popover-tooltip" data-js="popover" data-content="{\App\Purifier::encodeHtml(App\Language::translate($FIELD_MODEL->get('label')|cat:"_TOOLTIP", $QUALIFIED_MODULE))}" data-placement="top"></span>
						{/if}
					</label>
					<div class="col-md-8 fieldValue">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null}
					</div>
				</div>
			{/foreach}
		</div>
		{include file=App\Layout::getTemplatePath('Modals/Footer.tpl')}
	</form>
	<!-- /tpl-Settings-Base-Edit-Modal -->
{/strip}
