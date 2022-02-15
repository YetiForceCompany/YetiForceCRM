{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-RecordPopover -->
	<div>
		<h5 class="c-popover--link__header px-2 py-1 mb-0 bg-light">
			{assign var=IMAGE value=$RECORD->getImage()}
			{if $IMAGE}
				<img class="rounded-circle u-max-w-40px" data-js="click" title="{$RECORD->getName()}" src="{$IMAGE['url']}">
			{else}
				<span class="yfm-{$MODULE_NAME} mr-1"></span>
			{/if}
			<span class="u-text-ellipsis--no-hover mr-2" title="{$RECORD->getDisplayName()}">{$RECORD->getDisplayName()}</span>
			{assign var=RECORD_STATE value=\App\Record::getState($RECORD->getId())}
			{if $RECORD_STATE && $RECORD_STATE !== 'Active'}
				{assign var=COLOR value=App\Config::search('LIST_ENTITY_STATE_COLOR')}
				<span class="badge badge-secondary ml-1 mr-1 float-right" {if $COLOR[$RECORD_STATE]}style="background-color: {$COLOR[$RECORD_STATE]};" {/if}>
					{if \App\Record::getState($RECORD->getId()) === 'Trash'}
						<span class="fas fa-trash-alt mr-2"></span>
						{\App\Language::translate('LBL_ENTITY_STATE_TRASH')}
					{else}
						<span class="fas fa-archive mr-2"></span>
						{\App\Language::translate('LBL_ENTITY_STATE_ARCHIVED')}
					{/if}
				</span>
			{/if}
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
					{assign var=ICON value=$FIELD_MODEL->getIcon('RecordPopover')}
					{if isset($ICON['name'])}
						<span class="{$ICON['name']} mr-1"></span>
					{else if $FIELD_MODEL->isReferenceField() || isset($FIELDS_ICON[$FIELD_NAME])}
						<span class="mr-1 {if $FIELD_MODEL->isReferenceField()}yfm-{\App\Record::getType($RECORD->get($FIELD_NAME))}{else}{$FIELDS_ICON[$FIELD_NAME]}{/if}"></span>
					{/if}
					<label class="c-popover--link__label">{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}</label>
					: {$RECORD->getDisplayValue($FIELD_NAME)}
				</div>
			{/foreach}
		</div>
	</div>
	<!-- /tpl-Base-RecordPopover -->
{/strip}
