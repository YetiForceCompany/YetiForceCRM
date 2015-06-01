{*<!--
/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/
-->*}
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
	<div class="container-fluid" style="margin-top:10px;">
		<h3>{vtranslate('Backup', $QUALIFIED_MODULE_NAME)}</h3>&nbsp;{vtranslate('LBL_BACKUP_DESCRIPTION', $QUALIFIED_MODULE_NAME)}<hr>
		<ul id="tabs" class="nav nav-tabs layoutTabs massEditTabs" data-tabs="tabs">
			<li class="active"><a href="#tab_1" data-toggle="tab">{vtranslate('LBL_BACKUP_CREATING', $QUALIFIED_MODULE_NAME)}</a></li>
			<li><a href="#tab_2" data-toggle="tab">{vtranslate('LBL_FTP_SETTINGS', $QUALIFIED_MODULE_NAME)}</a></li>
			<li><a href="#tab_3" data-toggle="tab">{vtranslate('LBL_EMAIL_NOTIFICATIONS', $QUALIFIED_MODULE_NAME)}</a></li>
			<li><a href="#tab_4" data-toggle="tab">{vtranslate('LBL_GENERAL_SETTINGS', $QUALIFIED_MODULE_NAME)}</a></li>
		</ul>
		<div id="my-tab-content" class="tab-content layoutContent">
			<div class='tab-pane active' id="tab_1">
				<div style="text-align: center;">
					<button type="button" id="" class="saveBackUp btn btn-success" style="margin:30px;">{vtranslate('LBL_GENERATE_BACKUP', $QUALIFIED_MODULE_NAME)}</button>
					{if $BACKUP_EXIST eq 'pending'}
						<button type="button" id="resumeBackup" class="btn btn-info" style="margin:30px;">{vtranslate('LBL_RESUME_BACKUP', $QUALIFIED_MODULE_NAME)}</button>
					{/if}
				</div>
				<div>
					<div class="container container-progress-bar" style="text-align:center;">
						<p class='backup-db-prepare' style="display:none" >{vtranslate('LBL_BACKUP_PREPARE', $QUALIFIED_MODULE_NAME)} </p>
						<p class='backup-db-loading' style="display:none" >{vtranslate('LBL_DB_BACKUP_LOADING', $QUALIFIED_MODULE_NAME)} </p>
						<p class='backup-files-loading' style="display:none" >{vtranslate('LBL_FILE_BACKUP_LOADING', $QUALIFIED_MODULE_NAME)} </p>
						<p class='backup-ended' style="display:none" >{vtranslate('LBL_BACKUP_ENDED', $QUALIFIED_MODULE_NAME)} </p>
						<div  id="backup-progress-bar" class="progress progress-striped active" style="display:none">
							<div  class="bar-backup bar" style="width: 0%;"> </div>
						</div>
					</div>  
				</div>
				<div class="btn-group pull-right">
					<button class="btn pull-left" id="listViewPreviousPageButton" ><span class="icon-chevron-left"></span></button>
					<!--
					<p class="pull-left go-to-pages-title" >{vtranslate('LBL_PAGE',$QUALIFIED_MODULE_NAMEName)}</p>
					<input class="pull-left" type="text" id="pageToJump" class="listViewPagingInput" value="{$PAGE}"/>
					<p class="pull-left lbl-of">   {vtranslate('LBL_OF',$QUALIFIED_MODULE_NAMEName)}</p>
					<p class="pull-left pushUpandDown2per" id="totalPageCount">{$ALL_PAGES}</p>
					 <button class="btn pull-left goToPage" id="goToPage" > {vtranslate('LBL_GO_TO_PAGE',$QUALIFIED_MODULE_NAMEName)}</button>
					-->
					<button class="btn pull-left" id="listViewNextPageButton" {if ($NEXT_PAGE eq false) or (ALL_PAGES eq 1)} disabled {/if}><span class="icon-chevron-right"></span></button>
				</div>
				<table class="table table-bordered table-condensed themeTableColor brute_force_form" style="margin-top:50px; margin-bottom: 50px;">
					<thead>
						<tr class="blockHeader">
							<th colspan="1" class="mediumWidthType">
								<span class="alignMiddle">{vtranslate('LBL_CREATED_AT', $QUALIFIED_MODULE_NAME)}</span>
							</th>
							<th colspan="1" class="mediumWidthType">
								<span class="alignMiddle">{vtranslate('LBL_FILE_NAME', $QUALIFIED_MODULE_NAME)}</span>
							</th>
							<!--
							<th colspan="1" class="mediumWidthType">
									<span class="alignMiddle">{vtranslate('LBL_ACTION', $QUALIFIED_MODULE_NAME)}</span>
							</th>
							-->
						</tr>
					</thead>
					<tbody class='backup-list'>
						{foreach item=backup from=$BACKUPS name=backup}
							<tr>
								<td><label class="marginRight5px" >{$backup['created_at']}</label></td>
								<td><label class="marginRight5px" >{$backup['file_name']}</label></td>
								<!--
								<td><label class="marginRight5px" >
								{if $FTP_CONNECTION_STATUS eq true}
										<button disabled data-id="{$backup['backupid']}" class="btn btn-success ftp-button" type="button"  title="{vtranslate('LBL_SEND_TO_FTP', $QUALIFIED_MODULE_NAME)}">{vtranslate('LBL_SEND_TO_FTP', $QUALIFIED_MODULE_NAME)}
										</button>
								{else}
										<button disabled data-id="{$backup['backupid']}" class="btn btn-danger disables ftp-button" type="button"  title="{vtranslate('LBL_SEND_TO_FTP', $QUALIFIED_MODULE_NAME)}">{vtranslate('LBL_SEND_TO_FTP', $QUALIFIED_MODULE_NAME)}
										</button>
								{/if}
						</label>
				</td>
								-->
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
									<input type="text" value="{$FTP_HOST}" name="ftphost"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<span class="redColor pull-right">*</span>
									<label class="pull-right">{vtranslate('LBL_LOGIN', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<input type="text" value="{$FTP_LOGIN}" name="ftplogin"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<span class="redColor pull-right">*</span>
									<label class="pull-right">{vtranslate('LBL_PASSWORD', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<input type="password" value="{$FTP_PASSWORD}" name="ftppassword"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<label class="pull-right">{vtranslate('LBL_PORT', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<input type="text" value="{$FTP_PORT}" name="ftpport"></input>
								</td>
							</tr>
							<tr>
								<td style="width:25%">
									<span class="add-on pull-right"><i class="icon-info-sign popoverTooltip" data-content="{vtranslate('LBL_PATH_INFO', $QUALIFIED_MODULE)}"></i></span>
									<label class="pull-right">{vtranslate('LBL_PATH', $QUALIFIED_MODULE_NAME)}</label>
								</td>
								<td>
									<input type="text" value="{$FTP_PATH}" name="ftppath"></input>
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
					<input class="btn btn-success pull-right" id="saveConfig" type="submit" value="{vtranslate('LBL_SAVE', $QUALIFIED_MODULE_NAME)}">
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
									<select class="chzn-select" id="" name="selectedUsers" multiple>
										{foreach key=KEY  item=USER from=$ADMIN_USERS}
											<option name="selectedUsers" value="{$USER['id']}" {if $USERFORNOTIFICATIONS } {if array_key_exists($KEY, $USERFORNOTIFICATIONS)}  selected {/if} {/if}>{$USER['user_name']}</option>
										{/foreach}
									</select>
								</td>
							</tr>
						</tbody>
					</table>
					<input class="btn btn-success pull-right" id="saveUsersForNotifications" type="submit" value="{vtranslate('LBL_SAVE', $QUALIFIED_MODULE_NAME)}">
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
								<input class="span1 configField" type="checkbox" name="storage_folder"  {if $DIRSFROMCONFIG['storage_folder'] == 'true'}checked{/if} />
							</td>
						</tr>
						<tr>
							<td><label>{vtranslate('LBL_BACKUPFOLDER_INFO', $QUALIFIED_MODULE)}</label></td>
							<td>
								<input class="span1 configField" type="checkbox" name="backup_folder"  {if $DIRSFROMCONFIG['backup_folder'] == 'true'}checked{/if} />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	{/strip}
