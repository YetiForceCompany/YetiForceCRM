{*<!-- {[The file is published on the basis of YetiForce Public License 2.0 that can be found in the following directory: licenses/License.html or yetiforce.com]} -->*}
<div class="" id="menuEditorContainer">
    <div class="widget_header row">
        <div class="col-md-12">
	    {include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
	</div>
    </div>
    <div id="my-tab-content" class="tab-content" style="margin: 0 20px;" >
        <div class='editViewContainer' id="tpl" style="min-height:300px">
            <div class="row">
                <div class="col-md-4 paddingLRZero btn-toolbar">
                    <a class="btn btn-default addButton" href="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1">
                        <strong>{\App\Language::translate('LBL_NEW_TPL', $QUALIFIED_MODULE)}</strong>
                    </a>
                </div>
                <div class="col-md-3 paddingLRZero btn-toolbar marginLeftZero" >
                    <select class="chzn-select form-control" id="moduleFilter" style="margin-left:5px;" >
                        <option value="">{\App\Language::translate('LBL_CONDITION_ALL', $QUALIFIED_MODULE)}</option>
                        {foreach item=item key=key from=$SUPPORTED_MODULE_MODELS}
                            <option value="{$item}">{\App\Language::translate($item, $item)}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            <br />
            <div class="row" id="list_doc">
                <table class="table table-bordered table-condensed listViewEntriesTable">
                    <thead>
                        <tr class="listViewHeaders" >
                            <th width="30%">{\App\Language::translate('LBL_MODULE_NAME',$QUALIFIED_MODULE)}</th>
                            <th>{\App\Language::translate('DOC_NAME',$QUALIFIED_MODULE)}</th>
                            <th colspan="2"></th>
                        </tr>
                    </thead>
                    {if !empty($DOC_TPL_LIST)}

                    <tbody>
                        {foreach from=$DOC_TPL_LIST item=item key=key}
                        <tr class="listViewEntries" data-id="{$item.id}">
                                <td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}">{\App\Language::translate($item.module, $item.module)}</td>
                                <td onclick="location.href = jQuery(this).data('url')" data-url="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}"> {\App\Language::translate($item.summary, $QUALIFIED_MODULE)}</td>
                                <td><a class="pull-right edit_tpl" href="index.php?module={$MODULE_NAME}&parent=Settings&view=Step1&tpl_id={$item.id}"><!--<span title="{\App\Language::translate('LBL_EDIT')}" class="glyphicon glyphicon-pencil alignMiddle"></span>--></a>
                                    <a href='index.php?module={$MODULE_NAME}&parent=Settings&action=DeleteTemplate&tpl_id={$item.id}' class="pull-right marginRight10px">
                                        <span type="{\App\Language::translate('REMOVE_TPL', $QUALIFIED_MODULE)}" class="glyphicon glyphicon-trash alignMiddle"></span></a>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
                {else}
                    <table class="emptyRecordsDiv">
                        <tbody>
                            <tr>
                                <td>
                                    {\App\Language::translate('LBL_NO_TPL_ADDED',$QUALIFIED_MODULE)}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                {/if}
            </div>
        </div>
    </div>
</div>
