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
	<h3>{vtranslate('Backup', $MODULE)}</h3>&nbsp;{vtranslate('LBL_BACKUP_DESCRIPTION', $MODULE)}<hr>
	<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
		<li class="active"  id="backup_tab_btn_1" ><a href="#tab_1">{vtranslate('LBL_BACKUP_CREATING', $MODULE)}</a></li>
	   <!-- <li id="backup_tab_btn_2" ><a href="#tab_2">{vtranslate('LBL_FTP_SETTINGS', $MODULE)}</a></li>-->
	</ul>
	<div id="my-tab-content" class="tab-content" >
		<div class='editViewContainer tab-panel' id="backup_tab_1">
			<div style="text-align: center;">
				<button type="button" id="" class="saveBackUp btn btn-success" style="margin:30px;">{vtranslate('LBL_GENERATE_BACKUP', $MODULE)}</button>
				{if $BACKUP_EXIST eq 'pending'}
					<button type="button" id="resumeBackup" class="btn btn-info" style="margin:30px;">{vtranslate('LBL_RESUME_BACKUP', $MODULE)}</button>
				{/if}
			</div>
			<div>
				<div class="container container-progress-bar" style="text-align:center;">
					<p class='backup-db-prepare' style="display:none" >{vtranslate('LBL_BACKUP_PREPARE', $MODULE)} </p>
					<p class='backup-db-loading' style="display:none" >{vtranslate('LBL_DB_BACKUP_LOADING', $MODULE)} </p>
					<p class='backup-files-loading' style="display:none" >{vtranslate('LBL_FILE_BACKUP_LOADING', $MODULE)} </p>
					<p class='backup-ended' style="display:none" >{vtranslate('LBL_BACKUP_ENDED', $MODULE)} </p>
					<div  id="backup-progress-bar" class="progress progress-striped active" style="display:none">
						<div  class="bar-backup bar" style="width: 0%;"> </div>
					</div>
				</div>  
			</div>
			<div class="btn-group pull-right">
				<button class="btn pull-left" id="listViewPreviousPageButton" ><span class="icon-chevron-left"></span></button>
				<!--
				<p class="pull-left go-to-pages-title" >{vtranslate('LBL_PAGE',$moduleName)}</p>
				<input class="pull-left" type="text" id="pageToJump" class="listViewPagingInput" value="{$PAGE}"/>
				<p class="pull-left lbl-of">   {vtranslate('LBL_OF',$moduleName)}</p>
				<p class="pull-left pushUpandDown2per" id="totalPageCount">{$ALL_PAGES}</p>
				 <button class="btn pull-left goToPage" id="goToPage" > {vtranslate('LBL_GO_TO_PAGE',$moduleName)}</button>
				-->
				<button class="btn pull-left" id="listViewNextPageButton" {if ($NEXT_PAGE eq false) or (ALL_PAGES eq 1)} disabled {/if}><span class="icon-chevron-right"></span></button>
			</div>
			<table class="table table-bordered table-condensed themeTableColor brute_force_form" style="margin-top:50px; margin-bottom: 50px;">
				<thead>
					<tr class="blockHeader">
						<th colspan="1" class="mediumWidthType">
							<span class="alignMiddle">{vtranslate('LBL_CREATED_AT', $MODULE)}</span>
						</th>
						<th colspan="1" class="mediumWidthType">
							<span class="alignMiddle">{vtranslate('LBL_FILE_NAME', $MODULE)}</span>
						</th>
						<!--
						<th colspan="1" class="mediumWidthType">
							<span class="alignMiddle">{vtranslate('LBL_ACTION', $MODULE)}</span>
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
										<button disabled data-id="{$backup['backupid']}" class="btn btn-success ftp-button" type="button"  title="{vtranslate('LBL_SEND_TO_FTP', $MODULE)}">{vtranslate('LBL_SEND_TO_FTP', $MODULE)}
										</button>
									{else}
										<button disabled data-id="{$backup['backupid']}" class="btn btn-danger disables ftp-button" type="button"  title="{vtranslate('LBL_SEND_TO_FTP', $MODULE)}">{vtranslate('LBL_SEND_TO_FTP', $MODULE)}
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
	</div>
	<div class='editViewContainer tab-panel' id="backup_tab_2" style="display:none;">
		<table class="table table-bordered table-condensed themeTableColor"  style="width: 50%">
			<thead>
			<th colspan="2" class="mediumWidthType">
				<span class="alignMiddle">{vtranslate('LBL_FTP_SETTINGS', $MODULE)}</span>
			</th>
			</thead>
			<tbody>
				<tr>
					<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('LBL_FTP_SERVER_NAME', $MODULE)}</label></td>
					<td style="border-left: none;"><input type="text" name="ftpservername" id="min_length" value="{$FTP_SERVER_NAME}" /></td>
				</tr>
				<tr>
					<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('LBL_FTP_LOGIN', $MODULE)}</label></td>
					<td style="border-left: none;"><input type="text" name="ftplogin" id="min_length" value="{$FTP_LOGIN}" /></td>
				</tr>
				<tr>
					<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('LBL_FTP_PASSWORD', $MODULE)}</label></td>
					<td style="border-left: none;"><input type="password" name="ftppassword" id="min_length" value="{$FTP_PASSWORD}" /></td>

				</tr>
				<tr>
					<td width="30%"><label class="muted pull-right marginRight10px">{vtranslate('LBL_FTP_CONNECTION', $MODULE)}</label></td>
					<td style="border-left: none;"> <div id="connection-status" {if $FTP_CONNECTION_STATUS eq true}style="background-color: #5bb75b"{else}style="background-color: red" {/if} ></div></td>
				</tr>
			</tbody>
			<div  style="margin: 10px;">
				<button class="btn btn-success saveButton" type="submit" id='saveConfig' title="{vtranslate('LBL_FTP_SAVE_CONFIG', $MODULE)}">{vtranslate('LBL_FTP_SAVE_CONFIG', $MODULE)}</button>
			</div>
		</table>
	</div>  
</div>
{/strip}