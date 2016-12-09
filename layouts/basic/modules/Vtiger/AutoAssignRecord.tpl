{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="modal-header">
		<div class="col-xs-10">
			<h3 class="modal-title">{vtranslate('LBL_AUTOMATIC_ASSIGNMENT', $MODULE_NAME)}</h3>
		</div>
		<div class="pull-right btn-group">
			{if $RECORD->isEditable()}
				<a href="{$RECORD->getEditViewUrl()}" class="btn btn-default" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"><span class="glyphicon glyphicon-pencil summaryViewEdit"></span></a>
				{/if}
				{if $RECORD->isViewable()}
				<a href="{$RECORD->getDetailViewUrl()}" class="btn btn-default" title="{vtranslate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}"><span  class="glyphicon glyphicon-th-list summaryViewEdit"></span></a>
				{/if}
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="modal-body">
		<div class="row">
			{assign var=USERS value=$AUTO_ASSIGN_RECORD->getAvailableUsers()}
			{assign var=DEFAULT_OWNER value=$AUTO_ASSIGN_RECORD->getDefaultOwner()}
			{if $USERS}
				<div class="table-responsive col-xs-12">
					<table id="assignTable" class="table table-striped table-bordered dataTable">
						<thead>
							<tr>
								<th>
									<strong>{\App\Language::translate('LBL_USER', $MODULE_NAME)}</strong>
								</th>
								<th>
									<strong>{\App\Language::translate('LBL_ROLE', $MODULE_NAME)}</strong>
								</th>
								<th>
									<strong>{\App\Language::translate('LBL_NUMBER_OF_ASSIGNED_RECORDS', $MODULE_NAME)}</strong>
								</th>
								<th>
									<strong>{\App\Language::translate('LBL_ACTIONS', $MODULE_NAME)}</strong>
								</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$USERS key=USER_ID item=VALUE}
								{assign var=USER_MODEL value=\App\User::getUserModel($USER_ID)}
								<tr>
									<td>
										<strong>{$USER_MODEL->getName()}</strong>
									</td>
									<td>
										{\App\Language::translate(\App\PrivilegeUtil::getRoleName($USER_MODEL->getRole()), $MODULE_NAME)}
									</td>
									<td>
										{$VALUE}
									</td>
									<td>
										<a href="#" onclick="Vtiger_Index_Js.assignToOwner(this,{$USER_ID})" data-module="{$RECORD->getModuleName()}" data-record="{$RECORD->getid()}" id="user_{$USER_ID}" class="btn btn-xs btn-success" title="{\App\Language::translate('LBL_ASSIGN', $MODULE_NAME)}">
											<span class="glyphicon glyphicon-user"></span>
										</a>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			{elseif $DEFAULT_OWNER}
				<div class="col-md-12 text-center">
					<label>{\App\Language::translate('LBL_SET_DEFAULT_RECORD_OWNER', $MODULE_NAME)}&nbsp;</label>
					<a href="#" onclick="Vtiger_Index_Js.assignToOwner(this,{$DEFAULT_OWNER})" data-module="{$RECORD->getModuleName()}" data-record="{$RECORD->getid()}" id="user_{$DEFAULT_OWNER}" class="btn btn-xs btn-success" title="{\App\Language::translate('LBL_ASSIGN', $MODULE_NAME)}">
					<span class="glyphicon glyphicon-user">&nbsp;{\App\Fields\Owner::getLabel($DEFAULT_OWNER)}</span>
				</a>
				</div>
			{else}
				<div class="text-center">
					{\App\Language::translate('LBL_NO_USERS_TO_ASSIGN', $MODULE_NAME)}
				</div>
			{/if}
		</div>
	</div>
{/strip}
