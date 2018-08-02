{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<form class="tpl-Settings-SMSNotifier-Edit form-horizontal validateForm" id="editForm">
		<input type="hidden" id="record" name="record" value="{$RECORD_MODEL->getId()}">
		<div class="modal-header">
			<h5 class="modal-title u-text-ellipsis">
				{if !$RECORD_MODEL->getId()}
					<span class="fa fa-plus u-mr-5px mt-2"></span>{\App\Language::translate("LBL_ADD_CONFIGURATION", $QUALIFIED_MODULE)}
				{else}
					<span class="fa fa-edit u-mr-5px mt-2"></span>{\App\Language::translate("LBL_EDIT_RECORD", $QUALIFIED_MODULE)}
				{/if}
			</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body text-center">
			<div class="fieldsContainer">
				{foreach from=$RECORD_MODEL->getEditFields() item=LABEL key=FIELD_NAME name=fields}
					{assign var="FIELD_MODEL" value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME)->set('fieldvalue',$RECORD_MODEL->get($FIELD_NAME))}
					<div class="form-group form-row">
						<label class="col-form-label col-md-4 u-text-small-bold text-left text-md-right">
							{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}
							{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
						</label>
						<div class="col-md-8 fieldValue">
							{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE}
						</div>
					</div>
				{/foreach}
				{if $RECORD_MODEL->getId()}
					{assign var="PROVIDER" value=$RECORD_MODEL->getProviderInstance()}
					{foreach from=$PROVIDER->getSettingsEditFieldsModel() item=FIELD_MODEL name=fields}
						{assign var="FIELD_MODEL" value=$FIELD_MODEL->set('fieldvalue',$RECORD_MODEL->get($FIELD_NAME))}
						<div class="form-group form-row" data-provider="{$PROVIDER->getName()}">
							<label class="col-form-label col-md-4 u-text-small-bold text-right">
								{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
								{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}
							</label>
							<div class="col-md-8 fieldValue">
								{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE}
							</div>
						</div>
					{/foreach}
				{/if}
			</div>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-success"><span class="fa fa-check u-mr-5px"></span>{\App\Language::translate('BTN_SAVE', $QUALIFIED_MODULE)}</button>
			<button type="button" class="btn btn-warning dismiss" data-dismiss="modal"><span class="fa fa-times u-mr-5px"></span>{\App\Language::translate('BTN_CLOSE', $QUALIFIED_MODULE)}</button>
		</div>
	</form>
	<div class="providersFields d-none">
		{foreach from=$PROVIDERS item=PROVIDER}
			{foreach from=$PROVIDER->getSettingsEditFieldsModel() item=FIELD_MODEL name=fields}
				<div class="form-group form-row text-right" data-provider="{$PROVIDER->getName()}">
					<label class="col-form-label col-md-4 u-text-small-bold">
						{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $QUALIFIED_MODULE)}
						{if $FIELD_MODEL->isMandatory()}<span class="redColor"> *</span>{/if}:
					</label>
					<div class="col-md-8 fieldValue">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE}
					</div>
				</div>
			{/foreach}
		{/foreach}
	</div>
{/strip}
