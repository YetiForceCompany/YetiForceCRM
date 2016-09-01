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
	<div class="" id="DavKeysContainer">
		<div class="widget_header row">
			<div class="col-md-8">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				{vtranslate('LBL_DAV_KEYS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4"><button class="btn btn-primary addKey pull-right marginTop20">{vtranslate('LBL_ADD_KEY',$QUALIFIED_MODULE)}</button></div>
		</div>
		<div class="contents">
			{if $ENABLEDAV }
				<div class="alert alert-warning">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">{vtranslate('LBL_ALERT_DAV_NO_ACTIVE_TITLE', $QUALIFIED_MODULE)}</h4>
					<p>{vtranslate('LBL_ALERT_DAV_NO_ACTIVE_DESC', $QUALIFIED_MODULE)}</p>
				</div>	
			{/if}
			<div class="alert alert-info">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<h4 class="alert-heading">{vtranslate('LBL_ALERT_DAV_CONFIG_TITLE', $QUALIFIED_MODULE)}</h4>
				<p>{vtranslate('LBL_ALERT_DAV_CONFIG_DESC', $QUALIFIED_MODULE,AppConfig::main('site_URL'))}</p>
			</div>
			<div>
				<div class="contents tabbable">
					<table class="table table-bordered  tableRWD table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{vtranslate('LBL_LOGIN',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_KEY',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_DISPLAY_NAME',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_EMAIL',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_ACTIVE_USER',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('CardDAV',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('CalDAV',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('WebDAV',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_COUNT_CARD',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_COUNT_CAL',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{assign var=AMOUNT_DATA value=$MODULE_MODEL->getAmountData()}
							{foreach from=$MODULE_MODEL->getAllKeys() item=item key=key}
								{assign var=ADDRESSBOOK value=$AMOUNT_DATA['addressbook'][$item.addressbooksid]}
								{assign var=CALENDAR value=$AMOUNT_DATA['calendar'][$item.calendarsid]}
								<tr data-user="{$item.userid}" data-name="{$item.user_name}">
									<td>{$item.user_name}</td>
									<td>{$item.key}</td>
									<td>{$item.displayname}</td>
									<td>{$item.email}</td>
									<td>{vtranslate($item.status,'Users')}</td>
									<td>{if $item.addressbooksid}{vtranslate('LBL_YES')}{else}{vtranslate('LBL_NO')}{/if}</td>
									<td>{if $item.calendarsid}{vtranslate('LBL_YES')}{else}{vtranslate('LBL_NO')}{/if}</td>
									<td>{vtranslate('LBL_YES')}</td>
									<td>{if $ADDRESSBOOK}{$ADDRESSBOOK}{else}0{/if}</td>
									<td>{if $CALENDAR}{$CALENDAR}{else}0{/if}</td>
									<td>
										<button class="btn btn-danger deleteKey">{vtranslate('LBL_DELETE_KEY',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal addKeyContainer fade" tabindex="-1">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header contentsBackground">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h3 class="modal-title">{vtranslate('LBL_ADD_KEY', $QUALIFIED_MODULE)}</h3>
						</div>
						<div class="modal-body">
							<form class="form-horizontal">
								<div class="form-group">
									<label class="col-sm-3 control-label">{vtranslate('LBL_SELECT_USER', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-6 controls">
										<select class="select user form-control" name="user" data-validation-engine="validate[required]">
											{foreach from=$USERS item=item key=key}
												<option value="{$key}">{$item->getDisplayName()}</option>
											{/foreach}
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{vtranslate('LBL_SELECT_TYPE', $QUALIFIED_MODULE)}</label>
									<div class="col-sm-6 controls">
										<select multiple="" class="select type form-control" name="type">
											{foreach from=$MODULE_MODEL->getTypes() item=item}
												<option selected="" value="{$item}">{$item}</option>
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
		</div>	
	</div>
{/strip}
