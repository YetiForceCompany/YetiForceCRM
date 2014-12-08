{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
********************************************************************************/
-->*}
{strip}
    <div id="relatedTabOrder">
    <div class="container-fluid" id="layoutEditorContainer">
        <input id="selectedModuleName" type="hidden" value="{$SELECTED_MODULE_NAME}" />
        <div class="widget_header row-fluid">
            <div class="span8">
                <h3>{vtranslate('LBL_REL_MODULE_LAYOUT_EDITOR', $QUALIFIED_MODULE)}</h3>
            </div>
            <div class="span4">
                <div class="pull-right">
                    <select class="select2 span3" name="layoutEditorRelModules">
                        {foreach item=MODULE_NAME from=$SUPPORTED_MODULES}
                            <option value="{$MODULE_NAME}" {if $MODULE_NAME eq $SELECTED_MODULE_NAME} selected {/if}>{vtranslate($MODULE_NAME, $QUALIFIED_MODULE)}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <hr>
        <div class="relatedTabModulesList">
            {if empty($RELATED_MODULES)}
                <div class="emptyRelatedTabs">
                    <div class="recordDetails">
                        <p class="textAlignCenter">{vtranslate('LBL_NO_RELATED_INFORMATION',$QUALIFIED_MODULE)}</p>
                    </div>
                </div>
            {else}
                <div class="relatedListContainer">
                    <div class="row-fluid">
                        <div class="span2">
                            <strong>
                                {vtranslate('LBL_ARRANGE_RELATED_LIST', $QUALIFIED_MODULE)}
                            </strong>
                        </div>
                        <div class="span10 row-fluid">
                            <span class="span5">
                                <ul class="relatedModulesList" style="list-style: none;">
                                    {foreach item=MODULE_MODEL from=$RELATED_MODULES}
                                        {if $MODULE_MODEL->isActive()}
                                            <li class="relatedModule module_{$MODULE_MODEL->getId()} border1px contentsBackground" style="width: 200px; padding: 5px;" data-relation-id="{$MODULE_MODEL->getId()}">
                                                <a><img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/></a>&nbsp;&nbsp;
                                                <span class="moduleLabel">{vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->getRelationModuleName())}</span>
                                                <button class="close" data-dismiss="modal" title="{vtranslate('LBL_CLOSE')}">x</button>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            </span>
                            <span class="span7" style="padding: 5% 0;">
                                <i class="icon-info-sign alignMiddle"></i>&nbsp;{vtranslate('LBL_RELATED_LIST_INFO', $QUALIFIED_MODULE)}.<br><br>
                                <i class="icon-info-sign alignMiddle"></i>&nbsp;{vtranslate('LBL_REMOVE_INFO', $QUALIFIED_MODULE)}.<br><br>
                                <i class="icon-info-sign alignMiddle"></i>&nbsp;{vtranslate('LBL_ADD_MODULE_INFO', $QUALIFIED_MODULE)}
                            </span>
                        </div>
                    </div>
                    <br>
                    <div class="row-fluid">
                        <div class="span2">
                            <strong>
                                {vtranslate('LBL_SELECT_MODULE_TO_ADD', $QUALIFIED_MODULE)}
                            </strong>
                        </div>
                        <div class="span4">
                            {assign var=ModulesList value=[]}
                            {assign var=removedModuleIds value=array()}
                            <ul style="list-style: none; width:213px;" class="displayInlineBlock">
                                <li>
                                    <div class="row-fluid"><select class="select2" multiple name="addToList" placeholder="{vtranslate('LBL_SELECT_MODULE', $QUALIFIED_MODULE)}">
                                            {foreach item=MODULE_MODEL from=$RELATED_MODULES}
                                                {$ModulesList[$MODULE_MODEL->getId()] = vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->getRelationModuleName())}
                                                {if !$MODULE_MODEL->isActive()}
                                                    {array_push($removedModuleIds, $MODULE_MODEL->getId())}
                                                    <option value="{$MODULE_MODEL->getId()}">
                                                        {vtranslate($MODULE_MODEL->get('label'), $MODULE_MODEL->getRelationModuleName())}
                                                    </option>
                                                {/if}
                                            {/foreach}
                                        </select>
                                    </div>
                                </li>
                            </ul>
                            <input type="hidden" class="ModulesListArray" value='{ZEND_JSON::encode($ModulesList)}' />
                            <input type="hidden" class="RemovedModulesListArray" value='{ZEND_JSON::encode($removedModuleIds)}' />
                        </div>
                        <div class="span6">
                            <button class="btn btn-success saveRelatedList" type="button" disabled="disabled"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
                            <br>
                        </div>
                    </div>
                    <li class="moduleCopy hide border1px contentsBackground" style="width: 200px; padding: 5px;">
                        <a>
                            <img src="{vimage_path('drag.png')}" title="{vtranslate('LBL_DRAG',$QUALIFIED_MODULE)}"/>
                        </a>&nbsp;&nbsp;
                        <span class="moduleLabel"></span>
                        <button class="close" data-dismiss="modal" title="{vtranslate('LBL_CLOSE')}">x</button>
                    </li>
                </div>
            {/if}
        </div>
        </div>
    </div>
{/strip}