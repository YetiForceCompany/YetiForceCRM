{*+***********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
*************************************************************************************}
{strip}
	<br />
	<div>
		<table class="table table-bordered table-sm listViewEntriesTable js-workflow-tasks-list">
			<thead>
				<tr class="listViewHeaders">
					<th width="10%">{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}</th>
					<th width="30%">{\App\Language::translate('LBL_TASK_TYPE',$QUALIFIED_MODULE)}</th>
					<th width="60%">{\App\Language::translate('LBL_TASK_TITLE',$QUALIFIED_MODULE)}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$TASK_LIST item=TASK}
					<tr class="listViewEntries js-workflow-task" data-id="{$TASK->getId()}">
						<td width="10%">
							<a class="px-2 u-cursor-move js-drag" data-js="ui-sortable-handle"><img class="align-baseline" src="{\App\Layout::getImagePath('drag.png')}" title="{\App\Language::translate('LBL_DRAG',$QUALIFIED_MODULE)}" /></a> <input type="checkbox" class="taskStatus" data-statusurl="{$TASK->getChangeStatusUrl()}" {if $TASK->isActive()} checked="" {/if} />
						</td>
						<td width="30%">{\App\Language::translate($TASK->getTaskType()->getLabel(),$QUALIFIED_MODULE)}</td>
						<td width="60%">{$TASK->getName()}
							<div class="float-right actions">
								<span class="actionImages">
									{if $TASK->isEditable()}
										<a data-url="{$TASK->getEditViewUrl()}">
											<span class="yfi yfi-full-editing-view" title="{\App\Language::translate('LBL_EDIT',$QUALIFIED_MODULE)}"></span>
										</a>&nbsp;&nbsp;
									{else}
										<div class="js-popover-tooltip mr-2 d-inline text-danger" data-js="popover" data-content="{\App\Purifier::encodeHtml(App\Language::translate('LBL_ERROR_DELETE_ENTRY', $QUALIFIED_MODULE))}">
											<span class="fas fa-info-circle"></span>
										</div>
									{/if}
									<a class="deleteTask" data-deleteurl="{$TASK->getDeleteActionUrl()}">
										<span class="fas fa-trash-alt" title="{\App\Language::translate('LBL_DELETE',$QUALIFIED_MODULE)}"></span>
									</a>
								</span>
							</div>
						</td>
					<tr>
					{/foreach}
			</tbody>
		</table>
		{if empty($TASK_LIST)}
			<table class="emptyRecordsDiv">
				<tbody>
					<tr>
						<td>
							{\App\Language::translate('LBL_NO_TASKS_ADDED',$QUALIFIED_MODULE)}
						</td>
					</tr>
				</tbody>
			</table>
		{/if}
	</div>
{/strip}
