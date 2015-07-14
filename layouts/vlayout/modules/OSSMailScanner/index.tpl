{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-tab.js"></script>
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
<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs" style="margin: 20px;">
    <li class="active"><a href="#tab_accounts" data-toggle="tab">{vtranslate('E-mail Accounts', 'OSSMailScanner')} </a></li>
    <li><a href="#tab_actions" data-toggle="tab">{vtranslate('Actions', 'OSSMailScanner')}</a></li>
    <li><a href="#tab_folder" data-toggle="tab">{vtranslate('Folder configuration', 'OSSMailScanner')}</a></li> 
    <li><a href="#tab_email_search " data-toggle="tab">{vtranslate('General Configuration', 'OSSMailScanner')}</a></li>  
    <li><a href="#tab_record_numbering " data-toggle="tab">{vtranslate('Record Numbering', 'OSSMailScanner')}</a></li>
</ul>
<div id="my-tab-content" class="tab-content" style="margin: 0 20px;" >
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
		<div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr class="listViewHeaders">
                    <th>{vtranslate('username', 'OSSMailScanner')}</th>
                    <th>{vtranslate('mail_host', 'OSSMailScanner')}</th>
                    <th>{vtranslate('Actions', 'OSSMailScanner')}</th>
					<th>{vtranslate('User', 'OSSMailScanner')}</th>
					<th>{vtranslate('Status', 'OSSMailScanner')}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$ACCOUNTLIST item=row}
                    <tr id="row_account_{$row['user_id']}" style="{cycle values="'',background-color: #f9f9f9"}">
                        <td>{$row['username']}</td>
                        <td>{$row['mail_host']}</td>
                        <td>
                            <select class="form-control select2" multiple id="function_list_{$row['user_id']}" name="function_list_{$row['user_id']}">
                                <optgroup label="{vtranslate('Function_list', 'OSSMailScanner')}">
                                    {foreach item=item from=$EMAILACTIONSLISTNAME}
                                        <option value="{$item[1]}" {if $RECORD_MODEL->compare_vale($row['actions'],$item[1]) } selected="selected"{/if} >{vtranslate($item[0], 'OSSMailScanner')}</option>
                                    {/foreach}
                                </optgroup>
                            </select>
                        </td>
                        <td>
                            <select id="user_list_{$row['user_id']}" name="user_list_{$row['user_id']}" class="form-control select2">
                                <optgroup label="{vtranslate('User list', 'OSSMailScanner')}">
									{if $row['crm_user_id'] eq '0'}
										<option value="0" id="user_list_none">{vtranslate('None', 'OSSMailScanner')}</option>
									{/if}
                                    {foreach item=item from=$RECORD_MODEL->getUserList()}
                                        <option value="{$item['id']}" {if $RECORD_MODEL->compare_vale($row['crm_user_id'],$item['id']) } selected="selected"{/if} >{$item['first_name']} {$item['last_name']}</option>
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
                        <td><button title="{vtranslate('show_identities', 'OSSMailScanner')}" type="button" data-user-id="{$row['user_id']}" class="btn btn-default expand-hide"><i class="glyphicon glyphicon-chevron-down"></i></button>
							<button title="{vtranslate('delate_accont', 'OSSMailScanner')}" type="button" data-user-id="{$row['user_id']}" class="btn btn-default delate_accont"><i class="glyphicon glyphicon-trash"></i></button></td>
                    </tr>
                    <tr style="display: none;" data-user-id="{$row['user_id']}">
                        <td colspan="5">
                            <table class="table">
                                <tr>
                                    <th style="color: black; background-color: #d3d3d3;">{vtranslate('identities_name', 'OSSMailScanner')}</th>
                                    <th style="color: black; background-color: #d3d3d3;">{vtranslate('identities_adress', 'OSSMailScanner')}</th>
                                    <th colspan="2" style="color: black; background-color: #d3d3d3;">{vtranslate('identities_del', 'OSSMailScanner')}</th>
                                </tr>
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
		</div>
		{/if}
    </div>
    <div class='editViewContainer tab-pane' id="tab_actions">
        <div class="alert alert-info">{vtranslate('Alert_info_tab_actions', 'OSSMailScanner')}</div>
        <table class="table table-bordered">
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
    <div class='editViewContainer tab-pane' id="tab_folder">
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
                                    <option value="{$key}" {if $RECORD_MODEL->compare_vale($CONFIGFOLDERLIST['Received'],$key) } selected="selected"{/if} >{$item}</option>
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
    <div class='editViewContainer tab-pane' id="tab_email_search">
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
    <div class='editViewContainer tab-pane' id="tab_record_numbering">
        <div class="alert alert-info">{vtranslate('Alert_info_tab_record_numbering', 'OSSMailScanner')} <a class="btn" href="index.php?module=Vtiger&parent=Settings&view=CustomRecordNumbering">{vtranslate('ConfigCustomRecordNumbering','OSSMailScanner')}</a></div>	
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
</div>
{literal}
<script>
    jQuery(function() {
		$('#status').change(function() {
			$('#confirm').attr('disabled', !this.checked);
		});
		jQuery('#conftab_change_ticket_status').on('click', function(){
			var ajaxParams = {};
			ajaxParams.data = { module: 'OSSMailScanner', action: "SaveRcConfig", ct: "emailsearch", type: "change_ticket_status", vale: $("#conftab_change_ticket_status").prop('checked') },
			ajaxParams.async = true;
			AppConnector.request(ajaxParams).then(
				function(data) {
					if(data.success){
						var params = {
								text: data.result.data,
								type: 'info',
								animation: 'show'
						}
						Vtiger_Helper_Js.showPnotify(params);
					}
				},
				function(data, err) {
				}
			);  
		})
        jQuery('.delate_accont').on('click', function(){
            var button = this;
            if(window.confirm(app.vtranslate('whether_remove_an_identity'))){
                var ajaxParams = {};
				var userid = jQuery(this).data('user-id');
                ajaxParams.data = { module: 'OSSMailScanner', action: "AccontRemove", id: userid },
                ajaxParams.async = true;
                AppConnector.request(ajaxParams).then(
                    function(data) {
					console.log(data);
                        var params = {
								text: data.result.data,
                                type: 'info',
                                animation: 'show'
                            };
                        Vtiger_Helper_Js.showPnotify(params);
						jQuery('#row_account_'+userid).hide();
                    },
                    function(data, err) {

                    }
                );
            }
        });
        jQuery('.identities_del').on('click', function(){
            var button = this;
            if(window.confirm(app.vtranslate('whether_remove_an_identity'))){
                var ajaxParams = {};
                ajaxParams.data = { module: 'OSSMailScanner', action: "IdentitiesDel", id: jQuery(this).data('id') },
                ajaxParams.async = true;
                        
                AppConnector.request(ajaxParams).then(
                    function(data) {
                        var params = {
                                text: app.vtranslate('removed_identity'),
                                type: 'info',
                                animation: 'show'
                            };
                            
                        Vtiger_Helper_Js.showPnotify(params);
                        jQuery(button).parent().parent().remove();
                    },
                    function(data, err) {

                    }
                );
            }
        });
        
        jQuery('.expand-hide').on('click', function(){
            var userId = jQuery(this).data('user-id');
            var tr = jQuery('tr[data-user-id="' + userId + '"]');
            
            if('none' == tr.css('display')){
                tr.show();
            } else {
                tr.hide();
            }
            
        });
        
        $(".alert").alert();
        {/literal}{foreach from=$ACCOUNTLIST item=row}{literal}
        jQuery("#function_list_{/literal}{$row['user_id']}{literal}").change(function() {
            SaveActions('{/literal}{$row['user_id']}{literal}', jQuery('#function_list_{/literal}{$row['user_id']}{literal}').val());
        });
        jQuery("#user_list_{/literal}{$row['user_id']}{literal}").change(function() {
            SaveCRMuser('{/literal}{$row['user_id']}{literal}', jQuery('#user_list_{/literal}{$row['user_id']}{literal}').val());
        });
        {/literal}{/foreach}{literal}
        jQuery("#folder_inputReceived").change(function() {
            saveFolderList('Received', jQuery('#folder_inputReceived').val());
        });
        jQuery("#folder_inputSent").change(function() {
            saveFolderList('Sent', jQuery('#folder_inputSent').val());
        });
        jQuery("#folder_inputAll").change(function() {
            saveFolderList('All', jQuery('#folder_inputAll').val());
        });
        jQuery("#folder_inputSpam").change(function() {
            saveFolderList('Spam', jQuery('#folder_inputSpam').val());
        });
        jQuery("#folder_inputTrash").change(function() {
            saveFolderList('Trash', jQuery('#folder_inputTrash').val());
        });
        jQuery("#email_search").change(function() {
            saveEmailSearchList(jQuery('#email_search').val());
        });
        jQuery('#tab_email_view_widget_limit').on('blur', function() {
            saveWidgetConfig('widget_limit', jQuery(this).val(), 'email_list');
        });
        jQuery('#tab_email_view_open_window').on('change', function() {
            saveWidgetConfig('target', jQuery(this).val(), 'email_list');
        });


                
        jQuery('[name="email_to_notify"]').on('blur', function() {
            var value = jQuery(this).val();
            if (!!email_validate(value)) {
                saveWidgetConfig('email', value, 'cron');
            }
            else {
                var params = {
                    text: app.vtranslate('JS_mail_error'),
                    type: 'error',
                    animation: 'show'
                };
                                        
                Vtiger_Helper_Js.showPnotify(params);
            }
        });
        jQuery('[name="time_to_notify"]').on('blur', function() {
            var value = jQuery(this).val();
            if (!!number_validate(value)) {
                saveWidgetConfig('time', jQuery(this).val(), 'cron');
            } else {
                var params = {
                    text: app.vtranslate('JS_time_error'),
                    type: 'error',
                    animation: 'show'
                };
                                        
                Vtiger_Helper_Js.showPnotify(params);
            }
        });				
    });
    function SaveActions(userid, vale) {
        var params = {
            'module': 'OSSMailScanner',
            'action': "SaveActions",
            'userid': userid,
            'vale': vale
        }
        AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if (response['success']) {
                        var params = {
                            text: response['data'],
                            type: 'info',
                            animation: 'show'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    } else {
                        var params = {
                            text: response['data'],
                            animation: 'show'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(data, err) {

                }
        );
    }
    function SaveCRMuser(userid, vale) {
        var params = {
            'module': 'OSSMailScanner',
            'action': "SaveCRMuser",
            'userid': userid,
            'vale': vale
        }
        AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if (response['success']) {
                        var params = {
                            text: response['data'],
                            type: 'info',
                            animation: 'show'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    } else {
                        var params = {
                            text: response['data'],
                            animation: 'show'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(data, err) {

                }
        );
    }
	
    function isEmpty(val){
        if (!!val) {
            return val;
        }
        
        return '';
    }
    
    function saveFolderList(type, vale) {
        var params = {
            'module': 'OSSMailScanner',
            'action': "saveFolderList",
            'type': type,
            'vale': vale
        }
        AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if (response['success']) {
                        var params = {
                            text: response['data'],
                            type: 'info',
                            animation: 'show'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    } else {
                        var params = {
                            text: response['data'],
                            animation: 'show'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(data, err) {

                }
        );
    }
    function saveEmailSearchList(vale) {
        var params = {
            'module': 'OSSMailScanner',
            'action': "saveEmailSearchList",
            'vale': vale
        }
        AppConnector.request(params).then(
                function(data) {
                    var response = data['result'];
                    if (response['success']) {
                        var params = {
                            text: response['data'],
                            type: 'info',
                            animation: 'show'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    } else {
                        var params = {
                            text: response['data'],
                            animation: 'show'
                        };
                        Vtiger_Helper_Js.showPnotify(params);
                    }
                },
                function(data, err) {

                }
        );
    }
    
    function email_validate(src){
      var regex = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,63}$/;
      return regex.test(src);
    }
    
    function number_validate(value){
      var valid = !/^\s*$/.test(value) && !isNaN(value);
      console.log(valid);
        return valid;
    }

    function saveWidgetConfig(name, value, type) {
        var params = {
            'module': 'OSSMailScanner',
            'action': "SaveWidgetConfig",
            'conf_type': type,
            'name': name,
            'value': value
        }
        AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				if (response['success']) {
					var params = {
						text: response['data'],
						type: 'info',
						animation: 'show'
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					var params = {
						text: response['data'],
						animation: 'show'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			},
			function(data, err) {

			}
        );
    }
	
</script>
{/literal}
