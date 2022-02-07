{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-WebserviceUsers-Edit -->
	<form class="form-horizontal validateForm" id="editForm">
		<input type="hidden" name="module" value="WebserviceUsers">
		<input type="hidden" name="parent" value="Settings" />
		<input type="hidden" name="action" value="SaveAjax" />
		<input type="hidden" id="typeApi" name="typeApi" value="{$TYPE_API}">
		<input type="hidden" id="record" name="record" value="{$RECORD_MODEL->getId()}">
		<div class="modal-header">
			{if !$RECORD_MODEL->getId()}
				<h5 class="modal-title">
					<span class="fas fa-plus fa-sm mr-1"></span>{\App\Language::translate('LBL_CREATE_RECORD', $QUALIFIED_MODULE)}
				</h5>
			{else}
				<h5 class="modal-title">
					<span class="yfi yfi-full-editing-view fa-sm mr-1"></span>{\App\Language::translate('LBL_CREATE_RECORD', $QUALIFIED_MODULE)}
				</h5>
			{/if}
			<button class="btn btn-warning" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
			{foreach from=$RECORD_MODEL->getEditFields() item=LABEL key=FIELD_NAME name=fields}
				{assign var="FIELD_MODEL" value=$RECORD_MODEL->getFieldInstanceByName($FIELD_NAME)}
				{if $FIELD_MODEL}
					{assign var="FIELD_MODEL2" value= $FIELD_MODEL->set('fieldvalue',$RECORD_MODEL->get($FIELD_NAME))}
					<div class="form-group row">
						<label class="col-form-label col-md-3 u-text-small-bold text-right">
							{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}
							{if $FIELD_MODEL->isMandatory()}<span class="redColor">*</span>{/if}:
						</label>
						<div class="col-md-9 fieldValue">
							{include file=\App\Layout::getTemplatePath($FIELD_MODEL->getUITypeModel()->getTemplateName(), $QUALIFIED_MODULE) FIELD_MODEL=$FIELD_MODEL MODULE=$QUALIFIED_MODULE}
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
		{include file=App\Layout::getTemplatePath('Modals/Footer.tpl') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</form>
	<!-- /tpl-Settings-WebserviceUsers-Edit -->
{/strip}
