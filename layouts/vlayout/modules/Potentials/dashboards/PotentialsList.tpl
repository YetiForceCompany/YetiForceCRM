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
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
<div class="dashboardWidgetHeader potentialsListHeader">
	<table width="100%" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td class="span5">
					<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: 15em;"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
				</td>
				<td class="refresh span1" align="right">
				</td>
				<td class="span4" align="right">
					<input class="switchBtn potentialsListSwitch" type="checkbox" checked="" data-size="small" data-label-width="5" data-handle-width="100" data-on-text="{vtranslate('LBL_OWNER',$MODULE_NAME)}" data-off-text="{vtranslate('LBL_COMMON',$MODULE_NAME)}">
				</td>
				<td class="widgeticons span1" align="right">
					<div class="box pull-right">
						<a href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
							<i class="icon-refresh" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></i>
						</a>
						{if !$WIDGET->isDefault()}
							<a name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
								<i class="icon-remove" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REMOVE')}" alt="{vtranslate('LBL_REMOVE')}"></i>
							</a>
						{/if}
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<div class="dashboardWidgetContent">
	{include file="dashboards/PotentialsListContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
