{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var='EXTERNAL_MAIL' value= ($MODE == 'createMail' && $USER_MODEL->internal_mailer == 0)}
	<form class="createNotificationModal sendByAjax validateForm" id="createNotificationModal" name="createNotificationModal" method="post" action="index.php" enctype="multipart/form-data">
		<input type="hidden" name="module" value="{$MODULE}"/>
		<input type="hidden" name="mode" value="{$MODE}"/>
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
							{foreach from=$USER_MODEL->getAccessibleUsers() key=OWNER_ID item=OWNER_NAME}
								{if $USER_MODEL->getId() != $OWNER_ID}
									{if $EXTERNAL_MAIL}
										<option value="{Vtiger_Util_Helper::getUserDetail($OWNER_ID, 'email1')}">{$OWNER_NAME}</option>
									{else}
										<option value="{$OWNER_ID}">{$OWNER_NAME}</option>
									{/if}
								{/if}
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
			{if $EXTERNAL_MAIL}
				<button class="btn btn-success externalMail" type="button" >
					<span class="glyphicon glyphicon-send" aria-hidden="true"></span>&nbsp;&nbsp;
					<strong>{vtranslate('LBL_SEND', $MODULE)}</strong>
				</button>
			{else}
				<button class="btn btn-success" type="submit" name="saveButton">
					<span class="glyphicon glyphicon-send" aria-hidden="true"></span>&nbsp;&nbsp;
					<strong>{vtranslate('LBL_SEND', $MODULE)}</strong>
				</button>
			{/if}
			<button class="btn btn-warning" type="reset" data-dismiss="modal">
				<strong>{vtranslate('LBL_CANCEL', $MODULE)}</strong>
			</button>
		</div>
	</form>
{/strip}
