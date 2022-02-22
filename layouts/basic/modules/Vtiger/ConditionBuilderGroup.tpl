{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<!-- tpl-Base-ConditionBuilderGroup -->
	<div class="c-condition-builder__group pt-2 js-condition-builder-group-container">
		<div class="btn-group btn-group-toggle js-condition-switch mr-2" data-toggle="buttons">
			{assign var=GROUP_OPTION_ACTIVE value=empty($CONDITIONS_GROUP['condition']) || ($CONDITIONS_GROUP['condition'] eq 'AND')}
			<label class="btn btn-sm btn-outline-primary js-condition-switch-value {if $GROUP_OPTION_ACTIVE} active {/if}">
				<input type="radio" autocomplete="off" {if $GROUP_OPTION_ACTIVE} checked {/if}>
				AND
			</label>
			{assign var=GROUP_OPTION_ACTIVE value=!empty($CONDITIONS_GROUP['condition']) && $CONDITIONS_GROUP['condition'] eq 'OR'}
			<label class="btn btn-sm btn-outline-primary {if $GROUP_OPTION_ACTIVE} active {/if}">
				<input type="radio" autocomplete="off" {if $GROUP_OPTION_ACTIVE} checked {/if}>
				OR
			</label>
		</div>
		<div class="btn-group btn-group-toggle">
			<button type="button" class="btn btn-sm btn-success js-condition-add" data-js="click">
				<span class="yfi yfi-users-2 mr-1"></span>{\App\Language::translate('LBL_ADD_CONDITION',$MODULE_NAME)}
			</button>
			<button type="button" class="btn btn-sm c-btn-light-green js-group-add" data-js="click">
				<span class="adminIcon-groups mr-1"></span>{\App\Language::translate('LBL_ADD_CONDITION_GROUP',$MODULE_NAME)}
			</button>
			{if empty($ROOT_ITEM)}
				<button type="button" class="btn btn-sm btn-danger js-group-delete" data-js="click">
					<span class="fa fa-trash"></span>
				</button>
			{/if}
		</div>
		<div class="js-condition-builder-conditions-container">
			{if !empty($CONDITIONS_GROUP['condition']) && !empty($CONDITIONS_GROUP['rules'])}
				{foreach from=$CONDITIONS_GROUP['rules'] item=CONDITION_ITEM}
					{if isset($CONDITION_ITEM['condition'])}
						{include file=\App\Layout::getTemplatePath('ConditionBuilderGroup.tpl', $MODULE_NAME) CONDITIONS_GROUP=$CONDITION_ITEM ROOT_ITEM=false}
					{else}
						{include file=\App\Layout::getTemplatePath('ConditionBuilderRow.tpl', $MODULE_NAME) CONDITIONS_ROW=$CONDITION_ITEM }
					{/if}
				{/foreach}
			{/if}
		</div>
	</div>
	<!-- /tpl-Base-ConditionBuilderGroup -->
{/strip}
