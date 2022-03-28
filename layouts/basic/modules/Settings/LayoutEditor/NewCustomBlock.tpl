{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce S.A.
********************************************************************************/
-->*}
{strip}
	<div class="newCustomBlockCopy d-none mb-2 border1px {if !empty($IS_BLOCK_SORTABLE)}blockSortable {/if}" data-block-id="" data-sequence="" style="border-radius: 4px; background: white;">
		<div class="layoutBlockHeader d-flex flex-wrap justify-content-between m-0 p-1 pt-1 w-100">
			<div class="blockLabel u-white-space-nowrap">
				<img class="align-middle" src="{\App\Layout::getImagePath('drag.png')}" alt="" />&nbsp;&nbsp;
			</div>
			<div class="btn-toolbar pl-1" role="toolbar" aria-label="Toolbar with button groups">
				<div class="btn-group btn-group-sm u-h-fit mr-1 mt-1">
					<button class="btn btn-success addCustomField" type="button">
						<span class="fas fa-plus u-mr-5px"></span><strong>{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
					</button>
				</div>
				<div class="btn-group btn-group-sm btn-group-toggle mt-1" data-toggle="buttons">
					<label class="js-block-visibility btn btn-outline-secondary c-btn-collapsible {if $BLOCK_MODEL->isHidden()} active{/if}" data-visible="0"
						data-block-id="{$BLOCK_MODEL->get('id')}" data-js="click | data">
						<input type="radio" name="options" id="options-option1" autocomplete="off" {if $BLOCK_MODEL->isHidden()} checked{/if}>
						<span class="fas fa-fw mr-1 fa-eye-slash"></span>
						<span class="c-btn-collapsible__text">{App\Language::translate('LBL_ALWAYS_HIDE', $QUALIFIED_MODULE)}</span>
					</label>
					<label class="js-block-visibility btn btn-outline-secondary c-btn-collapsible {if !$BLOCK_MODEL->isHidden() && !$BLOCK_MODEL->isDynamic()} active{/if}" data-visible="1"
						data-block-id="{$BLOCK_MODEL->get('id')}" data-js="click | data">
						<input type="radio" name="options" id="options-option2" autocomplete="off" {if !$BLOCK_MODEL->isHidden() && !$BLOCK_MODEL->isDynamic()} checked{/if}>
						<span class="fas fa-fw mr-1 fa-eye"></span>
						<span class="c-btn-collapsible__text">{App\Language::translate('LBL_ALWAYS_SHOW', $QUALIFIED_MODULE)}</span>
					</label>
					<label class="js-block-visibility btn btn-outline-secondary c-btn-collapsible {if $BLOCK_MODEL->isDynamic()} active{/if}" data-visible="2"
						data-block-id="{$BLOCK_MODEL->get('id')}" data-js="click | data">
						<input type="radio" name="options" id="options-option3" autocomplete="off" {if $BLOCK_MODEL->isDynamic()} checked{/if}>
						<span class="fas fa-fw mr-1 fa-atom"></span>
						<span class="c-btn-collapsible__text">{App\Language::translate('LBL_DYNAMIC_SHOW', $QUALIFIED_MODULE)}</span>
					</label>
				</div>
				<div class="btn-group btn-group-sm ml-1 mt-1 u-h-fit" role="group" aria-label="Third group">
					<button class="js-delete-custom-block-btn c-btn-collapsible btn btn-danger js-popover-tooltip" data-js="click">
						<span class="fas fa-trash-alt mr-1"></span>
						<span class="c-btn-collapsible__text">{App\Language::translate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</span>
					</button>
				</div>
			</div>
		</div>
		<div class="blockFieldsList row blockFieldsSortable m-0 p-1" style="min-height: 27px;">
			<ul class="sortTableUl js-sort-table1 connectedSortable col-md-6 ui-sortable p-1" style="list-style-type: none; float: left;min-height:1px;" name="sortable1"></ul>
			<ul class="sortTableUl js-sort-table2 connectedSortable col-md-6 ui-sortable m-0 p-1" style="list-style-type: none; float: left;min-height:1px;" name="sortable2"></ul>
		</div>
	</div>
{/strip}
