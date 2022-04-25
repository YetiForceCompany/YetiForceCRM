{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-SMSNotifier-Edit -->
	<div class="modal-body js-modal-body" data-js="container">
		<form class="validateForm">
			<input type="hidden" name="module" value="{$RECORD_MODEL->getModule()->getName()}" />
			<input type="hidden" name="parent" value="{$PARENT_MODULE}" />
			<input type="hidden" name="providertype" value="{$PROVIDER->getName()}" />
			<input type="hidden" id="record" name="record" value="{$RECORD_MODEL->getId()}">
			{foreach from=$RECORD_MODEL->getEditFields() item=FIELD_MODEL key=FIELD_NAME name=fields}
				<div class="form-group form-row">
					<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
						{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
					</label>
					<div class="col-md-8 fieldValue">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=false}
					</div>
				</div>
			{/foreach}
		</form>
	</div>
	<!-- /tpl-Settings-SMSNotifier-Edit -->
{/strip}
