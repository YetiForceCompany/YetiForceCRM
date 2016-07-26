{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{include file="Header.tpl"|vtemplate_path:$MODULE}
	<div class="bodyContents" id="centerPanel">
		<div class="mainContainer">
			<div class="contentsDiv col-md-9 marginLeftZero rowContent">
				<div class="widget_header row">
					<div class="col-xs-9 col-sm-6">
						{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
					</div>
					<div class="dashboardHeading col-xs-3 col-sm-5 pull-right marginRight10">
						<div class="notificationsNotice pull-right">
							<div class="btn-group">	
								{if Users_Privileges_Model::isPermitted('Dashboard', 'NotificationCreateMessage')}
									<button type="button" class="btn btn-default sendNotification" title="{vtranslate('LBL_SEND_NOTIFICATION',$MODULE)}">
										<span>{vtranslate('LBL_SEND_NOTIFICATION',$MODULE)}</span>
									</button>
								{/if}	
							</div>
							<div class="btn-group">
								<button class="btn btn-default notificationConf" title="{vtranslate('LBL_NOTIFICATION_SETTINGS', $MODULE)}">
									<span class="glyphicon glyphicon-cog"></span>
								</button>
							</div>
						</div>
					</div>
				</div>
				<div class="notificationContainer marginRight10">
			{/strip}
