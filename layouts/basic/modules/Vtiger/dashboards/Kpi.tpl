{*<!--
/*********************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 ********************************************************************************/
-->*}
<script type="text/javascript">
	Vtiger_KpiBarchat_Widget_Js('Vtiger_Kpi_Widget_Js',{},{});
</script>
{strip}
<div class="dashboardWidgetHeader">
	{foreach key=index item=cssModel from=$STYLES}
		<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
	{/foreach}
	{foreach key=index item=jsModel from=$SCRIPTS}
		<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
	{/foreach}
	<table width="100%" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td class="col-md-5">
					<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: 15em;"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
				</td>
				<td class="refresh col-md-2" align="right">
					<span style="position:relative;">&nbsp;</span>
				</td>
				<td class="widgeticons col-md-5" align="right">
					<div class="box pull-right">
						<a name="dfilter">
							<i class='icon-cog' border='0' align="absmiddle" title="{vtranslate('LBL_FILTER')}" alt="{vtranslate('LBL_FILTER')}"/>
						</a>
						<!--
						<a class="dprint" name="dprint">
							<i class='icon-print' border='0' align="absmiddle" title="{vtranslate('LBL_PRINT')}" alt="{vtranslate('LBL_PRINT')}"/>
						</a>
						-->
						<a href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
							<i class="glyphicon glyphicon-refresh" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></i>
						</a>
						{if !$WIDGET->isDefault()}
							<a name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
								<i class="glyphicon glyphicon-remove" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REMOVE')}" alt="{vtranslate('LBL_REMOVE')}"></i>
							</a>
						{/if}
					</div>
				</td>
			</tr>
		</tbody>
	</table>



	<div class="row filterContainer hide" style="position:absolute;z-index:100001">
		<div class="row">
			<span class="col-md-4">
				<span class="pull-right">
					{vtranslate('LBL_TIME', $MODULE_NAME)}
				</span>
			</span>
			<span class="col-md-8">
				<input type="text" name="time" title="{vtranslate('LBL_CHOOSE_DATE')}" class="dateRange widgetFilter" />
			</span>
		</div>
		<div class="row">
			<span class="col-md-4">
				<span class="pull-right">
					{vtranslate('Services', $MODULE_NAME)}
				</span>
			</span>
			<span class="col-md-8">
				<select class="widgetFilter" name="service">
					<option value="">{vtranslate('--None--', $MODULE_NAME)}</option>
					{foreach key=KEY item=ITEM from=$KPILIST}
						<option value="{$KEY}">{$ITEM}</option>
					{/foreach}
				</select>
			</span>
		</div>
		<div class="row">
			<span class="col-md-4">
				<span class="pull-right">
					{vtranslate('Types', $MODULE_NAME)}
				</span>
			</span>
			<span class="col-md-8">
				<select class="widgetFilter" name="type">
					<option value="">{vtranslate('--None--', $MODULE_NAME)}</option>
					{foreach key=KEY item=ITEM from=$KPITYPES}
						<option value="{$KEY}">{$ITEM}</option>
					{/foreach}
				</select>
			</span>
		</div>
	</div>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/KpiContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
{/strip}
