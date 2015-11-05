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
<div class="widget_header row">
	<div class="col-md-12">
		{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	</div>
</div>
<br>
{if $ISADMIN eq 1}

	{if $ERROR|count_characters:true gt 0}
		<div class="alert alert-warning">
			<strong>{vtranslate('Error', $MODULENAME)}</strong> {vtranslate($ERROR, $MODULENAME)}
		</div>
	{elseif $INFO|count_characters:true gt 0}
		<div class="alert alert-info">
			<strong>{vtranslate('Info', $MODULENAME)}</strong> {vtranslate($INFO, $MODULENAME)}
		</div>
	{elseif $SUCCESS|count_characters:true gt 0}
		<div class="alert alert-success">
			<strong>{vtranslate('Success', $MODULENAME)}</strong> {vtranslate($SUCCESS, $MODULENAME)}
		</div>
	{/if}

	<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
		<li class="active"><a href="#encoding" data-toggle="tab">{vtranslate('Encoding', $MODULENAME)}</a></li>
		<li><a href="#confpass" data-toggle="tab">{vtranslate('LBL_ConfigurePass', $MODULENAME)}</a></li>
	</ul>
	<br>
	<div id="my-tab-content" class="tab-content">
		{* encryption configuration *}
		<div class='editViewContainer tab-pane active' id="encoding">
			{* check if the ini file exists *}
			{if $CONFIG neq false}
				<ul id="pills" class="nav nav-pills">
					<li class="active">
						<a href="#edit" data-toggle="tab">{vtranslate('Edit Password Key', $MODULENAME)}</a>
					</li>
					<li><a href="#stop" data-toggle="tab">{vtranslate('Stop Password Encryption', $MODULENAME)}</a></li>
				</ul>
				<div id="my-tab-content2" class="tab-content">
					<div class='editViewContainer tab-pane active' id="edit">
						<form class="form-horizontal recordEditView" id="EditView" name="edit_pass_key" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings&parent=Settings">                
							<input type="hidden" name="encrypt" value="edit" />
							<div class="contentHeader row">
								<span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('Change Password Key', $MODULENAME)}</span>
							</div>

							<table class="table table-bordered blockContainer showInlineTable">
								<tr>
									<th class="blockHeader" colspan="4">{vtranslate('Edit Encryption Key', $MODULENAME)}</th>
								</tr>
								<tr>
									<td class="fieldLabel">
										<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Old Key', $MODULENAME)}:</label>
									</td>
									<td class="fieldValue" >
										<div class="row"><span class="col-md-10">
												<input id="oldKey" type="text" class="form-control nameField" name="oldKey" value="" min="8" /></span>
										</div>
									</td>
								</tr>
								<tr>
									<td class="fieldLabel">
										<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('New Key', $MODULENAME)}:</label>
									</td>
									<td class="fieldValue" >
										<div class="row"><span class="col-md-10">
												<input id="newKey" type="text" class="form-control nameField" name="newKey" value="" min="8" /></span>
										</div>
									</td>
								</tr>
							</table>
							<div class="contentHeader">
								<span class="pull-right">
									<button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
									<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
								</span>
							</div>
						</form>
					</div>
					{* stop encrypting passwords *}
					<div class='editViewContainer tab-pane' id="stop">
						<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">                
							<input type="hidden" name="encrypt" value="stop" />
							<div class="contentHeader row">
								<span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('Cancel Encrypting Passwords', $MODULENAME)}</span>
							</div>

							<table class="table table-bordered blockContainer showInlineTable">
								<tr>
									<th class="blockHeader" colspan="4">{vtranslate('Enter Your Old Password', $MODULENAME)}</th>
								</tr>
								<tr>
									<td class="fieldLabel">
										<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Encryption Password', $MODULENAME)}:</label>
									</td>
									<td class="fieldValue" >
										<div class="row"><span class="col-md-10">
												<input id="passKey" type="text" class="form-control nameField" name="passKey" value="" min="8" /></span>
										</div>
									</td>
								</tr>
							</table>
							<div class="contentHeader">
								<span class="pull-right">
									<button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
									<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
								</span>
							</div>
						</form>
					</div>
				</div>
			{else}
				<form class="form-horizontal recordEditView" id="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">
					<input type="hidden" name="encrypt" value="start" />
					<div class="contentHeader row">
						<span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('Encrypt Passwords', $MODULENAME)}</span>
					</div>

					<table class="table table-bordered blockContainer showInlineTable">
						<tr>
							<th class="blockHeader" colspan="4">{vtranslate('Enter encryption password', $MODULENAME)}</th>
						</tr>
						<tr>
							<td class="fieldLabel">
								<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Encryption password', $MODULENAME)}:</label>
							</td>
							<td class="fieldValue" >
								<div class="row"><span class="col-md-10 col-sm-10 col-xs-10">
										<input id="pass_key" type="text" class="form-control nameField" name="pass_key" value="" min="8" /></span>
								</div>
							</td>
						</tr>
					</table>
					<div class="contentHeader">
						<span class="pull-right">
							<button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
							<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
						</span>
					</div>
				</form>
			{/if}
		</div>

		{* password configuration form *}
		<div class='editViewContainer tab-pane' id="confpass">
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">
				<div class="contentHeader row">
					<span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('LBL_ConfigurePass', $MODULENAME)}</span>
				</div>

				<table class="table table-bordered blockContainer showInlineTable">
					<tr>
						<th class="blockHeader" colspan="4">{vtranslate('Password Length', $MODULENAME)}</th>
					</tr>
					<tr>
						<td class="fieldLabel">
							<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Minimum Length', $MODULENAME)}:</label>
						</td>
						<td class="fieldValue" >
							<div class="row"><span class="col-md-10 col-sm-10 col-xs-10">
									<input id="OSSPasswords_editView_fieldName_pass_length_min" type="number" class="form-control nameField" name="pass_length_min" value="{$MIN}" min="1" /></span>
							</div>
						</td>
					</tr>
					<tr>
						<td class="fieldLabel">
							<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Maximum Length', $MODULENAME)}:</label>
						</td>
						<td class="fieldValue" >
							<div class="row"><span class="col-md-10 col-sm-10 col-xs-10">
									<input id="OSSPasswords_editView_fieldName_pass_length_max" type="number" class="form-control nameField" name="pass_length_max" value="{$MAX}" min="1" /></span>
							</div>
						</td>
					</tr>
					<tr>
						<th class="blockHeader" colspan="4">{vtranslate('Allowed Characters', $MODULENAME)}</th>
					</tr>
					<tr>
						<td class="fieldLabel"> </td>
						<td align="center" class="fieldValue" >
							<div class="row">
								<span class="col-md-10 col-sm-10 col-xs-10"><textarea id="OSSPasswords_editView_fieldName_pass_allow_chars" class="form-control" name="pass_allow_chars" rows="4" cols="80">{$ALLOWEDCHARS}</textarea></span>
							</div>
						</td>
					</tr>
					<tr>
						<th class="blockHeader" colspan="4">{vtranslate('LBL_REGISTER_CHANGES', $MODULENAME)}</th>
					</tr>
					<tr>
						<td class="fieldLabel"> </td>
						<td align="center" class="fieldValue" >
							<div class="row">
								<span class="col-md-10 col-sm-10 col-xs-10" style="text-align: left;">
									<input id="register_changes" type="checkbox" class="nameField" name="register_changes" {$REGISTER} value="1" data-toggle="modal" data-target="#myRegisterModal" /> 
									{vtranslate('LBL_START_REGISTER', $MODULENAME)}</span>
							</div>
						</td>
					</tr>
				</table>
				<div class="contentHeader">
					<span class="pull-right">
						<button class="btn btn-success" name="save" value="save" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
						<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
					</span>
				</div>
			</form>
		</div>
	</div>

	{* modal promtp for modtracker register changes *}
	<div id="myRegisterModal" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title">{vtranslate('LBL_REGISTER_WARN1', $MODULENAME)}</h3>
				</div>
				<div class="modal-body">
					<p>{vtranslate('LBL_REGISTER_WARN2', $MODULENAME)}</p>
					<p><input id="statusRegistration" name="status" type="checkbox" {$REGISTER} value="1" required="required" /> {vtranslate('LBL_START_REGISTER', $MODULENAME)}</p>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success okay-button" id="confirmRegistration" type="submit" name="uninstall" form="EditView">{vtranslate('Yes', $MODULENAME)}</button>
					<button class="btn btn-warning" data-dismiss="modal">{vtranslate('No', $MODULENAME)}</button>
				</div>
			</div>
		</div>
	</div>
{else}
    <div class="alert alert-warning" style="margin:10px 15px;">
        <strong>{vtranslate('Error', $MODULENAME)}</strong> {vtranslate('Access denied!', $MODULENAME)}
    </div>
{/if}
