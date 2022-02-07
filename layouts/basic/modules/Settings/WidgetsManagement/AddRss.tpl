{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div id="addRssWidgetContainer" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-plus mr-1"></span>
						{\App\Language::translate('LBL_ADD_RSS', $MODULE)}
					</h5>
					<button type="button" class="btn btn-primary addChannel ml-auto">{\App\Language::translate('LBL_ADD_CHANNEL', $MODULE)}</button>
					<button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal validateForm">
					<input type="hidden" name="module" value="{$MODULE_NAME}" />
					<input type="hidden" name="action" value="SaveAjax" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="mode" value="save" />
					<input type="hidden" name="blockId" />
					<input type="hidden" name="linkId" />
					<input type="hidden" name="width" value="4" />
					<input type="hidden" name="height" value="4" />
					<div class="formContainer">
						<div class="form-group row m-0 p-1">
							<label class="col-sm-4 col-form-label text-right">{\App\Language::translate('LBL_TITLE_WIDGET', $MODULE)}<span class="redColor">*</span> </label>
							<div class="col-sm-8 controls">
								<input type="text" name="title" class="form-control" data-validation-engine="validate[required]" />
							</div>
						</div>
						<div class="form-group row m-0 p-1">
							<label class="col-sm-4 col-form-label text-right">{\App\Language::translate('LBL_ADDRESS_RSS', $MODULE)}<span class="redColor">*</span> </label>
							<div class="col-sm-8 controls">
								<div class="input-group">
									<input type="text" class="form-control channelRss" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator='[ { "name":"Url" } ]' />
									<span class="input-group-btn">
										<button class="removeChannel btn btn-light" type="button"><span class="fas fa-times"></span></button>
									</span>
								</div>
							</div>
						</div>
						<div class="form-group row m-0 p-1 newChannel d-none">
							<label class="col-sm-4 col-form-label text-right">{\App\Language::translate('LBL_ADDRESS_RSS', $MODULE)}<span class="redColor">*</span> </label>
							<div class="col-sm-8 controls">
								<div class="input-group">
									<input type="text" disabled="disabled" class="form-control channelRss" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator='[ { "name":"Url" } ]' />
									<span class="input-group-btn">
										<button class="removeChannel btn btn-light" type="button"><span class="fas fa-times"></span></button>
									</span>
								</div>
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>

			</div>
		</div>
	</div>
{/strip}
