{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="typeSavingModal" tabindex="-1">
		<div  class="modal fade">
			<div class="modal-dialog modal-lg ">
				<div class="modal-content">
					<div class="modal-header row no-margin">
						<div class="col-xs-12 paddingLRZero">
							<div class="col-xs-8 paddingLRZero">
								<h4>{App\Language::translate('LBL_TITLE_TYPE_SAVING', $MODULE)}</h4>
							</div>
							<div class="pull-right">
								<button class="btn btn-warning marginLeft10" type="button" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
							</div>
						</div>
					</div>
					<div class="modal-body row">
						<div class="col-xs-12">
							<div class="col-xs-12 paddingLRZero marginBottom10px">
								<div class="col-xs-4">
									<button class="btn btn-primary btn-sm typeSavingBtn" data-value="2">
										{App\Language::translate('LBL_UPDATE_THIS_EVENT', $MODULE)}
									</button>
								</div>
								<div class="col-xs-8">
									{App\Language::translate('LBL_UPDATE_THIS_EVENT_DESCRIPTION', $MODULE)}
								</div>
							</div>
							<div class="col-xs-12 paddingLRZero marginBottom10px">	
								<div class="col-xs-4">
									<button class="btn btn-primary btn-sm typeSavingBtn" data-value="3">
										{App\Language::translate('LBL_UPDATE_FUTURE_EVENTS', $MODULE)}
									</button>
								</div>
								<div class="col-xs-8">
									{App\Language::translate('LBL_UPDATE_FUTURE_EVENTS_DESCRIPTION', $MODULE)}
								</div>
							</div>
							<div class="col-xs-12 paddingLRZero marginBottom10px">	
								<div class="col-xs-4">
									<button class="btn btn-primary btn-sm typeSavingBtn" data-value="1">
										{App\Language::translate('LBL_UPDATE_ALL_EVENTS', $MODULE)}
									</button>
								</div>
								<div class="col-xs-8">
									{App\Language::translate('LBL_UPDATE_ALL_EVENTS_DESCRIPTION', $MODULE)}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="hide" id="repeatUI" >
		<input type="hidden" name="typeSaving">
		<input id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" type="hidden" name="{$FIELD_MODEL->getFieldName()}" 
			   value="{$FIELD_MODEL->get('fieldvalue')}" />
		{assign var="RECURRING_INFORMATION" value=Vtiger_Recurrence_UIType::getRecurringInfo($FIELD_MODEL->get('fieldvalue'))}
		<div class="clearfix">
			<div class="col-xs-4 paddingLRZero marginBottom10px">
				<span class="control-label pull-left alignMiddle">{vtranslate('LBL_RECURRING_TYPE', $MODULE)}</span>
			</div>
			<div class="col-xs-8 paddingLRZero marginBottom10px">
				<select class="select2 form-control" name="recurringtype" id="recurringType" title="{vtranslate('LBL_RECURRING_TYPE', $MODULE)} {$MODULE}">
					<option title="{vtranslate('LBL_DAYS_TYPE', $MODULE)}" value="DAILY" {if $RECURRING_INFORMATION['FREQ'] eq 'DAILY'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $MODULE)}</option>
					<option title="{vtranslate('LBL_WEEKS_TYPE', $MODULE)}" value="WEEKLY" {if $RECURRING_INFORMATION['FREQ'] eq 'WEEKLY'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $MODULE)}</option>
					<option title="{vtranslate('LBL_MONTHS_TYPE', $MODULE)}" value="MONTHLY" {if $RECURRING_INFORMATION['FREQ'] eq 'MONTHLY'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $MODULE)}</option>
					<option title="{vtranslate('LBL_YEAR_TYPE', $MODULE)}" value="YEARLY" {if $RECURRING_INFORMATION['FREQ'] eq 'YEARLY'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $MODULE)}</option>
				</select>
			</div>
			<div class="col-xs-4 paddingLRZero marginBottom10px">
				<span class="control-label pull-left alignMiddle">{vtranslate('LBL_REPEAT_INTERVAL', $MODULE)}</span>
			</div>
			<div class="col-xs-8 paddingLRZero marginBottom10px">
				<select class="select2 form-control" id="repeatFrequency" title="{vtranslate('LBL_REPEAT_FOR', $MODULE)}">
					{for $FREQUENCY = 1 to 31}
						<option value="{$FREQUENCY}" title="{$FREQUENCY}" {if $FREQUENCY eq $RECURRING_INFORMATION['INTERVAL']}selected{/if}>{$FREQUENCY}</option>
					{/for}
				</select>
			</div>
			<div class="{if $RECURRING_INFORMATION['FREQ'] neq 'WEEKLY'}hide{/if}"  id="repeatWeekUI" style="margin-top:10px;">
				<span class="col-md-4 paddingLRZero">
					<span class="medium">{vtranslate('LBL_REAPEAT_IN', $MODULE)}</span>
				</span>
				<span class="col-md-8 paddingLRZero marginBottom10px">
					<div class="btn-group" data-toggle="buttons">
						<label title="{vtranslate('LBL_DAY0', $MODULE)}" class="btn btn-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SU') !== false}active{/if}">
							<input type="checkbox" autocomplete="off"  {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SU') !== false}checked{/if} value="SU">
							{vtranslate('LBL_SM_SUN', $MODULE)}
						</label>
						<label title="{vtranslate('LBL_DAY1', $MODULE)}"  class="btn btn-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'MO') !== false}active{/if}">
							<input type="checkbox" autocomplete="off" {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'MO') !== false}checked{/if} value="MO">
							{vtranslate('LBL_SM_MON', $MODULE)}
						</label>
						<label title="{vtranslate('LBL_DAY2', $MODULE)}" class="btn btn-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TU') !== false}active{/if}">
							<input type="checkbox" autocomplete="off"  {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TU') !== false}checked{/if} value="TU">
							{vtranslate('LBL_SM_TUE', $MODULE)}
						</label>
						<label title="{vtranslate('LBL_DAY3', $MODULE)}"  class="btn btn-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'WE') !== false}active{/if}">
							<input type="checkbox" autocomplete="off" {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'WE') !== false}checked{/if} value="WE">
							{vtranslate('LBL_SM_WED', $MODULE)}
						</label>
						<label title="{vtranslate('LBL_DAY4', $MODULE)}" class="btn btn-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TH') !== false}active{/if}">
							<input type="checkbox" autocomplete="off"  {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'TH') !== false}checked{/if} value="TH">
							{vtranslate('LBL_SM_THU', $MODULE)}
						</label>
						<label title="{vtranslate('LBL_DAY5', $MODULE)}"  class="btn btn-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'FR') !== false}active{/if}">
							<input type="checkbox" autocomplete="off" {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'FR') !== false}checked{/if} value="FR">
							{vtranslate('LBL_SM_FRI', $MODULE)}
						</label>
						<label title="{vtranslate('LBL_DAY6', $MODULE)}" class="btn btn-primary {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SA') !== false}active{/if}">
							<input type="checkbox" autocomplete="off"  {if isset($RECURRING_INFORMATION['BYDAY']) && strpos($RECURRING_INFORMATION['BYDAY'], 'SA') !== false}checked{/if} value="SA">
							{vtranslate('LBL_SM_SAT', $MODULE)}
						</label>
					</div>
				</span>
			</div>

			<div class="{if $RECURRING_INFORMATION['FREQ'] neq 'MONTHLY'}hide{/if} col-xs-12 paddingLRZero" id="repeatMonthUI" style="margin-top:10px;">
				<span class="col-md-4 paddingLRZero">
					<span class="medium">{vtranslate('LBL_REAPEAT_BY', $MODULE)}</span>
				</span>
				<span class="col-md-8 paddingLRZero">
					<div class="input-group marginBottom10px">
						<span class="input-group-addon">
							<input type="radio" class="calendarMontlyType" value="DATE">
						</span>
						<input type="text" class="form-control" aria-label="{vtranslate('LBL_DAY_IN_MONTH', $MODULE)}" value="{vtranslate('LBL_DAY_IN_MONTH', $MODULE)}" readonly="readonly">
					</div>
					<div class="input-group marginBottom10px">
						<span class="input-group-addon">
							<input type="radio" class="calendarMontlyType" value="DAY" {if isset($RECURRING_INFORMATION['BYDAY'])}checked{/if}>
						</span>
						<input type="text" class="form-control" aria-label="{vtranslate('LBL_DAY_IN_WEEK', $MODULE)}" value="{vtranslate('LBL_DAY_IN_WEEK', $MODULE)}" readonly="readonly">
					</div>
				</span>
			</div>
			<div class="col-xs-4 paddingLRZero marginBottom10px">
				<span class="control-label pull-left alignMiddle">{vtranslate('LBL_REPEAT_END', $MODULE)}</span>
			</div>
			<div class="col-xs-8 paddingLRZero marginBottom10px">
				<div class="input-group marginBottom10px">
					<span class="input-group-addon">
						<input type="radio" name="calendarEndType" value="never" {if isset($RECURRING_INFORMATION['COUNT']) && $RECURRING_INFORMATION['COUNT'] eq 0}checked{/if}>
					</span>
					<input type="text" class="form-control" aria-label="" value="{vtranslate('LBL_NEVER', $MODULE)}" readonly="readonly">
				</div>
				<div class="input-group marginBottom10px">
					<span class="input-group-addon">
						<input type="radio" name="calendarEndType" value="count" {if isset($RECURRING_INFORMATION['COUNT']) && $RECURRING_INFORMATION['COUNT'] neq 0}checked{/if}>
						&nbsp;{vtranslate('LBL_COUNT', $MODULE)}
					</span>
					<input type="text" class="form-control countEvents" {if isset($RECURRING_INFORMATION['COUNT']) && $RECURRING_INFORMATION['COUNT'] neq 0}value="{$RECURRING_INFORMATION['COUNT']}"{else}disabled="disabled" {/if}>
				</div>
				<div class="input-group marginBottom10px date">
					<span class="input-group-addon">
						<input type="radio" name="calendarEndType" value="until" {if isset($RECURRING_INFORMATION['UNTIL'])}checked{/if}>
						&nbsp;{vtranslate('LBL_UNTIL', $MODULE)}
					</span>
					<input type="text"class="dateField form-control calendarUntil" {if isset($RECURRING_INFORMATION['UNTIL'])} value="{$RECURRING_INFORMATION['UNTIL']}"  {else} disabled="disabled"{/if}name="calendarUntil" data-date-format="{$USER_MODEL->get('date_format')}" 
						   title="{vtranslate('LBL_UNTIL', $MODULE)}"
						   data-validation-engine='validate[required,funcCall[Vtiger_Date_Validator_Js.invokeValidation]]' data-validator='{\App\Json::encode([['name' => 'greaterThanDependentField', 'params' => ['date_start']]])}'/>
					<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
				</div>
			</div>
		</div>
	</div>
{/strip}
