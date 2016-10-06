{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<form class="createNotificationModal sendByAjax validateForm" id="createNotificationModal" name="createNotificationModal" method="post" action="index.php" enctype="multipart/form-data">
		<input type="hidden" name="module" value="{$MODULE}"/>
		<input type="hidden" name="mode" value="createMessage"/>
		<input type="hidden" name="action" value="Notification"/>
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3 id="massEditHeader" class="modal-title">{vtranslate('LBL_CREATING_NOTIFICATION', $MODULE)}</h3>
		</div>
		<div class="modal-body col-md-12">
			<div class="well">
				<div class="form-group">
					<label for="notificationUsers">{vtranslate('LBL_USERS', $MODULE)}</label>
					<div class="clearfix">
						<select class="chzn-select form-control" id="notificationUsers" name="users" data-validation-engine="validate[required]" multiple>
							{foreach from=$USERS key=OWNER_ID item=OWNER_NAME}
								<option value="{$OWNER_ID}" data-mail="{Vtiger_Util_Helper::getUserDetail($OWNER_ID, 'email1')}">
									{$OWNER_NAME}
								</option>
							{/foreach}
						</select>
					</div>
				</div>
				<div class="form-group">
					<label for="notificationTitle">{vtranslate('LBL_TITLE', $MODULE)}</label>
					<input type="text" name="title" class="form-control" id="notificationTitle" data-validation-engine="validate[required]">
				</div>
				<div class="form-group">
					<label for="notificationMessage">{vtranslate('LBL_MESSAGE', $MODULE)}</label>
					<textarea class="form-control messageContent" name="message" rows="5" id="notificationMessage" data-validation-engine="validate[required]" aria-describedby="notificationMessage"></textarea>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			{if Users_Privileges_Model::isPermitted('Dashboard', 'NotificationCreateMessage')}
				<button class="btn btn-success" type="submit" name="saveButton" data-mode="createMessage">
					<span class="glyphicon glyphicon-send" aria-hidden="true"></span>&nbsp;&nbsp;
					<strong>{vtranslate('LBL_SEND_NOTIFICATION_MESSAGE', $MODULE)}</strong>
				</button>
			{/if}
			{if Users_Privileges_Model::isPermitted('Dashboard', 'NotificationCreateMail') && AppConfig::main('isActiveSendingMails') && Users_Privileges_Model::isPermitted('OSSMail')}
				{if $USER_MODEL->internal_mailer == 0}
					<a class="btn btn-success externalMail" href="mailto:">
						<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>&nbsp;&nbsp;
						<strong>{vtranslate('LBL_SEND_NOTIFICATION_MAIL', $MODULE)}</strong>
					</a>
				{/if}
				{if $USER_MODEL->internal_mailer != 0 && Emails_Record_Model::checkSendMailStatus()}
					<button class="btn btn-success" type="submit" name="saveButton" data-mode="createMail">
						<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>&nbsp;&nbsp;
						<strong>{vtranslate('LBL_SEND_NOTIFICATION_MAIL', $MODULE)}</strong>
					</button>
				{/if}
			{/if}
			<button class="btn btn-warning" type="reset" data-dismiss="modal">
				<strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong>
			</button>
		</div>
	</form>
{/strip}
