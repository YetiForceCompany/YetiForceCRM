{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-Workflows-ScheduleBox -->
	<div id="scheduleBox"
		class="well u-timetable js-wf-execution-item {if $EXECUTION_CONDITION neq 6} d-none {/if}">
		<div class="form-row">
			<div class="col-md-2 d-flex align-items-center">{\App\Language::translate('LBL_RUN_WORKFLOW', $QUALIFIED_MODULE)}</div>
			{if !empty($WORKFLOW_MODEL_OBJ->schtypeid)}
				{assign var=SCHTYPE_ID value=$WORKFLOW_MODEL_OBJ->schtypeid}
			{/if }
			<div class="col-md-6 d-flex align-items-center">
				<select class="select2 form-control" id="schtypeid" name="schtypeid">
					{foreach from= Workflow::$SCHEDULED_LIST item=LABEL key=ID}
						<option value="{$ID}" {if !empty($SCHTYPE_ID) && ($SCHTYPE_ID eq $ID)}selected{/if}>
							{\App\Language::translate($LABEL, $QUALIFIED_MODULE)}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
		{* show weekdays for weekly option *}
		<div class="form-row {if empty($SCHTYPE_ID) || $SCHTYPE_ID neq 3} d-none {/if}"
			id="scheduledWeekDay">
			<div class="col-md-2 d-flex align-items-center">{\App\Language::translate('LBL_ON_THESE_DAYS', $QUALIFIED_MODULE)}</div>
			<div class="col-md-6 d-flex align-items-center">
				{if !empty($WORKFLOW_MODEL_OBJ->schdayofweek)}
					{assign var=SCHDAY_OF_WEEK value=$WORKFLOW_MODEL_OBJ->schdayofweek}
					{assign var=DAY_OF_WEEK value=\App\Json::decode($SCHDAY_OF_WEEK)}
				{/if }
				<select multiple class="select2 col-md-6"
					data-validation-engine="validate[rquired,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
					name="schdayofweek" id="schdayofweek">
					<option value="7" {if !empty($DAY_OF_WEEK) && (is_array($DAY_OF_WEEK) && in_array('7', $DAY_OF_WEEK))} selected {/if}>{\App\Language::translate('LBL_DAY0', 'Calendar')}</option>
					<option value="1" {if !empty($DAY_OF_WEEK) && (is_array($DAY_OF_WEEK) && in_array('1', $DAY_OF_WEEK))} selected {/if}>{\App\Language::translate('LBL_DAY1', 'Calendar')}</option>
					<option value="2" {if !empty($DAY_OF_WEEK) && (is_array($DAY_OF_WEEK) && in_array('2', $DAY_OF_WEEK))} selected {/if}>{\App\Language::translate('LBL_DAY2', 'Calendar')}</option>
					<option value="3" {if !empty($DAY_OF_WEEK) && (is_array($DAY_OF_WEEK) && in_array('3', $DAY_OF_WEEK))} selected {/if}>{\App\Language::translate('LBL_DAY3', 'Calendar')}</option>
					<option value="4" {if !empty($DAY_OF_WEEK) && (is_array($DAY_OF_WEEK) && in_array('4', $DAY_OF_WEEK))} selected {/if}>{\App\Language::translate('LBL_DAY4', 'Calendar')}</option>
					<option value="5" {if !empty($DAY_OF_WEEK) && (is_array($DAY_OF_WEEK) && in_array('5', $DAY_OF_WEEK))} selected {/if}>{\App\Language::translate('LBL_DAY5', 'Calendar')}</option>
					<option value="6" {if !empty($DAY_OF_WEEK) && (is_array($DAY_OF_WEEK) && in_array('6', $DAY_OF_WEEK))} selected {/if}>{\App\Language::translate('LBL_DAY6', 'Calendar')}</option>
				</select>
			</div>
		</div>

		{* show month view by dates *}
		<div class="form-row {if empty($SCHTYPE_ID) || $SCHTYPE_ID neq 5} d-none {/if}"
			id="scheduleMonthByDates">
			<div class="col-md-2 d-flex align-items-center">{\App\Language::translate('LBL_ON_THESE_DAYS', $QUALIFIED_MODULE)}</div>
			<div class="col-md-6 d-flex align-items-center">

				<select multiple class="select2"
					data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
					name='schdayofmonth' id='schdayofmonth'>
					{if !empty($WORKFLOW_MODEL_OBJ->schdayofmonth)}
						{assign var=SCHDAY_OF_MONTH value=$WORKFLOW_MODEL_OBJ->schdayofmonth}
					{/if }
					{if !empty($SCHDAY_OF_MONTH)}
						{assign var=DAYS value=\App\Json::decode($SCHDAY_OF_MONTH)}
					{/if }
					{section name=foo loop=31}
						<option value={$smarty.section.foo.iteration} {if !empty($DAYS) && is_array($DAYS) && in_array($smarty.section.foo.iteration, $DAYS)}selected{/if}>{$smarty.section.foo.iteration}</option>
					{/section}
				</select>
			</div>
		</div>

		{* show specific date *}
		<div class='form-row {if empty($SCHTYPE_ID) || $SCHTYPE_ID neq 4} d-none {/if}'
			id='scheduleByDate'>
			<div class="col-md-2 d-flex align-items-center">{\App\Language::translate('LBL_CHOOSE_DATE', $QUALIFIED_MODULE)}</div>
			<div class="col-md-6 d-flex align-items-center">
				<div class="date w-100">
					<div class="input-group dateTime">
						{assign var=SCHANNUAL_DATES value=''}
						{if !empty($SCHTYPE_ID) && $SCHTYPE_ID eq 4 && !empty($WORKFLOW_MODEL_OBJ->schannualdates)}
							{assign var=SCHANNUAL_DATES value=\App\Json::decode($WORKFLOW_MODEL_OBJ->schannualdates)}
							{assign var=SCHANNUAL_DATES value=\App\Purifier::encodeHtml(implode(',',array_map('App\Fields\DateTime::formatToDisplay',$SCHANNUAL_DATES)))}
						{/if}
						<input type="text" class="dateTimePickerField form-control datepicker" name="schdate"
							value="{if !empty($SCHANNUAL_DATES)}{$SCHANNUAL_DATES}{/if}"
							data-date-format="{$USER_MODEL->get('date_format')}"
							data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
						<div class=" input-group-append">
							<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
								<span class="fas fa-calendar-alt"></span> &nbsp; <span class="far fa-clock"></span>
							</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		{* show month view by weekday *}
		<div class='form-row {if empty($SCHTYPE_ID) || $SCHTYPE_ID neq 6} d-none {/if}'
			id='scheduleMonthByWeekDays'>
		</div>
		{* show month view by anually *}
		<div class="form-row my-1 {if empty($SCHTYPE_ID) || $SCHTYPE_ID neq 7} d-none {/if}" id='scheduleAnually'>
			<div class="col-md-2">{\App\Language::translate('LBL_SELECTED_DATES', $QUALIFIED_MODULE)}</div>
			<div class="input-group col-md-10 date">
				{assign var=SCHANNUAL_DATES value=''}
				{if !empty($SCHTYPE_ID) && $SCHTYPE_ID eq 7 && !empty($WORKFLOW_MODEL_OBJ->schannualdates)}
					{assign var=SCHANNUAL_DATES value=\App\Json::decode($WORKFLOW_MODEL_OBJ->schannualdates)}
					{assign var=SCHANNUAL_DATES value=\App\Purifier::encodeHtml(implode(',',array_map('App\Fields\Date::formatToDisplay',$SCHANNUAL_DATES)))}
				{/if}
				<textarea class="dateField datepicker form-control" id="annualDates" name="schannualdates" readonly="readonly"
					data-date="{if !empty($SCHANNUAL_DATES)}{$SCHANNUAL_DATES}{/if}">
							{if !empty($SCHANNUAL_DATES)}{$SCHANNUAL_DATES}{/if}
						</textarea>
				<div class="input-group-append">
					<span class="input-group-text u-cursor-pointer js-date__btn"
						data-js="click">
						<span class="fas fa-calendar-alt"></span>
					</span>
				</div>
			</div>
		</div>
		{* show time for all other than Hourly option*}
		<div class="form-row pt-1 pb-2 px-0 {if empty($SCHTYPE_ID) || !in_array($SCHTYPE_ID, [2,11,12,13])} d-none {/if}"
			id="scheduledTime">
			<div class="col-md-2 d-flex align-items-center">
				{\App\Language::translate('LBL_AT_TIME', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-6 d-flex align-items-center" id="schtime">
				<div class="input-group time">
					{if !empty($WORKFLOW_MODEL_OBJ->schtime)}
						{assign var=SCHTIME value=\App\Fields\Time::formatToDisplay($WORKFLOW_MODEL_OBJ->schtime)}
					{/if}
					<input type="text" class="clockPicker form-control"
						data-format="{$USER_MODEL->get('hour_format')}"
						name="schtime" value="{if !empty($SCHTIME)}{$SCHTIME}{/if}"
						autocomplete="off"
						data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
					<div class="input-group-append">
						<span class="input-group-text u-cursor-pointer js-clock__btn"
							data-js="click">
							<span class="far fa-clock"></span>
						</span>
					</div>
				</div>
			</div>
		</div>
		<div class="mb-2 mt-2 js-wf-execution-item" data-js="container">
			<div class="form-check">
				<input type="hidden" name="params[iterationOff]" value="0">
				<input class="form-check-input" type="checkbox" value="1" id="iterationOff" name="params[iterationOff]" {if !empty($PARAMS['iterationOff'])} checked="checked" {/if}>
				<label class="form-check-label ml-1" for="iterationOff">
					{\App\Language::translate('LBL_WORKFLOW_TRIGGER_RECORD_RESTRICTION_OFF', $QUALIFIED_MODULE)}
				</label>
			</div>
		</div>
		{if !empty($WORKFLOW_MODEL_OBJ->nexttrigger_time)}
			<hr class="mt-2">
			<div class="form-row">
				<div class="col-md-2 d-flex align-items-center">
					<span>{\App\Language::translate('LBL_NEXT_TRIGGER_TIME', $QUALIFIED_MODULE)}</span>
				</div>
				<div class="col-md-6 d-flex align-items-center">
					{\App\Fields\DateTime::formatToDisplay($WORKFLOW_MODEL_OBJ->nexttrigger_time)}
					<span>&nbsp;({$USER_MODEL->time_zone})</span>
				</div>
			</div>
		{/if}
	</div>
	<!-- /tpl-Settings-Workflows-ScheduleBox -->
{/strip}
