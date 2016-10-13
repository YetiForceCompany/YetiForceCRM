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
<div class=" ActivityTypes">
	<div class="widget_header row">
		<div class="col-md-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_ACTIVITY_TYPES_DESCRIPTION', $QUALIFIED_MODULE)}
		</div>
	<hr>
	</div>
	<div class=" contents tabbable">
		<table class="table customTableRWD table-bordered table-condensed listViewEntriesTable">
			<thead>
				<tr class="blockHeader">
					<th><strong>{vtranslate('LBL_ACTIVITY_NAME',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_MODULE',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_ACTIVE',$QUALIFIED_MODULE)}</strong></th>
					<th><strong>{vtranslate('LBL_COLOR',$QUALIFIED_MODULE)}</strong></th>
					<th data-hide='phone'><strong>{vtranslate('LBL_TOOLS',$QUALIFIED_MODULE)}</strong></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$MODULE_MODEL->getCalendarViewTypes() item=item key=key}
					<tr data-viewtypesid="{$item.id}" data-color="{$item.color}">
						<td>{vtranslate($item.fieldname,$item.module)}</td>
						<td>{vtranslate($item.module,$item.module)}</td>
						<td>
							<label class="">
								<input class="activeType" type="checkbox" name="active" value="1" {if $item.active eq '1'}checked=""{/if}>
							</label> 
						</td>
						<td class="calendarColor" style="background: {$item.defaultcolor};"></td>
						<td>
							<button class="btn btn-primary marginLeftZero updateColor">{vtranslate('LBL_UPDATE_COLOR',$QUALIFIED_MODULE)}</button>&ensp;
							<button class="btn btn-info generateColor">{vtranslate('LBL_GENERATE_COLOR',$QUALIFIED_MODULE)}</button>
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class="modal editColorContainer fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title">{vtranslate('LBL_EDIT_COLOR', $QUALIFIED_MODULE)}</h3>
				</div>
				<div class="modal-body">
					<form class="form-horizontal">
						<input type="hidden" class="selectedColor" value="" />
						<div class="form-group">
							<label class=" col-sm-3 control-label">{vtranslate('LBL_SELECT_COLOR', $QUALIFIED_MODULE)}</label>
							<div class=" col-sm-8 controls">
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
