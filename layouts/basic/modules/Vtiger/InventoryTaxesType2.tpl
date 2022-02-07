{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="card js-panel  mb-2" data-js="class: js-active">
		<div class="card-header py-1">
			<strong>{\App\Language::translate('LBL_INDIVIDUAL_TAX', $MODULE)}</strong>
			<div class="float-right">
				<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="individual" class="activeCheckbox">
			</div>
		</div>
		<div class="card-body js-panel__body d-none" data-js="class: d-none">
			<div class="container-fluid">
				<div class="row">
					<div class="input-group individualTaxContainer">
						<input type="text" name="individualTax" class="form-control individualTaxValue" value="0" data-validation-engine="validate[required,min[0]]" data-js="focusout">
						<div class="input-group-append">
							<span class="input-group-text">%</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
