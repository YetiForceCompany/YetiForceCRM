{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Products-Modals-StocktakingModal -->
	<div class="modal-body js-modal-body" data-js="container">
		<form method="post" enctype="multipart/form-data">
			<input type="hidden" name="randomKey" class="js-randomKey">
			<div class="js-step" data-step="sendFile">
				<div class="form-group">
					<label for="storage_quantity_location">{\App\Language::translate('LBL_STORAGE_QUANTITY_LOCATION', $MODULE_NAME)}</label>
					<select name="storage" class="form-control select2" id="storage_quantity_location">
						<option value="0">{\App\Language::translate('LBL_PRODUCTS_STORAGE_FIELD', $MODULE_NAME)}</option>
						{foreach from=Products_Stocktaking_Model::getStorage() key=STORAGE_ID item=STORAGE_NAME}
							<option value="{$STORAGE_ID}">{$STORAGE_NAME}</option>
						{/foreach}
					</select>
				</div>
				<input type="file" name="file" accept=".csv" data-validation-engine="validate[required]" />
				<button type="button" class="btn btn-primary float-right js-send-file">
					<span class="fas fa-upload mr-2"></span>
					{App\Language::translate('BTN_ANALYZE_FILE',$MODULE_NAME)}
				</button>
			</div>
			<div class="js-step d-none" data-step="selectColumn">
				<div class="form-group row mb-1">
					<label for="count" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_ALL_ENTRIES', $MODULE_NAME)}</label>
					<div class="col-sm-7">
						<input type="text" readonly class="form-control-plaintext js-count" id="count" />
					</div>
				</div>
				<div class="form-group row mb-1">
					<label for="encoding" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_CHARACTER_ENCODING', 'Import')}</label>
					<div class="col-sm-7">
						<input type="text" readonly class="form-control-plaintext js-encoding" id="encoding" />
					</div>
				</div>
				<div class="form-group row mb-1">
					<label for="skuColumnSeq" class="col-sm-5 col-form-label">{\App\Language::translate('FL_EAN_SKU', $MODULE_NAME)}</label>
					<div class="col-sm-7">
						<select name="skuColumnSeq" class="form-control" id="skuColumnSeq"></select>
					</div>
				</div>
				<div class="form-group row mb-1">
					<label for="qtyColumnSeq" class="col-sm-5 col-form-label">{\App\Language::translate('FL_QTY_IN_STOCK', $MODULE_NAME)}</label>
					<div class="col-sm-7">
						<select name="qtyColumnSeq" class="form-control" id="qtyColumnSeq"></select>
					</div>
				</div>
				<button type="button" class="btn btn-primary float-right js-compare">
					<span class="fas fa-upload mr-2"></span>
					{App\Language::translate('BTN_COMPARE_STOCK_LEVELS',$MODULE_NAME)}
				</button>
			</div>
			<div class="js-step d-none" data-step="showCompare">
				<div class="form-group row mb-1">
					<label for="count2" class="col-sm-7 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_ALL_ENTRIES', $MODULE_NAME)}</label>
					<div class="col-sm-5">
						<input type="text" readonly class="form-control-plaintext js-count" id="count2" />
					</div>
				</div>
				<div class="form-group row mb-1">
					<label for="entries-update" class="col-sm-7 col-form-label">{\App\Language::translate('LBL_NUMBER_ENTRIES_TO_UPDATE', $MODULE_NAME)}</label>
					<div class="col-sm-5">
						<input type="text" readonly class="form-control-plaintext js-entries-update" id="entries-update" />
					</div>
				</div>
				<div class="form-group row mb-1">
					<label for="entries-no-update" class="col-sm-7 col-form-label">{\App\Language::translate('LBL_NUMBER_ENTRIES_NO_UPDATE', $MODULE_NAME)}</label>
					<div class="col-sm-5">
						<input type="text" readonly class="form-control-plaintext js-entries-no-update" id="entries-no-update" />
					</div>
				</div>
				<div class="form-group row mb-1">
					<label for="entries-not-found" class="col-sm-7 col-form-label">{\App\Language::translate('LBL_NUMBER_ENTRIES_NOT_FOUND', $MODULE_NAME)}</label>
					<div class="col-sm-5">
						<input type="text" readonly class="form-control-plaintext js-entries-not-found" id="entries-not-found" />
					</div>
				</div>
				<div class="form-group row mb-1">
					<label for="list-entries-not-found" class="col-sm-4 col-form-label">{\App\Language::translate('LBL_LIST_ENTRIES_NOT_FOUND', $MODULE_NAME)}</label>
					<div class="col-sm-8">
						<textarea readonly class="form-control-plaintext js-list-entries-not-found" id="list-entries-not-found"></textarea>
					</div>
				</div>
				<div class="form-group row mb-1 d-none js-record-name">
					<label for="record-name" class="col-sm-4 col-form-label">{\App\Language::translate('LBL_NAME', $MODULE_NAME)}</label>
					<div class="col-sm-8">
						<input name="recordName" type="text" class="form-control " id="record-name" data-validation-engine="validate[required]" />
					</div>
				</div>
				<button type="button" class="btn btn-primary float-right js-import">
					<span class="fas fa-upload mr-2"></span>
					{App\Language::translate('LBL_IMPORT',$MODULE_NAME)}
				</button>
			</div>
			<div class="js-step text-center d-none" data-step="showSummary">
				<div class="form-group row mb-1 d-none js-imported">
					<label for="imported-counter" class="col-sm-5 col-form-label">{\App\Language::translate('LBL_NUMBER_OF_IMPORTED_ENTRIES', $MODULE_NAME)}</label>
					<div class="col-sm-7">
						<input type="text" readonly class="form-control-plaintext js-imported-counter" id="imported-counter" />
					</div>
				</div>
				<div class="alert alert-warning d-none js-alert" role="alert">
					{App\Language::translate('LBL_IMPORT_STOCK_ALERT',$MODULE_NAME)}
				</div>
				<a target="_blank" class="btn btn-primary mr-4 d-none js-btn-igin" href="#" data-js="container">
					<span class="yfm-IGIN mr-2"></span>{\App\Language::translateSingularModuleName('IGIN')}
				</a>
				<a target="_blank" class="btn btn-primary mr-4 d-none js-btn-iidn" href="#" data-js="container">
					<span class="yfm-IIDN mr-2"></span>{\App\Language::translateSingularModuleName('IIDN')}
				</a>
			</div>
		</form>
	</div>
	<!-- /tpl-Products-Modals-StocktakingModal -->
{/strip}
