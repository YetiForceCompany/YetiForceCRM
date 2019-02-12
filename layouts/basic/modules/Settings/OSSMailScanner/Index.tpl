{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="widget_header row">
	<div class="col-12">
		{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
	</div>
</div>
{if ($CHECKCRON[0]['status'] == 0 ) || !$CHECKCRON || ($CHECKCRON[1]['status'] == 0)}
	<div class="alert alert-block alert-warning">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<h4 class="alert-heading">{\App\Language::translate('OSSMailScanner', 'OSSMailScanner')}
			- {\App\Language::translate('Alert_active_cron', 'OSSMailScanner')}</h4>
		<p>{\App\Language::translate('Alert_active_cron_desc', 'OSSMailScanner')}</p>
		<p>
			<a class="btn btn-light" role="button"
			   href="index.php?module=CronTasks&parent=Settings&view=List">{\App\Language::translate('Scheduler','Settings:Vtiger')}</a>
		</p>
	</div>
{/if}
{if ( $CHECKCRON[1]['frequency'] * 2) > $CHECKCRON[0]['frequency']}
	<div class="alert alert-block alert-warning">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<h4 class="alert-heading">{\App\Language::translate('OSSMailScanner', 'OSSMailScanner')}
			- {\App\Language::translate('Alert_active_crontime', 'OSSMailScanner')}</h4>
		<p>{\App\Language::translate('Alert_active_crontime_desc', 'OSSMailScanner')}</p>
		<p>
			<a class="btn btn-light" role="button"
			   href="index.php?module=CronTasks&parent=Settings&view=List">{\App\Language::translate('Scheduler','Settings:Vtiger')}</a>
		</p>
	</div>
{/if}
<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
	<li class="nav-item"><a class="nav-link active" href="#tab_accounts"
							data-toggle="tab">{\App\Language::translate('E-mail Accounts', 'OSSMailScanner')} </a></li>
	<li class="nav-item"><a class="nav-link" href="#tab_actions"
							data-toggle="tab">{\App\Language::translate('Actions', 'OSSMailScanner')}</a></li>
	<li class="nav-item"><a class="nav-link" href="#tab_email_search"
							data-toggle="tab">{\App\Language::translate('General Configuration', 'OSSMailScanner')}</a>
	</li>
	<li class="nav-item"><a class="nav-link" href="#tab_record_numbering"
							data-toggle="tab">{\App\Language::translate('Record Numbering', 'OSSMailScanner')}</a></li>
	<li class="nav-item"><a class="nav-link" href="#exceptions"
							data-toggle="tab">{\App\Language::translate('LBL_EXCEPTIONS', 'OSSMailScanner')}</a></li>
</ul>
<div id="my-tab-content" class="tab-content marginTop20">
	<div class='editViewContainer tab-pane active' id="tab_accounts">
		<div class="alert alert-info">{\App\Language::translate('Alert_info_tab_accounts', 'OSSMailScanner')}</div>
		{if $ERRORNOMODULE}
			<div class="alert alert-block alert-warning">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{\App\Language::translate('OSSMail', 'OSSMail')}
					- {\App\Language::translate('Alert_no_module_title', 'OSSMailScanner')}</h4>
				<p>{\App\Language::translate('Alert_no_module_desc', 'OSSMailScanner')}</p>
				<p>
					<a class="btn btn-danger" role="button"
					   href="index.php?module=ModuleManager&parent=Settings&view=List">{\App\Language::translate('LBL_STUDIO','Settings:Vtiger')}</a>
					<a class="btn btn-light" role="button"
					   href="index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1">{\App\Language::translate('LBL_IMPORT_MODULE_FROM_FILE','Settings:ModuleManager')}</a>
				</p>
			</div>
		{/if}
		{if $ACCOUNTS_LIST eq false}
			<div class="alert alert-block alert-warning">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{\App\Language::translate('OSSMail', 'OSSMail')}
					- {\App\Language::translate('Alert_no_accounts_title', 'OSSMailScanner')}</h4>
				<p>{\App\Language::translate('Alert_no_accounts_desc', 'OSSMailScanner')}</p>
				<p><a class="btn btn-light" role="button"
					  href="index.php?module=OSSMail&view=Index">{\App\Language::translate('OSSMail','OSSMail')}</a></p>
			</div>
		{else}
			<table class="table tableRWD table-bordered">
				<thead>
				<tr class="listViewHeaders">
					<th data-tablesaw-priority="1">{\App\Language::translate('username', 'OSSMailScanner')}</th>
					<th data-tablesaw-priority="2">{\App\Language::translate('mail_host', 'OSSMailScanner')}</th>
					<th data-tablesaw-priority="3">{\App\Language::translate('Actions', 'OSSMailScanner')}</th>
					<th data-tablesaw-priority="4">{\App\Language::translate('User', 'OSSMailScanner')}</th>
					<th data-tablesaw-priority="5">&nbsp;</th>
				</tr>
				</thead>
				<tbody>
				{assign var=USERS_ENTITY_INFO value=\App\Module::getEntityInfo('Users')}
				{foreach from=$ACCOUNTS_LIST item=row}
					{assign var=FOLDERS value=$RECORD_MODEL->getFolders($row['user_id'])}
					<tr id="row_account_{$row['user_id']}" style="{cycle values="'',background-color: #f9f9f9"}">
						<td>{$row['username']}</td>
						<td>{$row['mail_host']}</td>
						<td class='functionList'>
							<select class="form-control select2" multiple data-user-id="{$row['user_id']}"
									id="function_list_{$row['user_id']}" name="function_list_{$row['user_id']}">
								<optgroup label="{\App\Language::translate('Function_list', 'OSSMailScanner')}">
									{foreach item=ACTION from=$ACTIONS_LIST}
										<option value="{\App\Purifier::encodeHtml($ACTION)}" {if in_array($ACTION, $row['actions'])} selected="selected"{/if} >
											{\App\Language::translate($ACTION, 'OSSMailScanner')}
										</option>
									{/foreach}
								</optgroup>
							</select>
						</td>
						<td>
							<select id="user_list_{$row['user_id']}" data-user="{$row['user_id']}"
									name="user_list_{$row['user_id']}" class="form-control select2">
								<optgroup label="{\App\Language::translate('User list', 'OSSMailScanner')}">
									{if $row['crm_user_id'] eq '0'}
										<option value="0"
												id="user_list_none">{\App\Language::translate('None', 'OSSMailScanner')}</option>
									{/if}
									{foreach item=item from=$RECORD_MODEL->getUserList()}
										<option value="{$item['id']}" {if $row['crm_user_id'] == $item['id']} selected="selected"{/if} >{foreach from=$USERS_ENTITY_INFO['fieldnameArr'] item=ENTITY}{$item[$ENTITY]} {/foreach}</option>
									{/foreach}
								</optgroup>
								<optgroup label="{\App\Language::translate('Group list', 'OSSMailScanner')}">
									{foreach item=item from=$RECORD_MODEL->getGroupList()}
										<option value="{$item['groupid']}" {if $row['crm_user_id'] == $item['groupid'] } selected="selected"{/if} >{$item['groupname']}</option>
									{/foreach}
								</optgroup>
							</select>
						</td>
						<td class='scanerMailActionsButtons'>
							<div class="btn-toolbar">
								<div class="btn-group">
									<button title="{\App\Language::translate('LBL_SHOW_ACCOUNT_DETAILS', 'OSSMailScanner')}"
											type="button" data-user-id="{$row['user_id']}"
											class="btn btn-light expand-hide">
										<span class="fas fa-chevron-down"></span>
									</button>
									<button title="{\App\Language::translate('LBL_EDIT_FOLDER_ACCOUNT', 'OSSMailScanner')}"
											type="button" data-user="{$row['user_id']}"
											class="btn btn-light editFolders">
										<span class="fas fa-folder-open"></span>
									</button>
									<button title="{\App\Language::translate('LBL_DELETE_ACCOUNT', 'OSSMailScanner')}"
											type="button" data-user-id="{$row['user_id']}"
											class="btn btn-light delate_accont">
										<span class="fas fa-trash-alt"></span>
									</button>
								</div>
							</div>
							<span class="js-empty-folders-alert badge badge-danger{if !empty($FOLDERS)} d-none{/if}">{\App\Language::translate('ERR_NO_CONFIGURATION_FOLDERS', 'OSSMailScanner')}</span>
						</td>
					</tr>
					<tr style="display: none;" data-user-id="{$row['user_id']}">
						<td colspan="6">
							<div>
								<h5>
									<strong {if empty($FOLDERS)}class="text-danger"{/if}>
										{\App\Language::translate('Folder configuration', 'OSSMailScanner')}:
									</strong>
									{foreach item=FOLDER from=$FOLDERS}
										{$FOLDER['folder']} ({\App\Language::translate($FOLDER['type'], 'OSSMailScanner')}),
										{foreachelse}
										{\App\Language::translate('--None--', 'OSSMailScanner')}
									{/foreach}
								</h5>
							</div>
							<hr/>
							<div>
								<table class="table">
									<thead>
									<tr>
										<th style="color: black; background-color: #d3d3d3;">{\App\Language::translate('identities_name', 'OSSMailScanner')}</th>
										<th style="color: black; background-color: #d3d3d3;">{\App\Language::translate('identities_adress', 'OSSMailScanner')}</th>
										<th colspan="2"
											style="color: black; background-color: #d3d3d3;">{\App\Language::translate('identities_del', 'OSSMailScanner')}</th>
									</tr>
									</thead>
									{foreach item=item from=$IDENTITYLIST[$row['user_id']]}
										<tr style="{cycle values="'',background-color: #f9f9f9"}">
											<td>{$item['name']}</td>
											<td>{$item['email']}</td>
											<td colspan="2" style="text-align: center;">
												<button data-id="{$item['identity_id']}" type="button"
														class="btn btn-danger identities_del">{\App\Language::translate('identities_del', 'OSSMailScanner')}</button>
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
		<div class="alert alert-info">{\App\Language::translate('Alert_info_tab_actions', 'OSSMailScanner')}</div>
		<table data-tablesaw-mode="stack" class="table table-bordered">
			<thead>
			<tr class="listViewHeaders">
				<th>{\App\Language::translate('nazwa', 'OSSMailScanner')}</th>
				<th>{\App\Language::translate('katalog', 'OSSMailScanner')}</th>
				<th>{\App\Language::translate('opis', 'OSSMailScanner')}</th>
			</tr>
			</thead>
			<tbody>
			{foreach from=$ACTIONS_LIST item=NAME}
				<tr>
					<td>{\App\Language::translate($NAME, 'OSSMailScanner')}</td>
					<td>modules/OSSMailScanner/scanneractions/{$NAME}.php</td>
					<td>{\App\Language::translate('desc_'|cat:$NAME, 'OSSMailScanner')}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
	<div class='editViewContainer tab-pane marginTop20' id="tab_email_search">
		<h3>{\App\Language::translate('Search email configuration', 'OSSMailScanner')}</h3>
		<hr/>
		<div class="alert alert-info">
			<h4>{\App\Language::translate('Alert_info_tab_email_search', 'OSSMailScanner')}</h4></div>
		<form class="form-horizontal">
			<select multiple id="email_search" name="email_search" class="select2 form-control">
				{foreach item=item key=key from=$EMAILSEARCH}
					{if !isset($last_value) || $last_value neq $item['name']}
						<optgroup label="{\App\Language::translate($item['name'], $item['name'])}">
					{/if}
					<option value="{$item['key']}" {if in_array($item['key'], $EMAILSEARCHLIST) } selected="selected"{/if}>{\App\Language::translate($item['name'], $item['name'])}
						- {\App\Language::translate($item['fieldlabel'], $item['name'])}</option>
					{assign var=last_value value=$item['name']}
					{if $last_value neq $item['name']}
						</optgroup>
					{/if}
				{/foreach}
			</select>
		</form>
		<h3>{\App\Language::translate('LBL_TICKET_REOPEN', 'OSSMailScanner')}</h3>
		<hr/>
		<div class="alert alert-info">
			<h4>{\App\Language::translate('LBL_CONFTAB_CHANGE_TICKET_STATUS', 'OSSMailScanner')}</h4></div>
		<form class="form-horizontal">
			<div class="form-group col-sm-12">
				<div class="radio">
					<label>
						<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus"
							   value="noAction"
							   {if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'noAction'}checked
							   data-active="1"{/if}>
						<strong>{\App\Language::translate('LBL_NO_ACTION', 'OSSMailScanner')}</strong>
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus"
							   value="openTicket"
							   {if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'openTicket'}checked
							   data-active="1"{/if}>
						<strong>{\App\Language::translate('LBL_OPEN_TICKET', 'OSSMailScanner')}</strong>
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus"
							   value="createTicket"
							   {if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'createTicket'}checked
							   data-active="1"{/if}>
						<strong>{\App\Language::translate('LBL_CREATE_TICKET', 'OSSMailScanner')}</strong>
					</label>
				</div>
			</div>
		</form>
	</div>
	<div class='editViewContainer tab-pane marginTop20' id="tab_record_numbering">
		<div class="alert alert-info">{\App\Language::translate('Alert_info_tab_record_numbering', 'OSSMailScanner')}
			&nbsp; <a class="btn btn-info" role="button"
					  href="index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering">{\App\Language::translate('ConfigCustomRecordNumbering','OSSMailScanner')}</a>
		</div>
		<form id="EditView">
			<table class="table table-bordered">
				<thead>
				<tr>
					<th>{\App\Language::translate('Module', 'OSSMailScanner')}</th>
					<th>{\App\Language::translate('LBL_USE_PREFIX', 'Settings:Vtiger')}</th>
					<th>{\App\Language::translate('LBL_START_SEQUENCE', 'Settings:Vtiger')}</th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				{foreach item=item key=key from=$RECORDNUMBERING}
					<tr {if $item->get('prefix') eq ''}class="error"{/if}
						style="{cycle values="'',background-color: #f9f9f9"}">
						<td>{\App\Language::translate($key, $key)}</td>
						<td>{$item->get('prefix')}</td>
						<td>{$item->get('cur_id')}</td>
						<td>{if $item->get('prefix') eq ''}{\App\Language::translate('Alert_scanner_not_work', 'OSSMailScanner')} {/if}</td>
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
				{\App\Language::translate('LBL_EXCEPTIONS_CREATING_EMAIL', 'OSSMailScanner')}
			</label>
			<div>
				<select multiple id="crating_mails" name="crating_mails" class="select2 form-control test"
						data-placeholder="{\App\Language::translate('LBL_WRITE_AND_ENTER','OSSMailScanner')}">
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
				{\App\Language::translate('LBL_EXCEPTIONS_CREATING_TICKET', 'OSSMailScanner')}
			</label>
			<div>
				<select multiple id="crating_tickets" name="crating_tickets" class="select2 form-control"
						data-placeholder="{\App\Language::translate('LBL_WRITE_AND_ENTER','OSSMailScanner')}">
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
