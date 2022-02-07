{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{if $RECENT_ACTIVITY->getParent()->getModule()->isInventory() && $RECENT_ACTIVITY->getInventoryChanges()}
		{assign var=INVENTORY value=Vtiger_Inventory_Model::getInstance($RECENT_ACTIVITY->getParent()->getModuleName())}
		{assign var=FIELD value=$INVENTORY->getField('name')}
		<div class='font-x-small updateInfoContainer'>
			{foreach item=CHANGES key=KEY from=$RECENT_ACTIVITY->getInventoryChanges()}
				{assign var=SHOW_NAME value=true}
				{assign var=ROW value=['name'=>$CHANGES['item']]}
				{foreach item=CHANGE key=KEY from=$CHANGES['data']}
					{if !$CHANGE['field']->isVisible()}{continue}{/if}
					{if $SHOW_NAME}
						{\App\Language::translate($CHANGES['historyState'], 'ModTracker')} {$FIELD->getDisplayValue($CHANGES['item'])}
						{assign var=SHOW_NAME value=false}
					{/if}
					<div class="ml-2">
						{\App\Language::translate($CHANGE['field']->get('label'), $CHANGE['field']->getModuleName())}:
						{if isset($CHANGE['prevalue'])}
							<strong class="ml-1 mr-1">{$CHANGE['field']->getDisplayValue($CHANGE['prevalue'], $ROW)}</strong>
						{/if}
						{if $CHANGES['historyState'] eq 'LBL_INV_UPDATED'}
							{\App\Language::translate('LBL_TO')}
						{/if}
						{if isset($CHANGE['postvalue'])}
							<strong class="ml-1">{$CHANGE['field']->getDisplayValue($CHANGE['postvalue'], $ROW)}</strong>
						{/if}
					</div>
				{/foreach}
			{/foreach}
		</div>
	{/if}
{/strip}
