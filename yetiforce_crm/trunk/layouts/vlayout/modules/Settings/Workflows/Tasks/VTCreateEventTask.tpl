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
		<div class="row-fluid padding-bottom1per">
			<span class="span2">{vtranslate('LBL_EVENT_NAME',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
			<input data-validation-engine='validate[required]' class="span9" name="eventName" type="text" value="{$TASK_OBJECT->eventName}" />
		</div>
		<div class="row-fluid padding-bottom1per">
			<span class="span2">{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</span>
			<textarea class="span9" name="description">{$TASK_OBJECT->description}</textarea>
		</div>
		<div class="row-fluid padding-bottom1per">
			<span class="span2">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</span>
			<span class="span5">
				{assign var=STATUS_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('eventstatus')->getPickListValues()}
				<select name="status" class="chzn-select">
					{foreach  from=$STATUS_PICKLIST_VALUES item=STATUS_PICKLIST_VALUE key=STATUS_PICKLIST_KEY}
						<option value="{$STATUS_PICKLIST_KEY}" {if $STATUS_PICKLIST_KEY eq $TASK_OBJECT->status} selected="" {/if}>{$STATUS_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row-fluid padding-bottom1per">
			<span class="span2">{vtranslate('LBL_TYPE',$QUALIFIED_MODULE)}</span>
			<span class="span5">
				{assign var=EVENTTYPE_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('activitytype')->getPickListValues()}
				<select name="eventType" class="chzn-select">
					{foreach  from=$EVENTTYPE_PICKLIST_VALUES item=EVENTTYPE_PICKLIST_VALUE key=EVENTTYPE_PICKLIST_KEY}
						<option value="{$EVENTTYPE_PICKLIST_KEY}" {if $EVENTTYPE_PICKLIST_KEY eq $TASK_OBJECT->eventType} selected="" {/if}>{$EVENTTYPE_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row-fluid padding-bottom1per">
			<span class="span2">{vtranslate('LBL_ASSIGNED_TO',$QUALIFIED_MODULE)}</span>
			<span class="span5">
				<select name="assigned_user_id" class="chzn-select">
					<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>
					{foreach from=$ASSIGNED_TO key=LABEL item=ASSIGNED_USERS_LIST}
						<optgroup label="{vtranslate($LABEL,$QUALIFIED_MODULE)}">
							{foreach from=$ASSIGNED_USERS_LIST item=ASSIGNED_USER key=ASSIGNED_USER_KEY}
								<option value="{$ASSIGNED_USER_KEY}" {if $ASSIGNED_USER_KEY eq $TASK_OBJECT->assigned_user_id} selected="" {/if}>{$ASSIGNED_USER}</option>
							{/foreach}
						</optgroup>
					{/foreach}
                    <optgroup label="{vtranslate('LBL_SPECIAL_OPTIONS')}">
                            <option value="copyParentOwner" {if $TASK_OBJECT->assigned_user_id eq 'copyParentOwner'} selected="" {/if}>{vtranslate('LBL_PARENT_OWNER')}</option>
                    </optgroup>
				</select>
			</span>
		</div>
		<div class="row-fluid padding-bottom1per">
			{if $TASK_OBJECT->startTime neq ''}
				{assign var=START_TIME value=$TASK_OBJECT->startTime}
			{else}
				{assign var=DATE_TIME_VALUE value=Vtiger_Datetime_UIType::getDateTimeValue('now')}
				{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
				{assign var=START_TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
			{/if}
			<span class="span2">{vtranslate('LBL_START_TIME',$QUALIFIED_MODULE)}</span>
			<div class="input-append time span6">
				<input  type="text" class="timepicker-default input-small" data-format="{$timeFormat}" value="{$START_TIME}" name="startTime" />
				<span class="add-on cursorPointer">
					<i class="icon-time"></i>
				</span>
			</div>
		</div>
		<div class="row-fluid padding-bottom1per">
			<span class="span2">{vtranslate('LBL_START_DATE',$QUALIFIED_MODULE)}</span>
			<span class="span2 row-fluid">
				<input class="span6" type="text" value="{$TASK_OBJECT->startDays}" name="startDays"
					   data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">&nbsp;
				<span class="alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
			</span>
			<span class="span marginLeftZero">
				<select class="chzn-select" name="startDirection" style="width: 100px">
					<option  {if $TASK_OBJECT->startDirection eq 'after'}selected{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
					<option {if $TASK_OBJECT->startDirection eq 'before'}selected{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
				</select>
			</span>
			<span class="span6">
				<select class="chzn-select" name="startDatefield">
					{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
						<option {if $TASK_OBJECT->startDatefield eq $DATETIME_FIELD->get('name')}selected{/if}  value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row-fluid padding-bottom1per">
			{if $TASK_OBJECT->endTime neq ''}
				{assign var=END_TIME value=$TASK_OBJECT->endTime}
			{else}
				{assign var=DATE_TIME_VALUE value=Vtiger_Datetime_UIType::getDateTimeValue('now')}
				{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
				{assign var=END_TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
			{/if}
			<span class="span2">{vtranslate('LBL_END_TIME',$QUALIFIED_MODULE)}</span>
			<div class="input-append time span6">
				<input  type="text" class="timepicker-default input-small" value="{$END_TIME}" name="endTime" />
				<span class="add-on cursorPointer">
					<i class="icon-time"></i>
				</span>
			</div>
		</div>
		<div class="row-fluid padding-bottom1per">
			<span class="span2">{vtranslate('LBL_END_DATE',$QUALIFIED_MODULE)}</span>
			<span class="span2 row-fluid">
				<input class="span6" type="text" value="{$TASK_OBJECT->endDays}" name="endDays"
					   data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">&nbsp;
				<span class="alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
			</span>
			<span class="span marginLeftZero">
				<select class="chzn-select" name="endDirection" style="width: 100px">
					<option  {if $TASK_OBJECT->endDirection eq 'after'}selected{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
					<option {if $TASK_OBJECT->endDirection eq 'before'}selected{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
				</select>
			</span>
			<span class="span6">
				<select class="chzn-select" name="endDatefield">
					{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
						<option {if $TASK_OBJECT->endDatefield eq $DATETIME_FIELD->get('name')}selected{/if}  value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$QUALIFIED_MODULE)}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row-fluid padding-bottom1per">
			<span class="span2">{vtranslate('LBL_ENABLE_REPEAT',$QUALIFIED_MODULE)}</span>
			<div class="span6">
				<input type="checkbox" name="recurringcheck" {if $TASK_OBJECT->recurringcheck eq 'on'}checked{/if} />
			</div>
		</div>
		<div class="row-fluid padding-bottom1per">
			<span class="span2">&nbsp;</span>
			<div class="row-fluid span8">
				<div>
					{assign var=QUALIFIED_MODULE value='Events'}
					<div class="{if $TASK_OBJECT->recurringcheck eq 'on'}show{else}hide{/if}" id="repeatUI">
						<div class="row-fluid">
							<div class="span">
								<span class="alignMiddle">{vtranslate('LBL_REPEATEVENT', $QUALIFIED_MODULE )}</span>
							</div>
							<div class="span">
								<select class="select2 input-mini" name="repeat_frequency">
									{for $FREQUENCY = 1 to 14}
									<option value="{$FREQUENCY}" {if $FREQUENCY eq $TASK_OBJECT->repeat_frequency}selected{/if}>{$FREQUENCY}</option>
									{/for}
								</select>
							</div>
							<div class="span">
								<select class="select2 input-medium" name="recurringtype" id="recurringType">
									<option value="Daily" {if $TASK_OBJECT->recurringtype eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $QUALIFIED_MODULE)}</option>
									<option value="Weekly" {if $TASK_OBJECT->recurringtype eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $QUALIFIED_MODULE)}</option>
									<option value="Monthly" {if $TASK_OBJECT->recurringtype eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $QUALIFIED_MODULE)}</option>
									<option value="Yearly" {if $TASK_OBJECT->recurringtype eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $QUALIFIED_MODULE)}</option>
								</select>
							</div>
							<div class="span">
								<span class="alignMiddle">{vtranslate('LBL_UNTIL', $QUALIFIED_MODULE)}</span>
							</div>
							<div class="span">
								<div class="input-append date">
									<input type="text" id="calendar_repeat_limit_date" class="dateField input-small" name="calendar_repeat_limit_date" data-date-format="{$dateFormat}"
										   value="{$REPEAT_DATE}" data-validation-engine='validate[funcCall[Vtiger_Date_Validator_Js.invokeValidation]]'/>
									<span class="add-on"><i class="icon-calendar"></i></span>
								</div>
							</div>
						</div>
						<div class="{if $TASK_OBJECT->recurringtype eq 'Weekly'}show{else}hide{/if}" id="repeatWeekUI">
							<label class="checkbox inline"><input name="sun_flag" value="sunday" {if $TASK_OBJECT->sun_flag eq "sunday"}checked{/if} type="checkbox"/>{vtranslate('LBL_SM_SUN', $QUALIFIED_MODULE)}</label>
							<label class="checkbox inline"><input name="mon_flag" value="monday" {if $TASK_OBJECT->mon_flag eq "monday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_MON', $QUALIFIED_MODULE)}</label>
							<label class="checkbox inline"><input name="tue_flag" value="tuesday" {if $TASK_OBJECT->tue_flag eq "tuesday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_TUE', $QUALIFIED_MODULE)}</label>
							<label class="checkbox inline"><input name="wed_flag" value="wednesday" {if $TASK_OBJECT->wed_flag eq "wednesday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_WED', $QUALIFIED_MODULE)}</label>
							<label class="checkbox inline"><input name="thu_flag" value="thursday" {if $TASK_OBJECT->thu_flag eq "thursday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_THU', $QUALIFIED_MODULE)}</label>
							<label class="checkbox inline"><input name="fri_flag" value="friday" {if $TASK_OBJECT->fri_flag eq "friday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_FRI', $QUALIFIED_MODULE)}</label>
							<label class="checkbox inline"><input name="sat_flag" value="saturday" {if $TASK_OBJECT->sat_flag eq "saturday"}checked{/if} type="checkbox">{vtranslate('LBL_SM_SAT', $QUALIFIED_MODULE)}</label>
						</div>
						<div class="{if $TASK_OBJECT->recurringtype eq 'Monthly'}show{else}hide{/if}" id="repeatMonthUI">
							<div class="row-fluid">
								<div class="span"><input type="radio" id="repeatDate" name="repeatMonth" checked value="date" {if $TASK_OBJECT->repeatMonth eq 'date'} checked {/if}/></div>
								<div class="span"><span class="alignMiddle">{vtranslate('LBL_ON', $QUALIFIED_MODULE)}</span></div>
								<div class="span"><input type="text" id="repeatMonthDate" class="input-mini" name="repeatMonth_date" data-validation-engine='validate[funcCall[Calendar_RepeatMonthDate_Validator_Js.invokeValidation]]' value="{$TASK_OBJECT->repeatMonth_date}"/></div>
								<div class="span alignMiddle">{vtranslate('LBL_DAY_OF_THE_MONTH', $QUALIFIED_MODULE)}</div>
							</div>
							<div class="clearfix"></div>
							<div class="row-fluid" id="repeatMonthDayUI">
								<div class="span"><input type="radio" id="repeatDay" name="repeatMonth" value="day" {if $TASK_OBJECT->repeatMonth eq 'day'} checked {/if}/></div>
								<div class="span"><span class="alignMiddle">{vtranslate('LBL_ON', $QUALIFIED_MODULE)}</span></div>
								<div class="span">
									<select id="repeatMonthDayType" class="select2 input-small" name="repeatMonth_daytype">
										<option value="first" {if $TASK_OBJECT->repeatMonth_daytype eq 'first'} selected {/if}>{vtranslate('LBL_FIRST', $QUALIFIED_MODULE)}</option>
										<option value="last" {if $TASK_OBJECT->repeatMonth_daytype eq 'last'} selected {/if}>{vtranslate('LBL_LAST', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
								<div class="span">
									<select id="repeatMonthDay" class="select2 input-medium" name="repeatMonth_day">
										<option value=1 {if $TASK_OBJECT->repeatMonth_day eq 1} selected {/if}>{vtranslate('LBL_DAY1', $QUALIFIED_MODULE)}</option>
										<option value=2 {if $TASK_OBJECT->repeatMonth_day eq 2} selected {/if}>{vtranslate('LBL_DAY2', $QUALIFIED_MODULE)}</option>
										<option value=3 {if $TASK_OBJECT->repeatMonth_day eq 3} selected {/if}>{vtranslate('LBL_DAY3', $QUALIFIED_MODULE)}</option>
										<option value=4 {if $TASK_OBJECT->repeatMonth_day eq 4} selected {/if}>{vtranslate('LBL_DAY4', $QUALIFIED_MODULE)}</option>
										<option value=5 {if $TASK_OBJECT->repeatMonth_day eq 5} selected {/if}>{vtranslate('LBL_DAY5', $QUALIFIED_MODULE)}</option>
										<option value=6 {if $TASK_OBJECT->repeatMonth_day eq 6} selected {/if}>{vtranslate('LBL_DAY6', $QUALIFIED_MODULE)}</option>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}