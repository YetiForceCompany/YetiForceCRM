{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="POSTFIX" value=substr($FIELD_MODEL->getName(), -1)}
	{assign var="FIELD_MODEL_CITY_TOWNSHIP" value='addresslevel4'|cat:$POSTFIX}
	{assign var="FIELD_NAME_CITY_DISTRICT" value='addresslevel6'|cat:$POSTFIX}

	{$FIELD_MODEL->getDisplayValue(decode_html($FIELD_MODEL->get('fieldvalue')), $RECORD->getId(), $RECORD)}
	&nbsp;
	{if $RECORD->has($FIELD_MODEL_CITY_TOWNSHIP)}
		{$RECORD->getDisplayValue($FIELD_MODEL_CITY_TOWNSHIP, $RECORD->getId())}
	{/if}
	&nbsp;
	{if $RECORD->has($FIELD_NAME_CITY_DISTRICT)}
		{assign var="LOCAL_VALUE" value=$RECORD->getDisplayValue($FIELD_NAME_CITY_DISTRICT, $RECORD->getId())}
		{if $LOCAL_VALUE !== ''}
			{$LOCAL_VALUE}
		{/if}
	{/if}
{/strip}
