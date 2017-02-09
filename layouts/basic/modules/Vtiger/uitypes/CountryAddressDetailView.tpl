{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="POSTFIX" value=substr($FIELD_MODEL->getName(), -1)}
	{assign var="FIELD_MODEL_CITY_STATE" value='addresslevel2'|cat:$POSTFIX}

	{$FIELD_MODEL->getDisplayValue(decode_html($FIELD_MODEL->get('fieldvalue')), $RECORD->getId(), $RECORD)}
	&nbsp;
	{if $RECORD->has($FIELD_MODEL_CITY_STATE)}
		{$RECORD->getDisplayValue($FIELD_MODEL_CITY_STATE, $RECORD->getId())}
	{/if}
{/strip}
