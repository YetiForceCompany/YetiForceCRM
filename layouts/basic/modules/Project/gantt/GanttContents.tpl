{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Project-gantt-GanntContents c-gantt" data-js="container">
		<input type="hidden" id="ganttData" value="{\App\Purifier::encodeHtml(\App\Json::encode($DATA))}">
		<div id="c-gantt__container" class="j-gantt" data-js="container"></div>
	</div>
{/strip}
{literal}
	<script>
		$(document).ready(function(){
			setTimeout(()=>{ // wait for all events to end and trigger next one
				app.event.trigger('gantt.view.shown');
			},0);
		});
	</script>
{/literal}
