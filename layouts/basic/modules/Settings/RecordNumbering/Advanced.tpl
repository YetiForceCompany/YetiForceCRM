{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-RecordNumbering-Advanced -->
	<div class="modal-body">
		{if !empty($PICKLISTS_VALUES)}
			<form class="form-modal js-custom-record-numbering-advanced" method="POST" data-js="container">
				<div class="modal-Fields">
					<table class="table table-bordered">
						<thead>
							<tr>
								<th>
									{\App\Language::translate('LBL_PREFIX', $QUALIFIED_MODULE)}
								</th>
								<th class="">
									{\App\Language::translate('LBL_START_SEQUENCE', $QUALIFIED_MODULE)}
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$PICKLISTS_VALUES item=$PICKLIST_VALUE}
								<tr>
									<td class="py-2 u-font-weight-550 align-middle border-bottom">
										{\App\Language::translate(\App\Purifier::decodeHtml($PICKLIST_VALUE['prefix']), $QUALIFIED_MODULE)}
									</td>
									<td class="py-2 position-relative w-50 border-bottom">
										<input type="text" class="form-control js-picklist-sequence" value="{$PICKLIST_VALUE['cur_id']}"
											data-old-sequence-number="{$PICKLIST_VALUE['cur_id']}"
											name="{\App\Purifier::encodeHtml($PICKLIST_VALUE['prefix'])}" data-js="value"
											data-validation-engine="validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]" />
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</form>
		{else}
			<div class="col-12 paddingLRZero">
				<div class="alert alert-info">
					{\App\Language::translate('LBL_RECORDS_NO_FOUND',$QUALIFIED_MODULE)}
				</div>
			</div>
		{/if}
	</div>
	<!-- tpl-Settings-RecordNumbering-Advanced -->
{/strip}
