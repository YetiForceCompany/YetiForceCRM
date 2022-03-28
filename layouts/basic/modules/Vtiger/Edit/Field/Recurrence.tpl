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
		<div class="typeSavingModal">
			<div class="modal fade" tabindex="-1" role="dialog">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content">
						<div class="modal-header m-0 d-flex align-items-center">
							<h5 class="modal-title m-0"><span
									class="fas fa-save mr-2"></span>{App\Language::translate('LBL_TITLE_TYPE_SAVING', $MODULE)}
							</h5>
							<button type="button" class="close" data-dismiss="modal"
								aria-label="{\App\Language::translate('LBL_CLOSE')}">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
						<div class="modal-body">
							<div class="col-12 px-0 mb-3 form-row m-0">
								<div class="col-12 col-lg-4 px-0">
									<button class="btn btn-primary btn-sm typeSavingBtn" data-value="2">
										{App\Language::translate('LBL_UPDATE_THIS_EVENT', $MODULE)}
									</button>
								</div>
								<div class="col-12 col-lg-8 px-0">
									{App\Language::translate('LBL_UPDATE_THIS_EVENT_DESCRIPTION', $MODULE)}
								</div>
							</div>
							<div class="col-12 px-0 mb-3 form-row m-0">
								<div class="col-12 col-lg-4 px-0">
									<button class="btn btn-primary btn-sm typeSavingBtn" data-value="3">
										{App\Language::translate('LBL_UPDATE_FUTURE_EVENTS', $MODULE)}
									</button>
								</div>
								<div class="col-12 col-lg-8 px-0">
									{App\Language::translate('LBL_UPDATE_FUTURE_EVENTS_DESCRIPTION', $MODULE)}
								</div>
							</div>
							<div class="col-12 px-0 mb-3 form-row m-0">
								<div class="col-12 col-lg-4 px-0">
									<button class="btn btn-primary btn-sm typeSavingBtn" type="button" data-value="1">
										{App\Language::translate('LBL_UPDATE_ALL_EVENTS', $MODULE)}
									</button>
								</div>
								<div class="col-12 col-lg-8 px-0">
									{App\Language::translate('LBL_UPDATE_ALL_EVENTS_DESCRIPTION', $MODULE)}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="js-repeat-ui" data-js="container">
			<input type="hidden" name="typeSaving">
			<input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}" type="hidden"
				name="{$FIELD_MODEL->getFieldName()}"
				value="{\App\Purifier::encodeHtml($FIELD_MODEL->get('fieldvalue'))}" />
			{assign var="RECURRING_INFORMATION" value=Vtiger_Recurrence_UIType::getRecurringInfo($FIELD_MODEL->get('fieldvalue'))}
			{if empty($RECURRING_INFORMATION)}
				{assign var="RECURRING_INFORMATION" value=['FREQ'=>'','INTERVAL'=>0]}
			{/if}
			<div class="clearfix form-row">
				<div class="col-4 mb-2">
					<span class="col-form-label float-left">{\App\Language::translate('LBL_RECURRING_TYPE', $MODULE)}</span>
				</div>
				<div class="col-8 mb-2">
					<select class="select2 form-control recurringType"
						title="{\App\Language::translate('LBL_RECURRING_TYPE', $MODULE)} {$MODULE}">
						<option title="{\App\Language::translate('LBL_DAYS_TYPE', $MODULE)}"
							value="DAILY" {if $RECURRING_INFORMATION['FREQ'] eq 'DAILY'} selected {/if}>{\App\Language::translate('LBL_DAYS_TYPE', $MODULE)}</option>
						<option title="{\App\Language::translate('LBL_WEEKS_TYPE', $MODULE)}"
							value="WEEKLY" {if $RECURRING_INFORMATION['FREQ'] eq 'WEEKLY'} selected {/if}>{\App\Language::translate('LBL_WEEKS_TYPE', $MODULE)}</option>
						<option title="{\App\Language::translate('LBL_MONTHS_TYPE', $MODULE)}"
							value="MONTHLY" {if $RECURRING_INFORMATION['FREQ'] eq 'MONTHLY'} selected {/if}>{\App\Language::translate('LBL_MONTHS_TYPE', $MODULE)}</option>
						<option title="{\App\Language::translate('LBL_YEAR_TYPE', $MODULE)}"
							value="YEARLY" {if $RECURRING_INFORMATION['FREQ'] eq 'YEARLY'} selected {/if}>{\App\Language::translate('LBL_YEAR_TYPE', $MODULE)}</option>
					</select>
				</div>
				<div class="col-4 mb-2">
					<span class="col-form-label float-left">{\App\Language::translate('LBL_REPEAT_INTERVAL', $MODULE)}</span>
				</div>
				<div class="col-8 mb-2">
					<select class="select2 form-control repeatFrequency"
						title="{\App\Language::translate('LBL_REPEAT_FOR', $MODULE)}">
						{for $FREQUENCY = 1 to 31}
							<option value="{$FREQUENCY}" title="{$FREQUENCY}"
								{if $FREQUENCY eq $RECURRING_INFORMATION['INTERVAL']}selected{/if}>{$FREQUENCY}</option>
						{/for}
					</select>
				</div>
				<div class="{if $RECURRING_INFORMATION['FREQ'] neq 'WEEKLY'}d-none{/if} row col-12 form-row repeatWeekUI">
					<span class="col-md-4 mb-2">
						<span class="medium">{\App\Language::translate('LBL_REAPEAT_IN', $MODULE)}</span>
					</span>
					<span class="col-md-8 text-center mb-2">
						<div class="btn-group btn-group-toggle" data-toggle="buttons">
							<label title="{\App\Language::translate('LBL_DAY0', $MODULE)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SU') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SU') !== false}checked{/if}
									value="SU">
								{\App\Language::translate('LBL_SM_SUN', $MODULE)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY1', $MODULE)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'MO') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'MO') !== false}checked{/if}
									value="MO">
								{\App\Language::translate('LBL_SM_MON', $MODULE)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY2', $MODULE)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TU') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TU') !== false}checked{/if}
									value="TU">
								{\App\Language::translate('LBL_SM_TUE', $MODULE)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY3', $MODULE)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'WE') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'WE') !== false}checked{/if}
									value="WE">
								{\App\Language::translate('LBL_SM_WED', $MODULE)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY4', $MODULE)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TH') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TH') !== false}checked{/if}
									value="TH">
								{\App\Language::translate('LBL_SM_THU', $MODULE)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY5', $MODULE)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'FR') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'FR') !== false}checked{/if}
									value="FR">
								{\App\Language::translate('LBL_SM_FRI', $MODULE)}
							</label>
							<label title="{\App\Language::translate('LBL_DAY6', $MODULE)}"
								class="btn btn-outline-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SA') !== false}active{/if}">
								<input type="checkbox" autocomplete="off"
									{if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SA') !== false}checked{/if}
									value="SA">
								{\App\Language::translate('LBL_SM_SAT', $MODULE)}
							</label>
						</div>
					</span>
				</div>

				<div class="{if $RECURRING_INFORMATION['FREQ'] neq 'MONTHLY'}d-none{/if} row col-12 form-row repeatMonthUI">
					<span class="col-md-4">
						<span class="medium">{\App\Language::translate('LBL_REAPEAT_BY', $MODULE)}</span>
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
								aria-label="{\App\Language::translate('LBL_DAY_IN_MONTH', $MODULE)}"
								value="{\App\Language::translate('LBL_DAY_IN_MONTH', $MODULE)}" readonly="readonly">
						</div>
						<div class="input-group mb-2 {$WIDTHTYPE_GROUP}">
							<div class="input-group-prepend">
								<span class="input-group-text">
									<input type="radio" name="calendarMontlyType" class="calendarMontlyType" value="DAY"
										{if isset($RECURRING_INFORMATION['BYDAY'])}checked{/if}>
								</span>
							</div>
							<input type="text" class="form-control"
								aria-label="{\App\Language::translate('LBL_DAY_IN_WEEK', $MODULE)}"
								value="{\App\Language::translate('LBL_DAY_IN_WEEK', $MODULE)}" readonly="readonly">
						</div>
					</span>
				</div>
				<div class="col-4 mb-2">
					<span class="col-form-label float-left">{\App\Language::translate('LBL_REPEAT_END', $MODULE)}</span>
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
							value="{\App\Language::translate('LBL_NEVER', $MODULE)}" readonly="readonly">
					</div>
					<div class="input-group mb-2 {$WIDTHTYPE_GROUP}">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<input type="radio" name="calendarEndType" value="count"
									{if isset($RECURRING_INFORMATION['COUNT'])}checked{/if}>
								&nbsp;{\App\Language::translate('LBL_COUNT', $MODULE)}
							</span>
						</div>
						<input type="text" class="form-control countEvents"
							{if isset($RECURRING_INFORMATION['COUNT'])}value="{$RECURRING_INFORMATION['COUNT']}"
							{else}disabled="disabled" 
							{/if}
							title="{\App\Language::translate('LBL_COUNT', $MODULE)}"
							data-validation-engine='validate[required,funcCall[Vtiger_Integer_Validator_Js.invokeValidation]]' />
					</div>
					<div class="input-group mb-2 date {$WIDTHTYPE_GROUP}">
						<div class="input-group-prepend">
							<span class="input-group-text">
								<input type="radio" name="calendarEndType" value="until"
									{if isset($RECURRING_INFORMATION['UNTIL'])}checked{/if}>
								&nbsp;{\App\Language::translate('LBL_UNTIL', $MODULE)}
							</span>
						</div>
						<input type="text"
							class="dateField form-control calendarUntil datepicker" {if isset($RECURRING_INFORMATION['UNTIL'])}
							value="{$RECURRING_INFORMATION['UNTIL']}" {else} disabled="disabled"
							{/if}name="calendarUntil" data-date-format="{$USER_MODEL->get('date_format')}"
							title="{\App\Language::translate('LBL_UNTIL', $MODULE)}"
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
