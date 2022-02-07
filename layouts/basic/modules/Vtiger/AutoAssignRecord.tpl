{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-AutoAssignRecord -->
	<div class="modal-body">
		<div class="">
			{assign var=USERS value=$AUTO_ASSIGN_RECORD->getOwners()}
			{assign var=DEFAULT_OWNER value=$AUTO_ASSIGN_RECORD->getDefaultOwner()}
			<div class="table-responsive">
				<table id="assignTable" class="table table-striped table-bordered js-modal-data-table" data-ordering="false">
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
						{foreach from=$USERS item=VALUE}
							{if $CURRENT_OWNER eq $VALUE.id}{continue}{/if}
							{assign var=USER_MODEL value=\App\User::getUserModel($VALUE.id)}
							<tr>
								<td>
									<strong>{$USER_MODEL->getName()}</strong>
								</td>
								<td>
									{\App\Language::translate(\App\PrivilegeUtil::getRoleName($USER_MODEL->getRole()), $MODULE_NAME)}
								</td>
								<td>
									{if $VALUE.count}{$VALUE.count}{else}0{/if}
								</td>
								<td>
									<a href="#" onclick="Vtiger_Index_Js.assignToOwner(this,{$USER_MODEL->getId()})" data-module="{$RECORD->getModuleName()}" data-record="{$RECORD->getid()}" id="user_{$USER_MODEL->getId()}" class="btn btn-sm btn-success" title="{\App\Language::translate('LBL_ASSIGN', $MODULE_NAME)}">
										<span class="fas fa-user"></span>
									</a>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{if empty($USERS) && $DEFAULT_OWNER && $DEFAULT_OWNER neq $CURRENT_OWNER}
				<div class="mt-2">
					<div class="col-12 text-left alert alert-info">
						<label>{\App\Language::translate('LBL_SET_DEFAULT_RECORD_OWNER', $MODULE_NAME)}&nbsp;</label>
						<a href="#" onclick="Vtiger_Index_Js.assignToOwner(this,{$DEFAULT_OWNER})" data-module="{$RECORD->getModuleName()}" data-record="{$RECORD->getid()}" id="user_{$DEFAULT_OWNER}" class="btn btn-sm btn-success" title="{\App\Language::translate('LBL_ASSIGN', $MODULE_NAME)}">
							<span class="fas fa-user">&nbsp;{\App\Fields\Owner::getLabel($DEFAULT_OWNER)}</span>
						</a>
					</div>
				</div>
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-AutoAssignRecord -->
{/strip}
