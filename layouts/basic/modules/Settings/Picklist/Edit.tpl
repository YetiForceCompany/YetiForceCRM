{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Picklist-Edit -->
	<div class="modal-body js-modal-body" data-js="container">
		<form id="renameItemForm" class="form-horizontal validateForm" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="action" value="SaveAjax" />
			<input type="hidden" name="mode" value="edit" />
			<input type="hidden" name="picklistName" value="{$FIELD_MODEL->getName()}" />
			{if $PICKLIST_VALUE}
				<input type="hidden" name="primaryKeyId" value="{$PICKLIST_VALUE['picklistValueId']}" />
				<input type="hidden" name="picklist_valueid" value="{$PICKLIST_VALUE['picklist_valueid']}" />
			{/if}
			{foreach from=$ITEM_MODEL->getEditFields() item=$FIELD_MODEL key=key name=name}
				<div class="form-group row align-items-center">
					<label class="u-font-weight-600 col-lg-3 textAlignRight align-self-center col-form-label">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
						{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
						{if $FIELD_MODEL->get('tooltip')}
							<div class="js-popover-tooltip ml-1 d-inline my-auto u-h-fit u-cursor-pointer" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate($FIELD_MODEL->get('tooltip'), $QUALIFIED_MODULE)}">
								<span class="fas fa-info-circle"></span>
							</div>
						{/if}:
					</label>
					<div class="fieldValue col-lg-9">
						{if $FIELD_MODEL->isEditableReadOnly()}
							<input type="text" disabled="disabled" class="form-control-plaintext" value="{\App\Purifier::encodeHtml($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), false, false, true))}" />
						{else}
							{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE MODULE_NAME=$QUALIFIED_MODULE RECORD=null}
						{/if}
					</div>
				</div>
			{/foreach}
		</form>
	</div>
	<!-- /tpl-Settings-Picklist-Edit -->
{/strip}
