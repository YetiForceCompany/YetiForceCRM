{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
<div class="dashboardWidgetHeader">
	{include file="dashboards/WidgetHeader.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
<div class='dashboardWidgetContent' id="openTicketdWidgetContent" style="height:85%">
	{include file="dashboards/OpenTicketsContents.tpl"|@vtemplate_path:$MODULE_NAME}
</div>
<style>
#openTicketContainer{
	display:block;
	width:60%;
} 
#legend p{
	margin-bottom: 1px;
}
</style>

<script type="text/javascript">
	Vtiger_OpenTicketPieChart_Widget_Js('Vtiger_Opentickets_Widget_Js',{},{		 
		generateData : function() {
			this.preLoadWidget();
			var container = this.getContainer();		
			var jData = container.find('.widgetData').val();
			var data = JSON.parse(jData);
			var chartData = [];
			var links = [];
			for(var index in data) {
				var row = data[index];
				{literal}
				var rowData = {'value' : parseInt(row[0]),  'color' : row.color};
				var link = {'color' : row.color, 'link' : row.links};
				{/literal}
				chartData.push(rowData);
				links.push(link);
			}

			return {literal}{'chartData':chartData, 'links':links}{/literal};
		},
		preLoadWidget: function(){
			var container = this.getContainer();
			var h = container.find('#openTicketdWidgetContent').height();
			var newHeight = h * 90  / 100;
			container.find('#openTicketContainer').css('height', newHeight);

			var c = container.find('#openTicketsChart');
			var ct = c.get(0).getContext('2d');
			var container = $(c).parent();
	 
			$(window).resize(respondCanvas);

			function respondCanvas(){ 
				c.attr('width', $(container).width()); 
				c.attr('height', $(container).height()); 
			}	
			respondCanvas();
		}
	});
</script>