{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	{assign var="POSTFIX" value=substr($FIELD_MODEL->getName(), -1)}
	{assign var="FIELD_NAME_BUILDING_NUMBER" value='buildingnumber'|cat:$POSTFIX}
	{assign var="FIELD_NAME_LOCAL_NUMBER" value='localnumber'|cat:$POSTFIX}

	{$FIELD_MODEL->getDisplayValue(decode_html($FIELD_MODEL->get('fieldvalue')), $RECORD->getId(), $RECORD)}
	&nbsp;
	{if $RECORD->has($FIELD_NAME_BUILDING_NUMBER)}
		{$RECORD->getDisplayValue($FIELD_NAME_BUILDING_NUMBER, $RECORD->getId())}
	{/if}
	{if $RECORD->has($FIELD_NAME_LOCAL_NUMBER)}
		{assign var="LOCAL_VALUE" value=$RECORD->getDisplayValue($FIELD_NAME_LOCAL_NUMBER, $RECORD->getId())}
		{if $LOCAL_VALUE !== ''}
			&nbsp;/&nbsp;{$LOCAL_VALUE}
		{/if}
	{/if}
{/strip}
