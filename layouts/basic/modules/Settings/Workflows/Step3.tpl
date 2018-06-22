{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*************************************************************************************}
{strip}
	<form name="EditWorkflow" action="index.php" method="post" id="workflow_step3" class="tpl-Settings-Workflows-Step3 form-horizontal">
		<input type="hidden" name="module" value="Workflows"/>
		<input type="hidden" name="record" value="{$RECORD}"/>
		<input type="hidden" class="step" value="3"/>
		<div class="btn-group">
			<a class="btn dropdown-toggle btn-light addButton" data-toggle="dropdown" href="#">
				<strong>{\App\Language::translate('LBL_ADD_TASK',$QUALIFIED_MODULE)}</strong>&nbsp;
			</a>
			<ul class="dropdown-menu">
				{foreach from=$TASK_TYPES item=TASK_TYPE}
					<li><a class="u-cursor-pointer dropdown-item"
						   data-url="{$TASK_TYPE->getEditViewUrl()}&for_workflow={$RECORD}">{\App\Language::translate($TASK_TYPE->get('label'),$QUALIFIED_MODULE)}</a>
					</li>
				{/foreach}
			</ul>
		</div>
		<div id="taskListContainer">
			{include file=\App\Layout::getTemplatePath('TasksList.tpl', $QUALIFIED_MODULE)}
		</div>
		<br/>
		<div class="float-right">
			<button class="btn btn-secondary backStep mr-1" type="button">
				<strong>
					<span class="fas fa-caret-left mr-1"></span>
					{\App\Language::translate('LBL_BACK', $QUALIFIED_MODULE)}
				</strong>
			</button>
			<button class="btn btn-success" type="button" onclick="javascript:window.history.back();">
				<strong>
					<span class="fas fa-caret-right mr-1"></span>
					{\App\Language::translate('LBL_FINISH', $QUALIFIED_MODULE)}
				</strong>
			</button>
		</div>
		<div class="clearfix"></div>
	</form>
{/strip}
