{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-RecordCollectors -->
	{foreach item=COLLECTOR_LINK from=$RECORD_COLLECTOR}
		{assign var=COLLECTOR value=\App\RecordCollector::getInstance($COLLECTOR_LINK->get('linkurl'), $MODULE_NAME)}
		{if !empty($COLLECTOR) && $COLLECTOR->isActive() && $COLLECTOR_LINK->get('linkicon') eq 1}
			<button type="button" aria-label="{App\Language::translate($COLLECTOR->label, 'Other.RecordCollector')}" class="btn btn-outline-dark js-popover-tooltip js-record-collector-modal ml-1" {if isset(Vtiger_Field_Model::$tabIndexLastSeq)}tabindex="{Vtiger_Field_Model::$tabIndexLastSeq}" {/if} data-type={$COLLECTOR_LINK->get('linkurl')} title="{App\Language::translate($COLLECTOR->label, 'Other.RecordCollector')}" {if !empty($COLLECTOR->description)}data-content="{App\Language::translate($COLLECTOR->description, 'Other.RecordCollector')}" {/if} data-js="click|popover">
				<span class="{$COLLECTOR->icon}"></span>
			</button>
		{/if}
	{/foreach}
	<button class="btn btn-outline-dark dropdown-toggle ml-2" type="button" id="{App\Language::translate('LBL_RECORD_COLLECTOR')}" data-toggle="dropdown" aria-expanded="false">
		{App\Language::translate('LBL_RECORD_COLLECTOR')}
	</button>
	<div class="dropdown-menu" aria-label="{App\Language::translate('LBL_RECORD_COLLECTOR')}">
		{foreach item=COLLECTOR_LINK from=$RECORD_COLLECTOR}
			{assign var=COLLECTOR value=\App\RecordCollector::getInstance($COLLECTOR_LINK->get('linkurl'), $MODULE_NAME)}
			{if !empty($COLLECTOR) && $COLLECTOR->isActive() && $COLLECTOR_LINK->get('linkicon') neq 1}
				<a class="dropdown-item js-popover-tooltip js-record-collector-modal" href="#" data-toggle="popover" data-placement="right" data-type={$COLLECTOR_LINK->get('linkurl')} {if !empty($COLLECTOR->description)} data-content="{App\Language::translate($COLLECTOR->description, 'Other.RecordCollector')}" {/if}><span class="{$COLLECTOR->icon} mr-1"></span> {App\Language::translate($COLLECTOR->label, 'Other.RecordCollector')}</a>
			{/if}
		{/foreach}
	</div>
	<!-- /tpl-Base-RecordCollectors -->
{/strip}
