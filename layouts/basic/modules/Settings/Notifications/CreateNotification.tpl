{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="validationEngineContainer">
		<div class="modal-header row no-margin">
			<div class="col-12 paddingLRZero">
				<div class="col-8 paddingLRZero">
					<h4>{\App\Language::translate('LBL_TITLE_ADDED', $QUALIFIED_MODULE)}</h4>
				</div>
				<div class="float-right">
					<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
				</div>
			</div>
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
