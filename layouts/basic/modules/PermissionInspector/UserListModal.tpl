{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-PermissionInspector-Modals-UserListModal -->
	<div class="modal-header">
		<h5 class="modal-title">
			<span class="fas fa-user-secret mr-1"></span>
			{\App\Language::translate('LBL_INSPECTION_PERMISSION_HEADER', $MODULE_NAME)}
		</h5>
		<button type="button" class="close" data-dismiss="modal" aria-label="{\App\Language::translate('LBL_CLOSE')}">
			<span aria-hidden="true" title="{\App\Language::translate('LBL_CLOSE')}">&times;</span>
		</button>
	</div>
	<div class="modal-body">
		<table class="table-responsive table table-sm js-modal-data-table">
			<thead>
				<tr>
					{if $WATCHDOG || $SRC_RECORD_ID neq 0}
						<th class="u-w-60px text-left"></th>
					{/if}
					<th class="text-center">{\App\Language::translate('LBL_FULL_USER_NAME', $MODULE_NAME)}</th>
					<th class="text-center">{\App\Language::translate('LBL_VIEW_PRVILIGE', $MODULE_NAME)}</th>
					<th class="text-center">{\App\Language::translate('LBL_CREATE_PRIVILIGE', $MODULE_NAME)}</th>
					<th class="text-center">{\App\Language::translate('LBL_EDIT_PRIVILIGE', $MODULE_NAME)}</th>
					<th class="text-center">{\App\Language::translate('LBL_DELETE_PRIVILIGE', $MODULE_NAME)}</th>
				</tr>
			</thead>
			<tbody>
				{foreach key=USER_ID item=ITEM from=$USERS_PERMISSION}
					<tr data-id="{$USER_ID}">
						{if $WATCHDOG || $SRC_RECORD_ID neq 0}
							<th scope="row" class="text-center">
								{if $WATCHDOG}
									{if $ITEM['watchdog']['active']}
										<button type="button" class="btn btn-info btn-xs"
											data-off="btn-sm btn-light" data-on="btn-sm btn-info"
											data-value="0" data-user="{$USER_ID}"
											data-record="{$SRC_RECORD_ID}"
											onclick="Vtiger_Index_Js.changeWatching(this);">
											<span class="fas fa-eye"></span>
										</button>
									{else}
										<button type="button" class="btn btn-light btn-xs"
											data-off="btn-sm btn-light" data-on="btn-sm btn-info"
											data-value="1" data-user="{$USER_ID}"
											data-record="{$SRC_RECORD_ID}"
											onclick="Vtiger_Index_Js.changeWatching(this);">
											<span class="far fa-eye-slash"></span>
										</button>
									{/if}
								{/if}
								{if !empty($UNREVIEWED_CHANGES[$USER_ID]['a'])}
									<span class="badge bgDanger ml-1" title="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}">
										{$UNREVIEWED_CHANGES[$USER_ID]['a']}
									</span>
								{/if}
								{if !empty($UNREVIEWED_CHANGES[$USER_ID]['m'])}
									<span class="badge bgBlue mail ml-1" title="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}">
										{$UNREVIEWED_CHANGES[$USER_ID]['m']}
									</span>
								{/if}
							</th>
						{/if}
						<th scope="row" class="text-center">
							{$ITEM['userName']}
						</th>
						{foreach item=ACTION from=$ITEM['privileges']}
							{if !empty($ACTION['param'])}
								{assign var=ACCESSLOG value=\App\Language::translate($ACTION['accessLog'], $MODULE_NAME, $ACTION['param'])}
							{else}
								{assign var=ACCESSLOG value=\App\Language::translate($ACTION['accessLog'], $MODULE_NAME)}
							{/if}
							<td class="text-center {if !empty($ACTION['text'])}{$ACTION['text']}{/if}">
								<span class="u-cursor-pointer js-popover-tooltip" data-js="popover"
									{if $ACTION['profiles']}title="{\App\Language::translate('LBL_PROFILES', $MODULE_NAME)} {$ACTION['profiles']}" {/if}
									data-content="{$ACCESSLOG}" data-placement="top">
									{if $ACTION['isPermitted']}
										<span class="fas fa-check text-success" aria-hidden="true"></span>
										<span class="d-none" aria-hidden="true">1</span>
									{else}
										<span class="fas fa-times text-danger" aria-hidden="true"></span>
										<span class="d-none" aria-hidden="true">0</span>
									{/if}
								</span>
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class="modal-footer">
		<button class="btn btn-danger" type="reset" data-dismiss="modal">
			<span class="fas fa-times mr-1"></span>
			<strong>{\App\Language::translate('LBL_CLOSE', $MODULE_NAME)}</strong>
		</button>
	</div>
	<!-- /tpl-PermissionInspector-Modals-UserListModal -->
{/strip}
