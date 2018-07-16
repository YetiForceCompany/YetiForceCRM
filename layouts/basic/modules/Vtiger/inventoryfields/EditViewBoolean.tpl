{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{assign var=VALUE value=$FIELD->getValue($ITEM_VALUE)}
	{assign var='LABEL' value=$FIELD->getDefaultLabel()}
	{if $FIELD->get('label') }
		{assign var='LABEL' value=$FIELD->get('label')}
	{/if}
	<input type="hidden" name="{$FIELD->getColumnName()}{$ROW_NO}" value="0"/>
	<input name="{$FIELD->getColumnName()}{$ROW_NO}" class="form-control {$FIELD->getColumnName()} booleanVal"{' '}
		   title="{\App\Language::translate($LABEL, $MODULE)}" type="checkbox" value="1"{' '}
			{if $FIELD->get('displaytype') == 10}readonly="readonly"{/if} {if $FIELD->getEditValue($VALUE)}checked{/if}/>
{/strip}
