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
	<div class="tpl-Detail-Field-Recurrence typeRemoveModal" tabindex="-1">
		<div class="modal fade">
			<div class="modal-dialog modal-lg ">
				<div class="modal-content">
					<div class="modal-header row no-margin">
						<div class="col-12">
							<h5 class="modal-title">{App\Language::translate('LBL_TITLE_TYPE_DELETE', $MODULE)}</h5>
							<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
								<span aria-hidden="true">&times;</span>
							</button>
						</div>
					</div>
					<div class="modal-body row">
						<div class="col-12">
							<div class="col-12 paddingLRZero marginBottom10px">
								<div class="col-4">
									<button class="btn btn-primary btn-sm typeSavingBtn" data-value="2">
										{App\Language::translate('LBL_DELETE_THIS_EVENT', $MODULE)}
									</button>
								</div>
								<div class="col-8">
									{App\Language::translate('LBL_DELETE_THIS_EVENT_DESCRIPTION', $MODULE)}
								</div>
							</div>
							<div class="col-12 paddingLRZero marginBottom10px">
								<div class="col-4">
									<button class="btn btn-primary btn-sm typeSavingBtn" data-value="3">
										{App\Language::translate('LBL_DELETE_FUTURE_EVENTS', $MODULE)}
									</button>
								</div>
								<div class="col-8">
									{App\Language::translate('LBL_DELETE_FUTURE_EVENTS_DESCRIPTION', $MODULE)}
								</div>
							</div>
							<div class="col-12 paddingLRZero marginBottom10px">
								<div class="col-4">
									<button class="btn btn-primary btn-sm typeSavingBtn" data-value="1">
										{App\Language::translate('LBL_DELETE_ALL_EVENTS', $MODULE)}
									</button>
								</div>
								<div class="col-8">
									{App\Language::translate('LBL_DELETE_ALL_EVENTS_DESCRIPTION', $MODULE)}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}
{/strip}
