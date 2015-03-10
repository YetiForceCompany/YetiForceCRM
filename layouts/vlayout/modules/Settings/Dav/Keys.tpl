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
	<div class="container-fluid" id="DavKeysContainer">
		<div class="widget_header row-fluid">
			<div class="span8"><h3>{vtranslate('LBL_DAV_KEYS', $QUALIFIED_MODULE)}</h3>{vtranslate('LBL_DAV_KEYS_DESCRIPTION', $QUALIFIED_MODULE)}</div>
			<div class="span4"><button class="btn btn-primary addKey pull-right">{vtranslate('LBL_ADD_KEY',$QUALIFIED_MODULE)}</button></div>
		</div>
		<hr>
		<div class="contents">
			{if $ENABLEDAV }
				<div class="alert alert-block alert-error fade in" style="margin-left: 10px;">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">{vtranslate('LBL_ALERT_DAV_NO_ACTIVE_TITLE', $QUALIFIED_MODULE)}</h4>
					<p>{vtranslate('LBL_ALERT_DAV_NO_ACTIVE_DESC', $QUALIFIED_MODULE)}</p>
				</div>	
			{/if}
			<div class="row-fluid">
				<div class="contents tabbable">
					<table class="table table-bordered table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{vtranslate('LBL_LOGIN',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_DISPLAY_NAME',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_EMAIL',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_ACTIVE_USER',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_KEY',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$MODULE_MODEL->getAllKeys() item=item key=key}
								<tr data-user="{$item.userid}" data-name="{$item.user_name}">
									<td>{$item.user_name}</td>
									<td>{$item.displayname}</td>
									<td>{$item.email}</td>
									<td>{vtranslate($item.status,'Users')}</td>
									<td>{$item.key}</td>
									<td>
										<button class="btn btn-danger deleteKey">{vtranslate('LBL_DELETE_KEY',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="modal addKeyContainer hide">
				<div class="modal-header contentsBackground">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>{vtranslate('LBL_ADD_KEY', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="modal-body">
					<form class="form-horizontal">
						<div class="control-group">
							<label class="control-label">{vtranslate('LBL_SELECT_USER', $QUALIFIED_MODULE)}</label>
							<div class="controls">
								<select class="select span4 user" name="user" data-validation-engine="validate[required]">
								{foreach from=$USERS item=item key=key}
									<option value="{$key}">{$item->getDisplayName()}</option>
								{/foreach}
								</select>
							</div>
						</div>
					</form>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>	
	</div>
{/strip}