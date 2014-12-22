{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
    <div class="workFlowContents" style="padding-left: 3%;padding-right: 3%">
        <form name="EditWorkflow" action="index.php" method="post" id="workflow_step1" class="form-horizontal">
            <input type="hidden" name="module" value="Workflows">
            <input type="hidden" name="view" value="Edit">
            <input type="hidden" name="mode" value="Step2" />
            <input type="hidden" name="parent" value="Settings" />
            <input type="hidden" class="step" value="1" />
            <input type="hidden" name="record" value="{$RECORDID}" />

            <div class="padding1per" style="border:1px solid #ccc;">
                <label>
                    <strong>{vtranslate('LBL_STEP_1',$QUALIFIED_MODULE)}: {vtranslate('LBL_ENTER_BASIC_DETAILS_OF_THE_WORKFLOW',$QUALIFIED_MODULE)}</strong>
                </label>
                <br>
                <div class="control-group">
                    <div class="control-label">
                        {vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
                    </div>
                    <div class="controls">
                        {if $MODE eq 'edit'}
                            <input type='text' disabled='disabled' value="{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}" >
                            <input type='hidden' name='module_name' value="{$MODULE_MODEL->get('name')}" >
                        {else}
                            <select class="chzn-select" id="moduleName" name="module_name" required="true" data-placeholder="Select Module...">
                                {foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
                                    <option value="{$MODULE_MODEL->getName()}" {if $SELECTED_MODULE == $MODULE_MODEL->getName()} selected {/if}>
										{if $MODULE_MODEL->getName() eq 'Calendar'}
											{vtranslate('LBL_TASK', $MODULE_MODEL->getName())}
										{else}
											{vtranslate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
										{/if}
									</option>
                                {/foreach}
                            </select>
                        {/if}
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label">
                        {vtranslate('LBL_DESCRIPTION', $QUALIFIED_MODULE)}<span class="redColor">*</span>
                    </div>
                    <div class="controls">
                        <input type="text" name="summary" class="span5" data-validation-engine='validate[required]' value="{$WORKFLOW_MODEL->get('summary')}" id="summary" />
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        {vtranslate('LBL_SPECIFY_WHEN_TO_EXECUTE', $QUALIFIED_MODULE)}
                    </div>
                    <div class="controls">
                        {assign var=WORKFLOW_MODEL_OBJ value=$WORKFLOW_MODEL->getWorkflowObject()}

                        {foreach from=$TRIGGER_TYPES item=LABEL key=LABEL_ID}
                            <div>
                                <label><input type="radio" class="alignTop" name="execution_condition" {if $WORKFLOW_MODEL_OBJ->executionCondition eq $LABEL_ID} checked="" {/if} value="{$LABEL_ID}" {if $WORKFLOW_MODEL->getId() eq '' && $SCHEDULED_WORKFLOW_COUNT >= $MAX_ALLOWED_SCHEDULED_WORKFLOWS && $LABEL_ID eq 6} disabled {/if} />&nbsp;&nbsp;{vtranslate($LABEL,$QUALIFIED_MODULE)}
                                    {if $WORKFLOW_MODEL->getId() eq '' && $SCHEDULED_WORKFLOW_COUNT >= $MAX_ALLOWED_SCHEDULED_WORKFLOWS && $LABEL_ID eq 6}
                                        <span class='alert alert-error' style="position:relative;left:100px">{vtranslate('LBL_EXCEEDING_MAXIMUM_LIMIT', $QUALIFIED_MODULE)} : {$MAX_ALLOWED_SCHEDULED_WORKFLOWS}</span>
                                    {/if}
                                    </label><br>
                                <div class="clearfix"></div>
                            </div>
                        {/foreach}
                        {if $SCHEDULED_WORKFLOW_COUNT <= $MAX_ALLOWED_SCHEDULED_WORKFLOWS}
                            <div id="scheduleBox" class='well contentsBackground {if $WORKFLOW_MODEL_OBJ->executionCondition neq 6} hide {/if}'>
                                <div class='row-fluid'>
                                    <div class='span2' style='position:relative;top:5px;'>{vtranslate('LBL_RUN_WORKFLOW', $QUALIFIED_MODULE)}</div>
                                    <div class='span4'><select class='chzn-select' id='schtypeid' name='schtypeid'>
                                            <option value="1" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 1}selected{/if}>{vtranslate('LBL_HOURLY', $QUALIFIED_MODULE)}</option>
                                            <option value="2" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 2}selected{/if}>{vtranslate('LBL_DAILY', $QUALIFIED_MODULE)}</option>
                                            <option value="3" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 3}selected{/if}>{vtranslate('LBL_WEEKLY', $QUALIFIED_MODULE)}</option>
                                            <option value="4" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 4}selected{/if}>{vtranslate('LBL_SPECIFIC_DATE', $QUALIFIED_MODULE)}</option>
                                            <option value="5" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 5}selected{/if}>{vtranslate('LBL_MONTHLY_BY_DATE', $QUALIFIED_MODULE)}</option>
                                            <!--option value="6" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 6}selected{/if}>{vtranslate('LBL_MONTHLY_BY_WEEKDAY', $QUALIFIED_MODULE)}</option-->
                                            <option value="7" {if $WORKFLOW_MODEL_OBJ->schtypeid eq 7}selected{/if}>{vtranslate('LBL_YEARLY', $QUALIFIED_MODULE)}</option>
                                        </select>
                                    </div>
                                </div>

                                {* show weekdays for weekly option *}
                                <div class='row-fluid {if $WORKFLOW_MODEL_OBJ->schtypeid neq 3} hide {/if}' id='scheduledWeekDay' style='padding:5px 0px;'>
                                    <div class='span2' style='position:relative;top:5px;'>{vtranslate('LBL_ON_THESE_DAYS', $QUALIFIED_MODULE)}</div>
                                    <div class='span4'>
                                        {assign var=dayOfWeek value=Zend_Json::decode($WORKFLOW_MODEL_OBJ->schdayofweek)}
                                        <select style='width:230px;' multiple class='chosen' data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name='schdayofweek' id='schdayofweek'>
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
                                <div class='row-fluid {if $WORKFLOW_MODEL_OBJ->schtypeid neq 5} hide {/if}' id='scheduleMonthByDates' style="padding:5px 0px;">
                                    <div class='span2' style='position:relative;top:5px;'>{vtranslate('LBL_ON_THESE_DAYS', $QUALIFIED_MODULE)}</div>
                                    <div class='span4'>
                                        {assign var=DAYS value=Zend_Json::decode($WORKFLOW_MODEL_OBJ->schdayofmonth)}
                                        <select style='width:230px;' multiple class="chosen-select" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" name='schdayofmonth' id='schdayofmonth' >
                                            {section name=foo loop=31}
                                                <option value={$smarty.section.foo.iteration} {if is_array($DAYS) && in_array($smarty.section.foo.iteration, $DAYS)}selected{/if}>{$smarty.section.foo.iteration}</option>
                                            {/section}
                                        </select>
                                    </div>
                                </div>

                                {* show specific date *}
                                <div class='row-fluid {if $WORKFLOW_MODEL_OBJ->schtypeid neq 4} hide {/if}' id='scheduleByDate' style="padding:5px 0px;">
                                    <div class='span2' style='position:relative;top:5px;'>{vtranslate('LBL_CHOOSE_DATE', $QUALIFIED_MODULE)}</div>
                                    <div class='span6'>
                                        <div class='input-append row-fluid'>
                                            <div class='row-fluid date'>
                                                {assign var=specificDate value=Zend_Json::decode($WORKFLOW_MODEL_OBJ->schannualdates)}
                                            {if $specificDate[0] neq ''} {assign var=specificDate1 value=DateTimeField::convertToUserFormat($specificDate[0])} {/if}
                                            <input style='width: 185px;' type="text" class="dateField" name="schdate" value="{$specificDate1}" data-date-format="{$CURRENT_USER->date_format}" data-validation-engine="validate[ required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"/>
                                            <span class="add-on"><i class="icon-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {* show month view by weekday *}
                            <div class='row-fluid {if $WORKFLOW_MODEL_OBJ->schtypeid neq 6} hide {/if}' id='scheduleMonthByWeekDays' style='padding:5px 0px;'>

                            </div>

                            {* show month view by anually *}
                            <div class='row-fluid {if $WORKFLOW_MODEL_OBJ->schtypeid neq 7} hide {/if}' id='scheduleAnually' style='padding:5px 0px;'>
                                <div class='span2' style='position:relative;top:5px;'>
                                    {vtranslate('LBL_SELECT_MONTH_AND_DAY', $QUALIFIED_MODULE)}
                                </div>
                                <div class='span6'>
                                    <div id='annualDatePicker'></div>
                                </div>
                                <div class='span2'>
                                    <div style='padding-bottom:5px;'>{vtranslate('LBL_SELECTED_DATES', $QUALIFIED_MODULE)}</div>
                                    <div>
                                        <input type=hidden id=hiddenAnnualDates value='{$WORKFLOW_MODEL_OBJ->schannualdates}' />
                                        <select multiple class="chosen-select" id='annualDates' name='schannualdates' data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]">
                                            {foreach item=DATES from=$ANNUAL_DATES}
                                                <option value="{$DATES}" selected>{$DATES}</option>
                                            {/foreach}
                                        </select>
                                    </div>
                                </div>
                            </div>
                            {* show time for all other than Hourly option*}
                            <div class='row-fluid {if $WORKFLOW_MODEL_OBJ->schtypeid < 2} hide {/if}' id='scheduledTime' style='padding:5px 0px 10px 0px;'>
                                <div class='span2' style='position:relative;top:5px;'>
                                    {vtranslate('LBL_AT_TIME', $QUALIFIED_MODULE)}
                                </div>
                                <div class='span4' id='schtime'>
                                    <div class="input-append time">
                                        <input type='text' class='timepicker-default input-small' data-format='24' name='schtime' value="{$WORKFLOW_MODEL_OBJ->schtime}" data-validation-engine="validate[required,funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"/>
                                        <span class="add-on cursorPointer"><i class="icon-time"></i></span>
                                    </div>
                                </div>
                            </div>
                            {if $WORKFLOW_MODEL_OBJ->nexttrigger_time}
                                <div class="row-fluid">
                                    <div class='span2'>
                                        <span class=''>{vtranslate('LBL_NEXT_TRIGGER_TIME', $QUALIFIED_MODULE)}</span>
                                    </div>
                                    <div class='span'>
                                        {if $WORKFLOW_MODEL_OBJ->schtypeid neq 4}
                                            {DateTimeField::convertToUserFormat($WORKFLOW_MODEL_OBJ->nexttrigger_time)}
                                            <span>&nbsp;({$ACTIVE_ADMIN->time_zone})</span>
                                        {/if}
                                    </div>
                                </div>
                            {/if}
                        </div>
                    {/if}
                </div>
            </div>

        </div>
        <br>
        <div class="pull-right">
            <button class="btn btn-success" type="submit" disabled="disabled"><strong>{vtranslate('LBL_NEXT', $QUALIFIED_MODULE)}</strong></button>
            <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
        </div>
        <div class="clearfix"></div>
    </form>
</div>
{/strip}