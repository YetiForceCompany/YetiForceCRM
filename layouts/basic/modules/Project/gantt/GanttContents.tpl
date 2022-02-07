{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{include file=\App\Layout::getTemplatePath('gantt/GanttHeader.tpl', $MODULE_NAME)}
	<div class="tpl-Project-gantt-GanntContents c-gantt" data-js="container">
		<input type="hidden" name="projectId" value="{$PROJECTID}">
		<div id="gantt_{$PROJECTID}" class="js-gantt__container" data-js="container"></div>
	</div>
{/strip}
