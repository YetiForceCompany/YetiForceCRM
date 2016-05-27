{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{include file="Header.tpl"|vtemplate_path:$MODULE}
	<div class="bodyContents">
		<div class="mainContainer">
			<div class="contentsDiv col-md-12 marginLeftZero">
				<div class="widget_header row">
					<div class="col-xs-9 col-sm-6">
						{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
					</div>
					<div class="dashboardHeading col-xs-3 col-sm-6">
						<div class="notificationsNotice pull-right">
							<div class="btn-group">
								<button class="btn btn-default notificationConf" title="{vtranslate('LBL_NOTIFICATION_SETTINGS', $MODULE)}">
									<span class="glyphicon glyphicon-cog"></span>
								</button>
								{if Users_Privileges_Model::isPermitted('Dashboard', 'NotificationCreateMessage')}
									<button type="button" class="btn btn-default sendNotification" title="{vtranslate('LBL_SEND_NOTIFICATION',$MODULE)}">
										<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
									</button>
								{/if}	
							</div>
						</div>
					</div>
				</div>
			{/strip}
