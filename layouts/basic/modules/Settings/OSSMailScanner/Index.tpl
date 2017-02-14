{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<div class="widget_header row">
	<div class="col-xs-12">
		{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	</div>
</div>
{if ($CHECKCRON[0]['status'] == 0 ) || !$CHECKCRON || ($CHECKCRON[1]['status'] == 0)}
	<div class="alert alert-block alert-warning fade in" style="margin-left: 10px;">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<h4 class="alert-heading">{vtranslate('OSSMailScanner', 'OSSMailScanner')} - {vtranslate('Alert_active_cron', 'OSSMailScanner')}</h4>
		<p>{vtranslate('Alert_active_cron_desc', 'OSSMailScanner')}</p>
		<p>
			<a class="btn btn-default" href="index.php?module=CronTasks&parent=Settings&view=List">{vtranslate('Scheduler','Settings:Vtiger')}</a>
		</p>
	</div>	
{/if}
{if ( $CHECKCRON[1]['frequency'] * 2) > $CHECKCRON[0]['frequency']}
	<div class="alert alert-block alert-warning fade in" style="margin-left: 10px;">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<h4 class="alert-heading">{vtranslate('OSSMailScanner', 'OSSMailScanner')} - {vtranslate('Alert_active_crontime', 'OSSMailScanner')}</h4>
		<p>{vtranslate('Alert_active_crontime_desc', 'OSSMailScanner')}</p>
		<p>
			<a class="btn btn-default" href="index.php?module=CronTasks&parent=Settings&view=List">{vtranslate('Scheduler','Settings:Vtiger')}</a>
		</p>
	</div>	
{/if}
<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
    <li class="active"><a href="#tab_accounts" data-toggle="tab">{vtranslate('E-mail Accounts', 'OSSMailScanner')} </a></li>
    <li><a href="#tab_actions" data-toggle="tab">{vtranslate('Actions', 'OSSMailScanner')}</a></li>
    <li><a href="#tab_email_search" data-toggle="tab">{vtranslate('General Configuration', 'OSSMailScanner')}</a></li>  
    <li><a href="#tab_record_numbering" data-toggle="tab">{vtranslate('Record Numbering', 'OSSMailScanner')}</a></li>
	<li><a href="#exceptions" data-toggle="tab">{vtranslate('LBL_EXCEPTIONS', 'OSSMailScanner')}</a></li>
</ul>
<div id="my-tab-content" class="tab-content marginTop20">
    <div class='editViewContainer tab-pane active' id="tab_accounts">
        <div class="alert alert-info">{vtranslate('Alert_info_tab_accounts', 'OSSMailScanner')}</div>
        {if $ERRORNOMODULE}
            <div class="alert alert-block alert-warning fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h4 class="alert-heading">{vtranslate('OSSMail', 'OSSMail')} - {vtranslate('Alert_no_module_title', 'OSSMailScanner')}</h4>
                <p>{vtranslate('Alert_no_module_desc', 'OSSMailScanner')}</p>
                <p>
                    <a class="btn btn-danger" href="index.php?module=ModuleManager&parent=Settings&view=List">{vtranslate('LBL_STUDIO','Settings:Vtiger')}</a>
                    <a class="btn btn-default" href="index.php?module=ModuleManager&parent=Settings&view=ModuleImport&mode=importUserModuleStep1">{vtranslate('LBL_IMPORT_MODULE_FROM_FILE','Settings:ModuleManager')}</a>
                </p>
            </div>	
        {/if}
        {if $ACCOUNTS_LIST eq false}
            <div class="alert alert-block alert-warning fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h4 class="alert-heading">{vtranslate('OSSMail', 'OSSMail')} - {vtranslate('Alert_no_accounts_title', 'OSSMailScanner')}</h4>
                <p>{vtranslate('Alert_no_accounts_desc', 'OSSMailScanner')}</p>
                <p><a class="btn btn-default" href="index.php?module=OSSMail&view=index">{vtranslate('OSSMail','OSSMail')}</a></p>
            </div>	
		{else}
			<table class="table tableRWD table-bordered">
				<thead>
					<tr class="listViewHeaders">
						<th data-tablesaw-priority="1">{vtranslate('username', 'OSSMailScanner')}</th>
						<th data-tablesaw-priority="2">{vtranslate('mail_host', 'OSSMailScanner')}</th>
						<th data-tablesaw-priority="3">{vtranslate('Actions', 'OSSMailScanner')}</th>
						<th data-tablesaw-priority="4">{vtranslate('User', 'OSSMailScanner')}</th>
						<th data-tablesaw-priority="5">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					{assign var=USERS_ENTITY_INFO value=\App\Module::getEntityInfo('Users')}
					{foreach from=$ACCOUNTS_LIST item=row}
						{assign var=FOLDERS value=$RECORD_MODEL->getFolders($row['crm_user_id'])}
						<tr id="row_account_{$row['user_id']}" style="{cycle values="'',background-color: #f9f9f9"}">
							<td>{$row['username']}</td>
							<td>{$row['mail_host']}</td>
							<td class='functionList'>
								<select class="form-control select2" multiple data-user-id="{$row['user_id']}" id="function_list_{$row['user_id']}" name="function_list_{$row['user_id']}">
									<optgroup label="{vtranslate('Function_list', 'OSSMailScanner')}">
										{foreach item=ACTION from=$ACTIONS_LIST}
											<option value="{$ACTION}" {if in_array($ACTION, $row['actions'])} selected="selected"{/if} >
												{vtranslate($ACTION, 'OSSMailScanner')}
											</option>
										{/foreach}
									</optgroup>
								</select>
							</td>
							<td>
								<select id="user_list_{$row['user_id']}" data-user="{$row['user_id']}" name="user_list_{$row['user_id']}" class="form-control select2">
									<optgroup label="{vtranslate('User list', 'OSSMailScanner')}">
										{if $row['crm_user_id'] eq '0'}
											<option value="0" id="user_list_none">{vtranslate('None', 'OSSMailScanner')}</option>
										{/if}
										{foreach item=item from=$RECORD_MODEL->getUserList()}
											<option value="{$item['id']}" {if $row['crm_user_id'] == $item['id']} selected="selected"{/if} >{foreach from=$USERS_ENTITY_INFO['fieldnameArr'] item=ENTITY}{$item[$ENTITY]} {/foreach}</option>
										{/foreach}
									</optgroup>
									<optgroup label="{vtranslate('Group list', 'OSSMailScanner')}">
										{foreach item=item from=$RECORD_MODEL->getGroupList()}
											<option value="{$item['id']}" {if $row['crm_user_id'] == $item['id'] } selected="selected"{/if} >{$item['groupname']}</option>
										{/foreach}
									</optgroup>
								</select>
							</td>
							<td class='scanerMailActionsButtons'>
								<div class="btn-toolbar">
									<div class="btn-group">
										<button title="{vtranslate('LBL_SHOW_ACCOUNT_DETAILS', 'OSSMailScanner')}" type="button" data-user-id="{$row['user_id']}" class="btn btn-default expand-hide">
											<span class="glyphicon glyphicon-chevron-down"></span>
										</button>
										<button title="{vtranslate('LBL_EDIT_FOLDER_ACCOUNT', 'OSSMailScanner')}" type="button" data-user="{$row['user_id']}" class="btn btn-default editFolders">
											<span class="glyphicon glyphicon-folder-open"></span>
										</button>
										<button title="{vtranslate('LBL_DELETE_ACCOUNT', 'OSSMailScanner')}" type="button" data-user-id="{$row['user_id']}" class="btn btn-default delate_accont">
											<span class="glyphicon glyphicon-trash"></span>
										</button>
									</div>
								</div>
								{if empty($FOLDERS)}
									<span class="label label-danger">{vtranslate('ERR_NO_CONFIGURATION_FOLDERS', 'OSSMailScanner')}</span>
								{/if}
							</td>
						</tr>
						<tr style="display: none;" data-user-id="{$row['user_id']}">
							<td colspan="6">
								<div>
									<h5>
										<strong {if empty($FOLDERS)}class="text-danger"{/if}>
											{vtranslate('Folder configuration', 'OSSMailScanner')}:
										</strong>
										{foreach item=FOLDER from=$FOLDERS}
											{$FOLDER['folder']} ({vtranslate($FOLDER['type'], 'OSSMailScanner')}),
										{foreachelse}
											{vtranslate('--None--', 'OSSMailScanner')}
										{/foreach}
									</h5>
								</div>
								<hr/>
								<div>
									<table class="table">
										<thead>
											<tr>
												<th style="color: black; background-color: #d3d3d3;">{vtranslate('identities_name', 'OSSMailScanner')}</th>
												<th style="color: black; background-color: #d3d3d3;">{vtranslate('identities_adress', 'OSSMailScanner')}</th>
												<th colspan="2" style="color: black; background-color: #d3d3d3;">{vtranslate('identities_del', 'OSSMailScanner')}</th>
											</tr>
										</thead>
										{foreach item=item from=$IDENTITYLIST[$row['user_id']]}
											<tr style="{cycle values="'',background-color: #f9f9f9"}">
												<td>{$item['name']}</td>
												<td>{$item['email']}</td>
												<td colspan="2" style="text-align: center;"><button data-id="{$item['identity_id']}" type="button" class="btn btn-danger identities_del">{vtranslate('identities_del', 'OSSMailScanner')}</button></td>
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
        <div class="alert alert-info">{vtranslate('Alert_info_tab_actions', 'OSSMailScanner')}</div>
        <table data-tablesaw-mode="stack" class="table table-bordered">
            <thead>
                <tr class="listViewHeaders">
                    <th>{vtranslate('nazwa', 'OSSMailScanner')}</th>
                    <th>{vtranslate('katalog', 'OSSMailScanner')}</th>
                    <th>{vtranslate('opis', 'OSSMailScanner')}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$ACTIONS_LIST item=NAME}
					<tr>
						<td>{vtranslate($NAME, 'OSSMailScanner')}</td>
						<td>modules/OSSMailScanner/scanneractions/{$NAME}.php</td>
						<td>{vtranslate('desc_'|cat:$NAME, 'OSSMailScanner')}</td>
					</tr>
                {/foreach}
            </tbody>
        </table>
    </div>
    <div class='editViewContainer tab-pane marginTop20' id="tab_email_search">
		<h3>{vtranslate('Search email configuration', 'OSSMailScanner')}</h3>
		<hr/>
        <div class="alert alert-info"><h4>{vtranslate('Alert_info_tab_email_search', 'OSSMailScanner')}</h4></div>
        <form class="form-horizontal">
			<select multiple id="email_search" name="email_search" class="select2 form-control">
				{foreach item=item key=key from=$EMAILSEARCH}
					{if $last_value neq $item['name']}
						<optgroup label="{vtranslate($item['name'], $item['name'])}">
						{/if}
						<option value="{$item['key']}" {if in_array($item['key'], $EMAILSEARCHLIST) } selected="selected"{/if}>{vtranslate($item['name'], $item['name'])} - {vtranslate($item['fieldlabel'], $item['name'])}</option>
						{assign var=last_value value=$item['name']}
						{if $last_value neq $item['name']}
						</optgroup>
					{/if}
				{/foreach}
			</select>
        </form>
		<h3>{vtranslate('LBL_TICKET_REOPEN', 'OSSMailScanner')}</h3>
		<hr/>
        <div class="alert alert-info"><h4>{vtranslate('LBL_CONFTAB_CHANGE_TICKET_STATUS', 'OSSMailScanner')}</h4>	</div>
		<form class="form-horizontal">
			<div class="form-group col-sm-12">
				<div class="radio">
					<label>
						<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus" value="noAction"  {if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'noAction'}checked data-active="1"{/if}>
						<strong>{vtranslate('LBL_NO_ACTION', 'OSSMailScanner')}</strong>
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus" value="openTicket" {if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'openTicket'}checked data-active="1"{/if}>
						<strong>{vtranslate('LBL_OPEN_TICKET', 'OSSMailScanner')}</strong>
					</label>
				</div>
				<div class="radio">
					<label>
						<input type="radio" name="conftabChangeTicketStatus" class="conftabChangeTicketStatus" value="createTicket" {if $WIDGET_CFG['emailsearch']['changeTicketStatus'] eq 'createTicket'}checked data-active="1"{/if}>
						<strong>{vtranslate('LBL_CREATE_TICKET', 'OSSMailScanner')}</strong>
					</label>
				</div>
			</div>
		</form>
    </div>
    <div class='editViewContainer tab-pane marginTop20' id="tab_record_numbering">
        <div class="alert alert-info">{vtranslate('Alert_info_tab_record_numbering', 'OSSMailScanner')} &nbsp; <a class="btn btn-info" href="index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering">{vtranslate('ConfigCustomRecordNumbering','OSSMailScanner')}</a></div>	
        <form id="EditView">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>{vtranslate('Module', 'OSSMailScanner')}</th>
                        <th>{vtranslate('LBL_USE_PREFIX', 'Settings:Vtiger')}</th>
                        <th>{vtranslate('LBL_START_SEQUENCE', 'Settings:Vtiger')}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {foreach item=item key=key from=$RECORDNUMBERING}
                        <tr {if $item['prefix'] eq ''}class="error"{/if} style="{cycle values="'',background-color: #f9f9f9"}">
                            <td>{vtranslate($key, $key)}</td>
                            <td>{$item['prefix']}</td>
                            <td>{$item['sequenceNumber']}</td>
                            <td>{if $item['prefix'] eq ''}{vtranslate('Alert_scanner_not_work', 'OSSMailScanner')} {/if}</td>
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
				{vtranslate('LBL_EXCEPTIONS_CREATING_EMAIL', 'OSSMailScanner')}
			</label>
			<div>
				<select multiple id="crating_mails" name="crating_mails" class="select2 form-control test" data-placeholder="{vtranslate('LBL_WRITE_AND_ENTER','OSSMailScanner')}">
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
				{vtranslate('LBL_EXCEPTIONS_CREATING_TICKET', 'OSSMailScanner')}
			</label>
			<div>
				<select multiple id="crating_tickets" name="crating_tickets" class="select2 form-control" data-placeholder="{vtranslate('LBL_WRITE_AND_ENTER','OSSMailScanner')}">
					{if $EXCEPTIONS.crating_tickets}
						{foreach item=item key=key from=explode(',',$EXCEPTIONS.crating_tickets)}
							<option value="{$item}" selected >{$item}</option>
						{/foreach}
					{/if}
				</select>
			</div>
		</div>
    </div>
</div>
