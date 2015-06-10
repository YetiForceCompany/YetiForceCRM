{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}	
	<style>
		#connection-status{
			width: 15px;
			height: 15px;
		}
		.go-to-pages-title,
		.lbl-of,
		#totalPageCount{
			margin: 0 5px;
			line-height: 25px;
		}
		#pageToJump{
			width: 25px;

		}
		#goToPage{
			margin-right: 5px;
		}
	</style>
	<input type='hidden' value="{$OFFSET}" class='offset'>
	<input type='hidden' value="{$PAGE}" class='current-page'>
	<input type='hidden' value="{$FTP_CONNECTION_STATUS}" class='ftp-connection-status'>
	<input type='hidden' value="{$PREV_PAGE}" class='prev-page'>
	<input type='hidden' value="{$NEXT_PAGE}" class='next-page'>
	<input type='hidden' value="{$BACKUP_INFO['id']}" class='backupID'>
	<div class="container-fluid" style="margin-top:10px;">
		<h3>{vtranslate('Backup', $QUALIFIED_MODULE_NAME)}</h3>&nbsp;{vtranslate('LBL_BACKUP_DESCRIPTION', $QUALIFIED_MODULE_NAME)}<hr>
		{if !extension_loaded('zip')}
			<div class="alert alert-block alert-error fade in" style="margin-left: 10px;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{vtranslate('LBL_ALERT_NO_ZIP_EXTENSION_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{vtranslate('LBL_ALERT_NO_ZIP_EXTENSION_DESC', $QUALIFIED_MODULE)}</p>
			</div>	
		{/if}
		{if !$BACKUP_MODEL->checkCron()}
			<div class="alert alert-block alert-error fade in" style="margin-left: 10px;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{vtranslate('LBL_ALERT_CRON_NOT_ACTIVE_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{vtranslate('LBL_ALERT_CRON_NOT_ACTIVE_DESC', $QUALIFIED_MODULE)}</p>
			</div>	
		{/if}
		<ul id="tabs" class="nav nav-tabs layoutTabs massEditTabs" data-tabs="tabs">
			<li class="active"><a href="#tab_0" data-toggle="tab">{vtranslate('LBL_BACKUP_CREATING', $QUALIFIED_MODULE_NAME)}</a></li>
			<li><a href="#tab_1" data-toggle="tab">{vtranslate('LBL_LOGS', $QUALIFIED_MODULE_NAME)}</a></li>
			<li><a href="#tab_2" data-toggle="tab">{vtranslate('LBL_FTP_SETTINGS', $QUALIFIED_MODULE_NAME)}</a></li>
			<li><a href="#tab_3" data-toggle="tab">{vtranslate('LBL_EMAIL_NOTIFICATIONS', $QUALIFIED_MODULE_NAME)}</a></li>
			<li><a href="#tab_4" data-toggle="tab">{vtranslate('LBL_GENERAL_SETTINGS', $QUALIFIED_MODULE_NAME)}</a></li>
		</ul>
		<div id="my-tab-content" class="tab-content layoutContent" style="padding-top: 5px;">
			<div class='tab-pane active' id="tab_0">
				<br/>
				{if $BACKUP_INFO}
					<div class="row-fluid row-bar mainBar">
						<div class="span4">Postęp czałkowity 
							<span class="pull-right"><span class="precent">0.00</span>%</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-danger active">
								<div class="bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					<hr /><br/>
					{for $NUM=1 to 9}
						<div class="row-fluid row-bar b{$NUM}">
							<div class="span4">{vtranslate('LBL_STAGE_'|cat:$NUM, $QUALIFIED_MODULE_NAME)}
								<span class="pull-right"><span class="precent">0.00</span> %</span>
							</div>
							<div class="span8">
								<div class="progress progress-striped progress-info active">
									<div class="bar " style="width: 0%;"> </div>
								</div>
							</div>
						</div>
					{/for}
				{else}
					<div class="textAlignCenter">
						<button class="runBackup btn btn-success" style="margin-top: 10px;">{vtranslate('LBL_SCHEDULE_BACKUP', $QUALIFIED_MODULE_NAME)}</button>
					</div>
				{/if}
			</div>
			<div class='tab-pane' id="tab_1">
				<div>
					<div class="btn-group pull-right">
						<button class="btn pull-left" id="listViewPreviousPageButton" ><span class="icon-chevron-left"></span></button>
						<button class="btn pull-left" id="listViewNextPageButton" {if ($NEXT_PAGE eq false) or (ALL_PAGES eq 1)} disabled {/if}><span class="icon-chevron-right"></span></button>
					</div>
				</div>
				<br /><br />
				<table class="table table-bordered table-condensed themeTableColor brute_force_form">
					<thead>
						<tr class="blockHeader">
							<th class="mediumWidthType">
								<span class="alignMiddle">{vtranslate('LBL_START_TIME', $QUALIFIED_MODULE_NAME)}</span>
							</th>
							<th class="mediumWidthType">
								<span class="alignMiddle">{vtranslate('LBL_END_TIME', $QUALIFIED_MODULE_NAME)}</span>
							</th>
							<th class="mediumWidthType">
								<span class="alignMiddle">{vtranslate('LBL_FILE_NAME', $QUALIFIED_MODULE_NAME)}</span>
							</th>
							<th class="mediumWidthType">
								<span class="alignMiddle">{vtranslate('LBL_STATUS', $QUALIFIED_MODULE_NAME)}</span>
							</th>
							<th class="mediumWidthType">
								<span class="alignMiddle">{vtranslate('LBL_BACKUP_TIME', $QUALIFIED_MODULE_NAME)}</span>
							</th>
						</tr>
					</thead>
					<tbody class='backup-list'>
						{foreach item=backup from=$BACKUPS name=backup}
							<tr data-id="{$backup['id']}">
								<td><label class="marginRight5px" >{$backup['starttime']}</label></td>
								<td><label class="marginRight5px" >{$backup['endtime']}</label></td>
								<td><label class="marginRight5px" >{$backup['filename']}</label></td>
								<td><label class="marginRight5px" >{$backup['status']}</label></td>
								<td><label class="marginRight5px" >{$backup['backuptime']}</label></td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			<div class="tab-pane" id="tab_2">
				<form>
					<table class="table table-bordered">
						<thead>
							<tr class="blockHeader">
								<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_FTP_SETTINGS', $QUALIFIED_MODULE_NAME)}</strong></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="width:25%">
									<span class="redColor pull-right">*</span>
									<label class="pull-right">{vtranslate('LBL_HOST', $QUALIFIED_MODULE_NAME)}</label></td>
								<td>
									<input type="text" value="{$FTP_HOST}" name="host"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<span class="redColor pull-right">*</span>
									<label class="pull-right">{vtranslate('LBL_LOGIN', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<input type="text" value="{$FTP_LOGIN}" name="login"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<span class="redColor pull-right">*</span>
									<label class="pull-right">{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<input type="password" value="{$FTP_PASSWORD}" name="password"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<label class="pull-right">{vtranslate('LBL_PORT', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<input type="text" value="{$FTP_PORT}" name="port"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<span class="add-on pull-right"><i class="icon-info-sign popoverTooltip" data-content="{vtranslate('LBL_PATH_INFO', $QUALIFIED_MODULE)}"></i></span>
									<label class="pull-right">{vtranslate('LBL_PATH', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<input type="text" value="{$FTP_PATH}" name="path"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%"><label class="pull-right">{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE_NAME)}</label></td>
								<td>
									<input type="checkbox" name="active" {if $FTP_ACTIVE} checked {/if}></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%"><label class="pull-right">{vtranslate('LBL_CONNECTION_STATUS', $QUALIFIED_MODULE_NAME)}</label></td>
								<td>
									<div id="connection-status" style="{if $FTP_CONNECTION_STATUS eq 0} background-color:red; {else}background-color:#5bb75b; {/if}" ></div>
								</td>
							</tr>
						</tbody>
					</table>
					<input class="btn btn-success pull-right" id="saveFtpConfig" value="{vtranslate('LBL_SAVE', $QUALIFIED_MODULE_NAME)}">
				</form>
			</div>
			<div class="tab-pane" id="tab_3">
				<form>
					<table class="table table-bordered">
						<thead>
							<tr class="blockHeader">
								<th colspan="2" class="{$WIDTHTYPE}"><strong>{vtranslate('LBL_USERS_FOR_NOTIFICATIONS', $QUALIFIED_MODULE_NAME)}</strong></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="width:25%;"><label class="pull-right" style="margin-top:5px;">{vtranslate('LBL_USERS', $QUALIFIED_MODULE_NAME)}</label></td>
								<td>
									<select class="chzn-select configField" data-type="notifications" id="" name="users" multiple>
										{foreach key=KEY  item=USER from=$ADMIN_USERS}
											<option name="selectedUsers" value="{$USER['id']}" {if $USERFORNOTIFICATIONS } {if array_key_exists($USER['id'], $USERFORNOTIFICATIONS)}  selected {/if} {/if}>{$USER['user_name']}</option>
										{/foreach}
									</select>
								</td>
							</tr>
						</tbody>
					</table>
				</form>
			</div>
			<div class="tab-pane" id="tab_4">
				<table class="table table-bordered table-condensed settingsTable">
					<thead>
						<tr class="blockHeader" >
							<th>
								<span>{vtranslate('LBL_DETAIL', $QUALIFIED_MODULE)}</span>
							</th>
							<th>
								<span>{vtranslate('LBL_VALUES', $QUALIFIED_MODULE)}</span>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><label>{vtranslate('LBL_STORAGEFOLDER_INFO', $QUALIFIED_MODULE)}</label></td>
							<td>
								<input class="span1 configField" type="checkbox" name="storage_folder" data-type="folder" {if $DIRSFROMCONFIG['storage_folder'] == 'true'}checked{/if} />
							</td>
						</tr>
						<tr>
							<td><label>{vtranslate('LBL_BACKUPFOLDER_INFO', $QUALIFIED_MODULE)}</label></td>
							<td>
								<input class="span1 configField" type="checkbox" name="backup_folder" data-type="folder" {if $DIRSFROMCONFIG['backup_folder'] == 'true'}checked{/if} />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	{/strip}
