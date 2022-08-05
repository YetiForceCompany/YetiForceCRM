{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-CustomView-AdvCondBody -->
	<div class="c-panel">
		{if !empty($ADVANCED_CONDITIONS['relationColumns']) }
			{assign var=RELATION_COLUMNS value=$ADVANCED_CONDITIONS['relationColumns']}
		{else}
			{assign var=RELATION_COLUMNS value=[]}
		{/if}
		{assign var=HIDE_CUSTOM_RELATION value=!empty($HIDDE_BLOCKS) && empty($RELATION_COLUMNS)}
		<div class="blockHeader c-panel__header py-2 js-toggle-block" data-js="click">
			<span class="js-toggle-icon fas {if $HIDE_CUSTOM_RELATION}fa-chevron-right{else}fa-chevron-down{/if} fa-xs m-1 mt-2 mr-3" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" data-js="container"></span>
			<h5>
				<span class="yfi-relationship-as-custom-column mr-2" aria-hidden="true"></span>
				{\App\Language::translate('LBL_CUSTOM_RELATION_COLUMN','CustomView')}
				<a href="#" class="js-popover-tooltip float-right u-cursor-pointer ml-2" data-placement="top"
					data-content="{\App\Language::translate('LBL_CUSTOM_RELATION_COLUMN_DESC','CustomView')}">
					<span class="fas fa-info-circle"></span>
				</a>
			</h5>
		</div>
		<div class="c-panel__body py-1 {if $HIDE_CUSTOM_RELATION}d-none{/if}">
			{foreach from=$RELATIONS item=RELATION}
				<div class="form-group form-check mb-2">
					<input type="checkbox" class="form-check-input u-ml-minus-5px u-cursor-pointer js-relation-checkbox" value="{$RELATION->getId()}" {if in_array($RELATION->getId(),$RELATION_COLUMNS)}checked="checked" {/if} id="relationCheckbox{$RELATION->getId()}" data-js="value" {if !method_exists($RELATION->getTypeRelationModel(), 'loadAdvancedConditionsByColumns')}disabled="disabled" {/if} />
					<label class=" form-check-label ml-4 u-cursor-pointer" for="relationCheckbox{$RELATION->getId()}">
						<span class="yfm-{$RELATION->getRelationModuleName()} mr-2"></span>
						{\App\Language::translate($RELATION->get('label'),$RELATION->getRelationModuleName())}
					</label>
				</div>
			{/foreach}
		</div>
	</div>
	<div class="c-panel js-toggle-panel" data-js="container">
		{if !empty($ADVANCED_CONDITIONS['relationId']) }
			{assign var=RELATION_ID value=$ADVANCED_CONDITIONS['relationId']}
			{assign var=RELATION_ADVANCE_CRITERIA value=[]}
			{if !empty($ADVANCED_CONDITIONS['relationConditions']) }
				{assign var=RELATION_ADVANCE_CRITERIA value=$ADVANCED_CONDITIONS['relationConditions']}
			{/if}
		{else}
			{assign var=RELATION_ID value=0}
		{/if}
		{assign var=HIDE_CUSTOM_CONDITIONS value=!empty($HIDDE_BLOCKS) && empty($RELATION_ID)}
		<div class="blockHeader c-panel__header py-2 js-toggle-block" data-js="click">
			<span class="js-toggle-icon fas {if $HIDE_CUSTOM_CONDITIONS}fa-chevron-right{else}fa-chevron-down{/if} fa-xs m-1 mt-2 mr-3" data-hide="fas fa-chevron-right" data-show="fas fa-chevron-down" data-js="container"></span>
			<h5>
				<span class="yfi-conditions-for-filtering-related-records mr-2" aria-hidden="true"></span>
				{\App\Language::translate('LBL_CUSTOM_CONDITIONS','CustomView')}
				<a href="#" class="js-popover-tooltip float-right u-cursor-pointer ml-2" data-placement="top"
					data-content="{\App\Language::translate('LBL_CUSTOM_CONDITIONS_DESC','CustomView')}">
					<span class="fas fa-info-circle"></span>
				</a>
			</h5>
		</div>
		<div class="c-panel__body py-1 {if $HIDE_CUSTOM_CONDITIONS}d-none{/if}">
			<div class="col-auto my-1">
				<select class="select2 form-control js-relation-select">
					<option value="0">-</option>
					{foreach from=$RELATIONS item=RELATION}
						{if $RELATION->getId() == $RELATION_ID}
							{assign var=RELATION_MODULE value=$RELATION->getRelationModuleName()}
						{/if}
						{if method_exists($RELATION->getTypeRelationModel(), 'loadAdvancedConditionsByRelationId')}
							<option value="{$RELATION->getId()}" data-module="{$RELATION->getRelationModuleName()}" {if $RELATION->getId() == $RELATION_ID}selected{/if}>
								{\App\Language::translate($RELATION->get('label'),$RELATION->getRelationModuleName())}
							</option>
						{/if}
					{/foreach}
				</select>
			</div>
			<div class="form-group">
				<div class="col-12 js-adv-condition-builder-view" data-js="container">
					{if isset($RELATION_MODULE)}
						{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl', $RELATION_MODULE) SOURCE_MODULE=$RELATION_MODULE RECORD_STRUCTURE_RELATED_MODULES=[] RECORD_STRUCTURE=Vtiger_RecordStructure_Model::getInstanceForModule(\Vtiger_Module_Model::getInstance($RELATION_MODULE))->getStructure() ADVANCE_CRITERIA=$RELATION_ADVANCE_CRITERIA}
					{/if}
				</div>
			</div>
		</div>
	</div>
	<!-- /tpl-Base-CustomView-AdvCondBody -->
{/strip}
