{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title">{\App\Language::translate('LBL_INSPECTION_PERMISSION_HEADER', $MODULE_NAME)}</h4>
	</div>
	<div class="modal-body" style="max-height: 500px;overflow-y: auto;">
		<div>
			<table class="table dataTable">
				<thead> 
					<tr>
						{if $WATCHDOG || $SRC_RECORD_ID neq 0}
							<th style="width:60px" class="text-left" ></th>
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
								<th scope="row" class="text-left">
									{if $WATCHDOG}
										{if $ITEM['watchdog']['active']}
											<button type="button" class="btn btn-info btn-xs"
													data-off="btn-sm btn-default" data-on="btn-sm btn-info"
													data-value="0" data-user="{$USER_ID}"
													data-record="{$SRC_RECORD_ID}"
													onclick="Vtiger_Index_Js.changeWatching(this);">
												<span class="glyphicon glyphicon-eye-open"></span>
											</button>
										{else}
											<button type="button" class="btn btn-default btn-xs"
													data-off="btn-sm btn-default" data-on="btn-sm btn-info"
													data-value="1" data-user="{$USER_ID}"
													data-record="{$SRC_RECORD_ID}"
													onclick="Vtiger_Index_Js.changeWatching(this);">
												<span class="glyphicon glyphicon-eye-close"></span>

											</button>
										{/if}
									{/if}
									{if !empty($UNREVIEWED_CHANGES[$USER_ID]['a'])}
										<span class="badge bgDanger marginLeft5" title="{\App\Language::translate('LBL_NUMBER_UNREAD_CHANGES', 'ModTracker')}">{$UNREVIEWED_CHANGES[$USER_ID]['a']}</span>
									{/if}
									{if !empty($UNREVIEWED_CHANGES[$USER_ID]['m'])}
										<span class="badge bgBlue mail marginLeft5" title="{\App\Language::translate('LBL_NUMBER_UNREAD_MAILS', 'ModTracker')}">{$UNREVIEWED_CHANGES[$USER_ID]['m']}</span>
									{/if}
								</th>
							{/if}
							<th scope="row" class="text-center">
								{$ITEM['userName']}
							</th>
							{foreach item=ACTION from=$ITEM['privileges']}
								{if $ACTION['param']}
									{assign var=ACCESSLOG value=\App\Language::translate($ACTION['accessLog'], $MODULE_NAME, $ACTION['param'])}
								{else}
									{assign var=ACCESSLOG value=\App\Language::translate($ACTION['accessLog'], $MODULE_NAME)}
								{/if}
								<td class="text-center {$ACTION['text']}">
									<span class="cursorPointer popoverTooltip" {if $ACTION['profiles']}title="{\App\Language::translate('LBL_PROFILES', $MODULE_NAME)} {$ACTION['profiles']}"{/if} data-content="{$ACCESSLOG}" data-placement="top">
										{if $ACTION['isPermitted']}
											<span class="glyphicon glyphicon-ok text-success" aria-hidden="true"></span>
											<span class="hide" aria-hidden="true">1</span>
										{else}	
											<span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></span>
											<span class="hide" aria-hidden="true">0</span>
										{/if}
									</span>
								</td>
							{/foreach}
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
	</div>
	<div class="modal-footer">
		<div class="pull-right">
			<button class="btn btn-primary" type="reset" data-dismiss="modal">
				<span class="glyphicon glyphicon-remove margin-right5px"></span>
				<strong>{\App\Language::translate('LBL_CLOSE', $MODULE)}</strong>
			</button>
		</div>
	</div>
{/strip}
