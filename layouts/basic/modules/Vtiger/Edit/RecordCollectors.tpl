{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-RecordCollectors -->
	{assign var=SHOW_DROPDOWN_BTN value=0}
	{foreach item=COLLECTOR_LINK from=$RECORD_COLLECTOR}
		{assign var=COLLECTOR value=\App\RecordCollector::getInstance($COLLECTOR_LINK->get('linkurl'), $MODULE_NAME)}
		{if !empty($COLLECTOR) && $COLLECTOR->isActive()}
			{if $COLLECTOR_LINK->get('linkicon') eq 1}
				<button type="button" aria-label="{App\Language::translate($COLLECTOR->label, 'Other.RecordCollector')}" class="btn btn-outline-dark js-popover-tooltip js-record-collector-modal ml-1" {if isset(Vtiger_Field_Model::$tabIndexLastSeq)}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}" {/if} data-type={$COLLECTOR_LINK->get('linkurl')} title="{App\Language::translate($COLLECTOR->label, 'Other.RecordCollector')}" {if !empty($COLLECTOR->description)}data-content="{App\Language::translate($COLLECTOR->description, 'Other.RecordCollector')}" {/if} data-js="click|popover">
					<span class="{$COLLECTOR->icon}"></span>
				</button>
			{else}
				{assign var=SHOW_DROPDOWN_BTN value=1}
			{/if}
		{/if}
	{/foreach}
	{if $SHOW_DROPDOWN_BTN}
		<button class="btn btn-outline-dark dropdown-toggle ml-2 {if !empty($SHOW_BTN_LABEL)}js-popover-tooltip{/if}" type="button" id="LBL_RECORD_COLLECTOR" title="{App\Language::translate('LBL_RECORD_COLLECTOR')}" data-toggle="dropdown" aria-expanded="false">
			<span class="yfi-record-collectors mr-2"></span>
			{if empty($SHOW_BTN_LABEL)}{App\Language::translate('LBL_RECORD_COLLECTOR')}{/if}
		</button>
		<div class="dropdown-menu" aria-label="LBL_RECORD_COLLECTOR">
			{foreach item=COLLECTOR_LINK from=$RECORD_COLLECTOR}
				{assign var=COLLECTOR value=\App\RecordCollector::getInstance($COLLECTOR_LINK->get('linkurl'), $MODULE_NAME)}
				{if !empty($COLLECTOR) && $COLLECTOR->isActive() && $COLLECTOR_LINK->get('linkicon') neq 1}
					<a class="dropdown-item js-popover-tooltip js-record-collector-modal px-2" href="#" data-toggle="popover" data-placement="right" data-type={$COLLECTOR_LINK->get('linkurl')} {if !empty($COLLECTOR->description)} data-content="{App\Language::translate($COLLECTOR->description, 'Other.RecordCollector')}" {/if}>
						<span class="{$COLLECTOR->icon} mr-2"></span>
						{App\Language::translate($COLLECTOR->label, 'Other.RecordCollector')}
					</a>
				{/if}
			{/foreach}
		</div>
	{/if}
	<!-- /tpl-Base-RecordCollectors -->
{/strip}
