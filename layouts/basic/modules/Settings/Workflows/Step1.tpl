{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<div class="tpl-Settings-Workflows-Step1 workFlowContents">
		<form name="EditWorkflow" action="index.php" method="post" id="workflow_step1" class="form-horizontal">
			<input type="hidden" name="module" value="Workflows">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="mode" value="Step2"/>
			<input type="hidden" name="parent" value="Settings"/>
			<input type="hidden" class="step" value="1"/>
			<input type="hidden" name="record" value="{$RECORDID}"/>
			<input type="hidden" id="weekStartDay" data-value='{$WEEK_START_ID}'/>

			<div class="u-p-1per border">
				<label>
					<strong>{\App\Language::translate('LBL_STEP_1',$QUALIFIED_MODULE)}
						: {\App\Language::translate('LBL_ENTER_BASIC_DETAILS_OF_THE_WORKFLOW',$QUALIFIED_MODULE)}</strong>
				</label>
				<br/>
				<div class="form-group form-row">
					<label class="col-sm-3 col-form-label u-text-small-bold text-right">
						{\App\Language::translate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls">
						{if isset($MODE) && $MODE eq 'edit'}
							<input type='text' disabled='disabled' class="form-control"
								   value="{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}">
							<input type='hidden' name='module_name' value="{$MODULE_MODEL->get('name')}">
						{else}
							<select class="select2 form-control" id="moduleName" name="module_name" required="true"
									data-placeholder="Select Module...">
								{foreach from=$ALL_MODULES key=TABID item=MODULE_MODEL}
									<option value="{$MODULE_MODEL->getName()}" {if isset($SELECTED_MODULE) && $SELECTED_MODULE == $MODULE_MODEL->getName()} selected {/if}>
										{\App\Language::translate($MODULE_MODEL->getName(), $MODULE_MODEL->getName())}
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
						<input type="text" name="summary" class="form-control"
							   data-validation-engine='validate[required]' value="{$WORKFLOW_MODEL->get('summary')}"
							   id="summary"/>
					</div>
				</div>
				<div class="form-group form-row">
					<label class="col-sm-3 col-form-label u-text-small-bold text-right">
						{\App\Language::translate('LBL_SPECIFY_WHEN_TO_EXECUTE', $QUALIFIED_MODULE)}
					</label>
					<div class="col-sm-6 controls js-wf-executions-container" data-js="container">
						{assign var=WORKFLOW_MODEL_OBJ value=$WORKFLOW_MODEL->getWorkflowObject()}
						{foreach from=$TRIGGER_TYPES item=LABEL key=LABEL_ID}
							{assign var=EXECUTION_CONDITION value=$WORKFLOW_MODEL_OBJ->executionCondition}
							<div class="js-wf-execution-container" data-js="container">
								<label>
									<input type="radio" class="alignTop"
										name="execution_condition" {if $EXECUTION_CONDITION eq $LABEL_ID} checked="checked" {/if}	value="{$LABEL_ID}"/>
									&nbsp;&nbsp;{\App\Language::translate($LABEL,$QUALIFIED_MODULE)}
								</label><br/>
								{assign var=PARAMS value=[]}
								{if !empty($WORKFLOW_MODEL_OBJ->params)}
									{assign var=PARAMS value=\App\Json::decode($WORKFLOW_MODEL_OBJ->params)}
								{/if}
								{if $LABEL_ID eq 8}
									<div class="col-12 mb-2 js-wf-execution-item{if $EXECUTION_CONDITION neq $LABEL_ID} d-none {/if}" data-js="container">
										<div class="form-check">
											<input type="hidden" name="params[showTasks]" value="0">
											<input class="form-check-input" type="checkbox" value="1" id="showTasks" name="params[showTasks]" {if !empty($PARAMS['showTasks'])} checked="checked" {/if}>
											<label class="form-check-label" for="showTasks">
												{\App\Language::translate('LBL_WORKFLOW_TRIGGER_SHOW_TASKS', $QUALIFIED_MODULE)}
											</label>
										</div>
										<div class="form-check">
											<input type="hidden" name="params[enableTasks]" value="0">
											<input class="form-check-input" type="checkbox" value="1" id="enableTasks" name="params[enableTasks]"
												{if !empty($PARAMS['enableTasks'])} checked="checked" {/if}>
											<label class="form-check-label" for="enableTasks">
												{\App\Language::translate('LBL_WORKFLOW_TRIGGER_ENABLE_DEACTIVATION_TASKS', $QUALIFIED_MODULE)}
											</label>
										</div>
									</div>
								{elseif $LABEL_ID eq 6}
									{include file=\App\Layout::getTemplatePath('ScheduleBox.tpl', $QUALIFIED_MODULE)}
								{/if}
							</div>
						{/foreach}
					</div>
				</div>
			</div>
			<br/>
			<div class="float-right mb-4">
				<button class="btn btn-success mr-1" type="submit" disabled="disabled">
					<strong>
						<span class="fas fa-caret-right mr-1"></span>
						{\App\Language::translate('LBL_NEXT', $QUALIFIED_MODULE)}
					</strong>
				</button>
				<button class="btn btn-danger cancelLink" type="reset" onclick="javascript:window.history.back();">
					<strong>
						<span class="fas fa-times mr-1"></span>
						{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
					</strong>
				</button>
			</div>
		</form>
	</div>
{/strip}
