{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Calendar-RecordPopover">
		<h5 class="c-popover--link__header px-2 py-2 bg-light d-flex align-items-baseline">
			{$RECORD->getDisplayName()}
			<div class="c-popover--link__header__buttons btn-group ml-auto">
				<a class="btn btn-sm btn-outline-secondary js-calendar-popover" href="{$DETAIL_URL}" data-js="click">
					<span title="{\App\Language::translate('LBL_SHOW_COMPLETE_DETAILS', $MODULE_NAME)}" class="fas fa-th-list"></span>
				</a>
				{if $EDIT_URL}
					<a class="btn btn-sm btn-outline-secondary js-calendar-popover" href="{$EDIT_URL}" data-js="click">
						<span title="{\App\Language::translate('LBL_EDIT', $MODULE_NAME)}" class="fas fa-edit"></span>
					</a>
				{/if}
			</div>
		</h5>
		<div class="c-popover--link__body px-2 pb-1">
			{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELDS}
				<div class="u-white-space-nowrap">
					<span class="{if $FIELD_MODEL->isReferenceField()}userIcon-{\App\Record::getType($RECORD->get($FIELD_NAME))}{else}{$FIELDS_ICON[$FIELD_NAME]}{/if}"></span>&nbsp;
					<label class="c-popover--link__label">{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}</label>
					: {$RECORD->getDisplayValue($FIELD_NAME)}
				</div>
			{/foreach}
		</div>
	</div>
{/strip}

