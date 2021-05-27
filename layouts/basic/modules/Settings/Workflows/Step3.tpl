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
				{foreach from=$TASK_RECORDS item=TASK_RECORD}
					<li><a class="u-cursor-pointer dropdown-item"
						   data-url="{$TASK_RECORD->getEditViewUrl()}">{\App\Language::translate($TASK_RECORD->getTaskType()->get('label'), $QUALIFIED_MODULE)}</a>
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
			<a class="btn btn-success" href="index.php?module=Workflows&parent=Settings&view=List" title="{\App\Language::translate('LBL_GO_TO_RECORDS_LIST')}"
				alt="{\App\Language::translate('LBL_GO_TO_RECORDS_LIST')}">
				<span class="fas fa-caret-right mr-1"></span>
				{\App\Language::translate('LBL_FINISH', $QUALIFIED_MODULE)}
			</a>
		</div>
		<div class="clearfix"></div>
	</form>
{/strip}
