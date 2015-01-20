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
	<div class="container-fluid" id="menuEditorContainer">
		<div class="widget_header row-fluid">
			<div class="span10"><h3>{vtranslate('LBL_MDULES_COLOR_EDITOR', $QUALIFIED_MODULE)}</h3>{vtranslate('LBL_MDULES_COLOR_EDITOR_DESCRIPTION', $QUALIFIED_MODULE)}</div>
			<div class="span2"></div>
		</div>
		<hr>
		<div class="contents">
			<div class="row-fluid">
				<div class="contents tabbable">
					<table class="table table-bordered table-condensed listViewEntriesTable">
						<thead>
							<tr class="blockHeader">
								<th><strong>{vtranslate('LBL_MODULE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
								<th><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$MODULE_MODEL->getModulesColors() item=item key=key}
								<tr data-id="{$item.id}" data-color="{$item.color}">
									<td>{vtranslate($item.module,$item.module)}</td>
									<td>
										<label class="checkbox">
											<input class="activeColor" type="checkbox" name="active" value="1" {if $item.active}checked=""{/if}>
										</label> 
									</td>
									<td class="moduleColor" style="background: {$item.color};"></td>
									<td>
										<button class="btn marginLeftZero updateColor">{vtranslate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>
									</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
				</div>
			</div>
			<div class="clearfix"></div>
			<div class="modal editColorContainer hide">
				<div class="modal-header contentsBackground">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3>{vtranslate('LBL_EDIT_COLOR', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="modal-body">
					<form class="form-horizontal">
						<input type="hidden" class="selectedColor" value="" />
						<div class="control-group">
							<label class="control-label">{vtranslate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)}</label>
							<div class="controls">
								<p class="calendarColorPicker"></p>
							</div>
						</div>
					</form>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</div>
		</div>	
	</div>
{/strip}