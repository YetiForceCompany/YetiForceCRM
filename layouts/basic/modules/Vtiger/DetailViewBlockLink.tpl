{strip}
	{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
	<div class="detailViewBlockLinks">
		{foreach item=BLOCK_MODEL from=$VIEW_MODEL->getBlocks($TYPE_VIEW)}
			{assign var=RELATED_MODULE_NAME value=$BLOCK_MODEL->getRelatedModuleName()}
			<div class="panel panel-default row no-margin detailViewBlockLink" data-url="{$BLOCK_MODEL->getUrl()}" data-reference="{$BLOCK_MODEL->getRelatedModuleName()}" data-count="{AppConfig::relation('SHOW_RECORDS_COUNT')|intval}">
				<div class="panel-heading row blockHeader no-margin">
					<div class="iconCollapse">
						<span class="cursorPointer blockToggle fas fa-angle-right" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id="{$TYPE_VIEW}_{$RELATED_MODULE_NAME}"></span>
						<span class="cursorPointer blockToggle glyphicon glyphicon-menu-down hide" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id="{$TYPE_VIEW}_{$RELATED_MODULE_NAME}"></span>
						<h4>
							<span class="moduleIcon userIcon-{$RELATED_MODULE_NAME}"></span>
							{\App\Language::translate($BLOCK_MODEL->getLabel(),$RELATED_MODULE_NAME)}
							{if AppConfig::relation('SHOW_RECORDS_COUNT')}
								&nbsp;<span class="count badge float-right">0</span>
							{/if}
						</h4>
						<h4 class="float-right">
							<span class="fas fa-link float-right popoverTooltip" data-content="{\App\Language::translate('LBL_RELATED_RECORDS_LIST')}" data-placement="left" aria-hidden="true"></span>
						</h4>
					</div>
				</div>
				<div class="panel-body col-xs-12 blockContent hide"></div>
			</div>
		{/foreach}		
	</div>	
{/strip}
