{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="tpl-Base-QuickDetail-Components-Fields">
		{foreach item=FIELD from=$COMPONENT['fields']}
			{assign var=VALUE value=$RECORD->get($FIELD['fieldName'])}
			{if $VALUE !== ''}
				{assign var=FIELD_MODEL value=$RECORD->getModule()->getFieldByName($FIELD['fieldName'])}
				<div class="row" data-field="{$FIELD['fieldName']}" data-field-type="{$FIELD_MODEL->getFieldDataType()}">
					<div class="col-md-12">
						{if isset($FIELD['icon'])}
							<span class="{$FIELD['icon']} mr-1"></span>
						{elseif $FIELD_MODEL->getFieldDataType() === 'reference'}
							{assign var=RELMOD value=\App\Record::getType($VALUE)}
							<span class="yfm-{$RELMOD} mr-1"></span>
						{/if}
						{if !empty($FIELD['showLabel'])}
							<span class="mr-1">{App\Language::translate($FIELD_MODEL->getFieldLabel(), $RECORD->getModuleName())}: </span>
						{/if}
						{if isset($FIELD['icon'])}
							{$FIELD_MODEL->getDisplayValue($VALUE, $RECORD->getId(), $RECORD)}
						{else}
							{$FIELD_MODEL->getDisplayValue($VALUE, $RECORD->getId(), $RECORD)}
						{/if}
					</div>
				</div>
			{/if}
		{/foreach}
	</div>
{/strip}
