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
<div class="" id="menuEditorContainer">
	<div class="widget_header row">
		<div class="col-md-12">
		    {include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<div id="my-tab-content" class="tab-content" style="margin: 0 20px;" >
		<div class='editViewContainer' id="tpl">
			<div class="row">
				<div class="col-md-4 btn-toolbar paddingLRZero">
					<a class="btn btn-default addButton">
						<strong>{vtranslate('LBL_NEW_TPL', $MODULE_NAME)}</strong>
					</a>
				</div>
				<div class="col-md-4 paddingLRZero">
					<select class="chzn-select form-control" id="moduleFilter" >
						<option value="">{vtranslate('LBL_ALL', $MODULE_NAME)}</option>
						{foreach item=MODULE_MODEL key=TAB_ID from=$SUPPORTED_MODULE_MODELS}
							<option {if $SOURCE_MODULE eq $MODULE_MODEL->getName()} selected="" {/if} value="{$MODULE_MODEL->getName()}">{vtranslate($MODULE_MODEL->getName(),$MODULE_MODEL->getName())}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<br>
			<div class="row">
				<table class="table table-bordered table-condensed listViewEntriesTable">
					<thead>
						<tr class="listViewHeaders" >
							<th width="30%">{vtranslate('LBL_TPL_NAME',$MODULE_NAME)}</th>
							<th>{vtranslate('LBL_PROJECT_NAME',$MODULE_NAME)}</th>
							<th colspan="2"></th>
						</tr>
					</thead>
					{if !empty($PROJECT_TPL_LIST)}

						<tbody>
							{foreach from=$PROJECT_TPL_LIST item=item key=key}
								<tr class="listViewEntries" data-id="{$key}">
									<td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module=OSSProjectTemplates&parent=Settings&view=Edit&tpl_id={$key}">{$item.tpl_name}</td>
									<td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module=OSSProjectTemplates&parent=Settings&view=Edit&tpl_id={$key}">{$item.projectname}</td>
									<td class='actions'> <a href='index.php?module=OSSProjectTemplates&parent=Settings&action=DeleteTemplate&tpl_id={$key}&base_module=Project' class="pull-right marginRight10px">
											<span type="{vtranslate('REMOVE_TPL', $MODULE_NAME)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
										<a class="pull-right edit_tpl"><span title="{vtranslate('LBL_EDIT')}" class="glyphicon glyphicon-pencil alignMiddle"></span></a>
									</td>
								<tr>
								{/foreach}
						</tbody>
					</table>
				{else}
					<table class="emptyRecordsDiv">
						<tbody>
							<tr>
								<td>
									{vtranslate('LBL_NO_PROJECT_TPL_ADDED',$MODULE_NAME)}
								</td>
							</tr>
						</tbody>
					</table>
				{/if}
			</div>
		</div>
	</div>
</div>
{include file='AddProjectModal.tpl'|@vtemplate_path:$SETTINGS_MODULE_NAME}
{include file='EditProjectModal.tpl'|@vtemplate_path:$SETTINGS_MODULE_NAME}
<script type="text/javascript" src="{Yeti_Layout::getLayoutFile('modules/Settings/OSSProjectTemplates/resources/Edit.js')}"></script>
