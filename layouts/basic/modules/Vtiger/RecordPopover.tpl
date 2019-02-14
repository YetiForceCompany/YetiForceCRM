{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-RecordPopover">
		<h5 class="c-popover--link__header px-2 py-1 bg-light">
			<span class="userIcon-{$MODULE_NAME} mr-1"></span>
			<span class="u-text-ellipsis--no-hover" title="{$RECORD->getDisplayName()}">{$RECORD->getDisplayName()}</span>
			{if $HEADER_LINKS}
				<div class="c-popover--link__header__buttons btn-group">
					{foreach item=LINK from=$HEADER_LINKS}
						{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='recordPopover'}
					{/foreach}
				</div>
			{/if}
		</h5>
		<div class="c-popover--link__body px-2 pb-1">
			{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELDS}
				<div class="u-white-space-nowrap u-text-ellipsis--no-hover">
					{if $FIELD_MODEL->isReferenceField() || isset($FIELDS_ICON[$FIELD_NAME])}
						<span class="{if $FIELD_MODEL->isReferenceField()}userIcon-{\App\Record::getType($RECORD->get($FIELD_NAME))}{else}{$FIELDS_ICON[$FIELD_NAME]}{/if}"></span>
						&nbsp;
					{/if}
					<label class="c-popover--link__label">{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}</label>
					: {$RECORD->getDisplayValue($FIELD_NAME)}
				</div>
			{/foreach}
		</div>
	</div>
{/strip}
