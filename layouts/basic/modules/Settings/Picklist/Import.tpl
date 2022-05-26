{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Picklist-Import -->
	<div class="modal-body js-modal-body pb-0" data-js="container">
		<form class="form-horizontal validateForm" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE_NAME}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="source_module" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="action" value="SaveAjax" />
			<input type="hidden" name="mode" value="import" />
			<input type="hidden" name="picklistName" value="{$FIELD_MODEL->getName()}" />
			<div class="form-group row">
				<div class="input-group col-12">
					<div class="input-group-prepend">
						<button type="button" class=" btn btn-default  ">
							<span class="fas fa-info-circle js-popover-tooltip u-cursor-pointer" data-js="popover" data-trigger="hover focus" data-content="{\App\Language::translate('LBL_CSV_DESC', $QUALIFIED_MODULE)}"></span>
						</button>
					</div>
					<input type="file" name="file" accept=".csv" class="form-control" data-validation-engine="validate[required]" />
				</div>
			</div>
			<div class="form-group row js-summary d-none mb-0">
				<label for="allNumber" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_ALL_ENTRIES',$QUALIFIED_MODULE)}</label>
				<div class="col-sm-7">
					<input type="text" readonly class="form-control-plaintext js-all-number" id="allNumber" />
				</div>
			</div>
			<div class="form-group row js-summary d-none mb-0">
				<label for="errorsNumber" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_ERRORS',$QUALIFIED_MODULE)}</label>
				<div class="col-sm-7">
					<input type="text" readonly class="form-control-plaintext js-errors-number" id="errorsNumber" />
				</div>
			</div>
			<div class="form-group row js-summary d-none mb-0">
				<label for="importedNumber" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_IMPORTED_ENTRIES',$QUALIFIED_MODULE)}</label>
				<div class="col-sm-7">
					<input type="text" readonly class="form-control-plaintext js-imported-number" id="importedNumber" />
				</div>
			</div>
			<div class="form-group row js-summary d-none mb-0">
				<div class="col-sm-12">
					<textarea readonly class="form-control-plaintext js-errors"></textarea>
				</div>
			</div>
		</form>
	</div>
	<!-- /tpl-Settings-Picklist-Import -->
{/strip}
