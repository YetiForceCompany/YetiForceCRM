{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
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
				<li class="{$CLASS}{if isset($STEP) && $STEP['label'] eq $STEP_MAP['label']} active{/if}">
					<a href="{$STEP_URL}{$STEP_ID}">{\App\Language::translate($STEP_MAP['label'], $MODULE_NAME)}</a>
				</li>
			{/foreach}
		</ul>
	</div>
	{if $PROCESS_WIZARD->checkPermissionsToStep()}
		<div class="process-content mt-2">
			{foreach item=BLOCK_ROW from=$PROCESS_WIZARD->getStepBlocks()}
				{if $BLOCK_ROW['type'] eq 'fields'}
					{include file=\App\Layout::getTemplatePath('Detail/BlockView.tpl', $MODULE_NAME) RECORD_STRUCTURE=$RECORD_STRUCTURE BLOCK_LABEL_KEY=$BLOCK_ROW['label'] FIELD_MODEL_LIST=$BLOCK_ROW['fieldsStructure'] BLOCK_ICON=$BLOCK_ROW['icon'] IS_HIDDEN=false IS_DYNAMIC=false}
				{elseif $BLOCK_ROW['type'] eq 'relatedLists' || $BLOCK_ROW['type'] eq 'relatedListsFromReference'}
					{assign var=BLOCK_MODEL value=$BLOCK_ROW['relationStructure']}
					{assign var=RELATED_MODULE_NAME value=$BLOCK_MODEL->getRelatedModuleName()}
					<div class="c-panel detailViewBlockLink" data-url="{$BLOCK_MODEL->getUrl()}" data-mode="show" data-reference="{$RELATED_MODULE_NAME}">
						<div class="blockHeader c-panel__header js-stop-propagation">
							<h5>
								<span class="moduleIcon yfm-{$RELATED_MODULE_NAME} mr-2"></span>
								{\App\Language::translate($BLOCK_MODEL->getLabel(),$RELATED_MODULE_NAME)}
								{if isset($BLOCK_ROW['desc'])}
									<a href="#" class="js-help-info u-cursor-pointer ml-2" title="{\App\Language::translate($BLOCK_MODEL->getLabel(),$RELATED_MODULE_NAME)}" data-placement="top" data-content="{\App\Language::translate($BLOCK_ROW['desc'])}">
										<span class="fas fa-info-circle"></span>
									</a>
								{/if}
							</h5>
						</div>
						<div class="blockContent c-panel__body"></div>
					</div>
				{elseif $BLOCK_ROW['type'] eq 'description'}
					<div class="c-panel" data-mode="show">
						<div class="blockHeader c-panel__header js-stop-propagation">
							<h5>
								{if $BLOCK_ROW['icon']}
									<span class="{$BLOCK_ROW['icon']} mr-2"></span>
								{/if}
								{\App\Language::translate($BLOCK_ROW['label'],$MODULE_NAME)}
							</h5>
						</div>
						<div class="blockContent c-panel__body p-1 pl-2">{$BLOCK_ROW['description']}</div>
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
