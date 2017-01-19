{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} -->*}
{strip}
	<div class="dashboardWidgetHeader">
		<div class="row">
			<div class="col-md-8">
				<div class="dashboardTitle textOverflowEllipsis" title="{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}"><strong>{vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</strong></div>
			</div>
			<div class="col-md-4">
				<div class="box pull-right">
					{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
				</div>
			</div>
		</div>
		<hr class="widgetHr"/>
		<div class="row" >
			<div class="col-xs-6">
				<select class="widgetFilter select2" name="type">
					{foreach from=$TYPES_NOTIFICATION key=KEY item=TYPE}
						<option value="{$KEY}">{$TYPE}</option>
					{/foreach}
				</select>
			</div>
			<div class="col-xs-6">
				<div class="btn-toolbar pull-right">
					{if \App\Privilege::isPermitted('Notification', 'CreateView')}
						<button type="button" class="btn btn-default" onclick="Vtiger_Index_Js.sendNotification()">
							<span class="glyphicon glyphicon-plus" title="{vtranslate('LBL_ADD_RECORD')}" alt="{vtranslate('LBL_ADD_RECORD')}"></span>
						</button>
					{/if}
					<button type="button"  class="btn btn-default" href="index.php?module=Notification&view=List">
						<span class="glyphicon glyphicon-th-list" title="{vtranslate('LBL_GO_TO_RECORDS_LIST')}" alt="{vtranslate('LBL_GO_TO_RECORDS_LIST')}"></span>
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file="dashboards/NotificationsContents.tpl"|@vtemplate_path:$MODULE_NAME}
	</div>
{/strip}
