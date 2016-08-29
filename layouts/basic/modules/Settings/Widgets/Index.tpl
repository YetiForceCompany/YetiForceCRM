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
<input type="hidden" id="filterAll" value='{$FILTERS}'>
<input type="hidden" id="checkboxAll" value='{$CHECKBOXS}'>
<input type="hidden" id="switchHeaderAll" value='{$SWITCHES_HEADER}'>
<div class="WidgetsManage">
	<input type="hidden" name="tabid" value="{$SOURCE}">
	<div class="widget_header row">
		<div class="col-md-8">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
			{vtranslate('LBL_MODULE_DESC', $QUALIFIED_MODULE)}
		</div>
		<div class="pull-right col-md-4 h3">
			<select class="select2 col-md-3 form-control" name="ModulesList">
				{foreach from=$MODULE_MODEL->getModulesList() item=item key=key}
					<option value="{$key}" {if $SOURCE eq $key}selected{/if}>{vtranslate($item['tablabel'], $item['name'])}</option>
				{/foreach}
			</select>
		</div>
	</div>
	<div>
		<div class="col-md-8 paddingLRZero">
			<h4>{vtranslate('List of widgets for the module', $QUALIFIED_MODULE)}: {vtranslate($SOURCEMODULE, $SOURCEMODULE)}</h4>
		</div>
		<div class="col-md-4 paddingLRZero">
			<button class="btn btn-success addWidget pull-right" type="button"><i class="glyphicon glyphicon-plus"></i>&nbsp;<strong>{vtranslate('Add widget', $QUALIFIED_MODULE)}</strong></button>
		</div>
		<div class="clearfix"></div>
	</div>
	<div class="blocks-content padding1per">
		<div class="row">
			{foreach from=$WIDGETS item=WIDGETCOL key=column}
			<div class="blocksSortable col-md-4" data-column="{$column}">
				{foreach from=$WIDGETCOL item=WIDGET key=key}
					<div class="blockSortable" data-id="{$key}">
						<div class="padding1per border1px">
							<div class="row">
								<div class="col-md-5">
									<img class="alignMiddle" src="{vimage_path('drag.png')}" /> &nbsp;&nbsp;{vtranslate($WIDGET['type'], $QUALIFIED_MODULE)}
								</div>
								<div class="col-md-5">
									{if $WIDGET['label'] eq '' && isset($WIDGET['data']['relatedmodule'])}
										{vtranslate(vtlib\Functions::getModuleName($WIDGET['data']['relatedmodule']),vtlib\Functions::getModuleName($WIDGET['data']['relatedmodule']))}
									{else}	
										{vtranslate($WIDGET['label'], $SOURCEMODULE)}&nbsp;
									{/if}									
								</div>
								<div class="col-md-2">
									<span class="pull-right">
										<i class="cursorPointer glyphicon glyphicon-pencil editWidget" title="{vtranslate('Edit', $QUALIFIED_MODULE)}"></i>
										&nbsp;&nbsp;<i class="cursorPointer glyphicon glyphicon-remove removeWidget" title="{vtranslate('Remove', $QUALIFIED_MODULE)}"></i>
									</span>
								</div>
							</div>
						</div>
					</div>
				{/foreach}
			</div>
			{/foreach}
		</div>
	</div>
	<div class="clearfix"></div>
{/strip}
