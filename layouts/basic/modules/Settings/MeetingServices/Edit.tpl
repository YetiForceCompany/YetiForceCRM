{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-MeetingServices-Edit -->
	<form class="form-horizontal validateForm sendByAjax" id="editForm">
		<input type="hidden" name="module" value="{$MODULE_NAME}">
		<input type="hidden" name="parent" value="Settings"/>
		<input type="hidden" name="action" value="SaveAjax"/>
		<input type="hidden" id="record" name="record" value="{$RECORD_MODEL->getId()}">
		<div class="modal-body">
			{foreach from=$RECORD_MODEL->getModule()->getFormFields() item=FIELD_INFO key=FIELD_NAME name=fields}
				{assign var="FIELD_MODEL" value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME)->set('fieldvalue',$RECORD_MODEL->get($FIELD_NAME))}
				<div class="form-group row">
					<label class="col-form-label col-md-3 u-text-small-bold text-right">
						{\App\Language::translate($FIELD_MODEL->get('label'), $QUALIFIED_MODULE)}
						{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}:
					</label>
					<div class="col-md-9 fieldValue">
						{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE RECORD=null}
					</div>
				</div>
			{/foreach}
		</div>
		{include file=App\Layout::getTemplatePath('Modals/Footer.tpl') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</form>
	<!-- /tpl-Settings-MeetingServices-Edit -->
{/strip}
