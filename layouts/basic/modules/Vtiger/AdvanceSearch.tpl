{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
    <div id="advanceSearchContainer" class="modal fade" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<span class="fas fa-th-large mr-1"></span>
						{\App\Language::translate('LBL_ADVANCED_SEARCH',$MODULE)}
					</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
				</div>
				<div class="modal-body">
					<div class="form-group row">
						<label for="searchModuleList" class="col-sm-2 col-form-label">
							<strong class="float-right">{\App\Language::translate('LBL_SEARCH_IN',$MODULE)}</strong>
						</label>
						<div class="col-sm-10">
							<select class="select2 form-control" id="searchModuleList" title="{\App\Language::translate('LBL_SELECT_MODULE')}" data-placeholder="{\App\Language::translate('LBL_SELECT_MODULE')}">
								<option></option>
								{foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
									<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SOURCE_MODULE}selected="selected"{/if}>{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="filterElements" id="searchContainer">
						<form name="advanceFilterForm">
							{if $SOURCE_MODULE eq 'Home'}
								<div class="textAlignCenter">{\App\Language::translate('LBL_PLEASE_SELECT_MODULE',$MODULE)}</div>
							{else}
								<input type="hidden" name="labelFields" data-value='{\App\Json::encode($SOURCE_MODULE_MODEL->getNameFields())}' />
								{include file=\App\Layout::getTemplatePath('AdvanceFilter.tpl')}
							{/if}	
						</form>
					</div>
				</div>
				<div class="actions modal-footer">
					{if $SAVE_FILTER_PERMITTED}
						<div class="col-3 ">
							<input class="zeroOpacity form-control" type="text" title="{\App\Language::translate('LBL_FILTER_NAME')}" value="" name="viewname" placeholder="{\App\Language::translate('LBL_FILTER_NAME')}" />
						</div>
						{if \App\Privilege::isPermitted($SOURCE_MODULE, 'CreateCustomFilter')}
							<button class="btn btn-success" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceIntiateSave">
								<span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_SAVE_AS_FILTER', $MODULE)}</strong>
							</button>
						{/if}
						<button class="btn d-none btn-success" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceSave">
							<span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_SAVE_FILTER', $MODULE)}</strong>
						</button>
					{/if}
					<button class="btn btn-info" id="advanceSearchButton" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if}  type="submit"><span class="fas fa-search mr-1"></span><strong>{\App\Language::translate('LBL_SEARCH', $MODULE)}</strong></button>
					<button class="cancelLink btn btn-danger" role="button" id="advanceSearchCancel" data-dismiss="modal"><span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
				</div>
			</div>
		</div>
	</div>
{/strip}
