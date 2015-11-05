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
<table class="table listViewEntriesTable doc-list-widget">
        {foreach from=$DOC_LIST item=item key=key}
            <tr>
                <td>
                    {if $item['doc_request'] eq '1'}
                        <span style="color: red;">{$item['doc_short_name']}</span>
                    {else}
                        <span style="color: blue;">{$item['doc_short_name']}</span>
                    {/if}
                        
                    
                </td>
                <td>{$item['folder_name']}</td>
                <td>
                    {if $item['is_attach']}
                        <i class="glyphicon glyphicon-ok-circle"></i>
                    {else}
                        <i class="icon-remove-circle"></i>
                    {/if}
                </td>
                <td class="col-md-1">
                    {if $item['is_attach']}
                        {$item['status']}
                    {else}
                        nd
                    {/if}
                </td>
                <td><button class="btn btn-primary pull-right add-doc" data-doc-name="{$item['doc_name']}" data-folder="{$item['doc_folder']}" type="button" ><i class="icon-white glyphicon glyphicon-plus" ></i></button></td>
            </tr>
        {/foreach}
    </table>

{literal}
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('.add-doc').on('click', function() {
                var name = jQuery(this).data('doc-name'),
                    folder = jQuery(this).data('folder'),
                    url = 'index.php?module=Documents&view=Edit';
                    
                url += '&folderid=' + folder + '&notes_title=' + name + '&sourceModule=' + app.getModuleName() + '&sourceRecord=' + jQuery('#recordId').val() + '&relationOperation=true';
                    
                app.showModalWindow(null, url).css('overflow', 'auto');
            })
        })
    </script>
{/literal}
