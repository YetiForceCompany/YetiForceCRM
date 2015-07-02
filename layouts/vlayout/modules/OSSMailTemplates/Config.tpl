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
<style>
    .drag-icon{
        padding-top: 2%;
    }
    #pre-sortable-box{
        position: absolute;
        top: 50%;
        left: 50%;
    }
    .row > [class*="span"]{
        margin-left: 1%;
    }
    .row > [class*="span"].blockSortable:first-child {
        margin-left: 1%;
    }
    .state-highlight{ height: 2em; line-height: 2em; border: 1px solid #FFD600;background-color:#F9FFB3; }
    .not_visible { color:grey; text-decoration:line-through; }
    .no_permission { color:red !important; }
    .paddingTop3 { padding-top:3px; }

    hr.style-one {
        border: 0;
        height: 1px;
        background: #333;
        background-image: -webkit-linear-gradient(left, #ccc, #333, #ccc); 
        background-image:    -moz-linear-gradient(left, #ccc, #333, #ccc); 
        background-image:     -ms-linear-gradient(left, #ccc, #333, #ccc); 
        background-image:      -o-linear-gradient(left, #ccc, #333, #ccc); 
    }
</style>
<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-tab.js"></script>
<div class="" id="layoutEditorContainer">
    <ul id="tabs" class="nav nav-tabs" data-tabs="tabs" style="margin-left:30px;">
        <li class="active"><a href="#help" data-toggle="tab">{vtranslate('LBL_HELP', $MODULENAME)}</a></li>
        <li><a href="#delete" data-toggle="tab">{vtranslate('LBL_DeleteModule', $MODULENAME)}</a></li>
    </ul>
    {if $ERROR neq ''}
        <div class="alert alert-warning">
            <strong>{vtranslate('Error', $MODULENAME)}</strong> {vtranslate($ERROR, $MODULENAME)}
        </div>
    {/if}
    <div id="my-tab-content" class="tab-content" class="row">
        {* help *}
        <div class='editViewContainer tab-pane active' id="help">
            <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=Configuration&parent=Settings">
                <input type="hidden" name="mode" value="ticket" />

                <table class="table table-bordered blockContainer showInlineTable">
                    <tr>
                        <th class="blockHeader" colspan="4">{vtranslate('LBL_HELP', $MODULENAME)}</th>
                    </tr>
                    <tr>
                        <td colspan="4">{vtranslate('HelpDescription', $MODULENAME)}</td>
                    </tr>
                    <tr>
                        <td class="fieldLabel">
                            <label class="muted pull-right marginRight10px"> {vtranslate('LBL_TroubleUrl', $MODULENAME)}</label>
                        </td>
                        <td class="fieldValue" >
                            <div class="row"><span class="col-md-10">
                                    <a href="{vtranslate('LBL_UrlLink', $MODULENAME)}" target="_blank">{vtranslate('LBL_UrlLink', $MODULENAME)}</a> ({vtranslate('LBL_UrlLinkInfo', $MODULENAME)})</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldLabel">
                            <label class="muted pull-right marginRight10px"> {vtranslate('LBL_OurWebsite', $MODULENAME)}</label>
                        </td>
                        <td class="fieldValue" >
                            <div class="row"><span class="col-md-10">
                                    {*
                                    // Removal of this link violates the principles of License
                                    // Usunięcie tego linku narusza zasady licencji *}
                                    <a href="{vtranslate('LBL_OurWebsiteLink', $MODULENAME)}" target="_blank">{vtranslate('LBL_OurWebsiteLink', $MODULENAME)}</a></span>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>

        {* delete module form *}
        <div class='editViewContainer tab-pane' id="delete">
            <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=Uninstall&parent=Settings&block={$smarty.get.block}&fieldid={$smarty.get.fieldid}">
                <input type="hidden" name="uninstall" value="uninstall" />
                <input type="hidden" name="status" value="1" />
                <div class="contentHeader row">
                    <span class="col-md-8 font-x-x-large textOverflowEllipsis">{vtranslate('LBL_DeleteModule', $MODULENAME)}</span>
                </div>

                <table class="table table-bordered blockContainer showInlineTable">
                    <tr>
                        <th class="blockHeader" colspan="4">{vtranslate('Delete_panel', $MODULENAME)} {vtranslate($MODULENAME, $MODULENAME)}</th>
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
    </div>

    {* modal promtp for uninstall *}
    <div id="myModal" class="modal fade" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h3 class="modal-title">{vtranslate('MSG_DEL_WARN1', $MODULENAME)}</h3>
				</div>
				<div class="modal-body">
					<p>{vtranslate('MSG_DEL_WARN2', $MODULENAME)}</p>
					<p><input id="status" onclick="jQuery('#confirm').attr('disabled', !this.checked);" name="status" type="checkbox" value="1" required="required" /> {vtranslate('LBL_DEL_CONFIRM', $MODULENAME)}</p>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-default" data-dismiss="modal">{vtranslate('No', $MODULENAME)}</a>
					<a href="index.php?module=OSSMailTemplates&view=Uninstall&parent=Settings" class="btn btn-danger okay-button" id="confirm" type="submit" name="uninstall" form="EditView" disabled="disabled">{vtranslate('Yes', $MODULENAME)}</a>
				</div>
			</div>
		</div>
    </div>
</div>
