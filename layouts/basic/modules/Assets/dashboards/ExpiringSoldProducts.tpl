{*<!--
/*********************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 ********************************************************************************/
-->*}
<div class="dashboardWidgetHeader">
	<div class="row">
		<div class="col-md-8">
			<div class="dashboardTitle" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
		</div>
		<div class="col-md-4">
			<div class="box pull-right">
				{if Users_Privileges_Model::isPermitted($MODULE_NAME, 'CreateView')}
					<a class="btn btn-default btn-xs" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('{$MODULE_NAME}'); return false;">
						<i class='glyphicon glyphicon-plus' border='0' title="{vtranslate('LBL_ADD_RECORD')}" alt="{vtranslate('LBL_ADD_RECORD')}"/>
					</a>
				{/if}
				<a class="btn btn-default btn-xs" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
					<i class="glyphicon glyphicon-refresh" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></i>
				</a>
				{if !$WIDGET->isDefault()}
					<a class="btn btn-default btn-xs" name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
						<i class="glyphicon glyphicon-remove" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_CLOSE')}" alt="{vtranslate('LBL_CLOSE')}"></i>
					</a>
				{/if}
			</div>
		</div>
	</div>
	<hr class="widgetHr"/>
	<div class="row" >
		<div class="col-md-12">
			<div class="pull-right">
				<input class="switchBtn calculationsSwitch" type="checkbox" checked="" data-size="mini" data-label-width="5" data-handle-width="75" data-on-text="{vtranslate('LBL_OWNER',$MODULE_NAME)}" data-off-text="{vtranslate('LBL_COMMON',$MODULE_NAME)}" data-on-val="owner" data-off-val="common" data-urlparams="showtype">
			</div>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/ExpiringSoldProductsContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
