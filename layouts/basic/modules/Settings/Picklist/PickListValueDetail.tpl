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
	<!-- tpl-Settings-Picklist-PicklistValueDetail -->
	<div>
		{if $SELECTED_PICKLIST_FIELDMODEL}
			{assign var=SHOW_ROLE value=$SELECTED_PICKLIST_FIELDMODEL->isRoleBased() && $SELECTED_PICKLIST_FIELDMODEL->isEditable()}
			<ul class="nav nav-tabs mr-0" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" href="#allValuesLayout"
						data-toggle="tab" role="tab"
						aria-controls="{\App\Language::translate('LBL_ALL_VALUES',$QUALIFIED_MODULE)}" {' '}
						aria-selected="true">
						<strong>{\App\Language::translate('LBL_ALL_VALUES',$QUALIFIED_MODULE)}</strong>
					</a>
				</li>
				{if $SHOW_ROLE}
					<li class="nav-item" id="assignedToRoleTab">
						<a class="nav-link" href="#assignedToRoleLayout" data-toggle="tab"
							role="tab"
							aria-controls="{\App\Language::translate('LBL_VALUES_ASSIGNED_TO_A_ROLE',$QUALIFIED_MODULE)}" {' '}
							aria-selected="false">
							<strong>{\App\Language::translate('LBL_VALUES_ASSIGNED_TO_A_ROLE',$QUALIFIED_MODULE)}</strong>
						</a>
					</li>
				{/if}
			</ul>
			<div class="tab-content layoutContent py-3 themeTableColor overflowVisible">
				<div class="tab-pane fade show active" role="tabpanel" id="allValuesLayout">
					<div class="row">
						<div class="col-12 btn-group flex-wrap flex-md-nowrap" role="group">
							{if $SELECTED_PICKLIST_FIELDMODEL->isEditable()}
								<button type="button" class="btn btn-primary js-picklist-create" id="addItem">{\App\Language::translate('LBL_ADD_VALUE',$QUALIFIED_MODULE)}</button>
								{if $SHOW_ROLE}
									<button type="button" class="btn btn-info js-picklist-role" id="assignValue">
										{\App\Language::translate('LBL_ASSIGN_VALUE',$QUALIFIED_MODULE)}
									</button>
								{/if}
								<button type="button" class="btn btn-warning js-picklist-import" id="importItem">{\App\Language::translate('LBL_IMPORT_VALUE',$QUALIFIED_MODULE)}</button>
							{/if}
							<button type="button" class="btn btn-success js-picklist-order" disabled="" id="saveSequence">{\App\Language::translate('LBL_SAVE_ORDER',$QUALIFIED_MODULE)}</button>
						</div>
						<div class="col-12">
							<div class="my-1 my-md-3 ml-2">
								<span class="fas fa-info-circle mr-1"></span>{\App\Language::translate('LBL_DRAG_ITEMS_TO_RESPOSITION',$QUALIFIED_MODULE)}
							</div>
						</div>
						<div class="col-12">
							<div class="table-responsive">
								<table id="pickListValuesTable" class="table table-bordered js-picklist-table">
									<thead>
										<tr class="listViewHeaders">
											<th>
												{\App\Language::translate($SELECTED_PICKLIST_FIELDMODEL->get('label'),$SELECTED_MODULE_NAME)}
												&nbsp;{\App\Language::translate('LBL_ITEMS',$QUALIFIED_MODULE)}
											</th>
										</tr>
									</thead>
									<tbody>
										{foreach key=PICKLIST_KEY item=PICKLIST_DATA from=\App\Fields\Picklist::getValues($SELECTED_PICKLIST_FIELDMODEL->getName())}
											<tr class="pickListValue js-picklist-value u-bg-white-darken" data-key-id="{$PICKLIST_KEY}">
												<td class="u-text-ellipsis p-2">
													<span class="mdi mdi-drag"></span>
													&nbsp;&nbsp;{\App\Language::translate($PICKLIST_DATA['picklistValue'],$SELECTED_MODULE_NAME)}
													<span class="float-right actions">
														<button class="btn btn-primary btn-xs ml-1 js-picklist-edit" title="{App\Language::translate('LBL_EDIT', $QUALIFIED_MODULE)}" data-id="{$PICKLIST_KEY}">
															<span class="yfi yfi-full-editing-view"></span>
														</button>
														{if $SELECTED_PICKLIST_FIELDMODEL->isEditable() && $PICKLIST_DATA['presence'] === 1}
															<button class="btn btn-danger btn-xs js-picklist-delete ml-1" title="{App\Language::translate('LBL_DELETE', $QUALIFIED_MODULE)}" data-id="{$PICKLIST_KEY}">
																<span class="fas fa-trash-alt"></span>
															</button>
														{/if}
													</span>
												</td>
											</tr>
										{/foreach}
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				{if $SHOW_ROLE}
					<div class="tab-pane fade" role="tabpanel" id="assignedToRoleLayout">
						<div class="row align-items-center">
							<div class="col-md-2 textAlignRight">{\App\Language::translate('LBL_ROLE_NAME',$QUALIFIED_MODULE)}</div>
							<div class="col-md-4">
								<select name="rolesSelected" class="form-control select2 js-role-list" id="rolesList"
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
	<!-- /tpl-Settings-Picklist-PicklistValueDetail -->
{/strip}
