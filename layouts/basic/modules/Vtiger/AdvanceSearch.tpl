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
    <div id="advanceSearchContainer" class="modal fade" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<div class="row no-margin">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close" aria-hidden="true">&times;</button>
						<div class="col-md-5 pushDown">
							<strong class="pull-right">{\App\Language::translate('LBL_SEARCH_IN',$MODULE)}</strong>
						</div>
						<div class="col-md-6">
							<select class="chzn-select form-control" id="searchModuleList" title="{\App\Language::translate('LBL_SELECT_MODULE')}" data-placeholder="{\App\Language::translate('LBL_SELECT_MODULE')}">
								<option></option>
								{foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
									<option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SOURCE_MODULE}selected="selected"{/if}>{\App\Language::translate($MODULE_NAME,$MODULE_NAME)}</option>
								{/foreach}
							</select>
						</div>
					</div>
				</div>
				<div class="modal-body">
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
					<a class="cancelLink pull-right btn btn-warning" type="reset" id="advanceSearchCancel" data-dismiss="modal"><span class="fas fa-times margin-right5px"></span>{\App\Language::translate('LBL_CANCEL', $MODULE)}</a>
					<button class="btn btn-info pull-right" id="advanceSearchButton" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if}  type="submit"><span class="fas fa-search margin-right5px"></span><strong>{\App\Language::translate('LBL_SEARCH', $MODULE)}</strong></button>
					{if $SAVE_FILTER_PERMITTED}
						<button class="btn hide btn-success pull-right" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceSave">
							<span class="glyphicon glyphicon-ok margin-right5px"></span><strong>{\App\Language::translate('LBL_SAVE_FILTER', $MODULE)}</strong>
						</button>
						{if \App\Privilege::isPermitted($SOURCE_MODULE, 'CreateCustomFilter')}
							<button class="btn btn-success pull-right" {if $SOURCE_MODULE eq 'Home'} disabled="" {/if} id="advanceIntiateSave">
								<span class="glyphicon glyphicon-ok margin-right5px"></span><strong>{\App\Language::translate('LBL_SAVE_AS_FILTER', $MODULE)}</strong>
							</button>
						{/if}
						<div class="col-xs-3 pull-right">
							<input class="zeroOpacity float-left form-control" type="text" title="{\App\Language::translate('LBL_FILTER_NAME')}" value="" name="viewname" placeholder="{\App\Language::translate('LBL_FILTER_NAME')}" />
						</div>
					{/if}
				</div>
			</div>
		</div>
	</div>
{/strip}
