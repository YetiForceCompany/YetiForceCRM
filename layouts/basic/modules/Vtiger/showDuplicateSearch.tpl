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
	<div class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header contentsBackground">
					<span class="modal-title h5">{\App\Language::translate('LBL_MERGING_CRITERIA_SELECTION', $MODULE)}</span>
					<button data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE')}"><span class="fas fa-times"></span></button>
				</div>
				<form class="form-horizontal" id="findDuplicate" action="index.php" method="POST">
					<input type='hidden' name='module' value='{$MODULE}' />
					<input type='hidden' name='view' value='FindDuplicates' />
					<br />
					<div class="form-group form-row px-3">
						<div class="col-sm-3 col-form-label">
							{\App\Language::translate('LBL_AVAILABLE_FIELDS', $MODULE)}
						</div>
						<div class="col-sm-6 controls form-row">
							<div class="col-md-10">
								<select id="fieldList" class="select2 form-control" multiple="true" title="{\App\Language::translate('LBL_AVAILABLE_FIELDS', $MODULE)}" name="fields[]"
										data-validation-engine="validate[required]">
									{foreach from=$FIELDS item=FIELD}
										{if $FIELD->isViewableInDetailView()}
											<option value="{$FIELD->getName()}">{\App\Language::translate($FIELD->get('label'), $MODULE)}</option>
										{/if}
									{/foreach}
								</select>
							</div>
							<div class="col-md-10">
								<label class="form-row m-0 pt-1"><input type="checkbox" name="ignoreEmpty" title="{\App\Language::translate('LBL_IGNORE_EMPTY_VALUES', $MODULE)}" checked /><span class="alignMiddle pl-1">{\App\Language::translate('LBL_IGNORE_EMPTY_VALUES', $MODULE)}</span></label>
							</div>
							<br />
						</div>
					</div>
					<div class="modal-footer">
						<div class="float-right cancelLinkContainer">
							<button class="cancelLink btn btn-warning" type="reset" data-dismiss="modal" data-dismiss="modal">{\App\Language::translate('LBL_CANCEL', $MODULE)}</button>
						</div>
						<button class="btn btn-success" type="submit" disabled="true">
							<strong>{\App\Language::translate('LBL_FIND_DUPLICATES', $MODULE)}</strong>
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>
{/strip}
