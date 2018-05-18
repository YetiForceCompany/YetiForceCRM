{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
* Contributor(s): YetiForce.com
********************************************************************************/
-->*}
{strip}
	<div class="newCustomBlockCopy d-none mb-2 border1px {if $IS_BLOCK_SORTABLE}blockSortable {/if}" data-block-id="" data-sequence="" style="border-radius: 4px; background: white;">
		<div class="row layoutBlockHeader m-0">
			<div class="col-md-6 blockLabel p-2">
				<img class="alignMiddle" src="{\App\Layout::getImagePath('drag.png')}" alt="" />&nbsp;&nbsp;
			</div>
			<div class="col-md-6 ml-0">
				<div class="float-right btn-toolbar blockActions m-1">
					<div class="btn-group">
						<button class="btn btn-success addCustomField d-none" type="button">
							<span class="fa fa-plus u-mr-5px"></span><strong>{App\Language::translate('LBL_ADD_CUSTOM_FIELD', $QUALIFIED_MODULE)}</strong>
						</button>
					</div>
					<div class="btn-group">
						<button class="btn btn-info dropdown-toggle" data-toggle="dropdown">
							<strong>{App\Language::translate('LBL_ACTIONS', $QUALIFIED_MODULE)}</strong>&nbsp;&nbsp;
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu float-right">
							<li class="blockVisibility" data-visible="1" data-block-id="">
								<a class="dropdown-item" href="javascript:void(0)">
									<span class="fas fa-check"></span>&nbsp;{App\Language::translate('LBL_ALWAYS_SHOW', $QUALIFIED_MODULE)}
								</a>
							</li>
							<li class="inActiveFields">
								<a class="dropdown-item" href="javascript:void(0)">{App\Language::translate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}</a>
							</li>
							<li class="deleteCustomBlock">
								<a class="dropdown-item" href="javascript:void(0)">{App\Language::translate('LBL_DELETE_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<div class="blockFieldsList row blockFieldsSortable m-0 p-1" style="min-height: 27px;">
			<ul class="connectedSortable col-md-6 ui-sortable p-1" style="list-style-type: none; float: left;min-height:1px;" name="sortable1"></ul>
			<ul class="connectedSortable col-md-6 ui-sortable m-0 p-1" style="list-style-type: none; float: left;min-height:1px;" name="sortable2"></ul>
		</div>
	</div>
{/strip}
