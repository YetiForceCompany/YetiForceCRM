{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Users-RecordPopover">
		<h5 class="c-popover--link__header px-2 pt-1 bg-light">
			{if $RECORD->getDisplayValue('imagename')}
				<span class="u-w-fit mr-1">{$RECORD->getDisplayValue('imagename')}</span>
			{else}
				<span class="fas fa-user mr-2"></span>
			{/if}
			<span class="mb-1 u-text-ellipsis--no-hover" title="{$RECORD->getDisplayName()}">{$RECORD->getDisplayName()}</span>
			{if $HEADER_LINKS}
				<div class="c-popover--link__header__buttons btn-group">
					{include file=\App\Layout::getTemplatePath('ButtonLinks.tpl', $MODULE_NAME) LINKS=$HEADER_LINKS BUTTON_VIEW='recordPopover' MODULE_NAME=$MODULE_NAME SKIP_GROUP=true}
				</div>
			{/if}
		</h5>
		<div class="c-popover--link__body px-2 pb-1">
			{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELDS}
				<div class="u-white-space-nowrap u-text-ellipsis--no-hover">
					{assign var=ICON value=$FIELD_MODEL->get('icon')}
					{if $ICON}{\App\Layout\Media::getImageHtml($ICON)}
					{else if $FIELD_MODEL->isReferenceField() || isset($FIELDS_ICON[$FIELD_NAME])}
						<span class="mr-1 {if $FIELD_MODEL->isReferenceField()}yfm-{\App\Record::getType($RECORD->get($FIELD_NAME))}{else}{$FIELDS_ICON[$FIELD_NAME]}{/if}"></span>
					{/if}
					<label class="c-popover--link__label">{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}</label>
					: {$RECORD->getDisplayValue($FIELD_NAME)}
				</div>
			{/foreach}
		</div>
	</div>
{/strip}
