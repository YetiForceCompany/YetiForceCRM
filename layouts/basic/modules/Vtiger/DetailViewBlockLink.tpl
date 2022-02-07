{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="detailViewBlockLinks">
		{foreach item=BLOCK_MODEL from=$VIEW_MODEL->getBlocks($TYPE_VIEW)}
			{assign var=RELATED_MODULE_NAME value=$BLOCK_MODEL->getRelatedModuleName()}
			<div class="js-toggle-panel c-panel detailViewBlockLink" data-js="click" data-url="{$BLOCK_MODEL->getUrl()}" data-reference="{$BLOCK_MODEL->getRelatedModuleName()}" data-count="{App\Config::relation('SHOW_RECORDS_COUNT')|intval}">
				<div class="blockHeader c-panel__header">
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id="{$TYPE_VIEW}_{$RELATED_MODULE_NAME}"></span>
					<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2 d-none" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id="{$TYPE_VIEW}_{$RELATED_MODULE_NAME}"></span>
					<h5>
						<span class="moduleIcon yfm-{$RELATED_MODULE_NAME}"></span>
						{\App\Language::translate($BLOCK_MODEL->getLabel(),$RELATED_MODULE_NAME)}
						{if App\Config::relation('SHOW_RECORDS_COUNT')}
							&nbsp;<span class="count badge">0</span>
						{/if}
						<span class="fas fa-link js-popover-tooltip ml-2" data-js="popover" data-content="{\App\Language::translate('LBL_RELATED_RECORDS_LIST')}" data-placement="left"></span>
					</h5>
				</div>
				<div class="blockContent c-panel__body d-none"></div>
			</div>
		{/foreach}
	</div>
{/strip}
