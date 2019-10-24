{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="tpl-Settings-Vtiger-CustomRecordNumberingAdvanced">
	<div class="modal-header">
		<h5 class="modal-title">
			<span class="yfi yfi-system-configuration mr-1"></span>
			{\App\Language::translate('LBL_ADVANCED_RECORD_NUMBERING', $QUALIFIED_MODULE)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		{if !empty($PICKLISTS_VALUES)}
			<form class="form-modal js-custom-record-numbering-advanced" method="POST" data-js="container">
				<div class="modal-Fields">
					<table class="table table-bordered">
						{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
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
								<td class="{$WIDTHTYPE}">
									<label class="float-right">
										<b>{\App\Language::translate($PICKLIST_VALUE['prefix'], $QUALIFIED_MODULE)}</b>
									</label>
								</td>
								<td class="fieldValue {$WIDTHTYPE} border-left-0 position-relative">
									<input type="text" class="form-control" value="{$PICKLIST_VALUE['cur_id']}"
									data-old-sequence-number="{$PICKLIST_VALUE['cur_id']}"
									name="{$PICKLIST_VALUE['picklistValue']}"
									data-validation-engine="validate[required,funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]"/>
								</td>
							</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
				{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $QUALIFIED_MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL' MODULE=$QUALIFIED_MODULE}
			</form>
		{else}
			<div class="col-12 paddingLRZero">
				<div class="alert alert-info">
					{\App\Language::translate('LBL_RECORDS_NO_FOUND',$QUALIFIED_MODULE)}
				</div>
			</div>
		{/if}
	</div>
</div>
