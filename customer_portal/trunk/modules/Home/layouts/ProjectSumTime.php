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
<div class="col-lg-6 ProjectSumTime">
	<input class="widgetData" type="hidden" value='<?php echo json_encode($data); ?>' />
	<div class="panel panel-default">
		<div class="panel-heading"><?php echo Language::translate("LBL_WIDGET_PROJECTSUMTIME"); ?></div>
		<div class="panel-body" style="height: 380px;">
			<div id="ProjectSumTime" style="height: 350px;"></div>
		</div>
	</div>
</div>
<script>
$(document).ready(function(){
	var widgetsProjectSumTime = $("#ProjectSumTime");
	var ProjectSumTimeData = JSON.parse($(".ProjectSumTime .widgetData").val());
	var ProjectSumTimeChartData = [];
	var ProjectSumTimeURLs = [];
	for(var index in ProjectSumTimeData) {
		var row = ProjectSumTimeData[index];
		var rowData = {name: row.projectname, count: parseFloat(row.count)};
		ProjectSumTimeChartData.push(rowData);
		//ProjectSumTimeURLs.push(row.id: rowData);
	}
    Morris.Bar({
        element: 'ProjectSumTime',
        data: ProjectSumTimeChartData,
        xkey: 'name',
        ykeys: ['count'],
        labels: [''],
        hideHover: 'auto',
        resize: true
    });
});
</script>