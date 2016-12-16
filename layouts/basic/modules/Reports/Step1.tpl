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
	<div class="reportContents">
		<form class="form-horizontal recordEditView" id="report_step1" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="view" value="{$VIEW}" />
			<input type="hidden" name="mode" value="step2" />
			<input type="hidden" class="step" value="1" />
			<input type="hidden" name="isDuplicate" value="{$IS_DUPLICATE}" />
			<input type="hidden" name="record" value="{$RECORD_ID}" />
			<input type="hidden" id="relatedModules" data-value="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode($RELATED_MODULES))}" />
			<input type="hidden" id="weekStartDay" data-value='{$WEEK_START_ID}' />
			<div class="well contentsBackground">
				<div class="row marginBottom5px">
					<div class="col-md-3"><span class="redColor">*</span>{vtranslate('LBL_REPORT_NAME',$MODULE)}</div>
					<div class="col-md-7"><input class="col-md-6 form-control" data-validation-engine='validate[required]' type="text" name="reportname" title="{vtranslate('LBL_REPORT_NAME', $MODULE)}" value="{$REPORT_MODEL->get('reportname')}"/></div>
				</div>
				<div class="row marginBottom5px paddingTop10">
					<div class="col-md-3"><span class="redColor">*</span>{vtranslate('LBL_REPORT_FOLDER',$MODULE)}</div>
					<div class="col-md-7">
						<select class="chzn-select col-md-6 form-control" name="folderid">
							<optgroup>
								{foreach item=REPORT_FOLDER from=$REPORT_FOLDERS}
									<option value="{$REPORT_FOLDER->getId()}"
											{if $REPORT_FOLDER->getId() eq $REPORT_MODEL->get('folderid')}
												selected=""
											{/if}
											>{vtranslate($REPORT_FOLDER->getName(), $MODULE)}</option>
								{/foreach}
							</optgroup>
						</select>
					</div>
				</div>
				<div class="row marginBottom5px paddingTop10">
					<div class="col-md-3"><span class="redColor">*</span>{vtranslate('PRIMARY_MODULE',$MODULE)}</div>
					<div class="col-md-7">
						<select class="col-md-6 chzn-select form-control" title="{vtranslate('PRIMARY_MODULE',$MODULE)}" id="primary_module" name="primary_module">
							<optgroup>
								{foreach key=RELATED_MODULE_KEY item=RELATED_MODULE from=$MODULELIST}
									<option value="{$RELATED_MODULE_KEY}" {if $REPORT_MODEL->getPrimaryModule() eq $RELATED_MODULE_KEY } selected="selected" {/if}>
										{vtranslate($RELATED_MODULE_KEY,$RELATED_MODULE_KEY)}
									</option>
								{/foreach}
							</optgroup>
						</select>
					</div>
				</div>
				<div class="row marginBottom5px paddingTop10">
					<div class="col-md-3">
						<div>{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}</div>
						<div>({vtranslate('LBL_MAX',$MODULE)}&nbsp;2)</div>
					</div>
					<div class="col-md-7">
						{assign var=SECONDARY_MODULES_ARR value=explode(':',$REPORT_MODEL->getSecondaryModules())}
						{assign var=PRIMARY_MODULE value=$REPORT_MODEL->getPrimaryModule()}

						{if $PRIMARY_MODULE eq ''}
							{foreach key=PARENT item=RELATED from=$RELATED_MODULES name=relatedlist}
								{if $smarty.foreach.relatedlist.index eq 0}
									{assign var=PRIMARY_MODULE value=$PARENT}
								{/if}
							{/foreach}
						{/if}
						{assign var=PRIMARY_RELATED_MODULES value=$RELATED_MODULES[$PRIMARY_MODULE]}
						<select class="col-md-6 select2-container form-control" id="secondary_module" multiple name="secondary_modules[]" title="{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}" data-placeholder="{vtranslate('LBL_SELECT_RELATED_MODULES',$MODULE)}">
							{foreach key=PRIMARY_RELATED_MODULE  item=PRIMARY_RELATED_MODULE_LABEL from=$PRIMARY_RELATED_MODULES}
								<option {if in_array($PRIMARY_RELATED_MODULE,$SECONDARY_MODULES_ARR)} selected="" {/if} value="{$PRIMARY_RELATED_MODULE}">{$PRIMARY_RELATED_MODULE_LABEL}</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="row marginBottom5px paddingTop10">
					<div class="col-md-3">{vtranslate('LBL_DESCRIPTION',$MODULE)}</div>
					<div class="col-md-7"><textarea class="col-md-6 form-control" type="text" title="{vtranslate('LBL_DESCRIPTION',$MODULE)}" name="description" >{$REPORT_MODEL->get('description')}</textarea></div>
				</div>
				<div class="row paddingTop10">
					<div class="col-xs-12">
						<input type="checkbox" title="{vtranslate('LBL_SCHEDULE_REPORTS',$MODULE)}"  {if $SCHEDULEDREPORTS->get('scheduleid') neq ''} checked="" {/if} value="{if $SCHEDULEDREPORTS->get('scheduleid') neq ''}true{/if}" name='enable_schedule' style="margin-top: 0px !important;"> &nbsp;
						<strong>{vtranslate('LBL_SCHEDULE_REPORTS',$MODULE)}</strong>
					</div>
				</div>
				<div id="scheduleBox" class='well contentsBackground {if $SCHEDULEDREPORTS->get('scheduleid') eq ''} hide {/if}'>
					<div class="row" style="padding:5px 0px;">
						<div class="col-md-3 marginBottom5px" style="position:relative;top:5px;">{vtranslate('LBL_FILE_TYPE', $MODULE)}</div>
						<div class="col-md-4">
							<select class="chzn-select form-control" title="{vtranslate('LBL_FILE_TYPE',$MODULE)}" name="scheduleFileType">
								<option value="CSV" {if $SCHEDULEDREPORTS->get('scheduleFileType') eq 'CSV'}selected{/if}>{vtranslate('LBL_CSV', $MODULE)}</option>
								<option value="EXCEL" {if $SCHEDULEDREPORTS->get('scheduleFileType') eq 'EXCEL'}selected{/if}>{vtranslate('LBL_EXCEL', $MODULE)}</option>
							</select>
						</div>
					</div>
					<div class='row' style="padding:5px 0px;">
						<div class='col-md-3 marginBottom5px' style='position:relative;top:5px;'>{vtranslate('LBL_RUN_REPORT', $MODULE)}</div>
						<div class='col-md-4'>
							{assign var=scheduleid value=$SCHEDULEDREPORTS->get('scheduleid')}
							<select class='chzn-select form-control' id='schtypeid' title="{vtranslate('LBL_SCHEDULE_REPORTS',$MODULE)}" name='schtypeid'>
								<option value="1" {if $scheduleid eq 1}selected{/if}>{vtranslate('LBL_DAILY', $MODULE)}</option>
								<option value="2" {if $scheduleid eq 2}selected{/if}>{vtranslate('LBL_WEEKLY', $MODULE)}</option>
								<option value="5" {if $scheduleid eq 5}selected{/if}>{vtranslate('LBL_SPECIFIC_DATE', $QUALIFIED_MODULE)}</option>
								<option value="3" {if $scheduleid eq 3}selected{/if}>{vtranslate('LBL_MONTHLY_BY_DATE', $MODULE)}</option>
								<option value="4" {if $scheduleid eq 4}selected{/if}>{vtranslate('LBL_YEARLY', $MODULE)}</option>
							</select>
						</div>
					</div>

					{* show weekdays for weekly option *}
					<div class='row {if $scheduleid neq 2} hide {/if}' id='scheduledWeekDay' style='padding:5px 0px;'>
						<div class='col-md-3 marginBottom5px' style='position:relative;top:5px;'>{vtranslate('LBL_ON_THESE_DAYS', $MODULE)}</div>
						<div class='col-md-4'>
							{assign var=dayOfWeek value=\App\Json::decode($SCHEDULEDREPORTS->get('schdayoftheweek'))}
							<select style='width:230px;' multiple class='chosen form-control' title="{vtranslate('LBL_ON_THESE_DAYS', $MODULE)}" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name='schdayoftheweek' id='schdayoftheweek'>
								<option value="7" {if is_array($dayOfWeek) && in_array('7', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY0', 'Calendar')}</option>
								<option value="1" {if is_array($dayOfWeek) && in_array('1', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY1', 'Calendar')}</option>
								<option value="2" {if is_array($dayOfWeek) && in_array('2', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY2', 'Calendar')}</option>
								<option value="3" {if is_array($dayOfWeek) && in_array('3', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY3', 'Calendar')}</option>
								<option value="4" {if is_array($dayOfWeek) && in_array('4', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY4', 'Calendar')}</option>
								<option value="5" {if is_array($dayOfWeek) && in_array('5', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY5', 'Calendar')}</option>
								<option value="6" {if is_array($dayOfWeek) && in_array('6', $dayOfWeek)} selected {/if}>{vtranslate('LBL_DAY6', 'Calendar')}</option>
							</select>
						</div>
					</div>

					{* show month view by dates *}
					<div class='row {if $scheduleid neq 3} hide {/if}' id='scheduleMonthByDates' style="padding:5px 0px;">
						<div class='col-md-3 marginBottom5px' style='position:relative;top:5px;'>{vtranslate('LBL_ON_THESE_DAYS', $MODULE)}</div>
						<div class='col-md-4'>
							{assign var=dayOfMonth value=\App\Json::decode($SCHEDULEDREPORTS->get('schdayofthemonth'))}
							<select style="width: 281px !important;" multiple class="chosen-select col-md-6 form-control" title="{vtranslate('LBL_ON_THESE_DAYS', $MODULE)}" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name='schdayofthemonth' id='schdayofthemonth' >
								{section name=foo loop=31}
									<option value={$smarty.section.foo.iteration} {if is_array($dayOfMonth) && in_array($smarty.section.foo.iteration, $dayOfMonth)}selected{/if}>{$smarty.section.foo.iteration}</option>
								{/section}
							</select>
						</div>
					</div>
					{* show specific date *}
					<div class='row {if $scheduleid neq 5} hide {/if}' id='scheduleByDate' style="padding:5px 0px;">
						<div class='col-md-3 marginBottom5px' style='position:relative;top:5px;'><span class="redColor">*</span>{vtranslate('LBL_CHOOSE_DATE', $MODULE)}</div>
						<div class='col-md-6'>
							<div class='input-group date' style='width: 185px;'>
								{assign var=specificDate value=\App\Json::decode($SCHEDULEDREPORTS->get('schdate'))}
								{if $specificDate[0] neq ''} {assign var=specificDate1 value=DateTimeField::convertToUserFormat($specificDate[0])} {/if}
								<input  type="text" class="dateField form-control input-sm col-md-6" id="schdate" name="schdate" value="{$specificDate1}" data-date-format="{$CURRENT_USER->date_format}" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"/>
								<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
							</div>
						</div>
					</div>
					{* show month view by anually *}
					<div class='row {if $scheduleid neq 4} hide {/if}' id='scheduleAnually' style='padding:5px 0px;'>
						<div class='col-md-3 ' style='position:relative;top:5px;'>
							{vtranslate('LBL_SELECT_MONTH_AND_DAY', $MODULE)}
						</div>
						<div class='col-md-5'>
							<div id='annualDatePicker'></div>
						</div>
						<div class='col-md-2'>
							<div style='padding-bottom:5px;'>{vtranslate('LBL_SELECTED_DATES', $MODULE)}</div>
							<div>
								<input type="hidden" id=hiddenAnnualDates value='{$SCHEDULEDREPORTS->get('schannualdates')}' />
								{assign var=ANNUAL_DATES value=\App\Json::decode($SCHEDULEDREPORTS->get('schannualdates'))}
								<select multiple class="chosen-select" id='annualDates' name='schannualdates' data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
									{foreach item=DATES from=$ANNUAL_DATES}
										<option value="{$DATES}" selected>{$DATES}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>

					{* show time for all other than Hourly option*}
					<div class='row' id='scheduledTime' style='padding:5px 0px 10px 0px;'>
						<div class='col-md-3 marginBottom5px' style='position:relative;top:5px;'>
							<span class="redColor">*</span>{vtranslate('LBL_AT_TIME', $MODULE)}
						</div>
						<div class='col-md-4' id='schtime'>
							<div class="input-group time">
								<input type='text' class='clockPicker input-sm form-control' data-format='24' name='schtime' value="{$SCHEDULEDREPORTS->get('schtime')}" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"/>
								<span class="input-group-addon cursorPointer"><i class="glyphicon glyphicon-time"></i></span>
							</div>
						</div>
					</div>
					{* show all the users,groups,roles and subordinat roles*}
					<div class='row' id='recipientsList' style='padding:5px 0px 10px 0px;'>
						<div class='col-md-3 marginBottom5px' style='position:relative;top:5px;'>
							<span class="redColor">*</span>{vtranslate('LBL_SELECT_RECIEPIENTS', $MODULE)}
						</div>
						<div class='col-md-4'>
							{assign var=ALL_ACTIVEUSER_LIST value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
							{assign var=ALL_ACTIVEGROUP_LIST value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
							{assign var=recipients value=\App\Json::decode($SCHEDULEDREPORTS->get('recipients'))}
							<select multiple data-placeholder="{vtranslate('LBL_SELECT_OPTION')}" title="{vtranslate('LBL_SELECT_RECIEPIENTS', $MODULE)}" class="chosen-select col-md-6 form-control" id='recipients' name='recipients' data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" style="width: 281px !important;">
								<optgroup label="{vtranslate('LBL_USERS')}">
									{foreach key=USER_ID item=USER_NAME from=$ALL_ACTIVEUSER_LIST}
										{assign var=USERID value="USER::{$USER_ID}"}
										<option value="{$USERID}" {if is_array($recipients) && in_array($USERID, $recipients)} selected {/if} data-picklistvalue= '{$USER_NAME}'> {$USER_NAME} </option>
									{/foreach}
								</optgroup>
								<optgroup label="{vtranslate('LBL_GROUPS')}">
									{foreach key=GROUP_ID item=GROUP_NAME from=$ALL_ACTIVEGROUP_LIST}
										{assign var=GROUPID value="GROUP::{$GROUP_ID}"}
										<option value="{$GROUPID}" {if is_array($recipients) && in_array($GROUPID, $recipients)} selected {/if} data-picklistvalue= '{$GROUP_NAME}'>{$GROUP_NAME}</option>
									{/foreach}
								</optgroup>
								<optgroup label="{vtranslate('Roles', 'Roles')}">
									{foreach key=ROLE_ID item=ROLE_OBJ from=$ROLES}
										{assign var=ROLEID value="ROLE::{$ROLE_ID}"}
										<option value="{$ROLEID}" {if is_array($recipients) && in_array($ROLEID, $recipients)} selected {/if} data-picklistvalue= '{$ROLE_OBJ->get('rolename')}'>{vtranslate($ROLE_OBJ->get('rolename'))}</option>
									{/foreach}
								</optgroup>
							</select>
						</div>
					</div>
					<div class='row' id='specificemailsids' style='padding:5px 0px 10px 0px;'>
						<div class='col-md-3 marginBottom5px' style='position:relative;top:5px;'>
							{vtranslate('LBL_SPECIFIC_EMAIL_ADDRESS', $MODULE)}
						</div>
						<div class='col-md-4'>
							{assign var=specificemailids value=\App\Json::decode($SCHEDULEDREPORTS->get('specificemails'))}
							<input id="specificemails" style="width: 281px !important;" class="col-md-6 form-control" title="{vtranslate('LBL_SPECIFIC_EMAIL_ADDRESS', $MODULE)}" type="text" value="{$specificemailids}" name="specificemails" data-validation-engine="validate[funcCall[Vtiger_MultiEmails_Validator_Js.invokeValidation]]"></input>
						</div>
					</div>
					<div class="row">
						<div class='col-md-3 marginBottom5px'>
							<span class=''>{vtranslate('LBL_NEXT_TRIGGER_TIME', $MODULE)}</span>
						</div>
						<div class='col-md-4'>
							{DateTimeField::convertToUserFormat($SCHEDULEDREPORTS->get('next_trigger_time'))}
							<span>&nbsp;({$ACTIVE_ADMIN->time_zone})</span>
						</div>
					</div>
				</div>
			</div>
			<div class="row pull-right no-margin">
				<button type="submit" class="btn btn-success nextStep"><strong>{vtranslate('LBL_NEXT',$MODULE)}</strong></button>&nbsp;&nbsp;
				<button onclick='window.history.back()' type="reset" class="cancelLink cursorPointer btn btn-warning">{vtranslate('LBL_CANCEL',$MODULE)}</button>
			</div>
		</form>
	</div>
{/strip}
