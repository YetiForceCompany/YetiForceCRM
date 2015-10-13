{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<strong>{$FIELD->getDisplayValue($ITEM_VALUE)}</strong>
	{if isset($FIELDS[2]['comment'|cat:$ROW_NO])}
		{assign var="COMMENT_FIELD" value=$FIELDS[2]['comment'|cat:$ROW_NO]}
		<br/>
		{$COMMENT_FIELD->getDisplayValue($INVENTORY_ROW[$COMMENT_FIELD->get('columnname')])}
	{/if}
{/strip}
