{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-Detail-HeaderValues -->
	{function SHOW_HEADER_FIELD_VALUE}
		{if !$RECORD->isEmpty($FIELD_MODEL->getName())}
			{assign var=VALUE value=$RECORD->getDisplayValue($FIELD_MODEL->getName())}
			{if $FIELD_MODEL->isReferenceField() && $FIELD_MODEL->getHeaderValue('rel_fields', []) && \App\Record::isExists($RECORD->get($FIELD_MODEL->getName()))}
				{assign var=REL_RECORD_MODEL value=Vtiger_Record_Model::getInstanceById($RECORD->get($FIELD_MODEL->getName()))}
				{assign var=REL_VALUES value=[]}
				{foreach from=$FIELD_MODEL->getHeaderValue('rel_fields', []) item=REL_FIELD_NAME}
					{if !$REL_RECORD_MODEL->isEmpty($REL_FIELD_NAME) && $REL_RECORD_MODEL->getField($REL_FIELD_NAME)->isViewableInDetailView()}
						{append var='REL_VALUES' value="<span class='u-fs-xs'>{$REL_RECORD_MODEL->getDisplayValue($REL_FIELD_NAME)}</span>" index=$REL_FIELD_NAME}
					{/if}
				{/foreach}
				{if $REL_VALUES}
					{assign var=VALUE value=$VALUE|cat:' ('|cat:implode(', ',$REL_VALUES)|cat:')'}
				{/if}
			{/if}
			<div class="js-popover-tooltip--ellipsis-icon d-flex flex-nowrap align-items-center" data-content="{\App\Purifier::encodeHtml($VALUE)}" data-toggle="popover" data-js="popover | mouseenter">
				<span class="mr-1 text-muted u-white-space-nowrap">
					{\App\Language::translate($FIELD_MODEL->getFieldLabel(), $MODULE_NAME)}:
				</span>
				<span class="js-popover-text" data-js="clone">{$VALUE}</span>
				<span class="fas fa-info-circle fa-sm js-popover-icon d-none" data-js="class: d-none"></span>
			</div>
		{/if}
	{/function}
	{if !empty($FIELDS_HEADER['value'])}
		{foreach from=$FIELDS_HEADER['value'] key=NAME item=FIELD_MODEL}
			{SHOW_HEADER_FIELD_VALUE}
		{/foreach}
	{else}
		{assign var=FIELD_MODEL value=$RECORD->getField('assigned_user_id')}
		{if $FIELD_MODEL && $FIELD_MODEL->isViewableInDetailView()}
			{SHOW_HEADER_FIELD_VALUE}
		{/if}
		{assign var=FIELD_MODEL value=$RECORD->getField('shownerid')}
		{if $FIELD_MODEL && $FIELD_MODEL->isViewableInDetailView()}
			{SHOW_HEADER_FIELD_VALUE}
		{/if}
	{/if}
	<!-- /tpl-Base-Detail-HeaderValues -->
{/strip}
