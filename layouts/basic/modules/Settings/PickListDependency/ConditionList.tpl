{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Settings-PickListDependency-ConditionList -->
	<div>
		<div class="m-0">
			{foreach from=$SOURCE_PICKLIST_VALUES item=item key=key name=source_base_loop}
				{assign var=SHOW_BLOCK value=isset($MAPPED_VALUES[$key])}
				<div class="js-toggle-panel c-panel" data-js="click">
					<div class="blockHeader c-panel__header py-2">
						<span class="iconToggle fas {if $SHOW_BLOCK}fa-chevron-down{else}fa-chevron-right{/if} fa-xs m-2" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down">
						</span>
						<h5>
							<span class=" mr-2" aria-hidden="true"></span>
							{\App\Language::translate($item, $SOURCE_MODULE)}
						</h5>
					</div>
					<div class="c-panel__body p-2 js-block-content {if !$SHOW_BLOCK}d-none{/if}">
						<div class="col-12 js-field-container">
							<input type="hidden" name="conditions[{$key}]" class="js-condition-value" value="[]" />
							{assign var=ADVANCE_CRITERIA value=[]}
							{if isset($MAPPED_VALUES[$key])}
								{assign var=ADVANCE_CRITERIA value=\App\Json::decode($MAPPED_VALUES[$key])}
							{/if}
							<div class="js-condition-builder-container">
								{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl', $QUALIFIED_MODULE) MODULE_NAME=$QUALIFIED_MODULE ADVANCE_CRITERIA=$ADVANCE_CRITERIA}
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
	<div class="c-form__action-panel">
		<button class="btn btn-success js-pd-save" type="button">
			<span class="fas fa-check mr-2"></span>
			{\App\Language::translate('LBL_SAVE', $QUALIFIED_MODULE)}
		</button>
		<button class="btn btn-danger ml-2" type="reset" onclick="javascript:window.history.back();">
			<span class="fa fa-times u-mr-5px"></span>
			{\App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}
		</button>
	</div>
	<!-- /tpl-Settings-PickListDependency-ConditionList -->
{/strip}
