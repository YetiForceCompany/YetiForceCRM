{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="tpl-Edit-Field-Recurrence">
		<div class="js-repeat-ui" data-js="container">
			<input type="hidden" name="typeSaving">
			<input id="{$MODULE_NAME}_editView_fieldName_{$FIELD_MODEL->getName()}" type="hidden"
				name="{$FIELD_MODEL->getFieldName()}"
				value="{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}" />
			{assign var="RECURRING_INFORMATION" value=Vtiger_Recurrence_UIType::getRecurringInfo($FIELD_MODEL->get('fieldvalue'))}
			{if empty($RECURRING_INFORMATION)}
				{assign var="RECURRING_INFORMATION" value=['FREQ'=>'','INTERVAL'=>0]}
			{/if}
			<div class="clearfix form-row">
				<div class="col-4 mb-2">
					<span class="col-form-label float-left">{\App\Language::translate('LBL_RECURRING_TYPE', $MODULE_NAME)}</span>
				</div>
				<div class="col-8 mb-2">
					<select class="select2 form-control recurringType"
						title="{\App\Language::translate('LBL_RECURRING_TYPE', $MODULE_NAME)} {$MODULE_NAME}">
						<option title="{\App\Language::translate('LBL_DAYS_TYPE', $MODULE_NAME)}"
							value="DAILY" {if $RECURRING_INFORMATION['FREQ'] eq 'DAILY'} selected {/if}>{\App\Language::translate('LBL_DAYS_TYPE', $MODULE_NAME)}</option>
						<option title="{\App\Language::translate('LBL_WEEKS_TYPE', $MODULE_NAME)}"
							value="WEEKLY" {if $RECURRING_INFORMATION['FREQ'] eq 'WEEKLY'} selected {/if}>{\App\Language::translate('LBL_WEEKS_TYPE', $MODULE_NAME)}</option>
						<option title="{\App\Language::translate('LBL_MONTHS_TYPE', $MODULE_NAME)}"
							value="MONTHLY" {if $RECURRING_INFORMATION['FREQ'] eq 'MONTHLY'} selected {/if}>{\App\Language::translate('LBL_MONTHS_TYPE', $MODULE_NAME)}</option>
						<option title="{\App\Language::translate('LBL_YEAR_TYPE', $MODULE_NAME)}"
							value="YEARLY" {if $RECURRING_INFORMATION['FREQ'] eq 'YEARLY'} selected {/if}>{\App\Language::translate('LBL_YEAR_TYPE', $MODULE_NAME)}</option>
					</select>
				</div>
				<div class="col-4 mb-2">
					<span class="col-form-label float-left">{\App\Language::translate('LBL_REPEAT_INTERVAL', $MODULE_NAME)}</span>
				</div>
				<div class="col-8 mb-2">
					<select class="select2 form-control repeatFrequency"
						title="{\App\Language::translate('LBL_REPEAT_FOR', $MODULE_NAME)}">
						{for $FREQUENCY = 1 to 31}
							<option value="{$FREQUENCY}" title="{$FREQUENCY}"
								{if $FREQUENCY eq $RECURRING_INFORMATION['INTERVAL']}selected{/if}>{$FREQUENCY}</option>
						{/for}
					</select>
				</div>
				<div class="{if $RECURRING_INFORMATION['FREQ'] neq 'WEEKLY'}d-none{/if} row col-12 form-row repeatWeekUI">
					<span class="col-md-4 mb-2">
						<span class="medium">{\App\Language::translate('LBL_REAPEAT_IN', $MODULE_NAME)}</span>
					</span>
					<span class="col-md-8 text-center mb-2">
						<div class="btn-group btn-group-toggle" data-toggle="buttons">
							<label title="{\App\Language::translate('LBL_DAY0', $MODULE_NAME)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SU') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SU') !== false}checked{/if}
									value="SU">
								{\App\Language::translate('LBL_SM_SUN', $MODULE_NAME)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY1', $MODULE_NAME)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'MO') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'MO') !== false}checked{/if}
									value="MO">
								{\App\Language::translate('LBL_SM_MON', $MODULE_NAME)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY2', $MODULE_NAME)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TU') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TU') !== false}checked{/if}
									value="TU">
								{\App\Language::translate('LBL_SM_TUE', $MODULE_NAME)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY3', $MODULE_NAME)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'WE') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'WE') !== false}checked{/if}
									value="WE">
								{\App\Language::translate('LBL_SM_WED', $MODULE_NAME)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY4', $MODULE_NAME)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TH') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TH') !== false}checked{/if}
									value="TH">
								{\App\Language::translate('LBL_SM_THU', $MODULE_NAME)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY5', $MODULE_NAME)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'FR') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'FR') !== false}checked{/if}
									value="FR">
								{\App\Language::translate('LBL_SM_FRI', $MODULE_NAME)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY6', $MODULE_NAME)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SA') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SA') !== false}checked{/if}
									value="SA">
								{\App\Language::translate('LBL_SM_SAT', $MODULE_NAME)}
							</label>
						</div>
					</span>
				</div>

				<div class="{if $RECURRING_INFORMATION['FREQ'] neq 'MONTHLY'}d-none{/if} row col-12 form-row repeatMonthUI">
					<span class="col-md-4">
						<span class="medium">{\App\Language::translate('LBL_REAPEAT_BY', $MODULE_NAME)}</span>
					</span>
					<span class="col-md-8 pl-2 pr-0">
						<div class="input-group mb-2 {$WIDTHTYPE_GROUP}">
							<div class="input-group-prepend">
								<span class="input-group-text">
									<input type="radio" name="calendarMontlyType" class="calendarMontlyType" value="DATE"
										{if isset($RECURRING_INFORMATION['BYMONTHDAY'])}checked{/if}>
								</span>
							</div>
							<input type="text" class="form-control"
								aria-label="{\App\Language::translate('LBL_DAY_IN_MONTH', $MODULE_NAME)}"
								value="{\App\Language::translate('LBL_DAY_IN_MONTH', $MODULE_NAME)}" readonly="readonly">
						</div>
						<div class="input-group mb-2 {$WIDTHTYPE_GROUP}">
							<div class="input-group-prepend">
								<span class="input-group-text">
									<input type="radio" name="calendarMontlyType" class="calendarMontlyType" value="DAY"
										{if isset($RECURRING_INFORMATION['BYDAY'])}checked{/if}>
								</span>
							</div>
							<input type="text" class="form-control"
								aria-label="{\App\Language::translate('LBL_DAY_IN_WEEK', $MODULE_NAME)}"
								value="{\App\Language::translate('LBL_DAY_IN_WEEK', $MODULE_NAME)}" readonly="readonly">
						</div>
					</span>
				</div>
				<div class="col-4 mb-2">
					<span class="col-form-label float-left">{\App\Language::translate('LBL_REPEAT_END', $MODULE_NAME)}</span>
				</div>
				<div class="col-8 mb-2">
					<div class="input-group mb-2 {$WIDTHTYPE_GROUP}">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<input type="radio" name="calendarEndType" value="never"
									{if !isset($RECURRING_INFORMATION['COUNT']) && !isset($RECURRING_INFORMATION['UNTIL'])}checked{/if}>
							</span>
						</div>
						<input type="text" class="form-control" aria-label=""
							value="{\App\Language::translate('LBL_NEVER', $MODULE_NAME)}" readonly="readonly">
					</div>
					<div class="input-group mb-2 {$WIDTHTYPE_GROUP}">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<input type="radio" name="calendarEndType" value="count"
									{if isset($RECURRING_INFORMATION['COUNT'])}checked{/if}>
								&nbsp;{\App\Language::translate('LBL_COUNT', $MODULE_NAME)}
							</span>
						</div>
						<input type="text" class="form-control countEvents"
							{if isset($RECURRING_INFORMATION['COUNT'])}value="{$RECURRING_INFORMATION['COUNT']}"
							{else}disabled="disabled"
							{/if}
							title="{\App\Language::translate('LBL_COUNT', $MODULE_NAME)}"
							data-validation-engine='validate[required,funcCall[Vtiger_Integer_Validator_Js.invokeValidation]]' />
					</div>
					<div class="input-group mb-2 date {$WIDTHTYPE_GROUP}">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<input type="radio" name="calendarEndType" value="until"
									{if isset($RECURRING_INFORMATION['UNTIL'])}checked{/if}>
								&nbsp;{\App\Language::translate('LBL_UNTIL', $MODULE_NAME)}
							</span>
						</div>
						<input type="text"
							class="dateField form-control calendarUntil datepicker" {if isset($RECURRING_INFORMATION['UNTIL'])}
							value="{$RECURRING_INFORMATION['UNTIL']}" {else} disabled="disabled"
							{/if}name="calendarUntil" data-date-format="{$USER_MODEL->get('date_format')}"
							title="{\App\Language::translate('LBL_UNTIL', $MODULE_NAME)}"
							data-validation-engine='validate[required,funcCall[Vtiger_Date_Validator_Js.invokeValidation]]'
							data-validator='{\App\Purifier::encodeHtml(\App\Json::encode([['name' => 'greaterThanDependentField', 'params' => ['date_start']]]))}' />
						<div class=" input-group-append">
							<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
								<span class="fas fa-calendar-alt"></span>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
