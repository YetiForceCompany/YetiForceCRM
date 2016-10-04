{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var=LABEL value=''}
	{assign var=TITLE value=''}
	<div class="inviteRow" data-crmid="{$INVITIE['crmid']}" data-ivid="{$INVITIE['inviteesid']}" data-email="{$INVITIE['email']}">
		<div class="input-group input-group-sm">
			<span class="input-group-addon inviteIcon">
				{if $INVITIE['crmid']}
					{assign var=INVITIE_RECORD value=vtlib\Functions::getCRMRecordMetadata($INVITIE['crmid'])}
					{assign var=LABEL value=$INVITIE_RECORD['label']}
					{assign var=TITLE value=Vtiger_Language_Handler::getTranslateSingularModuleName($INVITIE_RECORD['setype'])|cat:': '|cat:$LABEL|cat:' - '|cat:$INVITIE['email']}
					<span class="userIcon-{$INVITIE_RECORD['setype']}" aria-hidden="true"></span>
				{else}
					{assign var=LABEL value=$INVITIE['email']}
					<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>
				{/if}
			</span>
			<span class="input-group-addon inviteName {if $TITLE}popoverTooltip{/if}" data-content="{$TITLE}" style="width: 100px;">{$LABEL}</span>
			<span class="input-group-addon inviteStatus">
				{assign var=STATUS_LABEL value=Events_Record_Model::getInvitionStatus($INVITIE['status'])}
				{if $INVITIE['status'] == '1'}
					<span class="glyphicon glyphicon-ok-sign popoverTooltip" data-placement="top" data-content="{vtranslate($STATUS_LABEL,$MODULE)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}" aria-hidden="true"></span>
				{elseif $INVITIE['status'] == '2'}
					<span class="glyphicon glyphicon-minus-sign popoverTooltip" data-placement="top" data-content="{vtranslate($STATUS_LABEL,$MODULE)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}" aria-hidden="true"></span>
				{else}
					{assign var=LABEL value=$INVITIE['email']}
					<span class="glyphicon glyphicon-question-sign popoverTooltip" data-placement="top" data-content="{vtranslate($STATUS_LABEL,$MODULE)}" aria-hidden="true"></span>
				{/if}
			</span>
			<span class="input-group-btn">
				<button class="btn btn-default inviteRemove" type="button">
					<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
				</button>
			</span>
		</div>
	</div>
{/strip}
