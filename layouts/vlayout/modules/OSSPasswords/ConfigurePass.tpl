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

{if $ISADMIN eq 1}

{if $ERROR|count_characters:true gt 0}
    <div class="alert alert-warning" style="margin:10px 15px;">
        <strong>{vtranslate('Error', $MODULENAME)}</strong> {vtranslate($ERROR, $MODULENAME)}
    </div>
{elseif $INFO|count_characters:true gt 0}
    <div class="alert alert-info" style="margin:10px 15px;">
        <strong>{vtranslate('Info', $MODULENAME)}</strong> {vtranslate($INFO, $MODULENAME)}
    </div>
{elseif $SUCCESS|count_characters:true gt 0}
    <div class="alert alert-success" style="margin:10px 15px;">
        <strong>{vtranslate('Success', $MODULENAME)}</strong> {vtranslate($SUCCESS, $MODULENAME)}
    </div>
{/if}

<ul id="tabs" class="nav nav-tabs" data-tabs="tabs" style="margin-left:30px;">
    <li class="active"><a href="#encoding" data-toggle="tab">{vtranslate('Encoding', $MODULENAME)}</a></li>
    <li><a href="#confpass" data-toggle="tab">{vtranslate('LBL_ConfigurePass', $MODULENAME)}</a></li>
    <li><a href="#delete" data-toggle="tab">{vtranslate('LBL_DeletePassModule', $MODULENAME)}</a></li>
    {*
    // Removal of this link violates the principles of License
    // Usunięcie tego linku narusza zasady licencji *}
    <li><a href="#help" data-toggle="tab">{vtranslate('LBL_HELP', $MODULENAME)}</a></li>
</ul>

<div id="my-tab-content" class="tab-content">
    {* encryption configuration *}
    <div class='editViewContainer tab-pane active' id="encoding">
        {* check if the ini file exists *}
        {if $CONFIG neq false}
            <ul id="pills" class="nav nav-pills" style="margin-left:30px;">
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
                            <span class="pull-right">
                                <button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
                                <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
                            </span>
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
                        <div class="contentHeader row">
                            <span class="pull-right">
                                <button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
                                <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
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
                            <span class="pull-right">
                                <button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
                                <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
                            </span>
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
                        <div class="contentHeader row">
                            <span class="pull-right">
                                <button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
                                <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
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
                    <span class="pull-right">
                        <button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
                        <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
                    </span>
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
                            <div class="row"><span class="col-md-10">
                                <input id="pass_key" type="text" class="form-control nameField" name="pass_key" value="" min="8" /></span>
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="contentHeader row">
                    <span class="pull-right">
                        <button class="btn btn-success" name="encryption_pass" value="encryption_pass" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
                        <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
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
            <span class="pull-right">
                <button class="btn btn-success" name="save" value="save" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
                <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
            </span>
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
                    <div class="row"><span class="col-md-10">
                        <input id="OSSPasswords_editView_fieldName_pass_length_min" type="number" class="form-control nameField" name="pass_length_min" value="{$MIN}" min="1" /></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="fieldLabel">
                    <label class="muted pull-right marginRight10px"> <span class="redColor">*</span> {vtranslate('Maximum Length', $MODULENAME)}:</label>
                </td>
                <td class="fieldValue" >
                    <div class="row"><span class="col-md-10">
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
                        <span class="col-md-10"><textarea id="OSSPasswords_editView_fieldName_pass_allow_chars" name="pass_allow_chars" rows="4" cols="80">{$ALLOWEDCHARS}</textarea></span>
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
                        <span class="col-md-10" style="text-align: left;">
                        <input id="register_changes" type="checkbox" class="nameField" name="register_changes" {$REGISTER} value="1" data-toggle="modal" data-target="#myRegisterModal" /> 
                         {vtranslate('LBL_START_REGISTER', $MODULENAME)}</span>
                    </div>
                </td>
            </tr>
        </table>
        <div class="contentHeader row">
            <span class="pull-right">
                <button class="btn btn-success" name="save" value="save" type="submit"><strong>{vtranslate('Save', $MODULENAME)}</strong></button>
                <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a>
            </span>
        </div>
        </form>
    </div>
    
    {* delete module form *}
    <div class='editViewContainer tab-pane' id="delete">
        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=ConfigurePass&parent=Settings">
        <input type="hidden" name="uninstall" value="uninstall" />
        <input type="hidden" name="status" value="1" />
        <div class="contentHeader row">
            <span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('LBL_DeleteModule', $MODULENAME)}</span>
        </div>

        
        <table class="table table-bordered blockContainer showInlineTable">
            <tr>
                <th class="blockHeader" colspan="4">{vtranslate('Delete_panel', $MODULENAME)}{vtranslate('OSSPasswords', $MODULENAME)}</th>
            </tr>
            <tr>
                <td class="fieldLabel" colspan="4">
                <span class="pull-right">
                    <button class="btn btn-danger btn-lg" name="uninstall" type="submit"  data-toggle="modal" data-target="#myModal"><strong>{vtranslate('Uninstall', $MODULENAME)}</strong></button>
                    <a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('Cancel', $MODULENAME)}</a> 
                </span>
                </td>
            </tr>            
        </table>
        
        </form>
    </div>
    
    {* help *}
    <div class='editViewContainer tab-pane' id="help">
        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="">
        <input type="hidden" name="mode" value="ticket" />

        <table class="table table-bordered blockContainer showInlineTable">
            <tr>
                <th class="blockHeader" colspan="4">{vtranslate('LBL_HELP', $MODULENAME)}</th>
            </tr>
            <tr>
				<td class="fieldLabel">
                    <label class="muted pull-right marginRight10px"> {vtranslate('Information', $MODULENAME)}</label>
                </td>
                 <td class="fieldValue" >
				<span class="col-md-10">
                <a href="{vtranslate('LBL_UrlLink2', $MODULENAME)}" target="_blank">{vtranslate('LBL_UrlLink2', $MODULENAME)}
				</td>
            </tr>
            <tr>
                <td class="fieldLabel">
                    <label class="muted pull-right marginRight10px"> {vtranslate('LBL_Helpforthemodule', $MODULENAME)}</label>
                </td>
                <td class="fieldValue" >
                    <div class="row"><span class="col-md-10">
                        <a href="mailto:{vtranslate('LBL_UrlHelp', $MODULENAME)}" target="_blank">{vtranslate('LBL_UrlHelp', $MODULENAME)},&nbsp </a>
						<a href="mailto:{vtranslate('LBL_UrlHelp2', $MODULENAME)}" target="_blank">{vtranslate('LBL_UrlHelp2', $MODULENAME)}</a>
						</span>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="fieldLabel">
                    <label class="muted pull-right marginRight10px"> {vtranslate('LBL_License', $MODULENAME)}</label>
                </td>
                <td class="fieldValue" >
                    <div class="row"><span class="col-md-10">
                        {*
                        // Removal of this link violates the principles of License
                        // Usunięcie tego linku narusza zasady licencji *}
                        <a href="{vtranslate('LBL_UrlLicense', $MODULENAME)}" target="_blank">{vtranslate('LBL_UrlLicense', $MODULENAME)}</a></span>
                    </div>
                </td>
            </tr>
			<tr>
                <td class="fieldLabel">
                    <label class="muted pull-right marginRight10px"> {vtranslate('LBL_Company', $MODULENAME)}</label>
                </td>
                <td class="fieldValue" >
                    <div class="row"><span class="col-md-10">
                        <a href="{vtranslate('LBL_UrlCompany', $MODULENAME)}" target="_blank">{vtranslate('LBL_UrlCompany', $MODULENAME)}</a></span>
                    </div>
                </td>
            </tr>
        </table>
        </form>
    </div>
</div>

{* modal promtp for uninstall *}
<div id="myModal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>{vtranslate('MSG_DEL_WARN1', $MODULENAME)}</h3>
  </div>
  <div class="modal-body">
    <p>{vtranslate('MSG_DEL_WARN2', $MODULENAME)}</p>
    <p><input id="status" name="status" type="checkbox" value="1" required="required" /> {vtranslate('Uninstall OSSPasswords module', $MODULENAME)}</p>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" data-dismiss="modal">{vtranslate('No', $MODULENAME)}</a>
    <a href="#" class="btn btn-danger okay-button" id="confirm" type="submit" name="uninstall" form="EditView" disabled="disabled">{vtranslate('Yes', $MODULENAME)}</a>
  </div>
</div>

{* modal promtp for modtracker register changes *}
<div id="myRegisterModal" class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>{vtranslate('LBL_REGISTER_WARN1', $MODULENAME)}</h3>
  </div>
  <div class="modal-body">
    <p>{vtranslate('LBL_REGISTER_WARN2', $MODULENAME)}</p>
    <p><input id="statusRegistration" name="status" type="checkbox" {$REGISTER} value="1" required="required" /> {vtranslate('LBL_START_REGISTER', $MODULENAME)}</p>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn" data-dismiss="modal">{vtranslate('No', $MODULENAME)}</a>
    <a href="#" class="btn btn-danger okay-button" id="confirmRegistration" type="submit" name="uninstall" form="EditView">{vtranslate('Yes', $MODULENAME)}</a>
  </div>
</div>
{else}
    <div class="alert alert-warning" style="margin:10px 15px;">
        <strong>{vtranslate('Error', $MODULENAME)}</strong> {vtranslate('Access denied!', $MODULENAME)}
    </div>
{/if}
