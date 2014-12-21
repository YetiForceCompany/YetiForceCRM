<?php
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
?>
<div class="col-lg-6 TicketsByStatus">
	<input class="widgetData" type="hidden" value='<?php echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>' />
	<div class="panel panel-default">
		<div class="panel-heading"><?php echo Language::translate("LBL_WIDGET_TICKETSBYSTATUS"); ?></div>
		<div class="panel-body" style="height: 380px;">
			<div id="TicketsByStatus"></div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	var widgetsTicketsByStatus = $("#TicketsByStatus");
	var TicketsByStatusData = JSON.parse($(".TicketsByStatus .widgetData").val());
	var TicketsByStatusChartData = [];
	var TicketsByStatusURLs = [];
	for(var index in TicketsByStatusData) {
		var row = TicketsByStatusData[index];
		var rowData = {name: row.statusvalue, count: parseFloat(row.count)};
		TicketsByStatusChartData.push(rowData);
		//TicketsByStatusURLs.push(row.id: rowData);
	}
    Morris.Bar({
        element: 'TicketsByStatus',
        data: TicketsByStatusChartData,
        xkey: 'name',
        ykeys: ['count'],
        labels: [''],
        hideHover: 'auto',
        resize: true
    });
});
</script>