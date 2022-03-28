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
	<div class="modal addBlockModal fade tpl-Settings-LayoutEditor-AddBlockModal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5>{App\Language::translate('LBL_ADD_CUSTOM_BLOCK', $QUALIFIED_MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal addCustomBlockForm">
					<div class="modal-body">
						<div class="form-group">
							<div class="col-md-3 col-form-label">
								<span class="redColor">*</span>
								<span>{App\Language::translate('LBL_BLOCK_NAME', $QUALIFIED_MODULE)}</span>
							</div>
							<div class="col-md-8 controls">
								<input type="text" name="label" class="form-control" data-validation-engine="validate[required]" />
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-3 col-form-label">
								{App\Language::translate('LBL_ADD_AFTER', $QUALIFIED_MODULE)}
							</div>
							<div class="col-md-8 controls">
								<select class="form-control" name="beforeBlockId">
									{foreach key=BLOCK_ID item=BLOCK_LABEL from=$ALL_BLOCK_LABELS}
										<option value="{$BLOCK_ID}" data-label="{$BLOCK_LABEL}">{App\Language::translate($BLOCK_LABEL, $SELECTED_MODULE_NAME)}</option>
									{/foreach}
								</select>
							</div>
						</div>
					</div>
					{include file=App\Layout::getTemplatePath('Modals/Footer.tpl', 'Vtiger') BTN_SUCCESS='LBL_SAVE' BTN_DANGER='LBL_CANCEL'}
				</form>
			</div>
		</div>
	</div>
{/strip}
