{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>{\App\Language::translate('LBL_INDIVIDUAL_TAX', $MODULE)}</strong>
			<div class="float-right">
				<input type="{$AGGREGATION_INPUT_TYPE}" name="aggregationType" value="individual" class="activeCheckbox">
			</div>
		</div>
		<div class="panel-body" style="display: none;">
			<div class="container-fluid">
				<div class="row">
					<div class="input-group individualTaxContainer">
						<input type="text" name="individualTax" class="form-control individualTaxValue" value="0">
						<span class="input-group-addon">%</span>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
