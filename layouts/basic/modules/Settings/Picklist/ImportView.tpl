{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Settings-Picklist-ImportView -->
<div class='modelContainer modal fade basicImportView' tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">
					{\App\Language::translate('LBL_IMPORT_VALUE', $QUALIFIED_MODULE)}
					&nbsp;{\App\Language::translate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}
				</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<form class="form-horizontal" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}"/>
				<input type="hidden" name="parent" value="Settings"/>
				<input type="hidden" name="source_module" value="{$SELECTED_MODULE_NAME}"/>
				<input type="hidden" name="action" value="SaveAjax"/>
				<input type="hidden" name="mode" value="import"/>
				<input type="hidden" name="picklistName" value="{$SELECTED_PICKLIST_FIELDMODEL->get('name')}"/>
				<div class="modal-body">
					<div class="form-group row">
						<label class="col-sm-2 col-form-label">CSV:</label>
						<div class="col-sm-10">
							<input type="file" name="file" accept=".csv" data-validation-engine="validate[required]" />
						</div>
					</div>
					<div class="form-group row js-summary d-none">
						<label for="allNumber" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_ALL_ENTRIES',$QUALIFIED_MODULE)}</label>
						<div class="col-sm-7">
							<input type="text" readonly class="form-control-plaintext js-all-number" id="allNumber" />
						</div>
					</div>
					<div class="form-group row js-summary d-none">
						<label for="errorsNumber" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_ERRORS',$QUALIFIED_MODULE)}</label>
						<div class="col-sm-7">
							<input type="text" readonly class="form-control-plaintext js-errors-number" id="errorsNumber" />
						</div>
					</div>
					<div class="form-group row js-summary d-none">
						<label for="importedNumber" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_IMPORTED_ENTRIES',$QUALIFIED_MODULE)}</label>
						<div class="col-sm-7">
							<input type="text" readonly class="form-control-plaintext js-imported-number" id="importedNumber" />
						</div>
					</div>
					<div class="form-group row js-summary d-none">
						<div class="col-sm-12">
							<textarea readonly class="form-control-plaintext js-errors"></textarea>
						</div>
					</div>
				</div>
				{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_IMPORT' BTN_DANGER='LBL_CANCEL'}
			</form>
		</div>
	</div>
</div>
<!-- /tpl-Settings-Picklist-ImportView -->
{/strip}
