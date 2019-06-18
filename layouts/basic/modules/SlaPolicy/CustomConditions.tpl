{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-SlaPolicy-CustomConditions -->
	<input type="hidden" class="js-all-business-hours" value="{\App\Purifier::encodeHtml(\App\Json::encode($ALL_BUSINESS_HOURS))}">
	<div class="d-none js-conditions-template" data-js="container">
		{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl', 'SlaPolicy')}
	</div>
	<div class="table-responsive">
	<table class="table js-custom-conditions-table">
		<thead>
			<tr>
				<th>{\App\Language::translate('LBL_CONDITIONS', $SLA_POLICY_MODULE)}</th>
				<th>{\App\Language::translate('LBL_BUSINESS_HOURS', $SLA_POLICY_MODULE)}</th>
				<th>{\App\Language::translate('LBL_TIMES', $SLA_POLICY_MODULE)}</th>
			</tr>
		</thead>
		<tbody>
		{foreach item=ROW from=$ROWS key=$ROW_INDEX}
			{if $ROW['policy_type']===2}
			<tr class="js-custom-table-row" data-id="{$ROW['id']}" data-js="container">
				<td class="js-conditions-col">
					<input type="hidden" name="rowid[{$ROW_INDEX}]" value="{$ROW['id']}" class="js-custom-row-id" />
					<input type="hidden" name="conditions[{$ROW_INDEX}]" class="js-conditions-value" value="{\App\Purifier::encodeHtml($ROW['conditions'])}" data-js="container">
					{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl', 'SlaPolicy') ADVANCE_CRITERIA=\App\Json::decode($ROW['conditions'])}
				</td>
				<td>
					{assign var=ROW_HOURS value=explode(',',$ROW['business_hours'])}
					<select class="select2 js-business-hours" name="business_hours[{$ROW_INDEX}][]" multiple data-validation-engine="validate[required,,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
						{foreach item=BUSINESS_HOURS from=$ALL_BUSINESS_HOURS}
							<option value="{$BUSINESS_HOURS['id']}"{if in_array($BUSINESS_HOURS['id'], $ROW_HOURS)}selected="selected"{/if}>{$BUSINESS_HOURS['name']}</option>
						{/foreach}
					</select>
				</td>
				<td>
					<div class="d-flex">
						<div style="flex-grow:1;">
							<div class="js-reaction-time-container">
								<label>{\App\Language::translate('LBL_REACTION_TIME','Settings:BusinessHours')}</label>
								<div class="input-group time">
									<div style="width:226px">
										<input type="hidden" name="reaction_time[{$ROW_INDEX}]" class="c-time-period" value="{$ROW['reaction_time']}">
									</div>
								</div>
							</div>
							<div class="js-idle-time-container">
								<label>{\App\Language::translate('LBL_IDLE_TIME','Settings:BusinessHours')}</label>
								<div class="input-group time">
									<div style="width:226px"><input type="hidden" name="idle_time[{$ROW_INDEX}]" class="c-time-period" value="{$ROW['idle_time']}"></div>
								</div>
							</div>
							<div class="js-resolve-time-container">
								<label>{\App\Language::translate('LBL_RESOLVE_TIME','Settings:BusinessHours')}</label>
								<div class="input-group time">
									<div style="width:226px"><input type="hidden" name="resolve_time[{$ROW_INDEX}]" class="c-time-period" value="{$ROW['resolve_time']}"></div>
								</div>
							</div>
						</div>
						<div style="flex-grow:0;" class="border-left ml-2">
							<a href class="btn btn-danger js-delete-row-action ml-2"><span class="fas fa-trash-alt"></span></a>
						</div>
					</div>
				</td>
			</tr>
			{/if}
		{/foreach}
		</tbody>
	</table>
	</div>
<!-- /tpl-SlaPolicy-CustomConditions -->
{/strip}
