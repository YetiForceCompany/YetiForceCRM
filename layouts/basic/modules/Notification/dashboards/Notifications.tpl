{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters justify-content-end">
			<div class="col-ceq-xsm-6">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="input-group-text">
							<span class="fas fa-bell fa-fw"></span>
						</span>
					</div>
					<select class="widgetFilter form-control select2" aria-label="Small" aria-describedby="inputGroup-sizing-sm" name="type">
						{foreach from=$TYPES_NOTIFICATION key=KEY item=TYPE}
							<option value="{\App\Purifier::encodeHtml($KEY)}">{\App\Purifier::encodeHtml($TYPE)}</option>
						{/foreach}
					</select>
				</div>
			</div>
			<div class="col-ceq-xsm-6">
				<div class="btn-toolbar float-right">
					{if \App\Privilege::isPermitted('Notification', 'CreateView')}
						<button type="button" class="btn btn-sm btn-light" onclick="Vtiger_Index_Js.sendNotification()" title="{\App\Language::translate('LBL_ADD_RECORD')}" alt="{\App\Language::translate('LBL_ADD_RECORD')}">
							<span class="fas fa-plus"></span>
						</button>
					{/if}
					<a class="btn btn-sm btn-light" href="index.php?module=Notification&view=List" title="{\App\Language::translate('LBL_GO_TO_RECORDS_LIST')}"
						alt="{\App\Language::translate('LBL_GO_TO_RECORDS_LIST')}">
						<span class="fas fa-th-list"></span>
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/NotificationsContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
