{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-OSSMailScanner-Index -->
	<div class="o-breadcrumb widget_header row">
		<div class="col-12">
			{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			<div class="d-flex align-items-center ml-auto">
				<a href="https://yetiforce.com/en/knowledge-base/documentation/administrator-documentation/item/mail-scanner" target="_blank" class="btn btn-outline-info js-popover-tooltip" data-content="{App\Language::translate('BTM_GOTO_YETIFORCE_DOCUMENTATION')}" rel="noreferrer noopener" data-js="popover">
					<span class="mdi mdi-book-open-page-variant u-fs-lg"></span>
				</a>
			</div>
		</div>
	</div>
	{if ($CHECKCRON[0]['status'] == 0 ) || !$CHECKCRON || ($CHECKCRON[1]['status'] == 0)}
		<div class="alert alert-block alert-warning">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<h4 class="alert-heading">{\App\Language::translate($MODULE_NAME, $MODULE_NAME)}
				- {\App\Language::translate('Alert_active_cron', $MODULE_NAME)}</h4>
			<p>{\App\Language::translate('Alert_active_cron_desc', $MODULE_NAME)}</p>
			{if \App\Security\AdminAccess::isPermitted('CronTasks')}
				<p>
					<a class="btn btn-light" role="button"
						href="index.php?module=CronTasks&parent=Settings&view=List">{\App\Language::translate('Scheduler','Settings:Vtiger')}</a>
				</p>
			{/if}
		</div>
	{/if}
	{if ( $CHECKCRON[1]['frequency'] * 2) > $CHECKCRON[0]['frequency']}
		<div class="alert alert-block alert-warning">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<h4 class="alert-heading">{\App\Language::translate($MODULE_NAME, $MODULE_NAME)} - {\App\Language::translate('Alert_active_crontime', $MODULE_NAME)}</h4>
			<p>{\App\Language::translate('Alert_active_crontime_desc', $MODULE_NAME)}</p>
			{if \App\Security\AdminAccess::isPermitted('CronTasks')}
				<p>
					<a class="btn btn-light" role="button"
						href="index.php?module=CronTasks&parent=Settings&view=List">{\App\Language::translate('Scheduler','Settings:Vtiger')}</a>
				</p>
			{/if}
		</div>
	{/if}
	<ul id="tabs" class="nav nav-tabs nav-justified my-2 mr-0" data-tabs="tabs">
		<li class="nav-item"><a class="nav-link active" href="#tab_accounts" data-toggle="tab">
				<span class="fas fa-inbox mr-2"></span>
				{\App\Language::translate('E-mail Accounts', $MODULE_NAME)}
			</a></li>
		<li class="nav-item"><a class="nav-link" href="#tab_actions" data-toggle="tab">
				<span class="fas fa-play mr-2"></span>
				{\App\Language::translate('Actions', $MODULE_NAME)}
			</a></li>
		<li class="nav-item"><a class="nav-link" href="#tab_email_search" data-toggle="tab">
				<span class="fas fa-wrench mr-2"></span>
				{\App\Language::translate('General Configuration', $MODULE_NAME)}
			</a></li>
		<li class="nav-item"><a class="nav-link" href="#tab_record_numbering" data-toggle="tab">
				<span class="adminIcon-recording-control mr-2"></span>
				{\App\Language::translate('Record Numbering', $MODULE_NAME)}
			</a></li>
		<li class="nav-item"><a class="nav-link" href="#exceptions" data-toggle="tab">
				<span class="fas fa-exclamation mr-2"></span>
				{\App\Language::translate('LBL_EXCEPTIONS', $MODULE_NAME)}
			</a></li>
	</ul>
	<div id="my-tab-content" class="tab-content marginTop20">
		<div class='editViewContainer tab-pane active' id="tab_accounts">
			<div class="alert alert-info mb-2">{\App\Language::translate('Alert_info_tab_accounts', $MODULE_NAME)}</div>
			{if $ERRORNOMODULE}
				<div class="alert alert-block alert-warning">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">{\App\Language::translate('OSSMail', 'OSSMail')} - {\App\Language::translate('Alert_no_module_title', $MODULE_NAME)}</h4>
					<p>{\App\Language::translate('Alert_no_module_desc', $MODULE_NAME)}</p>
				</div>
			{/if}
			{if $ACCOUNTS_LIST eq false}
				<div class="alert alert-block alert-warning">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">{\App\Language::translate('OSSMail', 'OSSMail')} - {\App\Language::translate('Alert_no_accounts_title', $MODULE_NAME)}</h4>
					<p>{\App\Language::translate('Alert_no_accounts_desc', $MODULE_NAME)}</p>
					{if \App\Mail::checkInternalMailClient()}
						<p><a class="btn btn-light" role="button" href="index.php?module=OSSMail&view=Index">
								{\App\Language::translate('OSSMail','OSSMail')}
							</a></p>
					{/if}
				</div>
			{else}
				<table class="table tableRWD table-bordered">
					<thead>
						<tr class="listViewHeaders">
							<th data-tablesaw-priority="1">{\App\Language::translate('username', $MODULE_NAME)}</th>
							<th data-tablesaw-priority="2">{\App\Language::translate('mail_host', $MODULE_NAME)}</th>
							<th data-tablesaw-priority="3">{\App\Language::translate('Actions', $MODULE_NAME)}</th>
							<th data-tablesaw-priority="4">{\App\Language::translate('User', $MODULE_NAME)}</th>
							<th data-tablesaw-priority="5">&nbsp;</th>
						</tr>
					</thead>
					<tbody>
						{assign var=USERS_ENTITY_INFO value=\App\Module::getEntityInfo('Users')}
						{foreach from=$ACCOUNTS_LIST item=row}
							{assign var=IS_ACTIVE value=$row['crm_status'] == OSSMail_Record_Model::MAIL_BOX_STATUS_ACTIVE || $row['crm_status'] == OSSMail_Record_Model::MAIL_BOX_STATUS_INVALID_ACCESS}
							{assign var=IS_BLOCKED value=$row['crm_status'] == OSSMail_Record_Model::MAIL_BOX_STATUS_BLOCKED_TEMP || $row['crm_status'] == OSSMail_Record_Model::MAIL_BOX_STATUS_BLOCKED_PERM}
							{if $IS_ACTIVE}
								{assign var=FOLDERS value=$RECORD_MODEL->getFolders($row['user_id'])}
							{else}
								{assign var=FOLDERS value=[]}
							{/if}
							<tr id="row_account_{$row['user_id']}" class="
						{if $IS_BLOCKED}table-danger
						{elseif $row['crm_status'] == OSSMail_Record_Model::MAIL_BOX_STATUS_DISABLED}table-secondary{/if}">
								<td><span class="mr-2">{$row['username']}</span>
									{if $row['crm_status'] != OSSMail_Record_Model::MAIL_BOX_STATUS_ACTIVE}
										({\App\Language::translate(OSSMail_Record_Model::getStatusLabel($row['crm_status']), $MODULE_NAME)})
									{/if}
									{if $row['crm_error']}
										<span class="fas fa-exclamation-triangle u-fs-xlg text-danger float-right js-popover-tooltip" data-content="{\App\Language::translate('IMAP_ERROR', $MODULE_NAME)}:<br>{\App\Purifier::encodeHtml($row['crm_error'])}" data-js="popover"></span>
									{/if}
								</td>
								<td>{$row['mail_host']}</td>
								<td class='functionList'>
									<select class="form-control select2" multiple data-user-id="{$row['user_id']}" id="function_list_{$row['user_id']}" name="function_list_{$row['user_id']}">
										<optgroup label="{\App\Language::translate('Function_list', $MODULE_NAME)}">
											{foreach item=ACTION from=$ACTIONS_LIST}
												<option value="{\App\Purifier::encodeHtml($ACTION)}" {if in_array($ACTION, $row['actions'])} selected="selected" {/if}>
													{\App\Language::translate($ACTION, $MODULE_NAME)}
												</option>
											{/foreach}
										</optgroup>
									</select>
								</td>
								<td>
									<select id="user_list_{$row['user_id']}" data-user="{$row['user_id']}" name="user_list_{$row['user_id']}" class="form-control select2">
										<optgroup label="{\App\Language::translate('User list', $MODULE_NAME)}">
											{if $row['crm_user_id'] eq '0'}
												<option value="0" id="user_list_none">{\App\Language::translate('None', $MODULE_NAME)}</option>
											{/if}
											{foreach item=item from=$RECORD_MODEL->getUserList()}
												<option value="{$item['id']}" {if $row['crm_user_id'] == $item['id']} selected="selected" {/if}>{foreach from=$USERS_ENTITY_INFO['fieldnameArr'] item=ENTITY}{$item[$ENTITY]} {/foreach}</option>
											{/foreach}
										</optgroup>
										<optgroup label="{\App\Language::translate('Group list', $MODULE_NAME)}">
											{foreach item=item from=$RECORD_MODEL->getGroupList()}
												<option value="{$item['groupid']}" {if $row['crm_user_id'] == $item['groupid'] } selected="selected" {/if}>{$item['groupname']}</option>
											{/foreach}
										</optgroup>
									</select>
								</td>
								<td class='scanerMailActionsButtons'>
									<div class="btn-toolbar">
										<div class="btn-group">
											<button type="button" class="btn btn-light expand-hide" title="{\App\Language::translate('LBL_SHOW_ACCOUNT_DETAILS', $MODULE_NAME)}" data-user-id="{$row['user_id']}">
												<span class="fas fa-chevron-down"></span>
											</button>
											{if $IS_ACTIVE}
												<button type="button" class="btn btn-light js-edit-folders" title="{\App\Language::translate('LBL_EDIT_FOLDER_ACCOUNT', $MODULE_NAME)}" data-user="{$row['user_id']}">
													<span class="fas fa-folder-open"></span>
												</button>
											{/if}
											<button type="button" class="btn btn-light js-delate-account" title="{\App\Language::translate('LBL_DELETE_ACCOUNT', $MODULE_NAME)}" data-user-id="{$row['user_id']}" data-js="click">
												<span class="fas fa-trash-alt"></span>
											</button>
											{if $IS_ACTIVE || $row['crm_status'] == OSSMail_Record_Model::MAIL_BOX_STATUS_INVALID_ACCESS}
												<button type="button" class="btn btn-light js-edit-status" data-status="{OSSMail_Record_Model::MAIL_BOX_STATUS_DISABLED}" data-user="{$row['user_id']}" title="{\App\Language::translate('LBL_SET_STATUS_DISABLE', $MODULE_NAME)}" data-js="click">
													<span class="fas fa-stop"></span>
												</button>
											{elseif $row['crm_status'] == OSSMail_Record_Model::MAIL_BOX_STATUS_DISABLED || $IS_BLOCKED}
												<button type="button" class="btn btn-light js-edit-status" data-status="{OSSMail_Record_Model::MAIL_BOX_STATUS_ACTIVE}" data-user="{$row['user_id']}" title="{\App\Language::translate('LBL_SET_STATUS_ACTIVE', $MODULE_NAME)}" data-js="click">
													<span class="fas fa-play"></span>
												</button>
											{/if}
										</div>
									</div>
									<span class="js-empty-folders-alert badge badge-danger {if !$IS_ACTIVE || !empty($FOLDERS)}d-none{/if}">
										<span class="fas fa-question-circle mr-1"></span>
										{\App\Language::translate('ERR_NO_CONFIGURATION_FOLDERS', $MODULE_NAME)}
									</span>
								</td>
							</tr>
							<tr style="display: none;" data-user-id="{$row['user_id']}">
								<td colspan="6">
									<div>
										{if $IS_ACTIVE}
											<h5>
												<strong {if empty($FOLDERS)}class="text-danger" {/if}>
													{\App\Language::translate('Folder configuration', $MODULE_NAME)}:
												</strong>
												{foreach item=FOLDER from=$FOLDERS}
													{$FOLDER['folder']} ({\App\Language::translate($FOLDER['type'], $MODULE_NAME)}),
												{foreachelse}
													{\App\Language::translate('--None--', $MODULE_NAME)}
												{/foreach}
											</h5>
										{/if}
									</div>
									<hr />
									<div>
										<table class="table">
											<thead>
												<tr>
													<th style="color: black; background-color: #d3d3d3;">{\App\Language::translate('identities_name', $MODULE_NAME)}</th>
													<th style="color: black; background-color: #d3d3d3;">{\App\Language::translate('identities_adress', $MODULE_NAME)}</th>
													<th colspan="2" style="color: black; background-color: #d3d3d3;">{\App\Language::translate('identities_del', $MODULE_NAME)}</th>
												</tr>
											</thead>
											{foreach item=item from=$IDENTITYLIST[$row['user_id']]}
												<tr style="{cycle values="'',background-color: #f9f9f9"}">
													<td>{$item['name']}</td>
													<td>{$item['email']}</td>
													<td colspan="2" style="text-align: center;">
														<button data-id="{$item['identity_id']}" type="button" class="btn btn-sm btn-danger identities_del">
															<span class="fas fa-trash-alt mr-1"></span>
															{\App\Language::translate('identities_del', $MODULE_NAME)}
														</button>
													</td>
												</tr>
											{/foreach}
										</table>
									</div>
								</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			{/if}
		</div>
		<div class='editViewContainer tab-pane marginTop20' id="tab_actions">
			<div class="alert alert-info mb-2">{\App\Language::translate('Alert_info_tab_actions', $MODULE_NAME)}</div>
			<table data-tablesaw-mode="stack" class="table table-bordered">
				<thead>
					<tr class="listViewHeaders">
						<th>{\App\Language::translate('nazwa', $MODULE_NAME)}</th>
						<th>{\App\Language::translate('katalog', $MODULE_NAME)}</th>
						<th>{\App\Language::translate('opis', $MODULE_NAME)}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$ACTIONS_LIST item=NAME}
						<tr>
							<td>{\App\Language::translate($NAME, $MODULE_NAME)}</td>
							<td>modules/OSSMailScanner/scanneractions/{$NAME}.php</td>
							<td>{\App\Language::translate('desc_'|cat:$NAME, $MODULE_NAME)}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<div class='editViewContainer tab-pane marginTop20' id="tab_email_search">
			<div class="card mb-2">
				<div class="card-header">
					<h3>{\App\Language::translate('Search email configuration', $MODULE_NAME)}</h3>
				</div>
				<div class="card-body">
					<div class="alert alert-info">
						<h4>{\App\Language::translate('Alert_info_tab_email_search', $MODULE_NAME)}</h4>
					</div>
					<form class="form-horizontal">
						<select multiple id="email_search" name="email_search" class="select2 form-control">
							{foreach item=item key=key from=$EMAILSEARCH}
								{if !isset($last_value) || $last_value neq $item['name']}
									<optgroup label="{\App\Language::translate($item['name'], $item['name'])}">
									{/if}
									<option value="{$item['key']}" {if in_array($item['key'], $EMAILSEARCHLIST) || in_array($item['value'], $EMAILSEARCHLIST)} selected="selected" {/if}>{\App\Language::translate($item['name'], $item['name'])}
										- {\App\Language::translate($item['fieldlabel'], $item['name'])}</option>
									{assign var=last_value value=$item['name']}
									{if $last_value neq $item['name']}
									</optgroup>
								{/if}
							{/foreach}
						</select>
					</form>
				</div>
			</div>
			<div class="card">
				<div class="card-header">
					<h3>{\App\Language::translate('LBL_TICKET_REOPEN', $MODULE_NAME)}</h3>
				</div>
				<div class="card-body">
					<div class="alert alert-info">
						<h4>{\App\Language::translate('LBL_CONFTAB_CHANGE_TICKET_STATUS', $MODULE_NAME)}</h4>
					</div>
					<form class="form-horizontal">
						<div class="form-group col-sm-12">
							<div class="radio">
								<label>
									<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus" value="noAction"
										{if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'noAction'}checked data-active="1" {/if}>
									<strong class="ml-1">{\App\Language::translate('LBL_NO_ACTION', $MODULE_NAME)}</strong>
								</label>
							</div>
							<div class="radio">
								<label>
									<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus"
										value="openTicket"
										{if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'openTicket'} checked="checked" data-active="1" {/if}>
									<strong class="ml-1">
										{\App\Language::translate('LBL_OPEN_TICKET', $MODULE_NAME)}:&nbsp;
										"{\App\Language::translate(\Config\Modules\OSSMailScanner::$helpdeskBindOpenStatus, 'HelpDesk')}"
									</strong>
									{if empty(\Config\Modules\OSSMailScanner::$helpdeskBindOpenStatus) }
										<strong class="color-red-a200">{\App\Language::translate('LBL_EMPTY_PARAMETER', $MODULE_NAME)}</strong>
									{/if}
								</label>
							</div>
							<div class="radio">
								<label>
									<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus"
										value="createTicket"
										{if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'createTicket'} checked="checked" data-active="1" {/if}>
									<strong class="ml-1">{\App\Language::translate('LBL_CREATE_TICKET', $MODULE_NAME)}</strong>
								</label>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class='editViewContainer tab-pane marginTop20' id="tab_record_numbering">
			<div class="alert alert-info mb-2">{\App\Language::translate('Alert_info_tab_record_numbering', $MODULE_NAME)}
				{if \App\Security\AdminAccess::isPermitted('RecordNumbering')}
					&nbsp;<a class="btn btn-info" role="button"
						href="index.php?module=RecordNumbering&parent=Settings&view=CustomRecordNumbering">{\App\Language::translate('ConfigCustomRecordNumbering',$MODULE_NAME)}</a>
				{/if}
			</div>
			<form id="EditView">
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>{\App\Language::translate('Module', $MODULE_NAME)}</th>
							<th>{\App\Language::translate('LBL_USE_PREFIX', 'Settings:Vtiger')}</th>
							<th>{\App\Language::translate('LBL_START_SEQUENCE', 'Settings:Vtiger')}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{foreach item=item key=key from=$RECORDNUMBERING}
							<tr {if $item->get('prefix') eq ''}class="error" {/if}
								style="{cycle values="'',background-color: #f9f9f9"}">
								<td>{\App\Language::translate($key, $key)}</td>
								<td>{$item->get('prefix')}</td>
								<td>{$item->get('cur_id')}</td>
								<td>{if $item->get('prefix') eq ''}{\App\Language::translate('Alert_scanner_not_work', $MODULE_NAME)} {/if}</td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</form>
		</div>
		<div class='editViewContainer tab-pane marginTop20' id="exceptions">
			{assign var=EXCEPTIONS value=$WIDGET_CFG['exceptions']}
			<div class="form-group">
				<label class="">
					{\App\Language::translate('LBL_EXCEPTIONS_CREATING_EMAIL', $MODULE_NAME)}
				</label>
				<div>
					<select multiple id="crating_mails" name="crating_mails" class="select2 form-control test"
						data-placeholder="{\App\Language::translate('LBL_WRITE_AND_ENTER',$MODULE_NAME)}">
						{if $EXCEPTIONS.crating_mails}
							{foreach item=item key=key from=explode(',',$EXCEPTIONS.crating_mails)}
								<option value="{$item}" selected class='testt'>{$item}</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="">
					{\App\Language::translate('LBL_EXCEPTIONS_CREATING_TICKET', $MODULE_NAME)}
				</label>
				<div>
					<select multiple id="crating_tickets" name="crating_tickets" class="select2 form-control"
						data-placeholder="{\App\Language::translate('LBL_WRITE_AND_ENTER',$MODULE_NAME)}">
						{if $EXCEPTIONS.crating_tickets}
							{foreach item=item key=key from=explode(',',$EXCEPTIONS.crating_tickets)}
								<option value="{$item}" selected>{$item}</option>
							{/foreach}
						{/if}
					</select>
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Settings-OSSMailScanner-Index -->
{/strip}
