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
	<div id="moveReportsContainer" class='modelContainer modal fade' tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{\App\Language::translate('LBL_MOVE_REPORT', $MODULE)}</h5>
					<button type="button" class="close" data-dismiss="modal" title="{\App\Language::translate('LBL_CLOSE')}">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form class="form-horizontal contentsBackground" id="moveReports" method="post" action="index.php">
					<input type="hidden" name="module" value="{$MODULE}" />
					<input type="hidden" name="action" value="MoveReports" />
					<input type="hidden" name="selected_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($SELECTED_IDS))}" />
					<input type="hidden" name="excluded_ids" value="{\App\Purifier::encodeHtml(\App\Json::encode($EXCLUDED_IDS))}" />
					<input type="hidden" name="viewname" value="{$VIEWNAME}" />
					<div class="modal-body">
						<div class="row verticalBottomSpacing">
							<span class="col-md-4">{\App\Language::translate('LBL_FOLDERS_LIST', $MODULE)}<span class="redColor">*</span></span>
							<span class="col-md-8 row">
								<select class="chzn-select col-md-11 form-control" name="folderid">
									<optgroup label="{\App\Language::translate('LBL_FOLDERS', $MODULE)}">
										{foreach item=FOLDER from=$FOLDERS}
											<option value="{$FOLDER->getId()}">{\App\Language::translate($FOLDER->getName(), $MODULE)}</option>
										{/foreach}
									</optgroup>
								</select>
							</span>
						</div>
					</div>
					{include file=\App\Layout::getTemplatePath('Modals/Footer.tpl', $MODULE)}
				</form>
			</div>
		</div>
	</div>
{/strip}
