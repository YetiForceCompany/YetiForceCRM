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
		<div class="editViewContainer tab-pane active" id="encoding">
			{* check if the ini file exists *}
			{if $CONFIG neq false}
				<ul id="pills" class="nav nav-pills">
					<li class="active">
						<a href="#edit" data-toggle="tab">{vtranslate('Edit Password Key', $MODULENAME)}</a>
					</li>
					<li><a href="#stop" data-toggle="tab">{vtranslate('Stop Password Encryption', $MODULENAME)}</a></li>
				</ul>
				<div id="my-tab-content2" class="tab-content">
					<div class="editViewContainer tab-pane active" id="edit">
						<form class="form-horizontal recordEditView" id="EditView" name="edit_pass_key" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings&parent=Settings">                
							<input type="hidden" name="encrypt" value="edit" />
							<div class="contentHeader row">
								<span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('Change Password Key', $MODULENAME)}</span>
							</div>

							<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
								<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
									<h5>&nbsp;{vtranslate('Edit Encryption Key', $MODULENAME)}</h5>
								</div>
								<div class="col-md-12 paddingLRZero panel-body blockContent">									
									<div class="fieldRow col-md-8 col-xs-12">
										<div class="fieldLabel col-xs-5 col-sm-2">
											<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Old Key', $MODULENAME)}:</label>
										</div>
										<div class="fieldValue col-xs-7 col-sm-10" >
											<div class="row">
												<input id="oldKey" type="text" class="form-control nameField" name="oldKey" value="" min="8" />
											</div>
										</div>
									</div>
									<div class="fieldRow col-md-8 col-xs-12">
										<div class="fieldLabel col-xs-5 col-sm-2">
											<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('New Key', $MODULENAME)}:</label>
										</div>
										<div class="fieldValue col-xs-7 col-sm-10" >
											<div class="row">
												<input id="newKey" type="text" class="form-control nameField" name="newKey" value="" min="8" />
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="contentHeader">
								<span class="pull-right">
									<button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
									<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
								</span>
							</div>
						</form>
					</div>
					{* stop encrypting passwords *}
					<div class="editViewContainer tab-pane" id="stop">
						<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">                
							<input type="hidden" name="encrypt" value="stop" />
							<div class="contentHeader row">
								<span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('Cancel Encrypting Passwords', $MODULENAME)}</span>
							</div>
							<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
								<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
									<h5>&nbsp;{vtranslate('Enter Your Old Password', $MODULENAME)}</h5>
								</div>
								<div class="col-md-12 paddingLRZero panel-body blockContent">									
									<div class="fieldRow col-md-8 col-xs-12">
										<div class="fieldLabel col-xs-5 col-sm-2">
											<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Encryption Password', $MODULENAME)}:</label>
										</div>
										<div class="fieldValue col-xs-7 col-sm-10" >
											<div class="row">
												<input id="passKey" type="text" class="form-control nameField" name="passKey" value="" min="8" />
											</div>
										</div>
									</div>
								</div>
							</div>
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

					<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
						<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
							<h5>&nbsp;{vtranslate('Enter encryption password', $MODULENAME)}</h5>
						</div>
						<div class="col-md-12 paddingLRZero panel-body blockContent">
							<div class="fieldRow col-md-8 col-xs-12">
								<div class="fieldLabel col-xs-5 col-sm-2">
									<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Encryption password', $MODULENAME)}:</label>
								</div>
								<div class="fieldValue col-xs-7 col-sm-10">
									<div class="row">
										<input id="pass_key" type="text" class="form-control nameField" name="pass_key" value="" min="8" />
									</div>
								</div>
							</div>
						</div>
					</div>
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
		<div class="editViewContainer tab-pane" id="confpass">
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">
				<div class="contentHeader row">
					<span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('LBL_ConfigurePass', $MODULENAME)}</span>
				</div>
				
				<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
					<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
						<h5>&nbsp;{vtranslate('Password Length', $MODULENAME)}</h5>
					</div>
					<div class="col-md-12 paddingLRZero panel-body blockContent">
						<div class="fieldRow col-md-8 col-xs-12">
							<div class="fieldLabel col-xs-5 col-sm-2">
								<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Minimum Length', $MODULENAME)}:</label>
							</div>
							<div class="fieldValue col-xs-7 col-sm-10">
								<div class="row">
									<input id="OSSPasswords_editView_fieldName_pass_length_min" type="number" class="form-control nameField" name="pass_length_min" value="{$MIN}" min="1" />
								</div>
							</div>
						</div>
						<div class="fieldRow col-md-8 col-xs-12">
							<div class="fieldLabel col-xs-5 col-sm-2">
								<label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Maximum Length', $MODULENAME)}:</label>
							</div>
							<div class="fieldValue col-xs-7 col-sm-10">
								<div class="row">
									<input id="OSSPasswords_editView_fieldName_pass_length_max" type="number" class="form-control nameField" name="pass_length_max" value="{$MAX}" min="1" />
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
					<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
						<h5>&nbsp;{vtranslate('Allowed Characters', $MODULENAME)}</h5>
					</div>
					<div class="col-md-12 paddingLRZero panel-body blockContent">
						<div class="fieldRow col-md-8 col-xs-12">
							<div class="fieldLabel"> </div>
							<div align="center" class="fieldValue col-xs-12">
								<div class="row">
									<textarea id="OSSPasswords_editView_fieldName_pass_allow_chars" class="form-control" name="pass_allow_chars" rows="4" cols="80">{$ALLOWEDCHARS}</textarea>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
					<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
						<h5>&nbsp;{vtranslate('LBL_REGISTER_CHANGES', $MODULENAME)}</h5>
					</div>
					<div class="col-md-12 paddingLRZero panel-body blockContent">
						<div class="fieldRow col-md-8 col-xs-12">
							<div class="fieldLabel"> </div>
							<div align="center" class="fieldValue col-xs-7 col-sm-10">
								<div class="row pull-left">
									<input id="register_changes" type="checkbox" class="nameField" name="register_changes" {$REGISTER} value="1" /> 
										{vtranslate('LBL_START_REGISTER', $MODULENAME)}
								</div>
							</div>
						</div>
					</div>
				</div>
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
