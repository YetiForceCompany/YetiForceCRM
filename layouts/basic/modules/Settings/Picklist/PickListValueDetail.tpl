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
	<div class="tpl-Settings-Picklist-PicklistValueDetail">
		{if $SELECTED_PICKLIST_FIELDMODEL}
			<ul class="nav nav-tabs " role="tablist">
				<li class="nav-item">
					<a class="nav-link active" href="#allValuesLayout"
					   data-toggle="tab" role="tab"
					   aria-controls="{\App\Language::translate('LBL_ALL_VALUES',$QUALIFIED_MODULE)}"{' '}
					   aria-selected="true">
						<strong>{\App\Language::translate('LBL_ALL_VALUES',$QUALIFIED_MODULE)}</strong>
					</a>
				</li>
				{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
					<li class="nav-item" id="assignedToRoleTab">
						<a class="nav-link" href="#assignedToRoleLayout" data-toggle="tab"
						   role="tab"
						   aria-controls="{\App\Language::translate('LBL_VALUES_ASSIGNED_TO_A_ROLE',$QUALIFIED_MODULE)}"{' '}
						   aria-selected="false">
							<strong>{\App\Language::translate('LBL_VALUES_ASSIGNED_TO_A_ROLE',$QUALIFIED_MODULE)}</strong>
						</a>
					</li>
				{/if}
			</ul>
			<div class="tab-content layoutContent py-3 themeTableColor overflowVisible">
				<div class="tab-pane fade show active" role="tabpanel" id="allValuesLayout">
					<div class="row">
						<div class="col-md-5 ml-0 u-text-ellipsis">
							<table id="pickListValuesTable" class="table table-bordered">
								<thead>
								<tr class="listViewHeaders">
									<th>{\App\Language::translate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}
										&nbsp;{\App\Language::translate('LBL_ITEMS',$QUALIFIED_MODULE)}</th>
								</tr>
								</thead>
								<tbody>
								<input type="hidden" value="{\App\Layout::getImagePath('drag.png')}"
									   id="dragImagePath"/>
								{assign var=PICKLIST_VALUES value=$SELECTED_PICKLISTFIELD_ALL_VALUES}
								{foreach key=PICKLIST_KEY item=PICKLIST_VALUE from=$PICKLIST_VALUES}
									<tr class="pickListValue" data-key-id="{$PICKLIST_KEY}"
										data-key="{\App\Purifier::encodeHtml($PICKLIST_VALUE)}">
										<td class="u-text-ellipsis">
											<img class="alignMiddle" src="{\App\Layout::getImagePath('drag.png')}"/>&nbsp;&nbsp;{\App\Language::translate($PICKLIST_VALUE,$SELECTED_MODULE_NAME)}
										</td>
									</tr>
								{/foreach}
								</tbody>
							</table>
						</div>
						<div class="col-md-2 btn-group-vertical" role="group">
							{if $SELECTED_PICKLIST_FIELDMODEL->isEditable()}
								{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
									<button class="btn btn-primary"
											id="assignValue">{\App\Language::translate('LBL_ASSIGN_VALUE',$QUALIFIED_MODULE)}</button>
								{/if}
								<button class="btn btn-info"
										id="addItem">{\App\Language::translate('LBL_ADD_VALUE',$QUALIFIED_MODULE)}</button>
								<button class="btn btn-warning"
										id="renameItem">{\App\Language::translate('LBL_EDIT',$QUALIFIED_MODULE)}</button>
								<button class="btn btn-danger"
										id="deleteItem">{\App\Language::translate('LBL_DELETE_VALUE',$QUALIFIED_MODULE)}</button>
							{/if}
							<button class="btn btn-success" disabled=""
									id="saveSequence">{\App\Language::translate('LBL_SAVE_ORDER',$QUALIFIED_MODULE)}</button>
							<br/><br/>
						</div>
						<div class="col-md-5">
							<br/>
							<div>
								<i class="fas fa-info-circle"></i>&nbsp;<span>{\App\Language::translate('LBL_DRAG_ITEMS_TO_RESPOSITION',$QUALIFIED_MODULE)}</span>
							</div>
							<br/>
							<div>
								&nbsp;&nbsp;{\App\Language::translate('LBL_SELECT_AN_ITEM_TO_RENAME_OR_DELETE',$QUALIFIED_MODULE)}</div>
							<br/>
							<div>
								&nbsp;&nbsp;{\App\Language::translate('LBL_TO_DELETE_MULTIPLE_HOLD_CONTROL_KEY',$QUALIFIED_MODULE)}</div>
						</div>
					</div>
					<div id="createViewContents" class="d-none">
						{include file=\App\Layout::getTemplatePath('CreateView.tpl', $QUALIFIED_MODULE)}
					</div>
				</div>
				{if $SELECTED_PICKLIST_FIELDMODEL->isRoleBased()}
					<div class="tab-pane fade" role="tabpanel" id="assignedToRoleLayout"
						 aria-labelledby="assignedToRoleLayout">
						<div class="row">
							<div class="col-md-2 textAlignRight">{\App\Language::translate('LBL_ROLE_NAME',$QUALIFIED_MODULE)}</div>
							<div class="col-md-4">
								<select name="rolesSelected" class="form-control" id="rolesList"
										data-placeholder="{\App\Language::translate('LBL_CHOOSE_ROLES',$QUALIFIED_MODULE)}">
									{foreach from=$ROLES_LIST item=ROLE}
										<option value="{$ROLE->get('roleid')}">{\App\Language::translate($ROLE->get('rolename'), $QUALIFIED_MODULE)}</option>
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
	</div>
{/strip}
