{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-SlaPolicy-CustomConditions -->
	<input type="hidden" class="js-all-business-hours" value="{\App\Purifier::encodeHtml(\App\Json::encode($ALL_BUSINESS_HOURS))}">
	<table class="table js-custom-conditions-table">
		<thead>
			<tr>
				<th>{\App\Language::translate('LBL_CONDITIONS', $SLA_POLICY_MODULE)}</th>
				<th>{\App\Language::translate('LBL_BUSINESS_HOURS', $SLA_POLICY_MODULE)}</th>
				<th>{\App\Language::translate('LBL_TIMES', $SLA_POLICY_MODULE)}</th>
				<th>{\App\Language::translate('LBL_ACTIONS',$SLA_POLICY_MODULE)}</th>
			</tr>
		</thead>
		<tbody>
		{foreach item=ROW from=$ROWS}
			<tr data-id="{$ROW['id']}" data-hash="{$ROW['id']}" data-js="container">
				<td class="js-conditions" data-conditions="{\App\Purifier::encodeHtml($ROW['conditions'])}"></td>
				<td>
					{assign var=ROW_HOURS value=explode(',',$ROW['business_hours'])}
					<select class="select2 js-business-hours" multiple>
						{foreach item=BUSINESS_HOURS from=$ALL_BUSINESS_HOURS}
							<option value="{$BUSINESS_HOURS['id']}"{if in_array($BUSINESS_HOURS['id'], $ROW_HOURS)}selected="selected"{/if}>{$BUSINESS_HOURS['name']}</option>
						{/foreach}
					</select>
				</td>
				<td>
					<div class="js-reaction-time-container">
						<label>{\App\Language::translate('LBL_REACTION_TIME','Settings:BusinessHours')}</label>
						<div class="input-group time">
							<input type="hidden" name="reaction_time" class="c-time-period" value="{$ROW['reaction_time']}">
						</div>
					</div>
					<div class="js-idle-time-container">
						<label>{\App\Language::translate('LBL_IDLE_TIME','Settings:BusinessHours')}</label>
						<div class="input-group time">
							<input type="hidden" name="idle_time" class="c-time-period" value="{$ROW['idle_time']}">
						</div>
					</div>
					<div class="js-resolve-time-container">
						<label>{\App\Language::translate('LBL_RESOLVE_TIME','Settings:BusinessHours')}</label>
						<div class="input-group time">
							<input type="hidden" name="resolve_time" class="c-time-period" value="{$ROW['resolve_time']}">
						</div>
					</div>
				</td>
				<td>
					<a href class="btn btn-danger js-delete-row-action"><span class="fas fa-trash-alt"></span></a>
					<a href class="btn btn-primary ml-2 js-edit-row-action"><span class="fas fa-edit"></span></a>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
<!-- /tpl-SlaPolicy-CustomConditions -->
{/strip}
