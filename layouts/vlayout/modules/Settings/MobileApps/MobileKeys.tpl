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
	<div class="" id="MobileKeysContainer">
		<div class="widget_header row">
			<div class="col-md-8">
				{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
				{vtranslate('LBL_MOBILE_KEYS_DESCRIPTION', $QUALIFIED_MODULE)}
			</div>
			<div class="col-md-4"><button class="btn btn-primary addKey pull-right marginTop20">{vtranslate('LBL_ADD_KEY',$QUALIFIED_MODULE)}</button></div>
		</div>
		<hr>
		<div class="contents">
			{if $ENABLEMOBILE }
				<div class="alert alert-block alert-warning fade in" style="margin-left: 10px;">
					<button type="button" class="close" data-dismiss="alert">×</button>
					<h4 class="alert-heading">{vtranslate('LBL_ALERT_MOBILE_NO_ACTIVE_TITLE', $QUALIFIED_MODULE)}</h4>
					<p>{vtranslate('LBL_ALERT_MOBILE_NO_ACTIVE_DESC', $QUALIFIED_MODULE)}</p>
				</div>	
			{/if}
			<div>
				<div class="contents tabbable">
					<table class="table tableRWD table-bordered table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{vtranslate('LBL_USERNAME',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_SERVICE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_KEY',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_WHO_CAN_DIAL',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$MODULE_MODEL->getAllMobileKeys() item=item key=key}
								<tr data-service="{$item.service}" data-user="{$item.userid}">
									<td>
										<span title="{$item['fullusername']}">
											{$item.user_name}
										</span>
									</td>
									<td>{vtranslate($item.name,$QUALIFIED_MODULE)}</td>
									<td>{$item.key}</td>
									<td>
										{if $item.service == 'pushcall'}
										<select multiple class="chzn-select col-md-5 privileges_users" name="privileges_users" data-validation-engine="validate[required]">
											{assign var=ALLUSERS value=Users_Record_Model::getAll()}
											{foreach from=$ALLUSERS item=item2 key=key2}
												<option value="{$key2}" {if in_array($key2,$item.privileges_users)}selected{/if}>{$item2->getDisplayName()}</option>
											{/foreach}
										</select>
										{/if}
									</td>
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
								<label class="col-sm-3 control-label">{vtranslate('LBL_SELECT_SERVICE', $QUALIFIED_MODULE)}</label>
								<div class="col-sm-6 controls">
									<select class="select service form-control" name="service" data-validation-engine="validate[required]">
									{foreach from=$MODULE_MODEL->getAllService() item=item key=key}
										<option value="{$key}">{vtranslate($item,$QUALIFIED_MODULE)}</option>
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
