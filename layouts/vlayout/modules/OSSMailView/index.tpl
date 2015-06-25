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
    .table tbody tr.error > td {
        background-color: #f2dede;
    }
    .table th, .table td {
        padding: 3px;
    }
</style>

<div class="">
	<div class="clearfix treeView">
		<div class="widget_header row">
			<div class="col-md-8"><h3>{vtranslate('Configuration', 'OSSMailView')}</h3></div>
		</div>
		<hr>
<div id="my-tab-content" class="tab-content">
<div class="editViewContainer tab-pane active" id="cfg">
    <table>
        <tr>
            <td><label class="control-label">{vtranslate('Widget list limit', 'OSSMailView')}</label></td>
            <td><input id="tab_email_view_widget_limit" class="form-control" value="{$WIDGET_CFG['email_list']['widget_limit']}"/></td>
        </tr>
        <tr>
            <td><label class="control-label">{vtranslate('List open email', 'OSSMailView')}</label>&nbsp;</td>
            <td>
                <select id="tab_email_view_open_window" class="form-control">
                    <option value="_self" {if $WIDGET_CFG['email_list']['target'] eq '_self'}selected{/if}>{vtranslate('_self', 'OSSMailView')}</option>
                    <option value="_blank" {if $WIDGET_CFG['email_list']['target'] eq '_blank'}selected{/if}>{vtranslate('_blank', 'OSSMailView')}</option>
                </select>
            </td>
        </tr>
    </table>
    </div>
    {* delete module form *}
    <div class="editViewContainer tab-pane" id="uninstall">
        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=Uninstall&parent=Settings&block={$smarty.get.block}&fieldid={$smarty.get.fieldid}">
            <input type="hidden" name="uninstall" value="uninstall" />
            <input type="hidden" name="status" value="1" />
            <p> </p>            
            <table class="table table-bordered blockContainer showInlineTable">
                <tr>
                    <th class="blockHeader" colspan="4">{vtranslate('Delete_panel', $MODULENAME)}{$MODULENAME}</th>
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
        <form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php?module={$MODULENAME}&view=Configuration&parent=Settings">
            <input type="hidden" name="mode" value="ticket" />            
            <p> </p>            
            <table class="table table-bordered blockContainer showInlineTable">
                <tr>
                    <th class="blockHeader" colspan="4">{vtranslate('LBL_HELP_SETTINGS', $MODULENAME)}</th>
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
                        <label class="muted pull-right marginRight10px"> {vtranslate('LBL_Manual', $MODULENAME)}</label>
                    </td>
                    <td class="fieldValue" >
                        <div class="row"><span class="col-md-10">
                                <a href="{vtranslate('LBL_ManualLink', $MODULENAME)}" target="_blank">{vtranslate('OSSMailView_manual', $MODULENAME)}</a></span>
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
</div>

{* modal promtp for uninstall *}
<div id="myModal" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>{vtranslate('MSG_DEL_WARN1', $MODULENAME)}</h3>
    </div>
    <div class="modal-body">
        <p>{vtranslate('MSG_DEL_WARN2', $MODULENAME)}</p>
        <p><input id="status" name="status" type="checkbox" value="1" required="required" /> {vtranslate('Uninstall OSSMailScanner module', $MODULENAME)}</p>
    </div>
    <div class="modal-footer">
        <a href="#" class="btn btn-default" data-dismiss="modal">{vtranslate('No', $MODULENAME)}</a>
        <a href="#" class="btn btn-danger okay-button" id="confirm" type="submit" name="uninstall" form="EditView" disabled="disabled">{vtranslate('Yes', $MODULENAME)}</a>
    </div>          
</div>
	</div>
</div>
{literal}
<script>
    jQuery(function(){
        var saveWidgetConfig = function(name, value, type) {
            var params = {
                'module': 'OSSMailScanner',
                'action': "SaveWidgetConfig",
                'conf_type': type,
                'name': name,
                'value': value
            }
            AppConnector.request(params).then(
                function(data) {
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
                function(data, err) {

                }
            );
        }
        jQuery('#tab_email_view_widget_limit').on('blur', function() {
            saveWidgetConfig('widget_limit', jQuery(this).val(), 'email_list');
        });
        jQuery('#tab_email_view_open_window').on('change', function() {
            saveWidgetConfig('target', jQuery(this).val(), 'email_list');
        });
        
        jQuery('#email_permissions').select2();
        jQuery('#email_permissions').on('change', function() {
            saveWidgetConfig('permissions', jQuery(this).val(), 'email_list');
        });
		$("#myModal").css("z-index", "9999999");
		$('#myModal .okay-button').click(function() {
			var disabled = $('#confirm').attr('disabled');
			if (typeof disabled == 'undefined') {
				$('#myModal').modal('hide');
				$('#uninstall #EditView').submit();
			}
		});
		$('#status').change(function() {
			$('#confirm').attr('disabled', !this.checked);
		});
    });
</script>
{/literal}
