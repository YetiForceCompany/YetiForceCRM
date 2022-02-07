{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Detail-Field-Text -->
	{assign var=SIZE value='mini'}
	{if $SOURCE_TPL eq 'BlockView'}
		{assign var=SIZE value='medium'}
	{/if}
	<div class="u-paragraph-m-0 u-word-break">
		{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'), $RECORD->getId(), $RECORD, false, $SIZE)}
	</div>
	<!-- /tpl-Detail-Field-Text -->
{/strip}
