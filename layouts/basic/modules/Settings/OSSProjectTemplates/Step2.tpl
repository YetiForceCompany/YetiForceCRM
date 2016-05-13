{*<!--
/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/
-->*}
<div class="row">
	<span class="col-md-4 btn-toolbar">
		<a class="btn btn-default addTaskButton">
			<strong>{vtranslate('ADD_TASKS', $MODULE_NAME)}</strong>
		</a>
	</span>
</div>

<div class="">
	<table class="table table-bordered table-condensed listViewEntriesTable">
		<thead>
			<tr class="listViewHeaders">
				<th width="30%">{vtranslate('LBL_TPL_NAME',$MODULE_NAME)}</th>
				<th width="30%">{vtranslate('LBL_TASK_NAME',$MODULE_NAME)}</th>
				<th></th>
			</tr>
		</thead>
		{if !empty($PROJECT_TASK_TPL_LIST)}
			<tbody>
				{foreach from=$PROJECT_TASK_TPL_LIST item=item key=key}
					<tr class="listViewEntries" data-id="{$key}">
						<td>{$item.tpl_name}</td>
						<td>{$item.projecttaskname}</td>
						<td>
							<a href='index.php?module=OSSProjectTemplates&parent=Settings&action=DeleteTemplate&tpl_id={$key}&base_module={$BASE_MODULE}&parent_tpl_id={$PARENT_TPL_ID}&back_view=Edit2' 
							   class="pull-right marginRight10px"><span type="{vtranslate('REMOVE_TPL', $MODULE_NAME)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
							<a data-toggle="modal" data-target="#step_2_modal_edit" class="pull-right edit_tpl">
								<span title="{vtranslate('LBL_EDIT')}" class="glyphicon glyphicon-pencil alignMiddle"></span>
							</a>
						</td>
					<tr>
					{/foreach}
			</tbody>
		</table>
	{else}
		<table class="emptyRecordsDiv">
			<tbody>
				<tr>
					<td>
						{vtranslate('LBL_NO_TASKS_ADDED',$MODULE_NAME)}
					</td>
				</tr>
			</tbody>
		</table>
	{/if}
</div> 
<br />

<div id="step_2_modal" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>{vtranslate('FIELD_LIST', $MODULE_NAME)}</h3>
			</div>
			<form action="index.php" method="post" name="project_form">

				<div class="modal-body">

					<input type="hidden" name='module' value="OSSProjectTemplates" />
					<input type="hidden" name='base_module' value="{$BASE_MODULE}" />
					<input type="hidden" name='action' value="CreateTemplate" />
					<input type="hidden" name='parent' value="Settings" />
					<input type="hidden" name='parent_tpl_id' value="{$PARENT_TPL_ID}" />
					<input type="hidden" name='back_view' value="Edit2" />

					<table class="table table-bordered">
						<tr>
							<td><span class="redColor fieldLabel">*</span> {vtranslate('LBL_TPL_NAME', $MODULE_NAME)}</td>
							<td><input class="required form-control input-sm" name="tpl_name" value="" type="text" /></td>
								{assign var=FIRST_ROW value=0}	
								{assign var=COUNTER value=0}
								{foreach from=$FIELD_HTML key=key item=item}
									{if $COUNTER eq 2}
								</tr><tr>
									{assign var=COUNTER value=1}
								{else}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
								{if $item.mandatory}
									<td class="fieldLabel"><span class="redColor">*</span> {vtranslate($item.label, $BASE_MODULE)}</td>    
								{else}
									<td class="fieldLabel">{vtranslate($item.label, $BASE_MODULE)}</td>
								{/if}
								<td>{$item.html}</td>
								{if $FIRST_ROW eq 0}
									</tr>
									<tr>
									{assign var=COUNTER value=0}
									{assign var=FIRST_ROW value=$FIRST_ROW+1}
								{/if}
							{/foreach}
						</tr>
					</table>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success okay-button" >{vtranslate('Save', $MODULE_NAME)}</button>
					<a href="#" class="btn btn-warning" data-dismiss="modal" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
				</div>      
			</form>
		</div>
	</div>
</div>

<div id="step_2_modal_edit" class="modal fade" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 class="modal-title">{vtranslate('FIELD_LIST', $MODULE_NAME)}</h3>
			</div>
			<form action="index.php" method="post" name="edit_project_form">

				<div class="modal-body">

					<input type="hidden" name='module' value="OSSProjectTemplates" />
					<input type="hidden" name='base_module' value="{$BASE_MODULE}" />
					<input type="hidden" name='action' value="UpdateTemplate" />
					<input type="hidden" name='parent' value="Settings" />
					<input type="hidden" name='tpl_id' value="" />
					<input type="hidden" name='parent_tpl_id' value="{$PARENT_TPL_ID}" />
					<input type="hidden" name='back_view' value="Edit2" />

					<table class="table table-bordered">
						<tr>
							<td><span class="redColor fieldLabel">*</span> {vtranslate('LBL_TPL_NAME', $MODULE_NAME)}</td>
							<td><input class="required form-control input-sm" name="tpl_name" value="" type="text" /></td>
								{assign var=FIRST_ROW value=0}	
								{assign var=COUNTER value=0}
								{foreach from=$FIELD_HTML key=key item=item}
									{if $COUNTER eq 2}
								</tr><tr>
									{assign var=COUNTER value=1}
								{else}
									{assign var=COUNTER value=$COUNTER+1}
								{/if}
								{if $item.mandatory}
									<td class="fieldLabel"><span class="redColor">*</span> {vtranslate($item.label, $BASE_MODULE)}</td>    
								{else}
									<td class="fieldLabel">{vtranslate($item.label, $BASE_MODULE)}</td>
								{/if}
								<td>{$item.html}</td>
								{if $FIRST_ROW eq 0}
									</tr>
									<tr>
									{assign var=COUNTER value=0}
									{assign var=FIRST_ROW value=$FIRST_ROW+1}
								{/if}
							{/foreach}
						</tr>
					</table>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success okay-button" >{vtranslate('Save', $MODULE_NAME)}</button>
					<a href="#" class="btn btn-warning" data-dismiss="modal" type="reset">{vtranslate('LBL_CANCEL', $MODULE_NAME)}</a>
				</div>      
			</form>
		</div>
	</div>
</div>
