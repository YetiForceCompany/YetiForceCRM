{*<!--
/*+***********************************************************************************************************************************
* The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
* in compliance with the License.
* Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
* See the License for the specific language governing rights and limitations under the License.
* The Original Code is YetiForce.
* The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
* All Rights Reserved.
*************************************************************************************************************************************/
-->*}
{strip}
	<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-tab.js"></script>
	<style>
		.table tbody tr.error > td {
			background-color: #f2dede;
		}
	</style>
	<div class='widget_header row '>
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<ul id="tabs" class="nav nav-tabs" data-tabs="tabs" style="margin: 0px;">
		<li class="active"><a href="#tab_rc_config" data-toggle="tab">{vtranslate('Roundcube config', 'OSSMail')}</a></li>
	</ul>
	<div id="my-tab-content" class="tab-content" style="margin: 0 20px;" >
		<div class="editViewContainer tab-pane active" id="tab_rc_config">
			{if Settings_ModuleManager_Library_Model::checkLibrary('roundcube')}
				<br>
				<div class="alert alert-danger" role="alert">
					<div>
						<h4>{vtranslate('ERR_NO_REQUIRED_LIBRARY', 'Settings:Vtiger','roundcube')}</h4>
					</div>
				</div>
			{elseif !\App\Module::isModuleActive('OSSMail')}	
				<br>
				<div class="alert alert-danger" role="alert">
					<div>
						<h4>{vtranslate('ERR_NO_MODULE_IS_INACTIVE', $QUALIFIED_MODULE)}</h4>
					</div>
				</div>
			{else}
				<form id="RCConfigEditorForm" class="form-horizontal">
					{assign var=FIELD_DATA value=$RecordModel->getViewableData()}
					<br>
					{foreach key=FIELD_NAME item=FIELD_DETAILS from=$RecordModel->getEditableFields()}
						<div class="row marginBottom10px">
							<div class="row col-md-3">
								<label class="muted ">{vtranslate($FIELD_DETAILS['label'], 'OSSMail')}</label></td>
							</div>
							<div class="col-md-9">
								{if $FIELD_DETAILS['fieldType'] == 'picklist'}
									<div class=" row col-sm-8">
										<select class="select2 form-control" name="{$FIELD_NAME}">
											{foreach item=row from=$FIELD_DETAILS['value']}
												<option value="{$row}" {if $row == $FIELD_DATA[$FIELD_NAME]} selected {/if}>
													{if $FIELD_NAME != 'language'}
														{vtranslate($FIELD_NAME|cat:'_'|cat:$row, 'OSSMail')}
													{else}
														{$row}
													{/if}
												</option>
											{/foreach}
										</select>
									</div>
								{else if $FIELD_DETAILS['fieldType'] == 'multipicklist'}
									<div class="row col-md-8">
										<select class="form-control" name="{$FIELD_NAME}" multiple>
											{foreach item=ITEM key=KEY from=$FIELD_DATA[$FIELD_NAME]}
												<option value="{$KEY}" selected>{$KEY}</option>
											{/foreach}
										</select>
									</div>
								{else if $FIELD_DETAILS['fieldType'] == 'checkbox'}
									<div class=" row col-sm-8">
										<input type="hidden" name="{$FIELD_NAME}" value="false" />
										<input type="checkbox" name="{$FIELD_NAME}" value="true" {if $FIELD_DATA[$FIELD_NAME] == 'true'} checked {/if} />
									</div>
								{else}
									<div class="row col-sm-8">
										<input class="form-control" type="text" name="{$FIELD_NAME}" {if $FIELD_DETAILS['required'] == '1'}required{/if} value="{$FIELD_DATA[$FIELD_NAME]}" />
									</div>
									{if $FIELD_NAME == 'upload_maxsize'}&nbsp;{vtranslate('LBL_MB', 'OSSMail')}{/if}
								{/if}
							</div>
						</div>
					{/foreach}
					<div class="pull-right">
						<button class="btn btn-success saveButton" style="margin-top:10px;"type="submit" title=""><strong>{vtranslate('LBL_SAVE', 'OSSMail')}</strong></button>
					</div>
				</form>
			{/if}
		</div>
	</div>
{/strip}
<script type="text/javascript">
	jQuery(document).ready(function () {
		var params = {
			delimiter: ',',
			persist: false,
			create: function (input) {
				return {
					value: input,
					text: input
				}
			}
		}
		app.showSelectizeElementView($('[name="default_host"]'), params);
	});
	$('#status').change(function () {
		$('#confirm').attr('disabled', !this.checked);
	});

	function saveWidgetConfig(name, value, type) {
		var params = {
			'module': 'OSSMailScanner',
			'action': "SaveWidgetConfig",
			'conf_type': type,
			'name': name,
			'value': value
		}
		AppConnector.request(params).then(
				function (data) {

				},
				function (data, err) {

				}
		);
	}
	jQuery("#RCConfigEditorForm").submit(function (event) {
		var data = $("#RCConfigEditorForm").serializeFormData();
		var updatedFields = {};
		var status = true;
		jQuery.each(data, function (key, value) {
			if ($("#RCConfigEditorForm [name='" + key + "']").attr('required') != undefined && value == '') {
				var params = {
					title: app.vtranslate('JS_ERROR'),
					text: app.vtranslate('JS_ERROR_EMPTY'),
					type: 'error',
					animation: 'show'
				};
				Vtiger_Helper_Js.showPnotify(params);
				status = false;
			}
			updatedFields[key] = value;
		})
		if (status) {
			var params = {
				'module': 'OSSMail',
				'action': "SaveRcConfig",
				'updatedFields': JSON.stringify(updatedFields)
			}
			AppConnector.request(params).then(
					function (data) {
						var response = data['result'];
						if (response['success']) {
							var params = {
								text: response['data'],
								type: 'info',
								animation: 'show'
							};
							Vtiger_Helper_Js.showPnotify(params);
						} else {
							var params = {
								text: response['data'],
								animation: 'show'
							};
							Vtiger_Helper_Js.showPnotify(params);
						}
					},
					function (data, err) {

					}
			);
		}
		return event.preventDefault();
	});
</script>

