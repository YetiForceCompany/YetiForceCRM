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
	<div class="row">
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_EVENT_NAME',$QUALIFIED_MODULE)}<span class="redColor">*</span></span>
			<div class="col-md-9">
				<input data-validation-engine='validate[required]' class="form-control" name="eventName" type="text" value="{$TASK_OBJECT->eventName}" />
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_DESCRIPTION',$QUALIFIED_MODULE)}</span>
			<div class="col-md-9">
				<textarea class="form-control" name="description">{$TASK_OBJECT->description}</textarea>
			</div>
		</div>
		{*<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</span>
			<span class="col-md-5">
				{assign var=STATUS_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('activitystatus')->getPickListValues()}
				<select name="status" class="chzn-select form-control">
					{foreach  from=$STATUS_PICKLIST_VALUES item=STATUS_PICKLIST_VALUE key=STATUS_PICKLIST_KEY}
						<option value="{$STATUS_PICKLIST_KEY}" {if $STATUS_PICKLIST_KEY eq $TASK_OBJECT->status} selected="" {/if}>{$STATUS_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>*}
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_TYPE',$QUALIFIED_MODULE)}</span>
			<span class="col-md-5">
				{assign var=EVENTTYPE_PICKLIST_VALUES value=$TASK_TYPE_MODEL->getTaskBaseModule()->getField('activitytype')->getPickListValues()}
				<select name="eventType" class="chzn-select form-control">
					{foreach  from=$EVENTTYPE_PICKLIST_VALUES item=EVENTTYPE_PICKLIST_VALUE key=EVENTTYPE_PICKLIST_KEY}
						<option value="{$EVENTTYPE_PICKLIST_KEY}" {if $EVENTTYPE_PICKLIST_KEY eq $TASK_OBJECT->eventType} selected="" {/if}>{$EVENTTYPE_PICKLIST_VALUE}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_ASSIGNED_TO',$QUALIFIED_MODULE)}</span>
			<span class="col-md-5">
				<select name="assigned_user_id" class="chzn-select form-control">
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
						<option value="currentUser" {if $TASK_OBJECT->assigned_user_id eq 'currentUser'} selected="" {/if}>{vtranslate('LBL_CURRENT_USER',$QUALIFIED_MODULE)}</option>
						<option value="triggerUser" {if $TASK_OBJECT->assigned_user_id eq 'triggerUser'} selected="" {/if}>{vtranslate('LBL_TRIGGER_USER',$QUALIFIED_MODULE)}</option>
                    </optgroup>
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			{if $TASK_OBJECT->startTime neq ''}
				{assign var=START_TIME value=$TASK_OBJECT->startTime}
			{else}
				{assign var=DATE_TIME_VALUE value=Vtiger_Datetime_UIType::getDateTimeValue('now')}
				{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
				{assign var=START_TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
			{/if}
			<span class="col-md-2 control-label">{vtranslate('LBL_START_TIME',$QUALIFIED_MODULE)}</span>
			<div class="col-md-2">
				<div class="input-group time input-group-sm">
					<input  type="text" class="timepicker-default form-control" data-format="{$timeFormat}" value="{$START_TIME}" name="startTime" />
					<span class="input-group-addon cursorPointer">
						<i class="glyphicon glyphicon-time"></i>
					</span>
				</div>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_START_DATE',$QUALIFIED_MODULE)}</span>
			<div class="col-md-2">
				<input class="form-control" type="text" value="{$TASK_OBJECT->startDays}" name="startDays"
					   data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">
			</div>
			<span class="control-label pull-left alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
			<span class="span marginLeftZero col-md-3">
				<select class="chzn-select form-control" name="startDirection">
					<option  {if $TASK_OBJECT->startDirection eq 'after'}selected{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
					<option {if $TASK_OBJECT->startDirection eq 'before'}selected{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
				</select>
			</span>
			<span class="col-md-4">
				<select class="chzn-select form-control" name="startDatefield">
					{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
						<option {if $TASK_OBJECT->startDatefield eq $DATETIME_FIELD->get('name')}selected{/if}  value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			{if $TASK_OBJECT->endTime neq ''}
				{assign var=END_TIME value=$TASK_OBJECT->endTime}
			{else}
				{assign var=DATE_TIME_VALUE value=Vtiger_Datetime_UIType::getDateTimeValue('now')}
				{assign var=DATE_TIME_COMPONENTS value=explode(' ' ,$DATE_TIME_VALUE)}
				{assign var=END_TIME value=implode(' ',array($DATE_TIME_COMPONENTS[1],$DATE_TIME_COMPONENTS[2]))}
			{/if}
			<span class="col-md-2 control-label">{vtranslate('LBL_END_TIME',$QUALIFIED_MODULE)}</span>
			<div class="col-md-2">
				<div class="input-group time input-group-sm">
					<input  type="text" class="timepicker-default form-control" value="{$END_TIME}" name="endTime" />
					<span class="input-group-addon cursorPointer">
						<i class="glyphicon glyphicon-time"></i>
					</span>
				</div>
			</div>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_END_DATE',$QUALIFIED_MODULE)}</span>
			<span class="col-md-2">
				<input class="form-control" type="text" value="{$TASK_OBJECT->endDays}" name="endDays"
					   data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">
			</span>
			<span class="control-label pull-left alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
			<span class="col-md-3 marginLeftZero">
				<select class="chzn-select form-control" name="endDirection">
					<option  {if $TASK_OBJECT->endDirection eq 'after'}selected{/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
					<option {if $TASK_OBJECT->endDirection eq 'before'}selected{/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
				</select>
			</span>
			<span class="col-md-4">
				<select class="chzn-select form-control" name="endDatefield">
					{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
						<option {if $TASK_OBJECT->endDatefield eq $DATETIME_FIELD->get('name')}selected{/if}  value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$SOURCE_MODULE)}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row padding-bottom1per">
			<span class="col-md-2 control-label">{vtranslate('LBL_ENABLE_REPEAT',$QUALIFIED_MODULE)}</span>
			<div class="col-md-6 checkbox">
				<input type="checkbox" class="marginLeftZero" name="recurringcheck" {if $TASK_OBJECT->recurringcheck eq 'on'}checked{/if} />
			</div>
		</div>
		<div class="row padding-bottom1per">
			<div>
				{assign var=QUALIFIED_MODULE value='Events'}
				<div class="{if $TASK_OBJECT->recurringcheck neq 'on'}hide{/if}" id="repeatUI">
					<div class="row col-md-12">
						<span class="col-xs-2 alignMiddle control-label">{vtranslate('LBL_REPEATEVENT', $QUALIFIED_MODULE )}</span>
						<div class="col-xs-2">
							<select class="select2 form-control" name="repeat_frequency">
								{for $FREQUENCY = 1 to 14}
									<option value="{$FREQUENCY}" {if $FREQUENCY eq $TASK_OBJECT->repeat_frequency}selected{/if}>{$FREQUENCY}</option>
								{/for}
							</select>
						</div>
						<div class="col-xs-2">
							<select class="select2 form-control" name="recurringtype" id="recurringType">
								<option value="Daily" {if $TASK_OBJECT->recurringtype eq 'Daily'} selected {/if}>{vtranslate('LBL_DAYS_TYPE', $QUALIFIED_MODULE)}</option>
								<option value="Weekly" {if $TASK_OBJECT->recurringtype eq 'Weekly'} selected {/if}>{vtranslate('LBL_WEEKS_TYPE', $QUALIFIED_MODULE)}</option>
								<option value="Monthly" {if $TASK_OBJECT->recurringtype eq 'Monthly'} selected {/if}>{vtranslate('LBL_MONTHS_TYPE', $QUALIFIED_MODULE)}</option>
								<option value="Yearly" {if $TASK_OBJECT->recurringtype eq 'Yearly'} selected {/if}>{vtranslate('LBL_YEAR_TYPE', $QUALIFIED_MODULE)}</option>
							</select>

						</div>
						<span class="control-label pull-left alignMiddle">{vtranslate('LBL_UNTIL', $QUALIFIED_MODULE)}</span>
						<div class="col-xs-3">
							<div class="input-group date">
								<input type="text" id="calendar_repeat_limit_date" class="dateField form-control" name="calendar_repeat_limit_date" data-date-format="{$dateFormat}"
									   value="{$REPEAT_DATE}" data-validation-engine='validate[funcCall[Vtiger_Date_Validator_Js.invokeValidation]]'/>
								<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
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
						<div class="row">
							<div class="span"><input type="radio" id="repeatDate" name="repeatMonth" checked value="date" {if $TASK_OBJECT->repeatMonth eq 'date'} checked {/if}/></div>
							<div class="span"><span class="alignMiddle">{vtranslate('LBL_ON', $QUALIFIED_MODULE)}</span></div>
							<div class="span"><input type="text" id="repeatMonthDate" class="form-control" name="repeatMonth_date" data-validation-engine='validate[funcCall[Calendar_RepeatMonthDate_Validator_Js.invokeValidation]]' value="{$TASK_OBJECT->repeatMonth_date}"/></div>
							<div class="span alignMiddle">{vtranslate('LBL_DAY_OF_THE_MONTH', $QUALIFIED_MODULE)}</div>
						</div>
						<div class="clearfix"></div>
						<div class="row" id="repeatMonthDayUI">
							<div class="span"><input type="radio" id="repeatDay" name="repeatMonth" value="day" {if $TASK_OBJECT->repeatMonth eq 'day'} checked {/if}/></div>
							<div class="span"><span class="alignMiddle">{vtranslate('LBL_ON', $QUALIFIED_MODULE)}</span></div>
							<div class="span">
								<select id="repeatMonthDayType" class="select2 form-control" name="repeatMonth_daytype">
									<option value="first" {if $TASK_OBJECT->repeatMonth_daytype eq 'first'} selected {/if}>{vtranslate('LBL_FIRST', $QUALIFIED_MODULE)}</option>
									<option value="last" {if $TASK_OBJECT->repeatMonth_daytype eq 'last'} selected {/if}>{vtranslate('LBL_LAST', $QUALIFIED_MODULE)}</option>
								</select>
							</div>
							<div class="span">
								<select id="repeatMonthDay" class="select2 form-control" name="repeatMonth_day">
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
{/strip}
