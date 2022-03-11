{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-ServiceContracts-CustomConditions -->
	<input type="hidden" class="js-all-business-hours" value="{\App\Purifier::encodeHtml(\App\Json::encode($ALL_BUSINESS_HOURS))}">
	<div class="d-none js-conditions-template" data-js="container">
		{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl', $MODULE_NAME) ADVANCE_CRITERIA=[]}
	</div>
	<div class="js-custom-conditions" data-js="container">
		{foreach item=ROW from=$SLA_POLICY_ROWS key=$ROW_INDEX}
			{if $ROW['policy_type']===2}
				<div class="card js-custom-row shadow-sm mb-2" data-id="{$ROW['id']}" data-record-id="{$RECORD->getId()}" data-js="container">
					<div class="card-body">
						<div class="d-flex">
							<div class="d-block" style="flex-grow:1">
								<div class="row no-gutters">
									<div class="col-5 pr-2">
										{assign var=ROW_HOURS value=explode(',', $ROW['business_hours'])}
										<label>{\App\Language::translate('LBL_BUSINESS_HOURS', 'ServiceContracts')}</label>
										<div>
											<select class="select2 js-business-hours" name="business_hours[{$ROW_INDEX}][]" multiple data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
												{foreach item=BUSINESS_HOURS from=$ALL_BUSINESS_HOURS}
													<option value="{$BUSINESS_HOURS['id']}" {if in_array($BUSINESS_HOURS['id'], $ROW_HOURS)}selected="selected" {/if}>{$BUSINESS_HOURS['name']}</option>
												{/foreach}
											</select>
										</div>
									</div>
									<div class="col-2 pr-2">
										<label>{\App\Language::translate('LBL_REACTION_TIME','ServiceContracts')}</label>
										<div class="input-group time">
											<input type="hidden" name="reaction_time[{$ROW_INDEX}]" class="c-time-period" value="{$ROW['reaction_time']}">
										</div>
									</div>
									<div class="col-2 pr-2">
										<label>{\App\Language::translate('LBL_IDLE_TIME','ServiceContracts')}</label>
										<div class="input-group time">
											<input type="hidden" name="idle_time[{$ROW_INDEX}]" class="c-time-period" value="{$ROW['idle_time']}">
										</div>
									</div>
									<div class="col-2 pr-2">
										<label>{\App\Language::translate('LBL_RESOLVE_TIME','ServiceContracts')}</label>
										<div class="input-group time">
											<input type="hidden" name="resolve_time[{$ROW_INDEX}]" class="c-time-period" value="{$ROW['resolve_time']}">
										</div>
									</div>
								</div>
								<div class="row mt-2">
									<div class="js-conditions-col col">
										<label>{\App\Language::translate('LBL_CONDITIONS', $MODULE_NAME)}</label>
										<input type="hidden" name="rowid[{$ROW_INDEX}]" value="{$ROW['id']}" class="js-custom-row-id" />
										<input type="hidden" name="conditions[{$ROW_INDEX}]" class="js-conditions-value" value="{\App\Purifier::encodeHtml($ROW['conditions'])}" data-js="container">
										{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl', $MODULE_NAME) ADVANCE_CRITERIA=\App\Json::decode($ROW['conditions'])}
									</div>
								</div>
							</div>
							<div class="d-inline-flex text-right border-left" style="flex-grow:0">
								<div class="d-inline-block align-center" style="margin:auto 0;">
									<a href class="btn btn-danger ml-4 js-delete-row-action"><span class="fas fa-trash-alt"></span></a>
								</div>
							</div>
						</div>
					</div>
				</div>
			{/if}
		{/foreach}
	</div>
	<!-- /tpl-ServiceContracts-CustomConditions -->
{/strip}
