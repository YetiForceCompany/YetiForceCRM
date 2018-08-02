{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=LABEL value=''}
	{assign var=TITLE value=''}
	<div class="inviteRow" data-crmid="{$INVITIE['crmid']}" data-ivid="{$INVITIE['inviteesid']}" data-email="{$INVITIE['email']}">
		<div class="input-group input-group-sm">
			<span class="input-group-prepend inviteIcon">
				<span class="input-group-text">
					{if $INVITIE['crmid']}
						{assign var=INVITIE_RECORD value=vtlib\Functions::getCRMRecordMetadata($INVITIE['crmid'])}
						{assign var=LABEL value=$INVITIE_RECORD['label']}
						{assign var=TITLE value=\App\Language::translateSingularModuleName($INVITIE_RECORD['setype'])|cat:': '|cat:$LABEL|cat:' - '|cat:$INVITIE['email']}
						<span class="userIcon-{$INVITIE_RECORD['setype']}"></span>
					{else}
						{assign var=LABEL value=$INVITIE['email']}
						<span class="fas fa-envelope"></span>
					{/if}
				</span>
				<span class="input-group-text inviteName {if $TITLE}js-popover-tooltip{/if}" data-js="popover" data-content="{$TITLE}" style="width: 100px;">{$LABEL}</span>
				<span class="input-group-text inviteStatus">
					{assign var=STATUS_LABEL value=Events_Record_Model::getInvitionStatus($INVITIE['status'])}
					{if $INVITIE['status'] == '1'}
						<span class="fas fa-check-circle js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
					{elseif $INVITIE['status'] == '2'}
						<span class="fas fa-minus-circle js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE)} {if $INVITIE['time']}({DateTimeField::convertToUserFormat($INVITIE['time'])}){/if}"></span>
					{else}
						{assign var=LABEL value=$INVITIE['email']}
						<span class="fas fa-question-circle js-popover-tooltip" data-js="popover" data-placement="top" data-content="{\App\Language::translate($STATUS_LABEL,$MODULE)}"></span>
					{/if}
				</span>
			</span>
			<span class="input-group-append">
				<button class="btn btn-outline-secondary border inviteRemove" type="button">
					<span class="fas fa-times"></span>
				</button>
			</span>
		</div>
	</div>
{/strip}
