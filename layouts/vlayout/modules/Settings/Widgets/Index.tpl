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
<input type="hidden" id="filters" name="filters" value='{$FILTERS}'>
<input type="hidden" id="checkboxs" name="checkboxs" value='{$CHECKBOXS}'>
<div class="container-fluid WidgetsManage">
	<input type="hidden" name="tabid" value="{$SOURCE}">
	<div class="widget_header row-fluid">
		<div class="span8"><h3>{vtranslate($MODULE, $QUALIFIED_MODULE)}</h3>{vtranslate('LBL_MODULE_DESC', $QUALIFIED_MODULE)}</div>
		<div class="span4">
			<div class="pull-right">
				<select class="select2 span3" name="ModulesList">
					{foreach from=$MODULE_MODEL->getModulesList() item=item key=key}
						<option value="{$key}" {if $SOURCE eq $key}selected{/if}>{vtranslate($item['tablabel'], $item['name'])}</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>
	<hr>
	<div class="btn-toolbar">
		<span class="pull-left">
			<h4>{vtranslate('List of widgets for the module', $QUALIFIED_MODULE)}: {vtranslate($SOURCEMODULE, $SOURCEMODULE)}</h4>
		</span>
		<span class="pull-right">
			<button class="btn addWidget" type="button"><i class="icon-plus"></i>&nbsp;<strong>{vtranslate('Add widget', $QUALIFIED_MODULE)}</strong></button>
		</span>
		<div class="clearfix"></div>
	</div>
	<div class="blocks-content padding1per">
		<div class="row-fluid">
			{foreach from=$WIDGETS item=WIDGETCOL key=column}
			<div class="blocksSortable span4" data-column="{$column}">
				{foreach from=$WIDGETCOL item=WIDGET key=key}
					<div class="blockSortable" data-id="{$key}">
						<div class="padding1per border1px">
							<div class="row-fluid">
								<div class="span5">
									<img class="alignMiddle" src="{vimage_path('drag.png')}" /> &nbsp;&nbsp;{vtranslate($WIDGET['type'], $QUALIFIED_MODULE)}
								</div>
								<div class="span5">
									{vtranslate($WIDGET['label'], $SOURCEMODULE)}&nbsp;
								</div>
								<div class="span2">
									<span class="pull-right">
										<i class="cursorPointer icon-pencil editWidget" title="{vtranslate('Edit', $QUALIFIED_MODULE)}"></i>
										&nbsp;&nbsp;<i class="cursorPointer icon-remove removeWidget" title="{vtranslate('Remove', $QUALIFIED_MODULE)}"></i>
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
