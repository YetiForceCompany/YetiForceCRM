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
			<div class="col-md-12 textAlignRight">
				{if Users_Privileges_Model::isPermitted('Notification', 'CreateView')}
					<a class="btn btn-default btn-xs marginTop5" onclick="Vtiger_Index_Js.sendNotification()">
						<span class="glyphicon glyphicon-plus" title="{vtranslate('LBL_ADD_RECORD')}" alt="{vtranslate('LBL_ADD_RECORD')}"></span>
					</a>
				{/if}
				&nbsp;
				<a class="btn btn-xs btn-default marginTop5" href="index.php?module=Notification&view=List">
					<span class="glyphicon glyphicon-th-list" title="{vtranslate('LBL_GO_TO_RECORDS_LIST')}" alt="{vtranslate('LBL_GO_TO_RECORDS_LIST')}"></span>
				</a>
				<div class="col-xs-6">
					<select class="widgetFilter select2" name="type">
						{foreach from=$TYPES_NOTIFICATION key=KEY item=TYPE}
							<option value="{$KEY}">{$TYPE}</option>
						{/foreach}
					</select>
				</div>
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file="dashboards/NotificationsContents.tpl"|@vtemplate_path:$MODULE_NAME}
	</div>
{/strip}
