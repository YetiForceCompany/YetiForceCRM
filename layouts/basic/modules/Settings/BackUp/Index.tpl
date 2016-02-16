{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}	
	<style>
		#connection-status{
			width: 22px;
			height: 22px;
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
	{assign var=CHECK_CRON value=$BACKUP_MODEL->checkCron()}
	{assign var=CHECK_MAIL value=$BACKUP_MODEL->checkMail()}
	<div>
		<div class="widget_header row">
			<div class="col-xs-12">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				&nbsp;{vtranslate('LBL_BACKUP_DESCRIPTION', $QUALIFIED_MODULE_NAME)}
			</div>
		</div>
		{if !extension_loaded('zip')}
			<div class="alert alert-block alert-danger" style="margin-left: 10px;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{vtranslate('LBL_ALERT_NO_ZIP_EXTENSION_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{vtranslate('LBL_ALERT_NO_ZIP_EXTENSION_DESC', $QUALIFIED_MODULE)}</p>
			</div>	
		{/if}
		{if !$CHECK_CRON}
			<div class="alert alert-block alert-danger" style="margin-left: 10px;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{vtranslate('LBL_ALERT_CRON_NOT_ACTIVE_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{vtranslate('LBL_ALERT_CRON_NOT_ACTIVE_DESC', $QUALIFIED_MODULE)}</p>
			</div>	
		{/if}
		{if !$CHECK_MAIL}
			<div class="alert alert-block alert-danger" style="margin-left: 10px;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{vtranslate('LBL_ALERT_OUTGOING_MAIL_NOT_CONFIGURED_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{vtranslate('LBL_ALERT_OUTGOING_MAIL_NOT_CONFIGUREDE_DESC', $QUALIFIED_MODULE)}</p>
			</div>
		{/if}
		<ul id="tabs" class="nav nav-tabs layoutTabs massEditTabs" data-tabs="tabs">
			<li class="active"><a href="#tab_0" data-toggle="tab">{vtranslate('LBL_BACKUP_CREATING', $QUALIFIED_MODULE_NAME)}</a></li>
			<li><a href="#tab_1" data-toggle="tab">{vtranslate('LBL_LOGS', $QUALIFIED_MODULE_NAME)}</a></li>
				{if function_exists('ftp_connect')}
				<li><a href="#tab_2" data-toggle="tab">{vtranslate('LBL_FTP_SETTINGS', $QUALIFIED_MODULE_NAME)}</a></li>
				{/if}
				{if $CHECK_MAIL}
				<li><a href="#tab_3" data-toggle="tab">{vtranslate('LBL_EMAIL_NOTIFICATIONS', $QUALIFIED_MODULE_NAME)}</a></li>
				{/if}
			<li><a href="#tab_4" data-toggle="tab">{vtranslate('LBL_GENERAL_SETTINGS', $QUALIFIED_MODULE_NAME)}</a></li>
		</ul>
		<div id="my-tab-content" class="tab-content layoutContent" style="padding-top: 5px;">
			<div class='tab-pane active' id="tab_0">
				<br/>
				{if $BACKUP_INFO}
					<div class="textAlignCenter">
						<button class="stopBackup btn btn-danger" >{vtranslate('LBL_STOP_BACKUP', $QUALIFIED_MODULE_NAME)}</button>
					</div>
					<div class="row-fluid row-bar mainBar">
						<div class="span4">{vtranslate('LBL_TOTAL_PROGRESS', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span>%</span>
						</div>

						<div class="span8">
							<div class="progress progress-striped progress-danger active">
								<div class="progress-bar"  style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					<hr /><br/>
					<div class="row-fluid row-bar b1">
						<div class="span4">{vtranslate('LBL_STAGE_1', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span> %</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-info active">
								<div class="progress-bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					<div class="row-fluid row-bar b2">
						<div class="span4">{vtranslate('LBL_STAGE_2', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span> %</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-info active">
								<div class="progress-bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					<div class="row-fluid row-bar b3">
						<div class="span4">{vtranslate('LBL_STAGE_3', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span> %</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-info active">
								<div class="progress-bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					<div class="row-fluid row-bar b4">
						<div class="span4">{vtranslate('LBL_STAGE_4', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span> %</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-info active">
								<div class="progress-bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					<div class="row-fluid row-bar b5">
						<div class="span4">{vtranslate('LBL_STAGE_5', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span> %</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-info active">
								<div class="progress-bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					<div class="row-fluid row-bar b6">
						<div class="span4">{vtranslate('LBL_STAGE_6', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span> %</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-info active">
								<div class="progress-bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					<div class="row-fluid row-bar b7">
						<div class="span4">{vtranslate('LBL_STAGE_7', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span> %</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-info active">
								<div class="progress-bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
					{if $FTP_ACTIVE}
						<div class="row-fluid row-bar b8">
							<div class="span4">{vtranslate('LBL_STAGE_8', $QUALIFIED_MODULE_NAME)}
								<span class="pull-right"><span class="precent">0.00</span> %</span>
							</div>
							<div class="span8">
								<div class="progress progress-striped progress-info active">
									<div class="progress-bar " style="width: 0%;"> </div>
								</div>
							</div>
						</div>
					{/if}
					<div class="row-fluid row-bar b9">
						<div class="span4">{vtranslate('LBL_STAGE_9', $QUALIFIED_MODULE_NAME)}
							<span class="pull-right"><span class="precent">0.00</span> %</span>
						</div>
						<div class="span8">
							<div class="progress progress-striped progress-info active">
								<div class="progress-bar " style="width: 0%;"> </div>
							</div>
						</div>
					</div>
				{else}
					<div class="textAlignCenter">
						<button class="runBackup btn btn-success" style="margin-top: 10px;">{vtranslate('LBL_SCHEDULE_BACKUP', $QUALIFIED_MODULE_NAME)}</button>
					</div>
				{/if}
			</div>
			<div class='tab-pane' id="tab_1">
				<div>
					<div class="btn-group pull-right">
						<button class="btn btn-default pull-left" id="listViewPreviousPageButton" ><span class="glyphicon glyphicon-chevron-left"></span></button>
						<button class="btn btn-default pull-left" id="listViewNextPageButton" {if ($NEXT_PAGE eq false) or (ALL_PAGES eq 1)} disabled {/if}><span class="glyphicon glyphicon-chevron-right"></span></button>
					</div>
				</div>
				<br /><br />
				
					<table class="table tableRWD table-bordered table-condensed themeTableColor brute_force_form">
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
									<label class="pull-right">{vtranslate('LBL_HOST', $QUALIFIED_MODULE_NAME)}</label>
									<span class="redColor pull-right">*</span>
								</td>
								<td>
									<div class="col-md-3">
										<input class="form-control" type="text" value="{$FTP_HOST}" name="host"></input>
									</div>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<label class="pull-right">{vtranslate('LBL_LOGIN', $QUALIFIED_MODULE_NAME)}</label>
									<span class="redColor pull-right">*</span>
								</td>
								<td>
									<div class="col-md-3">
										<input class="form-control" type="text" value="{$FTP_LOGIN}" name="login"></input>
									</div>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<label class="pull-right">{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE_NAME)}</label>
									<span class="redColor pull-right">*</span>
								</td>
								<td>
									<div class="col-md-3">
										<input class="form-control" type="password" value="{$FTP_PASSWORD}" name="password"></input>
									</div>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<label class="pull-right">{vtranslate('LBL_PORT', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<div class="col-md-3">
										<input class="form-control" type="text" value="{$FTP_PORT}" name="port"></input>
									</div>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<span class="add-on pull-right"><i class="icon-info-sign popoverTooltip" data-content="{vtranslate('LBL_PATH_INFO', $QUALIFIED_MODULE)}"></i></span>
									<label class="pull-right">{vtranslate('LBL_PATH', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<div class="col-md-3">
										<input class="form-control" type="text" value="{$FTP_PATH}" name="path"></input>
									</div>
								</td>
							</tr>
							<tr>
								<td style="width:25%"><label class="pull-right">{vtranslate('LBL_ACTIVE', $QUALIFIED_MODULE_NAME)}</label></td>
								<td>
									<div class="col-md-3">
										<input type="checkbox" name="active" {if $FTP_ACTIVE} checked {/if}></input>
									</div>
								</td>
							</tr>
							<tr>
								<td style="width:25%"><label class="pull-right">{vtranslate('LBL_CONNECTION_STATUS', $QUALIFIED_MODULE_NAME)}</label></td>
								<td>
									<div class="col-md-3">
										<div id="connection-status"  style="{if $FTP_CONNECTION_STATUS eq 0} background-color:red; {else}background-color:#5bb75b; {/if}" ></div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
					<button class="btn btn-success pull-right" id="saveFtpConfig" type="button">{vtranslate('LBL_SAVE', $QUALIFIED_MODULE_NAME)}</button>
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
											<option name="selectedUsers" value="{$USER['id']}" {if $USERFORNOTIFICATIONS } {if in_array($USER['id'], $USERFORNOTIFICATIONS)}  selected {/if} {/if}>{$USER['user_name']}</option>
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
								<input class="span1 configField switchBtn" type="checkbox" name="storage_folder" data-on-text="{vtranslate('LBL_ON', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_OFF', $QUALIFIED_MODULE)}" data-type="folder" {if $DIRSFROMCONFIG['storage_folder'] == 'true'}checked{/if} />
							</td>
						</tr>
						<tr>
							<td><label>{vtranslate('LBL_BACKUPFOLDER_INFO', $QUALIFIED_MODULE)}</label></td>
							<td>
								<input class="span1 configField switchBtn" type="checkbox" name="backup_folder" data-on-text="{vtranslate('LBL_ON', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_OFF', $QUALIFIED_MODULE)}" data-type="folder" {if $DIRSFROMCONFIG['backup_folder'] == 'true'}checked{/if} />
							</td>
						</tr>
						<tr>
							<td><label>{vtranslate('LBL_BACKUP_COPY_TYPE', $QUALIFIED_MODULE)}</label></td>
							<td>
								<input name="type" data-type="main" class="span1 configField switchBtn" type="checkbox"  data-label-width="10" data-handle-width="100" data-on-text="{vtranslate('LBL_BACKUP_SINGLE', $QUALIFIED_MODULE)}" data-off-text="{vtranslate('LBL_BACKUP_OVERALL', $QUALIFIED_MODULE)}" {if $MAIN_CONFIG['type'] == 'true'}checked{/if} />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
{/strip}
