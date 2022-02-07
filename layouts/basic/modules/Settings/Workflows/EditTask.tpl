{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class='modelContainer modal fade' id="addTaskContainer" tabindex="-1">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_ADD_TASKS_FOR_WORKFLOW', $QUALIFIED_MODULE)}
						: {\App\Language::translate($TASK_TYPE_MODEL->get('label'), $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal" id="saveTask" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="parent" value="Settings" />
					<input type="hidden" name="action" value="TaskAjax" />
					<input type="hidden" name="mode" value="save" />
					<input type="hidden" name="for_workflow" value="{$WORKFLOW_ID}" />
					<input type="hidden" name="task_id" value="{$TASK_ID}" />
					<input type="hidden" name="taskType" id="taskType" value="{$TASK_TYPE_MODEL->get('tasktypename')}" />
					<div class="modal-body tabbable">
						<div class="form-row pb-3">
							<div class="col-md-2">
								<div class="float-left col-form-label">{\App\Language::translate('LBL_TASK_TITLE',$QUALIFIED_MODULE)}
									<span class="redColor">*</span>
								</div>

							</div>
							<div class="col-md-5">
								<input name="summary" class="form-control" data-validation-engine='validate[required]'
									type="text" value="{$TASK_MODEL->get('summary')}" />
							</div>
							<div class="col-md-4 form-control-plaintext">
								<div class="float-left">{\App\Language::translate('LBL_STATUS',$QUALIFIED_MODULE)}</div>
								<div class="float-left" id="taskStatus">
									<input type="radio" name="active"
										class="alignTop" {if $TASK_MODEL->get('status') eq 1} checked="" {/if}
										value="true">&nbsp;{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}
									&nbsp;&nbsp;
									<input type="radio" name="active"
										class="alignTop" {if $TASK_MODEL->get('status') neq 1} checked="" {/if}
										value="false" />&nbsp;{\App\Language::translate('LBL_IN_ACTIVE',$QUALIFIED_MODULE)}
								</div>
							</div>
						</div>
						{if isset($TASK_OBJECT->trigger)}
							{assign var=TRIGGER value=$TASK_OBJECT->trigger}
							{assign var=DAYS value=$TRIGGER['days']}

							{if ($DAYS < 0)}
								{assign var=DAYS value=$DAYS*-1}
								{assign var=DIRECTION value='before'}
							{else}
								{assign var=DIRECTION value='after'}
							{/if}
						{/if}
						{if $TASK_OBJECT->recordEventState === VTTask::RECORD_EVENT_ACTIVE || ($TASK_OBJECT->recordEventState === VTTask::RECORD_EVENT_DOUBLE_MODE && !$WORKFLOW_MODEL->getParams('iterationOff'))}
							<div class="form-row pb-3">
								<div class="col-md-2 checkbox d-flex align-items-center">
									<div class="mr-2 mb-0">
										{\App\Language::translate('LBL_EXECUTE_TASK',$QUALIFIED_MODULE)}
									</div>
									<input type="checkbox" class="alignTop" name="check_select_date"
										{if !empty($TRIGGER)} checked {/if} />
								</div>
								<div class="col-md-10 form-row {if !empty($TRIGGER)} show {else} d-none {/if}"
									id="checkSelectDateContainer">
									<div class="col-md-2">
										<input class="form-control" type="text" name="select_date_days"
											value="{if !empty($DAYS)}{$DAYS}{/if}"
											data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]">
									</div>
									<div class="col-form-label float-left alignMiddle">{\App\Language::translate('LBL_DAYS',$QUALIFIED_MODULE)}</div>
									<div class="col-md-2 ml-0">
										<select class="select2 form-control" name="select_date_direction">
											<option {if !empty($DIRECTION) && ($DIRECTION eq 'after')} selected="" {/if}
												value="after">{\App\Language::translate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
											<option {if !empty($DIRECTION) && ($DIRECTION eq 'before')} selected="" {/if}
												value="before">{\App\Language::translate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
										</select>
									</div>
									<div class="col-md-6 ml-0">
										<select class="select2" name="select_date_field">
											{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
												<option {if !empty($TRIGGER['field']) && ($TRIGGER['field'] eq $DATETIME_FIELD->get('name'))} selected="" {/if}
													value="{$DATETIME_FIELD->get('name')}">{\App\Language::translate($DATETIME_FIELD->get('label'), $DATETIME_FIELD->getModuleName())}</option>
											{/foreach}
										</select>
									</div>
								</div>
							</div>
						{/if}
						<div class="taskTypeUi well bg-light px-0 px-md-1 px-lg-3">
							{include file="{$TASK_TEMPLATE_PATH}" }
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE) BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
			</div>
			</form>
		</div>
	</div>
	</div>
{/strip}
