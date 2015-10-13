{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="panel panel-default">
		<div class="panel-heading">
			<strong>{vtranslate('LBL_INDIVIDUAL_TAX', $MODULE)}</strong>
			<div class="pull-right">
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
