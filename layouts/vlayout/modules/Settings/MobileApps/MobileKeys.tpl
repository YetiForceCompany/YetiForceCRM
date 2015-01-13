{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
	<div class="container-fluid" id="MobileKeysContainer">
		<div class="widget_header row-fluid">
			<div class="span8"><h3>{vtranslate('LBL_MOBILE_KEYS', $QUALIFIED_MODULE)}</h3>{vtranslate('LBL_MOBILE_KEYS_DESCRIPTION', $QUALIFIED_MODULE)}</div>
			<div class="span4"><button class="btn btn-primary addKey pull-right">{vtranslate('LBL_ADD_KEY',$QUALIFIED_MODULE)}</button></div>
		</div>
		<hr>
		<div class="contents">
			<div class="row-fluid">
				<div class="contents tabbable">
					<table class="table table-bordered table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{vtranslate('LBL_USERNAME',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_SERVICE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_KEY',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$MODULE_MODEL->getAllMobileKeys() item=item key=key}
								<tr data-service="{$item.service}" data-user="{$item.userid}">
									<td><span title="{$item.user_name}">{$item.first_name} {$item.last_name}</span></td>
									<td>{vtranslate($item.service,$QUALIFIED_MODULE)}</td>
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
						<div class="control-group">
							<label class="control-label">{vtranslate('LBL_SELECT_SERVICE', $QUALIFIED_MODULE)}</label>
							<div class="controls">
								<select class="select span4 service" name="service" data-validation-engine="validate[required]">
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
{/strip}