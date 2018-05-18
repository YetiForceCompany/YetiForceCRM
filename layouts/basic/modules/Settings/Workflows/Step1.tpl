{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
    <div class="workFlowContents">
        <form name="EditWorkflow" action="index.php" method="post" id="workflow_step1" class="form-horizontal">
            <input type="hidden" name="module" value="Workflows">
            <input type="hidden" name="view" value="Edit">
            <input type="hidden" name="mode" value="Step2" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" class="step" value="1" />
            <input type="hidden" name="record" value="{$RECORDID}" />
			<input type="hidden" id="weekStartDay" data-value='{$WEEK_START_ID}' />

            <div class="u-p-1per border">
                <label>
                    <strong>{\App\Language::translate('LBL_STEP_1',$QUALIFIED_MODULE)}: {\App\Language::translate('LBL_ENTER_BASIC_DETAILS_OF_THE_WORKFLOW',$QUALIFIED_MODULE)}</strong>
                </label>
                <br />
                <div class="form-group form-row">
                    <label class="col-sm-3 col-form-label u-text-small-bold text-right">
                        {\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
                    </label>
                    <div class="col-sm-6 controls">
                        {if $MODE eq 'edit'}
                            <input type='text' disabled='disabled' class="form-control" value="{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}" >
                            <input type='hidden' name='module_name' value="{$MODULE_MODEL->get('name')}" >
                        {else}
                            <select class="select2 form-control" id="moduleName" name="module_name" required="true" data-placeholder="Select Module...">
                                {foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
                                    <option value="{$MODULE_MODEL->getName()}" {if $SELECTED_MODULE == $MODULE_MODEL->getName()} selected {/if}>
										{if $MODULE_MODEL->getName() eq 'Calendar'}
											{\App\Language::translate('LBL_TASK', $MODULE_MODEL->getName())}
										{else}
											{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
										{/if}
									</option>
                                {/foreach}
                            </select>
                        {/if}
                    </div>
                </div>
                <div class="form-group form-row">
                    <label class="col-sm-3 col-form-label u-text-small-bold text-right">
                        {\App\Language::translate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}<span class="redColor">*</span>
                    </label>
                    <div class="col-sm-6 controls">
                        <input type="text" name="summary" class="form-control" data-validation-engine='validate[required]' value="{$WORKFLOW_MODEL->get('summary')}" id="summary" />
                    </div>
                </div>

                <div class="form-group form-row">
                    <label class="col-sm-3 col-form-label u-text-small-bold text-right">
                        {\App\Language::translate('LBL_SPECIFY_WHEN_TO_EXECUTE', $QUALIFIED_MODULE)}
                    </label>
                    <div class="col-sm-6 controls">
                        {assign var=WORKFLOW_MODEL_OBJ value=$WORKFLOW_MODEL->getWorkflowObject()}

                        {foreach from=$TRIGGER_TYPES item=LABEL key=LABEL_ID}
                            <div>
                                <label><input type="radio" class="alignTop" name="execution_condition" {if $WORKFLOW_MODEL_OBJ->executionCondition eq $LABEL_ID} checked="" {/if} value="{$LABEL_ID}" {if $WORKFLOW_MODEL->getId() eq '' && $SCHEDULED_WORKFLOW_COUNT >= $MAX_ALLOWED_SCHEDULED_WORKFLOWS && $LABEL_ID eq 6} disabled {/if} />&nbsp;&nbsp;{\App\Language::translate($LABEL,$QUALIFIED_MODULE)}
                                    {if $WORKFLOW_MODEL->getId() eq '' && $SCHEDULED_WORKFLOW_COUNT >= $MAX_ALLOWED_SCHEDULED_WORKFLOWS && $LABEL_ID eq 6}
                                        <span class='alert alert-warning' style="position:relative;left:100px">{\App\Language::translate('LBL_EXCEEDING_MAXIMUM_LIMIT', $QUALIFIED_MODULE)} : {$MAX_ALLOWED_SCHEDULED_WORKFLOWS}</span>
                                    {/if}
								</label><br />
                            </div>
                        {/foreach}
                        {if $SCHEDULED_WORKFLOW_COUNT <= $MAX_ALLOWED_SCHEDULED_WORKFLOWS}
                            <div id="scheduleBox" class="well contentsBackground u-timetable {if $WORKFLOW_MODEL_OBJ->executionCondition neq 6} d-none {/if}">
                                <div class="form-row">
                                    <div class="col-md-2 d-flex align-items-center">{\App\Language::translate('LBL_RUN_WORKFLOW', $QUALIFIED_MODULE)}</div>
                                    <div class="col-md-6 d-flex align-items-center">
										<select class="select2" id="schtypeid" name="schtypeid">
                                            <option value="1" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 1}selected{/if}>{\App\Language::translate('LBL_HOURLY', $QUALIFIED_MODULE)}</option>
                                            <option value="2" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 2}selected{/if}>{\App\Language::translate('LBL_DAILY', $QUALIFIED_MODULE)}</option>
                                            <option value="3" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 3}selected{/if}>{\App\Language::translate('LBL_WEEKLY', $QUALIFIED_MODULE)}</option>
                                            <option value="4" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 4}selected{/if}>{\App\Language::translate('LBL_SPECIFIC_DATE', $QUALIFIED_MODULE)}</option>
                                            <option value="5" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 5}selected{/if}>{\App\Language::translate('LBL_MONTHLY_BY_DATE', $QUALIFIED_MODULE)}</option>
                                            <!--option value="6" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 6}selected{/if}>{\App\Language::translate('LBL_MONTHLY_BY_WEEKDAY', $QUALIFIED_MODULE)}</option-->
                                            <option value="7" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 7}selected{/if}>{\App\Language::translate('LBL_YEARLY', $QUALIFIED_MODULE)}</option>
                                        </select>
                                    </div>
                                </div>

                                {* show weekdays for weekly option *}
                                <div class='form-row {if $WORKFLOW_MODEL_OBJ->schtypeid neq 3} d-none {/if}' id='scheduledWeekDay'>
                                    <div class="col-md-2 d-flex align-items-center">{\App\Language::translate('LBL_ON_THESE_DAYS', $QUALIFIED_MODULE)}</div>
									<div class="col-md-6 d-flex align-items-center">
										{assign var=dayOfWeek value=\App\Json::decode($WORKFLOW_MODEL_OBJ->schdayofweek)}
										<select multiple class="select2 col-md-6" data-validation-engine="validate[rquired,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name="schdayofweek" id="schdayofweek">
											<option value="7" {if is_array($dayOfWeek) && in_array('7', $dayOfWeek)} selected {/if}>{\App\Language::translate('LBL_DAY0', 'Calendar')}</option>
											<option value="1" {if is_array($dayOfWeek) && in_array('1', $dayOfWeek)} selected {/if}>{\App\Language::translate('LBL_DAY1', 'Calendar')}</option>
											<option value="2" {if is_array($dayOfWeek) && in_array('2', $dayOfWeek)} selected {/if}>{\App\Language::translate('LBL_DAY2', 'Calendar')}</option>
											<option value="3" {if is_array($dayOfWeek) && in_array('3', $dayOfWeek)} selected {/if}>{\App\Language::translate('LBL_DAY3', 'Calendar')}</option>
											<option value="4" {if is_array($dayOfWeek) && in_array('4', $dayOfWeek)} selected {/if}>{\App\Language::translate('LBL_DAY4', 'Calendar')}</option>
											<option value="5" {if is_array($dayOfWeek) && in_array('5', $dayOfWeek)} selected {/if}>{\App\Language::translate('LBL_DAY5', 'Calendar')}</option>
											<option value="6" {if is_array($dayOfWeek) && in_array('6', $dayOfWeek)} selected {/if}>{\App\Language::translate('LBL_DAY6', 'Calendar')}</option>
										</select>
									</div>
                                </div>

                                {* show month view by dates *}
                                <div class="form-row {if $WORKFLOW_MODEL_OBJ->schtypeid neq 5} d-none {/if}" id="scheduleMonthByDates">
                                    <div class="col-md-2 d-flex align-items-center">{\App\Language::translate('LBL_ON_THESE_DAYS', $QUALIFIED_MODULE)}</div>
                                    <div class="col-md-6 d-flex align-items-center">
                                        {assign var=DAYS value=\App\Json::decode($WORKFLOW_MODEL_OBJ->schdayofmonth)}
                                      	<select multiple class="select2" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name='schdayofmonth' id='schdayofmonth' >
                                            {section name=foo loop=31}
                                                <option value={$smarty.section.foo.iteration} {if is_array($DAYS) && in_array($smarty.section.foo.iteration, $DAYS)}selected{/if}>{$smarty.section.foo.iteration}</option>
                                            {/section}
                                        </select>
                                    </div>
                                </div>

                                {* show specific date *}
                                <div class='form-row {if $WORKFLOW_MODEL_OBJ->schtypeid neq 4} d-none {/if}' id='scheduleByDate'>
                                   	<div class="col-md-2 d-flex align-items-center">{\App\Language::translate('LBL_CHOOSE_DATE', $QUALIFIED_MODULE)}</div>
                                    <div class="col-md-6 d-flex align-items-center">
                                        <div class="date w-100">
                                            <div class="input-group">
                                                {assign var=specificDate value=\App\Json::decode($WORKFLOW_MODEL_OBJ->schannualdates)}
												{if $specificDate[0] neq ''} {assign var=specificDate1 value=DateTimeField::convertToUserFormat($specificDate[0])} {/if}
												<input type="text" class="dateField form-control" name="schdate" value="{$specificDate1}" data-date-format="{$USER_MODEL->get('date_format')}" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
												<div class=" input-group-append">
												<span class="input-group-text u-cursor-pointer js-date__btn" data-js="click">
													<span class="fas fa-calendar-alt"></span>
												</span>
												</div>	
											</div>
										</div>
									</div>
								</div>

								{* show month view by weekday *}
								<div class='form-row {if $WORKFLOW_MODEL_OBJ->schtypeid neq 6} d-none {/if}' id='scheduleMonthByWeekDays'>

								</div>

								{* show month view by anually *}
								<div class='form-row my-1 {if $WORKFLOW_MODEL_OBJ->schtypeid neq 7} d-none {/if}' id='scheduleAnually'>
									<div class="col-md-2">
										{\App\Language::translate('LBL_SELECT_MONTH_AND_DAY', $QUALIFIED_MODULE)}
									</div>
									<div class="col-md-10">
										<div id='annualDatePicker'></div>
									</div>
									<div class="col-md-2">
									</div>
									<div class="col-md-10 form-row">
										<div class="pr-2">{\App\Language::translate('LBL_SELECTED_DATES', $QUALIFIED_MODULE)}</div>
										<div>
											<input type="hidden" id=hiddenAnnualDates value='{$WORKFLOW_MODEL_OBJ->schannualdates}' />
											<select multiple class="select2" id='annualDates' name='schannualdates' data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
												{foreach item=DATES from=$ANNUAL_DATES}
													<option value="{$DATES}" selected>{$DATES}</option>
												{/foreach}
											</select>
										</div>
									</div>
								</div>
								{* show time for all other than Hourly option*}
								<div class="form-row pt-1 pb-2 px-0 {if $WORKFLOW_MODEL_OBJ->schtypeid < 2} d-none {/if}" id="scheduledTime">
									<div class="col-md-2 d-flex align-items-center">
										{\App\Language::translate('LBL_AT_TIME', $QUALIFIED_MODULE)}
									</div>
									<div class="col-md-6 d-flex align-items-center" id="schtime">
										<div class="input-group time">
											<input type='text' class="clockPicker form-control" data-format='24' name='schtime' value="{$WORKFLOW_MODEL_OBJ->schtime}" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" />
											<div class="input-group-append">
												<span class="input-group-text u-cursor-pointer js-clock__btn" data-js="click">
													<span class="far fa-clock"></span>
												</span>
											</div>
										</div>
									</div>
								</div>
								{if $WORKFLOW_MODEL_OBJ->nexttrigger_time}
									<div class="form-row">
										<div class="col-md-2 d-flex align-items-center">
											<span class=''>{\App\Language::translate('LBL_NEXT_TRIGGER_TIME', $QUALIFIED_MODULE)}</span>
										</div>
										<div class="col-md-6 d-flex align-items-center">
											{DateTimeField::convertToUserFormat($WORKFLOW_MODEL_OBJ->nexttrigger_time)}
											<span>&nbsp;({$ACTIVE_ADMIN->time_zone})</span>
										</div>
									</div>
								{/if}
							</div>
						{/if}
					</div>
				</div>

			</div>
			<br />
			<div class="float-right mb-4">
				<button class="btn btn-success" type="submit" disabled="disabled">
					<strong>
						<span class="fas fa-caret-right mr-1"></span>
						{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
					</strong>
				</button>
				<button class="btn btn-danger cancelLink" type="reset" onclick="javascript:window.history.back();">
					<span class="fas fa-times mr-1"></span>
					{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
				</button>
			</div>
		</form>
	</div>
{/strip}
