{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
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
{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
<hr>
<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs">
    <li class="active"><a href="#tab_accounts" data-toggle="tab">{vtranslate('E-mail Accounts', 'OSSMailScanner')} </a></li>
    <li><a href="#tab_actions" data-toggle="tab">{vtranslate('Actions', 'OSSMailScanner')}</a></li>
    <li><a href="#tab_folder" data-toggle="tab">{vtranslate('Folder configuration', 'OSSMailScanner')}</a></li> 
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
        {if $ACCOUNTLIST eq false}
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
		    <th data-tablesaw-priority="5">{vtranslate('Status', 'OSSMailScanner')}</th>
                    <th data-tablesaw-priority="6">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
				{assign var=USERS_ENTITY_INFO value=Vtiger_Functions::getEntityModuleInfoFieldsFormatted('Users')}
                {foreach from=$ACCOUNTLIST item=row}
                    <tr id="row_account_{$row['user_id']}" style="{cycle values="'',background-color: #f9f9f9"}">
                        <td>{$row['username']}</td>
                        <td>{$row['mail_host']}</td>
                        <td class='functionList'>
                            <select class="form-control select2" multiple data-user-id="{$row['user_id']}" id="function_list_{$row['user_id']}" name="function_list_{$row['user_id']}">
                                <optgroup label="{vtranslate('Function_list', 'OSSMailScanner')}">
                                    {foreach item=item from=$EMAILACTIONSLISTNAME}
                                        <option value="{$item[1]}" {if $RECORD_MODEL->compare_vale($row['actions'],$item[1]) } selected="selected"{/if} >{vtranslate($item[0], 'OSSMailScanner')}</option>
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
                                        <option value="{$item['id']}" {if $RECORD_MODEL->compare_vale($row['crm_user_id'],$item['id']) } selected="selected"{/if} >{foreach from=$USERS_ENTITY_INFO['fieldname'] item=ENTITY}{$item[$ENTITY]} {/foreach}</option>
                                    {/foreach}
                                </optgroup>
                                <optgroup label="{vtranslate('Group list', 'OSSMailScanner')}">
                                    {foreach item=item from=$RECORD_MODEL->getGroupList()}
                                        <option value="{$item['id']}" {if $RECORD_MODEL->compare_vale($row['crm_user_id'],$item['id']) } selected="selected"{/if} >{$item['groupname']}</option>
                                    {/foreach}
                                </optgroup>
                            </select>
                        </td>
						<td>{vtranslate($row['status'], 'OSSMailScanner')}</td>
                        <td class='scanerMailActionsButtons'>
							<div class="btn-toolbar">
								<div class="btn-group">
									<button title="{vtranslate('show_identities', 'OSSMailScanner')}" type="button" data-user-id="{$row['user_id']}" class="btn btn-default expand-hide"><i class="glyphicon glyphicon-chevron-down"></i></button>
							<button title="{vtranslate('delate_accont', 'OSSMailScanner')}" type="button" data-user-id="{$row['user_id']}" class="btn btn-default delate_accont"><i class="glyphicon glyphicon-trash"></i></button>
								</div>
							</div>
						</td>
                    </tr>
                    <tr style="display: none;" data-user-id="{$row['user_id']}">
                        <td colspan="6">
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
                {foreach from=$EMAILACTIONSLIST key=name item=row}
                    {if $row[0] eq 'files'}
                        <tr>
                            <td>{vtranslate($row[1], 'OSSMailScanner')}</td>
                            <td>/{$row[1]}.php</td>
                            <td>{vtranslate('desc_'|cat:$row[1], 'OSSMailScanner')}</td>
                        </tr>
                    {elseif $row[0] eq 'dir'}
                        {foreach from=$row[2] key=name item=row_dir}
                            <tr>
                                <td>{vtranslate($row_dir[1], 'OSSMailScanner')}</td>
                                <td>/{$row[1]}/{$row_dir[1]}.php</td>
                                <td>{vtranslate('desc_'|cat:$row[1]|cat:'_'|cat:$row_dir[1], 'OSSMailScanner')}</td>
                            </tr>
                        {/foreach}
                    {/if}
                {/foreach}
            </tbody>
        </table>
    </div>
    <div class='editViewContainer tab-pane marginTop20' id="tab_folder">
        <div class="alert alert-info">{vtranslate('Alert_info_tab_folder', 'OSSMailScanner')}</div>
        {if $FOLDERMAILBOXES eq false}
            <div class="alert alert-block alert-warning fade in">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <h4 class="alert-heading">{vtranslate('OSSMail', 'OSSMail')} - {vtranslate('Alert_no_email_acconts', 'OSSMailScanner')}</h4>
                <p>{vtranslate('Alert_no_email_acconts_desc', 'OSSMailScanner')}</p>
                <p>
                    <a class="btn btn-default" href="index.php?module=OSSMail&view=index">{vtranslate('OSSMail','OSSMail')}</a>
                </p>
            </div>	
        {else}
            <form class="form-horizontal">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputReceived">{vtranslate('Received', 'OSSMailScanner')}</label>
                    <div class="col-sm-6 controls">
                        <select multiple id="folder_inputReceived" name="folder_inputReceived" class="select2 form-control">
                            <optgroup label="{vtranslate('Folder_list', 'OSSMailScanner')}">
                                {foreach item=item key=key from=$FOLDERMAILBOXES}
                                    <option value="{$key}" {if $RECORD_MODEL->compare_vale($CONFIGFOLDERLIST['Received'],$key) } selected="selected"{/if} >{vtranslate($item, $QUALIFIED_MODULE)}</option>
                                {/foreach}
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputSent">{vtranslate('Sent', 'OSSMailScanner')}</label>
                    <div class="controls col-sm-6">
                        <select multiple id="folder_inputSent" name="folder_inputSent" class="select2 form-control">
                            <optgroup label="{vtranslate('Folder_list', 'OSSMailScanner')}">
                                {foreach item=item key=key from=$FOLDERMAILBOXES}
                                    <option value="{$key}" {if $RECORD_MODEL->compare_vale($CONFIGFOLDERLIST['Sent'],$key) } selected="selected"{/if} >{$item}</option>
                                {/foreach}
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputSpam">{vtranslate('Spam', 'OSSMailScanner')}</label>
                    <div class="col-sm-6 controls">
                        <select multiple id="folder_inputSpam" name="folder_inputSpam" class="select2 form-control">
                            <optgroup label="{vtranslate('Folder_list', 'OSSMailScanner')}">
                                {foreach item=item key=key from=$FOLDERMAILBOXES}
                                    <option value="{$key}" {if $RECORD_MODEL->compare_vale($CONFIGFOLDERLIST['Spam'],$key) } selected="selected"{/if} >{$item}</option>
                                {/foreach}
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputTrash">{vtranslate('Trash', 'OSSMailScanner')}</label>
                    <div class="col-sm-6 controls">
                        <select multiple id="folder_inputTrash" name="folder_inputTrash" class="select2 form-control">
                            <optgroup label="{vtranslate('Folder_list', 'OSSMailScanner')}">
                                {foreach item=item key=key from=$FOLDERMAILBOXES}
                                    <option value="{$key}" {if $RECORD_MODEL->compare_vale($CONFIGFOLDERLIST['Trash'],$key) } selected="selected"{/if} >{$item}</option>
                                {/foreach}
                            </optgroup>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="inputAll">{vtranslate('All_folder', 'OSSMailScanner')}</label>
                    <div class="col-sm-6 controls">
                        <select multiple id="folder_inputAll" name="folder_inputAll" class="select2 form-control">
                            <optgroup label="{vtranslate('Folder_list', 'OSSMailScanner')}">
                                {foreach item=item key=key from=$FOLDERMAILBOXES}
                                    <option value="{$key}" {if $RECORD_MODEL->compare_vale($CONFIGFOLDERLIST['All'],$key) } selected="selected"{/if} >{$item}</option>
                                {/foreach}
                            </optgroup>
                        </select>
                    </div>
                </div>
            </form>
        {/if}
    </div>
    <div class='editViewContainer tab-pane marginTop20' id="tab_email_search">
		<h3>{vtranslate('Search email configuration', 'OSSMailScanner')}</h3>
        <div class="alert alert-info">{vtranslate('Alert_info_tab_email_search', 'OSSMailScanner')}</div>
        <form class="form-horizontal">
			<select multiple id="email_search" name="email_search" class="select2 form-control">
				{foreach item=item key=key from=$EMAILSEARCH}
					{if $last_value neq $item[3]}
						<optgroup label="{vtranslate($item[3], $item[3])}">
						{/if}
						<option value="{$item[1]}={$item[2]}={$item[4]}" {if $RECORD_MODEL->compare_vale($EMAILSEARCHLIST['fields'], $item[1]|cat:'='|cat:$item[2]|cat:'='|cat:$item[4] ) } selected="selected"{/if} > {vtranslate($item[3], $item[3])} - {vtranslate($item[0], $item[3])}</option>
						{assign var=last_value value=$item[3]}
						{if $last_value neq $item[3]}
						</optgroup>
					{/if}
				{/foreach}
			</select>
        </form>
		<h3>{vtranslate('Change ticket status', 'OSSMailScanner')}</h3>
        <div class="alert alert-info">{vtranslate('Alert_info_conftab_change_ticket_status', 'OSSMailScanner')}</div>	
        <form class="form-horizontal">
            <div class="form-group col-sm-12">
                <div class="controls">
                    <input class="pull-left" style="margin-right: 10px;" type="checkbox" name="conftab_change_ticket_status" id="conftab_change_ticket_status" {if $WIDGET_CFG['emailsearch']['change_ticket_status'] eq 'true'} checked {/if}>
					<label class="">{vtranslate('Change_ticket_status', 'OSSMailScanner')}</label>
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
