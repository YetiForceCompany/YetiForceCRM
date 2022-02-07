{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="validationEngineContainer">
		<div class="modal-header">
			<h5 class="modal-title">{\App\Language::translate('LBL_TITLE_ADDED', $QUALIFIED_MODULE)}</h5>
			<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body row">
			<div class="col-12 form-horizontal">
				<div class="form-group">
					<div class="col-sm-3 col-form-label">
						<label>{\App\Language::translate('LBL_NAME', $QUALIFIED_MODULE)}</label>
					</div>
					<div class="col-sm-8">
						<input name="name" value="{$RECORD->getName()}" data-validation-engine="validate[required]" class="form-control">
					</div>
				</div>
			</div>
		</div>
		{include file=App\Layout::getTemplatePath('Modals/Footer.tpl') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
	</div>
{/strip}
