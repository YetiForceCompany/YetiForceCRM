{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
<!-- tpl-Base-Detail-ProcessWizard -->
<div class="process-line">
	<ul class="nav nav-tabs mt-1 c-process-line">
		{assign var=CLASS value='c-process-line__done'}
		{foreach item=STEP_MAP key=STEP_ID from=$PROCESS_WIZARD->getSteps()}
			{if isset($STEP_MAP['conditionsStatus']) && $STEP_MAP['conditionsStatus']}
				{assign var=CLASS value='c-process-line__next'}
			{elseif $CLASS === 'c-process-line__next'}
				{assign var=CLASS value=''}
			{/if}
			<li class="{$CLASS}{if $STEP['label'] eq $STEP_MAP['label']} active{/if}">
				<a href="{$STEP_URL}{$STEP_ID}">{\App\Language::translate($STEP_MAP['label'], $MODULE_NAME)}</a>
			</li>
		{/foreach}
	</ul>
</div>
{if $PROCESS_WIZARD->checkPermissionsToStep()}
	<div class="process-content mt-2">
		{foreach item=BLOCK_ROW from=$PROCESS_WIZARD->getStepBlocks()}
			{if $BLOCK_ROW['type'] eq 'fields'}
				{include file=\App\Layout::getTemplatePath('Detail/BlockView.tpl', $MODULE_NAME) BLOCK_LABEL_KEY=$BLOCK_ROW['label'] FIELD_MODEL_LIST=$BLOCK_ROW['fieldsStructure']}
			{elseif $BLOCK_ROW['type'] eq 'relatedLists'}
				{assign var=BLOCK_MODEL value=$BLOCK_ROW['relationStructure']}
				{assign var=RELATED_MODULE_NAME value=$BLOCK_MODEL->getRelatedModuleName()}
				<div class="js-toggle-panel c-panel detailViewBlockLink" data-url="{$BLOCK_MODEL->getUrl()}" data-mode="show" data-reference="{$RELATED_MODULE_NAME}">
					<div class="blockHeader c-panel__header">
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-right m-2 d-none" data-js="click" alt="{\App\Language::translate('LBL_EXPAND_BLOCK')}" data-mode="hide" data-id="{$TYPE_VIEW}_{$RELATED_MODULE_NAME}"></span>
						<span class="u-cursor-pointer js-block-toggle fas fa-angle-down m-2" data-js="click" alt="{\App\Language::translate('LBL_COLLAPSE_BLOCK')}" data-mode="show" data-id="{$TYPE_VIEW}_{$RELATED_MODULE_NAME}"></span>
						<h5>
							<span class="moduleIcon yfm-{$RELATED_MODULE_NAME} mr-2"></span>
							{\App\Language::translate($BLOCK_MODEL->getLabel(),$RELATED_MODULE_NAME)}
						</h5>
					</div>
					{if isset($BLOCK_ROW['desc'])}
						<div class="m-2">{$BLOCK_ROW['desc']}</div>
					{/if}
					<div class="blockContent c-panel__body"></div>
				</div>
			{/if}
		{foreachelse}
			<span class="pt-5">&nbsp;</span>
		{/foreach}
	</div>
	<div class="process-actions mt-3 mb-1 text-center">
		{foreach item=LINK from=$PROCESS_WIZARD->getActions()}
			{include file=\App\Layout::getTemplatePath('ButtonLink.tpl', $MODULE_NAME) BUTTON_VIEW='detailViewProcessWizard' BREAKPOINT='md' CLASS='c-btn-link--responsive'}
		{/foreach}
	</div>
{/if}
<!-- /tpl-Base-Detail-ProcessWizard -->
{/strip}
