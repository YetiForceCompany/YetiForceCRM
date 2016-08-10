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
	<div>
		<div class="checkbox">
			<label>
				<input type="checkbox" name="recurringcheck" value="" {if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}checked{/if} title="{vtranslate('Recurrence', $MODULE)}"/>
			</label>
		</div>
		<div class="{if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}show{else}hide{/if}" id="repeatUI" >
			<div class="clearfix">
				<div class="">
					<div class="col-xs-4 paddingLRZero marginBottom10px">
						<span class="control-label pull-left alignMiddle">{vtranslate('LBL_REPEATEVENT', $MODULE)}</span>
					</div>
					<div class="col-xs-4 marginBottom10px">
						<select class="select2 form-control" name="repeat_frequency" title="{vtranslate('LBL_REPEAT_FOR', $MODULE)}">
							{for $FREQUENCY = 1 to 14}
								<option value="{$FREQUENCY}" title="{$FREQUENCY}" {if $FREQUENCY eq $RECURRING_INFORMATION['repeat_frequency']}selected{/if}>{$FREQUENCY}</option>
							{/for}
						</select>
					</div>
					<div class="col-xs-4 paddingLRZero marginBottom10px">
						<select class="select2 form-control" name="recurringtype" id="recurringType" title="{vtranslate('LBL_RECURRING_TYPE', $MODULE)} {$MODULE}">
							<option title="{vtranslate('LBL_DAYS_TYPE', $MODULE)}" value="Daily" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $MODULE)}</option>
							<option title="{vtranslate('LBL_WEEKS_TYPE', $MODULE)}" value="Weekly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $MODULE)}</option>
							<option title="{vtranslate('LBL_MONTHS_TYPE', $MODULE)}" value="Monthly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $MODULE)}</option>
							<option title="{vtranslate('LBL_YEAR_TYPE', $MODULE)}" value="Yearly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $MODULE)}</option>
						</select>
					</div>
					<div class="col-xs-12 paddingLRZero">
						<span class="alignMiddle control-label displayInlineBlock pull-left">{vtranslate('LBL_UNTIL', $MODULE)}&nbsp;&nbsp;</span>
						<div class="input-group date">
							<input type="text" id="calendar_repeat_limit_date" class="dateField form-control" name="calendar_repeat_limit_date" data-date-format="{$USER_MODEL->get('date_format')}" 
								   value="{if $RECURRING_INFORMATION['recurringcheck'] neq 'Yes'}{$TOMORROWDATE}{elseif $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}{$RECURRING_INFORMATION['recurringenddate']}{/if}" title="{vtranslate('LBL_UNTIL', $MODULE)}"
								   data-validation-engine='validate[required,funcCall[Vtiger_Date_Validator_Js.invokeValidation]]'/>
							<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
						</div>
					</div>
				</div>
			</div>
			<div class="row {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'}show{else}hide{/if}"  id="repeatWeekUI" style="margin-top:10px;">
				<span class="col-md-2">
					<span class="pull-right medium">{vtranslate('LBL_ON', $MODULE)}</span>
				</span>
				<span class="col-md-10">
					<label class="checkbox inline" style="margin-left: 8px;"><input name="sun_flag" title="{vtranslate('LBL_DAY0', $MODULE)}" value="sunday" {$RECURRING_INFORMATION['week0']} type="checkbox"/>{vtranslate('LBL_SM_SUN', $MODULE)}</label>
					<label class="checkbox inline" style="margin-left: 8px;"><input name="mon_flag" title="{vtranslate('LBL_DAY1', $MODULE)}" value="monday" {$RECURRING_INFORMATION['week1']} type="checkbox">{vtranslate('LBL_SM_MON', $MODULE)}</label>
					<label class="checkbox inline" style="margin-left: 8px;"><input name="tue_flag" title="{vtranslate('LBL_DAY2', $MODULE)}" value="tuesday" {$RECURRING_INFORMATION['week2']} type="checkbox">{vtranslate('LBL_SM_TUE', $MODULE)}</label>
					<label class="checkbox inline" style="margin-left: 8px;"><input name="wed_flag" title="{vtranslate('LBL_DAY3', $MODULE)}" value="wednesday" {$RECURRING_INFORMATION['week3']} type="checkbox">{vtranslate('LBL_SM_WED', $MODULE)}</label>
					<label class="checkbox inline" style="margin-left: 8px;"><input name="thu_flag" title="{vtranslate('LBL_DAY4', $MODULE)}" value="thursday" {$RECURRING_INFORMATION['week4']} type="checkbox">{vtranslate('LBL_SM_THU', $MODULE)}</label>
					<label class="checkbox inline" style="margin-left: 8px;"><input name="fri_flag" title="{vtranslate('LBL_DAY5', $MODULE)}" value="friday" {$RECURRING_INFORMATION['week5']} type="checkbox">{vtranslate('LBL_SM_FRI', $MODULE)}</label>
					<label class="checkbox inline" style="margin-left: 8px;"><input name="sat_flag" title="{vtranslate('LBL_DAY6', $MODULE)}" value="saturday" {$RECURRING_INFORMATION['week6']} type="checkbox">{vtranslate('LBL_SM_SAT', $MODULE)}</label>
				</span>
			</div>
			<div class="{if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'}show{else}hide{/if}" id="repeatMonthUI" style="margin-top:10px;"RCa>
				<span class="pull-left">
					<fieldset>
						<legend class="hide">{vtranslate('LBL_REPEAT_EACH_MONTH',$MODULE)}</legend>
						<input type="radio" id="repeatDate" name="repeatMonth" checked value="date" title="{vtranslate('LBL_REPEAT_EACH_MONTH', $MODULE)}" {if $RECURRING_INFORMATION['repeatMonth'] eq 'date'} checked {/if}/>
						<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_ON', $MODULE)}</span>
					</fieldset>
				</span>
				<span class="col-md-8">
					<input type="text" id="repeatMonthDate" class="input-mini form-control" name="repeatMonth_date" data-validation-engine='validate[funcCall[Calendar_RepeatMonthDate_Validator_Js.invokeValidation]]' value="{if $RECURRING_INFORMATION['repeatMonth_date'] eq ''}2{else}{$RECURRING_INFORMATION['repeatMonth_date']}{/if}" title="{if $RECURRING_INFORMATION['repeatMonth_date'] eq ''}2{else}{$RECURRING_INFORMATION['repeatMonth_date']}{/if}"/>
					<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_DAY_OF_THE_MONTH', $MODULE)}</span>
				</span>
				<div class="clearfix"></div>

				<div id="repeatMonthDayUI" style="margin-top: 10px;">
					<span class="pull-left">
						<fieldset>
							<legend class="hide">{vtranslate('LBL_REPEAT_MONTH_DAY',$MODULE)}</legend>
							<input type="radio" id="repeatDay" name="repeatMonth" value="day" title="{vtranslate('LBL_REPEAT_MONTH_DAY', $MODULE)}" {if $RECURRING_INFORMATION['repeatMonth'] eq 'day'} checked {/if}/>
							<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_ON', $MODULE)}</span>
						</fieldset>
					</span>	
					<span class="col-md-5">
						<select id="repeatMonthDayType" title="{vtranslate('LBL_REPEAT_MONTH_DAY', $MODULE)}" class="select2 input-sm" name="repeatMonth_daytype" title="" >
							<option value="first" title="{vtranslate('LBL_FIRST', $MODULE)}" {if $RECURRING_INFORMATION['repeatMonth_daytype'] eq 'first'} selected {/if}>{vtranslate('LBL_FIRST', $MODULE)}</option>
							<option value="last"  title="{vtranslate('LBL_LAST', $MODULE)}" {if $RECURRING_INFORMATION['repeatMonth_daytype'] eq 'last'} selected {/if}>{vtranslate('LBL_LAST', $MODULE)}</option>
						</select>
					</span>
					<span class="col-md-5 margin0px">
						<select id="repeatMonthDay" class="select2 input-medium" name="repeatMonth_day" title="vtranslate('LBL_REPEAT_FOR_WEEK_DAY">
							<option title="{vtranslate('LBL_DAY0', $MODULE)}" value=0 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 0} selected {/if}>{vtranslate('LBL_DAY0', $MODULE)}</option>
							<option title="{vtranslate('LBL_DAY1', $MODULE)}" value=1 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 1} selected {/if}>{vtranslate('LBL_DAY1', $MODULE)}</option>
							<option title="{vtranslate('LBL_DAY2', $MODULE)}" value=2 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 2} selected {/if}>{vtranslate('LBL_DAY2', $MODULE)}</option>
							<option title="{vtranslate('LBL_DAY3', $MODULE)}" value=3 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 3} selected {/if}>{vtranslate('LBL_DAY3', $MODULE)}</option>
							<option title="{vtranslate('LBL_DAY4', $MODULE)}" value=4 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 4} selected {/if}>{vtranslate('LBL_DAY4', $MODULE)}</option>
							<option title="{vtranslate('LBL_DAY5', $MODULE)}" value=5 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 5} selected {/if}>{vtranslate('LBL_DAY5', $MODULE)}</option>
							<option title="{vtranslate('LBL_DAY6', $MODULE)}" value=6 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 6} selected {/if}>{vtranslate('LBL_DAY6', $MODULE)}</option>
						</select>
					</span>
				</div>
			</div>
		</div>
	</div>
{/strip}
