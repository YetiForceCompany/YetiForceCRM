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
<div class="row-fluid">
	<div class="span">
		<input type="checkbox" name="recurringcheck" value="" {if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}checked{/if}/>&nbsp;&nbsp;
	</div>
	<div class="{if $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}show{else}hide{/if} span" id="repeatUI" style="width:90%;">
		<div class="row-fluid">
			<span class="span" style="width:150px;">
				<span class="alignMiddle" style="margin-right: 6%">{vtranslate('LBL_REPEATEVENT', $MODULE)}</span>
				<select class="select2 input-mini" name="repeat_frequency">
					{for $FREQUENCY = 1 to 14}
						<option value="{$FREQUENCY}" {if $FREQUENCY eq $RECURRING_INFORMATION['repeat_frequency']}selected{/if}>{$FREQUENCY}</option>
					{/for}
				</select>
			</span>
			<span class="span">
				<select class="select2 input-medium" style="width:100px;" name="recurringtype" id="recurringType">
					<option value="Daily" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $MODULE)}</option>
					<option value="Weekly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $MODULE)}</option>
					<option value="Monthly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $MODULE)}</option>
					<option value="Yearly" {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $MODULE)}</option>
				</select>
			</span>
			<span class="span">
				<span class="alignMiddle displayInlineBlock" style="margin-right:10px;">{vtranslate('LBL_UNTIL', $MODULE)}</span>
				<span class="input-append date pull-right">
					<input type="text" id="calendar_repeat_limit_date" class="dateField input-small" name="calendar_repeat_limit_date" data-date-format="{$USER_MODEL->get('date_format')}" 
						   value="{if $RECURRING_INFORMATION['recurringcheck'] neq 'Yes'}{$TOMORROWDATE}{elseif $RECURRING_INFORMATION['recurringcheck'] eq 'Yes'}{$RECURRING_INFORMATION['recurringenddate']}{/if}" 
						   data-validation-engine='validate[required,funcCall[Vtiger_Date_Validator_Js.invokeValidation]]' style="width:100px;"/>
					<span class="add-on"><i class="icon-calendar"></i></span>
				</span>
			</span>
		</div>
		<div class="row-fluid {if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Weekly'}show{else}hide{/if}"  id="repeatWeekUI" style="margin-top:10px;">
			<span class="span2">
				<span class="pull-right medium">{vtranslate('LBL_ON', $MODULE)}</span>
			</span>
			<span class="span10">
				<label class="checkbox inline" style="margin-left: 8px;"><input name="sun_flag" value="sunday" {$RECURRING_INFORMATION['week0']} type="checkbox"/>{vtranslate('LBL_SM_SUN', $MODULE)}</label>
				<label class="checkbox inline" style="margin-left: 8px;"><input name="mon_flag" value="monday" {$RECURRING_INFORMATION['week1']} type="checkbox">{vtranslate('LBL_SM_MON', $MODULE)}</label>
				<label class="checkbox inline" style="margin-left: 8px;"><input name="tue_flag" value="tuesday" {$RECURRING_INFORMATION['week2']} type="checkbox">{vtranslate('LBL_SM_TUE', $MODULE)}</label>
				<label class="checkbox inline" style="margin-left: 8px;"><input name="wed_flag" value="wednesday" {$RECURRING_INFORMATION['week3']} type="checkbox">{vtranslate('LBL_SM_WED', $MODULE)}</label>
				<label class="checkbox inline" style="margin-left: 8px;"><input name="thu_flag" value="thursday" {$RECURRING_INFORMATION['week4']} type="checkbox">{vtranslate('LBL_SM_THU', $MODULE)}</label>
				<label class="checkbox inline" style="margin-left: 8px;"><input name="fri_flag" value="friday" {$RECURRING_INFORMATION['week5']} type="checkbox">{vtranslate('LBL_SM_FRI', $MODULE)}</label>
				<label class="checkbox inline" style="margin-left: 8px;"><input name="sat_flag" value="saturday" {$RECURRING_INFORMATION['week6']} type="checkbox">{vtranslate('LBL_SM_SAT', $MODULE)}</label>
			</span>
		</div>
		<div class="{if $RECURRING_INFORMATION['eventrecurringtype'] eq 'Monthly'}show{else}hide{/if}" id="repeatMonthUI" style="margin-top:10px;"RCa>
			<div class="row-fluid">
				<span class="span4">
					<span class="pull-right">
						<input type="radio" id="repeatDate" name="repeatMonth" checked value="date" {if $RECURRING_INFORMATION['repeatMonth'] eq 'date'} checked {/if}/>
						<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_ON', $MODULE)}</span>
					</span>	
				</span>
				<span class="span8">
					<input type="text" id="repeatMonthDate" class="input-mini" name="repeatMonth_date" data-validation-engine='validate[funcCall[Calendar_RepeatMonthDate_Validator_Js.invokeValidation]]' value="{if $RECURRING_INFORMATION['repeatMonth_date'] eq ''}2{else}{$RECURRING_INFORMATION['repeatMonth_date']}{/if}"/>
					<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_DAY_OF_THE_MONTH', $MODULE)}</span>
				</span>
				<div class="clearfix"></div>
			</div>
			
			<div class="row-fluid" id="repeatMonthDayUI" style="margin-top: 10px;">
				<span class="span4">
					<span class="pull-right">
						<input type="radio" id="repeatDay" name="repeatMonth" value="day" {if $RECURRING_INFORMATION['repeatMonth'] eq 'day'} checked {/if}/>
						<span class="alignMiddle" style="margin-left: 0.8em;">{vtranslate('LBL_ON', $MODULE)}</span>
					</span>	
				</span>
				<span class="span3">
					<select id="repeatMonthDayType" class="select2 input-small" name="repeatMonth_daytype">
						<option value="first" {if $RECURRING_INFORMATION['repeatMonth_daytype'] eq 'first'} selected {/if}>{vtranslate('LBL_FIRST', $MODULE)}</option>
						<option value="last" {if $RECURRING_INFORMATION['repeatMonth_daytype'] eq 'last'} selected {/if}>{vtranslate('LBL_LAST', $MODULE)}</option>
					</select>
				</span>
				<span class="span5 margin0px">
					<select id="repeatMonthDay" class="select2 input-medium" name="repeatMonth_day">
						<option value=0 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 0} selected {/if}>{vtranslate('LBL_DAY0', $MODULE)}</option>
						<option value=1 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 1} selected {/if}>{vtranslate('LBL_DAY1', $MODULE)}</option>
						<option value=2 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 2} selected {/if}>{vtranslate('LBL_DAY2', $MODULE)}</option>
						<option value=3 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 3} selected {/if}>{vtranslate('LBL_DAY3', $MODULE)}</option>
						<option value=4 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 4} selected {/if}>{vtranslate('LBL_DAY4', $MODULE)}</option>
						<option value=5 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 5} selected {/if}>{vtranslate('LBL_DAY5', $MODULE)}</option>
						<option value=6 {if $RECURRING_INFORMATION['repeatMonth_day'] eq 6} selected {/if}>{vtranslate('LBL_DAY6', $MODULE)}</option>
					</select>
				</span>
			</div>
		</div>
	</div>
</div>
{/strip}