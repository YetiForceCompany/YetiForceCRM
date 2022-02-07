{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-plus mr-1"></span>
						{\App\Language::translate('LBL_SAVE_DASHBOARD', $MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal validateForm sendByAjax">
					<input type="hidden" name="module" value="{$MODULE_NAME}" />
					<input type="hidden" name="action" value="Dashboard">
					<input type="hidden" name="mode" value="save" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="dashboardId" value="{$DASHBOARD_ID}">
					<div class="formContainer">
						<div class="form-group row m-0 p-1">
							<label class="col-sm-4 col-form-label text-right">{\App\Language::translate('LBL_NAME_DASHBOARD', $MODULE)}<span class="redColor">*</span> </label>
							<div class="col-sm-8 controls">
								<input type="text" name="name" class="form-control" data-validation-engine="validate[required]" value="{$DASHBOARD_NAME}" />
							</div>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>

			</div>
		</div>
	</div>
{/strip}
