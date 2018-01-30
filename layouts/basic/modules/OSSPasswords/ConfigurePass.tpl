{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="widget_header row">
	<div class="col-md-12">
		{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE)}
	</div>
</div>
{if $ISADMIN eq 1}

	{if $ERROR|count_characters:true gt 0}
		<div class="alert alert-warning">
			<strong>{\App\Language::translate('Error', $MODULENAME)}</strong> {\App\Language::translate($ERROR, $MODULENAME)}
		</div>
	{elseif $INFO|count_characters:true gt 0}
		<div class="alert alert-info">
			<strong>{\App\Language::translate('Info', $MODULENAME)}</strong> {\App\Language::translate($INFO, $MODULENAME)}
		</div>
	{elseif $SUCCESS|count_characters:true gt 0}
		<div class="alert alert-success">
			<strong>{\App\Language::translate('Success', $MODULENAME)}</strong> {\App\Language::translate($SUCCESS, $MODULENAME)}
		</div>
	{/if}

	<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
		<li class="active"><a href="#encoding" data-toggle="tab">{\App\Language::translate('Encoding', $MODULENAME)}</a></li>
		<li><a href="#confpass" data-toggle="tab">{\App\Language::translate('LBL_ConfigurePass', $MODULENAME)}</a></li>
	</ul>
	<br />
	<div id="my-tab-content" class="tab-content">
		{* encryption configuration *}
		<div class="editViewContainer tab-pane active" id="encoding">
			{* check if the ini file exists *}
			{if $CONFIG neq false}
				<ul id="pills" class="nav nav-pills">
					<li class="active">
						<a href="#edit" data-toggle="tab">{\App\Language::translate('Edit Password Key', $MODULENAME)}</a>
					</li>
					<li><a href="#stop" data-toggle="tab">{\App\Language::translate('Stop Password Encryption', $MODULENAME)}</a></li>
				</ul>
				<div id="my-tab-content2" class="tab-content">
					<div class="editViewContainer tab-pane active" id="edit">
						<form class="form-horizontal recordEditView" id="EditView" name="edit_pass_key" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings&parent=Settings">                
							<input type="hidden" name="encrypt" value="edit" />
							<div class="contentHeader row">
								<span class="col-md-8 font-x-x-large textOverflowEllipsis">{\App\Language::translate('Change Password Key', $MODULENAME)}</span>
							</div>

							<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
								<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
									<h5>&nbsp;{\App\Language::translate('Edit Encryption Key', $MODULENAME)}</h5>
								</div>
								<div class="col-md-12 paddingLRZero panel-body blockContent">									
									<div class="fieldRow col-md-8 col-xs-12">
										<div class="fieldLabel col-xs-5 col-sm-2">
											<label class="muted float-right marginRight10px"> <span class="redColor">*</span> {\App\Language::translate('Old Key', $MODULENAME)}:</label>
										</div>
										<div class="fieldValue col-xs-7 col-sm-10" >
											<div class="row">
												<input id="oldKey" type="text" class="form-control nameField" name="oldKey" value="" min="8" />
											</div>
										</div>
									</div>
									<div class="fieldRow col-md-8 col-xs-12">
										<div class="fieldLabel col-xs-5 col-sm-2">
											<label class="muted float-right marginRight10px"> <span class="redColor">*</span> {\App\Language::translate('New Key', $MODULENAME)}:</label>
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
								<span class="float-right">
									<button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{\App\Language::translate('Save', $MODULENAME)}</strong></button>
									<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{\App\Language::translate('Cancel', $MODULENAME)}</a>
								</span>
							</div>
						</form>
					</div>
					{* stop encrypting passwords *}
					<div class="editViewContainer tab-pane" id="stop">
						<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">                
							<input type="hidden" name="encrypt" value="stop" />
							<div class="contentHeader row">
								<span class="col-md-8 font-x-x-large textOverflowEllipsis">{\App\Language::translate('Cancel Encrypting Passwords', $MODULENAME)}</span>
							</div>
							<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
								<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
									<h5>&nbsp;{\App\Language::translate('Enter Your Old Password', $MODULENAME)}</h5>
								</div>
								<div class="col-md-12 paddingLRZero panel-body blockContent">									
									<div class="fieldRow col-md-8 col-xs-12">
										<div class="fieldLabel col-xs-5 col-sm-2">
											<label class="muted float-right marginRight10px"> <span class="redColor">*</span> {\App\Language::translate('Encryption Password', $MODULENAME)}:</label>
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
								<span class="float-right">
									<button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{\App\Language::translate('Save', $MODULENAME)}</strong></button>
									<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{\App\Language::translate('Cancel', $MODULENAME)}</a>
								</span>
							</div>
						</form>
					</div>
				</div>
			{else}
				<form class="form-horizontal recordEditView" id="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">
					<input type="hidden" name="encrypt" value="start" />
					<div class="contentHeader row">
						<span class="col-md-8 font-x-x-large textOverflowEllipsis">{\App\Language::translate('Encrypt Passwords', $MODULENAME)}</span>
					</div>

					<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
						<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
							<h5>&nbsp;{\App\Language::translate('Enter encryption password', $MODULENAME)}</h5>
						</div>
						<div class="col-md-12 paddingLRZero panel-body blockContent">
							<div class="fieldRow col-md-8 col-xs-12">
								<div class="fieldLabel col-xs-5 col-sm-2">
									<label class="muted float-right marginRight10px"> <span class="redColor">*</span> {\App\Language::translate('Encryption password', $MODULENAME)}:</label>
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
						<span class="float-right">
							<button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{\App\Language::translate('Save', $MODULENAME)}</strong></button>
							<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{\App\Language::translate('Cancel', $MODULENAME)}</a>
						</span>
					</div>
				</form>
			{/if}
		</div>

		{* password configuration form *}
		<div class="editViewContainer tab-pane" id="confpass">
			<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">
				<div class="contentHeader row">
					<span class="col-md-8 font-x-x-large textOverflowEllipsis">{\App\Language::translate('LBL_ConfigurePass', $MODULENAME)}</span>
				</div>
				
				<div class="panel panel-default row marginLeftZero marginRightZero blockContainer">
					<div class="row blockHeader panel-heading marginLeftZero marginRightZero">
						<h5>&nbsp;{\App\Language::translate('Password Length', $MODULENAME)}</h5>
					</div>
					<div class="col-md-12 paddingLRZero panel-body blockContent">
						<div class="fieldRow col-md-8 col-xs-12">
							<div class="fieldLabel col-xs-5 col-sm-2">
								<label class="muted float-right marginRight10px"> <span class="redColor">*</span> {\App\Language::translate('Minimum Length', $MODULENAME)}:</label>
							</div>
							<div class="fieldValue col-xs-7 col-sm-10">
								<div class="row">
									<input id="OSSPasswords_editView_fieldName_pass_length_min" type="number" class="form-control nameField" name="pass_length_min" value="{$MIN}" min="1" />
								</div>
							</div>
						</div>
						<div class="fieldRow col-md-8 col-xs-12">
							<div class="fieldLabel col-xs-5 col-sm-2">
								<label class="muted float-right marginRight10px"> <span class="redColor">*</span> {\App\Language::translate('Maximum Length', $MODULENAME)}:</label>
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
						<h5>&nbsp;{\App\Language::translate('Allowed Characters', $MODULENAME)}</h5>
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
						<h5>&nbsp;{\App\Language::translate('LBL_REGISTER_CHANGES', $MODULENAME)}</h5>
					</div>
					<div class="col-md-12 paddingLRZero panel-body blockContent">
						<div class="fieldRow col-md-8 col-xs-12">
							<div class="fieldLabel"> </div>
							<div align="center" class="fieldValue col-xs-7 col-sm-10">
								<div class="row float-left">
									<input id="register_changes" type="checkbox" class="nameField" name="register_changes" {$REGISTER} value="1" /> 
										{\App\Language::translate('LBL_START_REGISTER', $MODULENAME)}
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="contentHeader">
					<span class="float-right">
						<button class="btn btn-success" name="save" value="save" type="submit"><strong>{\App\Language::translate('Save', $MODULENAME)}</strong></button>
						<a class="cancelLink btn btn-warning" type="reset" onclick="javascript:window.history.back();">{\App\Language::translate('Cancel', $MODULENAME)}</a>
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
					<h3 class="modal-title">{\App\Language::translate('LBL_REGISTER_WARN1', $MODULENAME)}</h3>
				</div>
				<div class="modal-body">
					<p>{\App\Language::translate('LBL_REGISTER_WARN2', $MODULENAME)}</p>
					<p><input id="statusRegistration" name="status" type="checkbox" {$REGISTER} value="1" required="required" /> {\App\Language::translate('LBL_START_REGISTER', $MODULENAME)}</p>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success okay-button" id="confirmRegistration" type="submit" name="uninstall" form="EditView">{\App\Language::translate('Yes', $MODULENAME)}</button>
					<button class="btn btn-warning" data-dismiss="modal">{\App\Language::translate('No', $MODULENAME)}</button>
				</div>
			</div>
		</div>
	</div>
{else}
    <div class="alert alert-warning" style="margin:10px 15px;">
        <strong>{\App\Language::translate('Error', $MODULENAME)}</strong> {\App\Language::translate('Access denied!', $MODULENAME)}
    </div>
{/if}
