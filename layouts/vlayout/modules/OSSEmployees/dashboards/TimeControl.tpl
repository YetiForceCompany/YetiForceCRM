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

<script type="text/javascript">
	
	YetiForce_Bar_Widget_Js('YetiForce_Timecontrol_Widget_Js',{},{
		loadChart : function() {
			var thisInstance = this;
			var chartData = thisInstance.generateData();
			console.log(chartData)
			var options = {
				xaxis: {
					minTickSize: 1,
					ticks: chartData['ticks']
				},
				yaxis: { 
					min: 0 ,
					tickDecimals: 0
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				series: {
					bars: {
						show: true,
						barWidth: 0.8,
						dataLabels: false,
						align: "center",
						lineWidth: 0,
					},
					stack: true
				},
				legend: {
			        show: true,
			        labelFormatter: function(label, series) {
			      		return('<b>'+label+'</b>: '+chartData['legend'][label]+' h');
		       	}
	   		}
		};
			thisInstance.plotInstance = $.plot(thisInstance.getPlotContainer(false), chartData['chartData'], options);
	}
	});
</script>
<style>
#select-user{
	width: 75%;
}
#select-date{
	width: 74%;
}
</style>
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
				<td class="span2">
					<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: 15em;"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</b></div>
				</td>
				<td class="span3">
					<span style="margin-right:4px;">
						<span>
							<i style="margin-top:3px;" class="icon-calendar iconMiddle"></i>
						</span>
					</span>
					<span style="margin-right:4px;" class="">
						<input type="text" name="time" id="select-date" class="dateRange widgetFilter " style="margin-bottom:0;" />
					</span>
			
				</td>
				<td class="span3">
						<span style="margin-right:4px;">
						<span>
							<i style="margin-top:3px;" class="icon-user iconMiddle"></i>
						</span>
					</span>
					
						{assign var=ALL_ACTIVEUSER_LIST value=$CURRENTUSER->getAccessibleUsers()}
						{assign var=LOGGED_USER_ID value=$LOGGEDUSERID}
						<select class="widgetFilter " id="select-user" name="user" style="margin-bottom:0;" >
							<optgroup label="{vtranslate('LBL_USERS')}">
								{foreach key=OWNER_ID item=OWNER_NAME from=$ALL_ACTIVEUSER_LIST}
									<option {if $OWNER_ID eq $LOGGED_USER_ID } selected {/if} value="{$OWNER_ID}">
										{$OWNER_NAME}
									</option>
								{/foreach}
							</optgroup>
						</select>
				</td>	
				<td class="widgeticons span3" align="right">
					<div class="box pull-right">
						<a class="btn" onclick="Vtiger_Header_Js.getInstance().quickCreateModule('OSSTimeControl'); return false;">
							<i class='icon-plus' border='0' title="{vtranslate('LBL_ADD_RECORD')}" alt="{vtranslate('LBL_ADD_RECORD')}"/>
						</a>
						<a class="btn" href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
							<i class="icon-refresh" hspace="2" border="0" align="absmiddle" title="{vtranslate('LBL_REFRESH')}" alt="{vtranslate('LBL_REFRESH')}"></i>
						</a>
						{if !$WIDGET->isDefault()}
							<a class="btn" name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
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
	{include file="dashboards/TimeControlContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
