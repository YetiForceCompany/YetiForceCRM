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
<div class="modelContainer modal fade" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button data-dismiss="modal" class="close" title="{\App\Language::translate('LBL_CLOSE')}">x</button>
				<h3 class="modal-title">{\App\Language::translate('LBL_ADD_CRON', $QUALIFIED_MODULE)}</h3>
			</div>
			<form class="form-horizontal" id="cronJobSaveAjax" method="post" action="index.php">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="action" value="AddCron" />

				<div class="modal-body tabbable">
					<div class="form-group">
						 <label class="col-sm-3 control-label">
							<span class="redColor">*</span> {\App\Language::translate('MODULE', $QUALIFIED_MODULE)}
						</label>
						<div class="col-sm-8 controls ">
							<select class="chzn-select form-control" name="cron_module">
								{foreach from=$MODULE_LIST  item=item key=key}
									<option value="{$item->name}">{\App\Language::translate($item->name, $item->name)}</option>
								{/foreach}

							</select>
						</div>
					</div>
					<div class="form-group">
						 <label class="col-sm-3 control-label"><span class="redColor">*</span> {\App\Language::translate('LBL_NAME',$QUALIFIED_MODULE)}</label>
						<div class="col-sm-8 controls">
							<input value="" name="cron_name" class="form-control" data-validation-engine="validate[required]" id="name" />
						</div>
					</div>
					<div class="form-group">
						 <label class="col-sm-3 control-label"><span class="redColor">*</span> {\App\Language::translate('LBL_PATH_TO_FILE',$QUALIFIED_MODULE)}</label>
						<div class="col-sm-8 controls">
							<input value="" name="path" class="form-control" data-validation-engine="validate[required]" id="path" />
						</div>
					</div>
					<div class="form-group">
						 <label class="col-sm-3 control-label">{\App\Language::translate('LBL_STATUS',$QUALIFIED_MODULE)}</label>
						<div class="col-sm-8 controls">
							<select class="chzn-select form-control" name="status">
								<optgroup>
									<option value="1">{\App\Language::translate('LBL_ACTIVE',$QUALIFIED_MODULE)}</option>
									<option value="0">{\App\Language::translate('LBL_INACTIVE',$QUALIFIED_MODULE)}</option>
								</optgroup>
							</select>
						</div>
					</div>
					<div class="form-group">
						 <label class="col-sm-3 control-label">
							<span class="redColor">*</span> {\App\Language::translate('Frequency',$QUALIFIED_MODULE)}
						</label>
						<div class="controls col-sm-9">

							<div class="col-xs-6 col-sm-3 paddingLRZero">
								<input type="text" class="form-control" value="{$FIELD_VALUE}" id="frequency_value" name="frequency_value" />
							</div>
							<div class="col-xs-6 col-sm-8">
								<select class="chzn-select form-control" id="time_format" name="time_format">
									<optgroup>
										<option value="mins">{\App\Language::translate('LBL_MINUTES',$QUALIFIED_MODULE)}</option>
										<option value="hours">{\App\Language::translate('LBL_HOURS',$QUALIFIED_MODULE)}</option>
									</optgroup>
								</select>
							</div>
						</div>	
					</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">
										{\App\Language::translate('Description',$QUALIFIED_MODULE)}
									</label>
									<div class="col-sm-8 controls">
										<textarea name="description" class="form-control">

										</textarea>
									</div>
								</div>
				</div>
				{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
			</form>
		</div>		
	</div>		
</div>		
{/strip}	
