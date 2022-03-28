{*<!--
/*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
************************************************************************************/
-->*}
{strip}
	{assign var=ACCESSIBLE_USERS value=\App\Fields\Owner::getInstance()->getAccessibleUsers()}
	{assign var=ACCESSIBLE_GROUPS value=\App\Fields\Owner::getInstance()->getAccessibleGroups()}
	{assign var=CURRENTUSERID value=$CURRENTUSER->getId()}
	<div class="dashboardWidgetHeader">
		{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeadeAccessible.tpl', $MODULE_NAME)}
	</div>
	<div name="history" class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/AssignedProjectsTasksContents.tpl', $MODULE_NAME) WIDGET=$WIDGET}
	</div>
{/strip}
<script type='text/javascript'>
	jQuery(function() {
		jQuery('.dashboardWidgetContent').off('click', 'a[name="history_more"]');
		jQuery('.dashboardWidgetContent').on('click', 'a[name="history_more"]', function(e) {
			var element = jQuery(e.currentTarget);
			var parent = jQuery(e.delegateTarget).closest('.dashboardWidget');
			jQuery(parent).find('.slimScrollDiv').css('overflow', 'visible');
			var type = parent.find("[name='type']").val();
			var url = element.data('url') + '&content=true&type=' + type;
			AppConnector.request(url).done(function(data) {
				jQuery(parent).find('.dashboardWidgetContent').append(data);
				element.parent().remove();
			});
		});
	});
</script>
