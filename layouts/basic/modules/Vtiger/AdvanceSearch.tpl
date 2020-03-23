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
	<div class="tpl-AdvanceSearch modal fade" tabindex="-1" role="dialog" id="advanceSearchContainer">
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
					<div class="form-group form-row">
						<label for="searchModuleList"
							   class="col-md-12 col-lg-2 pl-2 col-form-label d-flex justify-content-start">
							<strong class="float-right">{\App\Language::translate('LBL_SEARCH_IN',$MODULE)}</strong>
						</label>
						<div class="col-md-12 col-lg-10">
							<select class="select2 form-control" id="searchModuleList"
									title="{\App\Language::translate('LBL_SELECT_MODULE')}"
									data-placeholder="{\App\Language::translate('LBL_SELECT_MODULE')}">
								<option></option>
								{foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
									<option value="{$MODULE_NAME}"
											{if $MODULE_NAME eq $SOURCE_MODULE}selected="selected"{/if}>{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="filterElements" id="searchContainer">
						<form name="advanceFilterForm">
							{if $SOURCE_MODULE eq 'Home'}
								<div class="textAlignCenter">{\App\Language::translate('LBL_PLEASE_SELECT_MODULE',$MODULE)}</div>
							{else}
								{include file=\App\Layout::getTemplatePath('ConditionBuilder.tpl') MODULE_NAME=$SOURCE_MODULE ADVANCE_CRITERIA=[]}
							{/if}
						</form>
					</div>
				</div>
				<div class="actions modal-footer d-flex justify-content-center justify-content-lg-end">
					<div class="form-row w-100 d-flex justify-content-center justify-content-lg-end">
						{if $SAVE_FILTER_PERMITTED}
							<div class="col-md-6 col-lg-2 mb-1 mb-md-2 mb-lg-0 pl-0 pr-0 pr-lg-0 pr-md-1 d-none js-name-filter"
								 data-js="class: .js-name-filter">
								<input class="form-control" type="text"
									   title="{\App\Language::translate('LBL_FILTER_NAME')}" value="" name="viewname"
									   placeholder="{\App\Language::translate('LBL_FILTER_NAME')}"/>
							</div>
							{if \App\Privilege::isPermitted($SOURCE_MODULE, 'CreateCustomFilter')}
								<button class="btn btn-success u-text-ellipsis col-lg-5 mb-1 mb-md-2 mb-lg-0" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if}
										id="advanceIntiateSave">
									<span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_SAVE_AS_FILTER', $MODULE)}</strong>
								</button>
							{/if}
							<div class="d-none ml-lg-2 mb-1 mb-md-2 mb-lg-0 pr-0 pl-0 pl-lg-0 pl-md-1 col-md-6 col-lg-2"
								 id="advanceSave">
								<button class="btn btn-success col-12" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} >
									<span class="fas fa-check mr-1"></span><strong>{\App\Language::translate('LBL_SAVE_FILTER', $MODULE)}</strong>
								</button>
							</div>
						{/if}
						<div class="ml-lg-2 pl-0 pr-0 pr-lg-0 pr-md-1 col-md-6 col-lg-2 mb-1 mb-md-0">
							<button class="btn btn-info col-12"
									id="advanceSearchButton" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if}
									type="submit">
								<span class="fas fa-search mr-1"></span><strong>{\App\Language::translate('LBL_SEARCH', $MODULE)}</strong>
							</button>
						</div>
						<div class="ml-lg-2 pr-0 pl-0 pl-lg-0 pl-md-1 col-md-6 col-lg-2">
							<button class="cancelLink btn btn-danger col-12" role="button" id="advanceSearchCancel"
									data-dismiss="modal">
								<span class="fas fa-times mr-1"></span>{\App\Language::translate('LBL_CANCEL', $MODULE)}
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
{/strip}
