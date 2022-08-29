{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-LayoutEditor-inventoryTypes-Base -->
	{foreach from=$FIELD_INSTANCE->getConfigFields() item=$FIELD_MODEL}
		<div class="form-group row align-items-center">
			<label class="u-font-weight-600 col-md-5 textAlignRight align-self-center col-form-label">
				{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
				{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
				{if $FIELD_MODEL->get('tooltip')}
					<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
						<span class="fas fa-info-circle"></span>
					</div>
				{/if}:
			</label>
			<div class="fieldValue col-md-7">
				{if $FIELD_MODEL->isEditableReadOnly()}
					<input type="text" disabled="disabled" class="form-control-plaintext" value="{\App\Purifier::encodeHtml($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), false, false, true))}" />
				{else}
					{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=null}
				{/if}
			</div>
		</div>
	{/foreach}
	<!-- /tpl-Settings-LayoutEditor-inventoryTypes-Base -->
{/strip}
