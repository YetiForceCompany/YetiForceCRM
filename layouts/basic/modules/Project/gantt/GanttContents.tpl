{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div id="c-detail-gantt" class="j-gantt" data-js="container"></div>
{literal}
<script>
	let ganttData = {/literal}{$DATA}{literal};
	$(document).ready(function(){
		App.Fields.Gantt.register('#c-detail-gantt', ganttData);
	});
</script>
{/literal}
