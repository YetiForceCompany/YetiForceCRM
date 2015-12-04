{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}
{strip}
<div class="">
	<div class='widget_header row '>
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
    <div class="contents">
        <form id="customerPortalForm" class="form-horizontal"  method="POST">
            <div class="row">
                <div class="col-md-6">
                    <input type="hidden" name="portalModulesInfo" value="" />
                    <div class="form-group">
                        <label class="col-md-3 control-label">{vtranslate('LBL_PRIVILEGES', $QUALIFIED_MODULE)}</label>
                        <div class="col-md-7 controls">
							<select name="privileges" class="select2 form-control">
								{foreach item=USER_MODEL from=$USER_MODELS}
									{assign var=USER_ID value=$USER_MODEL->getId()}
									<option value="{$USER_ID}" {if $CURRENT_PORTAL_USER eq $USER_ID} selected {/if}>{$USER_MODEL->getName()}</option>
								{/foreach}
							</select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label">{vtranslate('LBL_DEFAULT_ASSIGNEE', $QUALIFIED_MODULE)}</label>
                        <div class="col-md-7 controls">
							<select name="defaultAssignee" class="select2 form-control">
								<optgroup style="border: none" label="{vtranslate('LBL_USERS', $QUALIFIED_MODULE)}" >
									{foreach item=USER_MODEL from=$USER_MODELS}
										{assign var=USER_ID value=$USER_MODEL->getId()}
										<option value="{$USER_ID}" {if $CURRENT_DEFAULT_ASSIGNEE eq $USER_ID} selected {/if}>{$USER_MODEL->getName()}</option>
									{/foreach}
								</optgroup>
								<optgroup style="border: none" label="{vtranslate('LBL_GROUPS', $QUALIFIED_MODULE)}">
									{foreach item=GROUP_MODEL from=$GROUP_MODELS}
										{assign var=GROUP_ID value=$GROUP_MODEL->getId()}
										<option value="{$GROUP_ID}" {if $CURRENT_DEFAULT_ASSIGNEE eq $GROUP_ID} selected {/if}>{$GROUP_MODEL->getName()}</option>
									{/foreach}
								</optgroup>
							</select>
                        </div>	
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">{vtranslate('LBL_PORTAL_URL', $QUALIFIED_MODULE)}</label>
                        <div class="col-md-7 controls form-control-static">
                            <span class="help-inline"><a target="_blank" href="{$PORTAL_URL}">{$PORTAL_URL}</a></span>
                        </div>
                    </div>

                </div>

                <div class="col-md-6">
                    <div class="alert alert-info">
                        <p>{vtranslate('LBL_PRIVILEGES_MESSAGE', $QUALIFIED_MODULE)}</p>

                        <p>{vtranslate('LBL_DEFAULT_ASSIGNEE_MESSAGE', $QUALIFIED_MODULE)}</p>

                        <p>{vtranslate('LBL_PORTAL_URL_MESSAGE', $QUALIFIED_MODULE)}</p>
                    </div>
                </div>
            </div>

            <div class="">
                <i class="glyphicon glyphicon-info-sign"></i>&nbsp;
                {vtranslate('LBL_DRAG_AND_DROP_MESSAGE', $QUALIFIED_MODULE)}
            </div>
            <br>
            <div class="">
                <table id="portalModulesTable" class="table table-bordered table-condensed themeTableColor">
                    <thead>
                        <tr class="blockHeader">
                            <th class="textAlignCenter">
                                {vtranslate('LBL_MODULE_NAME', $QUALIFIED_MODULE)}
                            </th>
                            <th class="textAlignCenter">
                                {vtranslate('LBL_ENABLE_MODULE', $QUALIFIED_MODULE)}
                            </th>
                            <th class="textAlignCenter">
                                {vtranslate('LBL_VIEW_ALL_RECORDS', $QUALIFIED_MODULE)}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach key=TAB_ID item=MODEL from=$MODULES_MODELS}
                            {assign var=MODULE_NAME value=$MODEL->get('name')}
                            <tr class="portalModuleRow" data-id="{$TAB_ID}" data-sequence="{$MODEL->get('sequence')}" data-module="{$MODULE_NAME}">
                        <input type="hidden" name="portalModulesInfo[{$TAB_ID}][sequence]" value="{$MODEL->get('sequence')}" />
                        <td>
                            <div class="row">
                                <span class="col-md-1 col-xs-2"><a><img src="{vimage_path('drag.png')}" border="0" title="{vtranslate('LBL_DRAG',$MODULE)}"/></a></span>
                                <span class="col-md-11 col-xs-9">{vtranslate($MODULE_NAME, $MODULE_NAME)}</span>
                            </div>
                        </td>
                        <td class="textAlignCenter">
                            <input type="hidden" name="portalModulesInfo[{$TAB_ID}][visible]" value="0" />
                            <input type="checkbox" name="portalModulesInfo[{$TAB_ID}][visible]" value="1" {if $MODEL->get('visible') == '1'} checked {/if}/>
                        </td>
                        <td class="textAlignCenter">
                            <label class="radio col-sm-2">
                                <input type="radio" name="portalModulesInfo[{$TAB_ID}][prefValue]" value="1" {if $MODEL->get('prefvalue') == '1'} checked="checked" {/if}/>
                                &nbsp;{vtranslate('LBL_YES', $QUALIFIED_MODULE)}
                            </label>
                            <label class="radio col-sm-2">
                                <input type="radio" name="portalModulesInfo[{$TAB_ID}][prefValue]" value="0" {if $MODEL->get('prefvalue') == '0'} checked="checked" {/if}/>
                                &nbsp;{vtranslate('LBL_NO', $QUALIFIED_MODULE)}
                            </label>
                        </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>

            <div class="">
                <div class="padding1per paddingRightZero">
                    <button class="btn btn-success pull-right" type="submit" disabled="true" name="savePortalInfo"><strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong></button>
                </div>
                <div class="col-md-6">&nbsp;</div>
            </div>
        </form>
    </div>
</div>
{/strip}
