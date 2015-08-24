{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="row">
		<div class="col-md-4">
			<table class="table table-bordered blockContainer suppliesSummaryCurrencies">
				<tbody>
					{foreach item=FIELD from=$FIELDS[2]}
						<tr data-rownumber="1">
							<td>
								xx
							</td>
							<td>
								vv
							</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<div class="col-md-4">
			<table class="table table-bordered blockContainer suppliesSummaryDiscounts">
				<thead>
					<tr>
						<th>{vtranslate('LBL_TAX_RATE',$SUPMODULE)}</th>
						<th>{vtranslate('LBL_TAX_VALUE',$SUPMODULE)}</th>
					</tr>
				</thead>
				<tbody>
					
				</tbody>
			</table>
		</div>
		<div class="col-md-4">
			<table class="table table-bordered blockContainer suppliesSummaryTaxes">
				<thead>
					<tr>
						<th>{vtranslate('LBL_TAX_RATE',$SUPMODULE)}</th>
						<th>{vtranslate('LBL_TAX_VALUE',$SUPMODULE)}</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
	</div>
{/strip}
