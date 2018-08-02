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
	<div class="modal inactiveFieldsModal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{App\Language::translate('LBL_INACTIVE_FIELDS', $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal inactiveFieldsForm" method="POST">
					<div class="modal-body">
						<div class="row inActiveList"></div>
					</div>
					<div class="modal-footer">
						<div class="float-right cancelLinkContainer">
							<a class="cancelLink btn btn-warning" type="reset" data-dismiss="modal">{App\Language::translate('LBL_CANCEL', $QUALIFIED_MODULE)}</a>
						</div>
						<button class="btn btn-success" type="submit" name="reactivateButton">
							<strong>{App\Language::translate('LBL_REACTIVATE', $QUALIFIED_MODULE)}</strong>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
