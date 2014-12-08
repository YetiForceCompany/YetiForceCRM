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

	<div class='modelContainer' id="addTaskContainer">
		<div class="modal-header contentsBackground">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>{vtranslate('LBL_ADD_TASKS_FOR_WORKFLOW', $QUALIFIED_MODULE)} -> {$TASK_TYPE_MODEL->get('label')}</h3>
		</div>
		<form class="form-horizontal" id="saveTask" method="post" action="index.php">
			<input type="hidden" name="module" value="{$MODULE}" />
			<input type="hidden" name="parent" value="Settings" />
			<input type="hidden" name="action" value="TaskAjax" />
			<input type="hidden" name="mode" value="Save" />
			<input type="hidden" name="for_workflow" value="{$WORKFLOW_ID}" />
			<input type="hidden" name="task_id" value="{$TASK_ID}" />
			<input type="hidden" name="taskType" id="taskType" value="{$TASK_TYPE_MODEL->get('tasktypename')}" />
			<div id="scrollContainer">
				<div class="modal-body tabbable">
					<div class="row-fluid padding-bottom1per">
						<span class="span8 row-fluid">
							<div class="span3">{vtranslate('LBL_TASK_TITLE',$QUALIFIED_MODULE)}<span class="redColor">*</span></div>
							<div class="span9 row-fluid"><input name="summary" class="span12" data-validation-engine='validate[required]' type="text" value="{$TASK_MODEL->get('summary')}" /></div>
						</span>
						<span class="span">&nbsp;</span>
						<span class="span3 row-fluid">
							<div class="span3">{vtranslate('LBL_STATUS',$QUALIFIED_MODULE)}</div>
							<div class="span9">
								<input type="radio" name="active" class="alignTop" {if $TASK_MODEL->get('status') eq 1} checked="" {/if} value="true">&nbsp;{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}&nbsp;&nbsp;
								<input type="radio" name="active" class="alignTop" {if $TASK_MODEL->get('status') neq 1} checked="" {/if} value="false" />&nbsp;{vtranslate('LBL_IN_ACTIVE',$QUALIFIED_MODULE)}
							</div>
						</span>	
					</div>
					{if ($TASK_OBJECT->trigger!=null)}
						{assign var=trigger value=$TASK_OBJECT->trigger}
						{assign var=days value=$trigger['days']}

						{if ($days < 0)}
							{assign var=days value=$days*-1}
							{assign var=direction value='before'}
						{else}
							{assign var=direction value='after'}
						{/if}
					{/if}
					<div class="row-fluid padding-bottom1per">
						<div class="span2"><input type="checkbox" class="alignTop" name="check_select_date" {if $trigger neq null}checked{/if}/>&nbsp;{vtranslate('LBL_EXECUTE_TASK',$QUALIFIED_MODULE)}</div>
						<div class="span10 row-fluid {if $trigger neq null}show {else} hide {/if}" id="checkSelectDateContainer">
							<span class="span2 row-fluid">
								<input class="span6" type="text" name="select_date_days" value="{$days}" data-validation-engine="validate[funcCall[Vtiger_WholeNumber_Validator_Js.invokeValidation]]" >&nbsp;
								<span class="alignMiddle">{vtranslate('LBL_DAYS',$QUALIFIED_MODULE)}</span>
							</span>
							<span class="span2 marginLeftZero">
								<select class="chzn-select" name="select_date_direction" style="width: 100px">
									<option {if $direction eq 'after'} selected="" {/if} value="after">{vtranslate('LBL_AFTER',$QUALIFIED_MODULE)}</option>
									<option {if $direction eq 'before'} selected="" {/if} value="before">{vtranslate('LBL_BEFORE',$QUALIFIED_MODULE)}</option>
								</select>
							</span><span class="span">&nbsp;</span>
							<span class="span6 marginLeftZero">
								<select class="chzn-select" name="select_date_field">
									{foreach from=$DATETIME_FIELDS item=DATETIME_FIELD}
										<option {if $trigger['field'] eq $DATETIME_FIELD->get('name')} selected="" {/if} value="{$DATETIME_FIELD->get('name')}">{vtranslate($DATETIME_FIELD->get('label'),$QUALIFIED_MODULE)}</option>
									{/foreach}
								</select>
							</span>
						</div>
					</div>
					<div class="taskTypeUi well">
						{include file="{$TASK_TEMPLATE_PATH}" }
					</div>
				</div>
			</div>	
			{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</form>
	</div>
{/strip}