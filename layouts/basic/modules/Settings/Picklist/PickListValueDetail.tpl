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
	{if $SELECTED_PICKLIST_FIELDMODEL}
		<ul class="nav nav-tabs massEditTabs" style="margin-bottom: 0;border-bottom: 0">
			<li class="active"><a href="#allValuesLayout" data-toggle="tab"><strong>{vtranslate('LBL_ALL_VALUES',$QUALIFIED_MODULE)}</strong></a></li>
			{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}<li id="assignedToRoleTab"><a href="#AssignedToRoleLayout" data-toggle="tab"><strong>{vtranslate('LBL_VALUES_ASSIGNED_TO_A_ROLE',$QUALIFIED_MODULE)}</strong></a></li>{/if}
		</ul>
		<div class="tab-content layoutContent padding20 themeTableColor overflowVisible">
			<br>
			<div class="tab-pane active" id="allValuesLayout">	
				<div class="row">
					<div class="col-md-5 marginLeftZero textOverflowEllipsis">
						<table id="pickListValuesTable" class="table table-bordered" style="table-layout: fixed">
							<thead>
								<tr class="listViewHeaders"><th>{vtranslate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}&nbsp;{vtranslate('LBL_ITEMS',$QUALIFIED_MODULE)}</th></tr>
							</thead>
							<tbody>
							<input type="hidden" id="dragImagePath" value="{vimage_path('drag.png')}" />
							{assign var=PICKLIST_VALUES value=$SELECTED_PICKLISTFIELD_ALL_VALUES}
							{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
								<tr class="pickListValue" data-key-id="{$PICKLIST_KEY}" data-key="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE)}">
									<td class="textOverflowEllipsis"><img class="alignMiddle" src="{vimage_path('drag.png')}"/>&nbsp;&nbsp;{vtranslate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}</td>
								</tr>
							{/foreach}
							</tbody>
						</table>
					</div>
					<div class="col-md-2 btn-group-vertical" role="group">
						{if $SELECTED_PICKLIST_FIELDMODEL->isEditable()}
							{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
								<button class="btn btn-primary" id="assignValue">{vtranslate('LBL_ASSIGN_VALUE',$QUALIFIED_MODULE)}</button>
							{/if}	
							<button class="btn btn-info" id="addItem">{vtranslate('LBL_ADD_VALUE',$QUALIFIED_MODULE)}</button>
							<button class="btn btn-warning" id="renameItem">{vtranslate('LBL_RENAME_VALUE',$QUALIFIED_MODULE)}</button>
							<button class="btn btn-danger"  id="deleteItem">{vtranslate('LBL_DELETE_VALUE',$QUALIFIED_MODULE)}</button>
						{/if}
						<button class="btn btn-success" disabled=""  id="saveSequence">{vtranslate('LBL_SAVE_ORDER',$QUALIFIED_MODULE)}</button><br><br>
					</div>
					<div class="col-md-5">
						<br>
						<div><i class="glyphicon glyphicon-info-sign"></i>&nbsp;<span>{vtranslate('LBL_DRAG_ITEMS_TO_RESPOSITION',$QUALIFIED_MODULE)}</span></div>
						<br><div>&nbsp;&nbsp;{vtranslate('LBL_SELECT_AN_ITEM_TO_RENAME_OR_DELETE',$QUALIFIED_MODULE)}</div> 
						<br><div>&nbsp;&nbsp;{vtranslate('LBL_TO_DELETE_MULTIPLE_HOLD_CONTROL_KEY',$QUALIFIED_MODULE)}</div>
					</div>	
				</div>		
				<div id="createViewContents" class="hide">
					{include file="CreateView.tpl"|@vtemplate_path:$QUALIFIED_MODULE}
				</div>
			</div>
			{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
				<div class="tab-pane" id="AssignedToRoleLayout">
					<div class="row">
						<div class="col-md-2 textAlignRight">{vtranslate('LBL_ROLE_NAME',$QUALIFIED_MODULE)}</div>
						<div class="col-md-4">
							<select id="rolesList" class="form-control" name="rolesSelected" data-placeholder="{vtranslate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
								{foreach from=$ROLES_LIST item=ROLE}
									<option value="{$ROLE->get('roleid')}">{vtranslate($ROLE->get('rolename'), $QUALIFIED_MODULE)}</option>
								{/foreach}
							</select>	
						</div>
					</div>
					<div id="pickListValeByRoleContainer">
					</div>	
				</div>
			{/if}
		</div>	
	{/if}
{/strip}
