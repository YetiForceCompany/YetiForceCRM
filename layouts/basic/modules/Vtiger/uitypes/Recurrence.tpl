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
	<div class="hide" id="repeatUI" >
		<div class="clearfix">
			<div class="col-xs-4 paddingLRZero marginBottom10px">
				<span class="control-label pull-left alignMiddle">{vtranslate('LBL_RECURRING_TYPE', $MODULE)}</span>
			</div>
			<div class="col-xs-8 paddingLRZero marginBottom10px">
				<select class="select2 form-control" name="recurringtype" id="recurringType" title="{vtranslate('LBL_RECURRING_TYPE', $MODULE)} {$MODULE}">
					<option title="{vtranslate('LBL_DAYS_TYPE', $MODULE)}" value="Daily" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $MODULE)}</option>
					<option title="{vtranslate('LBL_WEEKS_TYPE', $MODULE)}" value="Weekly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $MODULE)}</option>
					<option title="{vtranslate('LBL_MONTHS_TYPE', $MODULE)}" value="Monthly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $MODULE)}</option>
					<option title="{vtranslate('LBL_YEAR_TYPE', $MODULE)}" value="Yearly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $MODULE)}</option>
				</select>
			</div>
			<div class="col-xs-4 paddingLRZero marginBottom10px">
				<span class="control-label pull-left alignMiddle">{vtranslate('LBL_REPEAT_INTERVAL', $MODULE)}</span>
			</div>
			<div class="col-xs-8 paddingLRZero marginBottom10px">
				<select class="select2 form-control" name="repeat_frequency" title="{vtranslate('LBL_REPEAT_FOR', $MODULE)}">
					{for $FREQUENCY = 1 to 31}
						<option value="{$FREQUENCY}" title="{$FREQUENCY}" {if $FREQUENCY eq $RECURRING_INFORMATION['repeat_frequency']}selected{/if}>{$FREQUENCY}</option>
					{/for}
				</select>
			</div>
			<div class="hide"  id="repeatWeekUI" style="margin-top:10px;">
				<span class="col-md-4 paddingLRZero">
					<span class="medium">{vtranslate('LBL_REAPEAT_IN', $MODULE)}</span>
				</span>
				<span class="col-md-8 paddingLRZero">
					<label class="inline" style="margin-left: 8px;"><input name="sun_flag" title="{vtranslate('LBL_DAY0', $MODULE)}" value="sunday" {$RECURRING_INFORMATION['week0']} type="checkbox"/>{vtranslate('LBL_SM_SUN', $MODULE)}</label>
					<label class="inline" style="margin-left: 8px;"><input name="mon_flag" title="{vtranslate('LBL_DAY1', $MODULE)}" value="monday" {$RECURRING_INFORMATION['week1']} type="checkbox">{vtranslate('LBL_SM_MON', $MODULE)}</label>
					<label class="inline" style="margin-left: 8px;"><input name="tue_flag" title="{vtranslate('LBL_DAY2', $MODULE)}" value="tuesday" {$RECURRING_INFORMATION['week2']} type="checkbox">{vtranslate('LBL_SM_TUE', $MODULE)}</label>
					<label class="inline" style="margin-left: 8px;"><input name="wed_flag" title="{vtranslate('LBL_DAY3', $MODULE)}" value="wednesday" {$RECURRING_INFORMATION['week3']} type="checkbox">{vtranslate('LBL_SM_WED', $MODULE)}</label>
					<label class="inline" style="margin-left: 8px;"><input name="thu_flag" title="{vtranslate('LBL_DAY4', $MODULE)}" value="thursday" {$RECURRING_INFORMATION['week4']} type="checkbox">{vtranslate('LBL_SM_THU', $MODULE)}</label>
					<label class="inline" style="margin-left: 8px;"><input name="fri_flag" title="{vtranslate('LBL_DAY5', $MODULE)}" value="friday" {$RECURRING_INFORMATION['week5']} type="checkbox">{vtranslate('LBL_SM_FRI', $MODULE)}</label>
					<label class="inline" style="margin-left: 8px;"><input name="sat_flag" title="{vtranslate('LBL_DAY6', $MODULE)}" value="saturday" {$RECURRING_INFORMATION['week6']} type="checkbox">{vtranslate('LBL_SM_SAT', $MODULE)}</label>
				</span>
			</div>
			<div class="hide col-xs-12 paddingLRZero" id="repeatMonthUI" style="margin-top:10px;">
				<span class="col-md-4 paddingLRZero">
					<span class="medium">{vtranslate('LBL_REAPEAT_BY', $MODULE)}</span>
				</span>
				<span class="col-md-8 paddingLRZero">
					<div class="radio">
						<label>
							<input type="radio" name="calendarMontlyType">
							{vtranslate('LBL_DAY_IN_MONTH', $MODULE)}
						</label>
					</div>
					<div class="radio">
						<label>
							<input type="radio" name="calendarMontlyType">
							{vtranslate('LBL_DAY_IN_WEEK', $MODULE)}
						</label>
					</div>
				</span>
			</div>
			<div class="col-xs-4 paddingLRZero marginBottom10px">
				<span class="control-label pull-left alignMiddle">{vtranslate('LBL_REPEAT_END', $MODULE)}</span>
			</div>
			<div class="col-xs-8 paddingLRZero marginBottom10px">
				<div class="radio">
					<label>
						<input type="radio" name="calendarEndType" value="never" checked>
						{vtranslate('LBL_NEVER', $MODULE)}
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="calendarEndType" value="count">
						{vtranslate('LBL_COUNT', $MODULE)}
						<input type="text" class="form-control countEvents" disabled="disabled">
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="calendarEndType" value="until">
						{vtranslate('LBL_UNTIL', $MODULE)}
						<div class="input-group date">
							<input type="text"class="dateField form-control calendarUntil" disabled="disabled" name="calendarUntil" data-date-format="{$USER_MODEL->get('date_format')}" 
								   value="" title="{vtranslate('LBL_UNTIL', $MODULE)}"
								   data-validation-engine='validate[required,funcCall[Vtiger_Date_Validator_Js.invokeValidation]]'/>
							<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
						</div>
					</label>
				</div>
			</div>
		</div>
	</div>
{/strip}
