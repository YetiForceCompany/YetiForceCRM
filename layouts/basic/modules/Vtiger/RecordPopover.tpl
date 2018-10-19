{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-RecordPopover">
		<h5 class="">{$RECORD->getDisplayName()}</h5>
		<div class="">
			{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELDS}
				<div class="u-white-space-nowrap">
					<label>{\App\Language::translate($FIELD_MODEL->getFieldLabel(),$MODULE_NAME)}</label>
					: {$RECORD->getDisplayValue($FIELD_NAME)}
				</div>
			{/foreach}
		</div>
	</div>
{/strip}
