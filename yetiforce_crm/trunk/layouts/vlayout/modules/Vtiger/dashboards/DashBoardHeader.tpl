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

<div class='dashboardHeading'>
    <div class="row-fluid">
        <div class="span3">
            {if $DASHBOARDHEADER_TITLE}
                    <h2 class="pull-left">{$DASHBOARDHEADER_TITLE}</h2>
            {/if}
        </div>
        <div class="span9">
            <div class="pull-right">
                <div class="btn-toolbar">
                    <span class="btn-group">
                            {if $WIDGETS|count gt 0}
                            <button class='btn addButton dropdown-toggle' data-toggle='dropdown'>
                                    <strong>{vtranslate('LBL_ADD_WIDGET')}</strong>
                                    <i class="caret"></i>
                            </button>

                            <ul class="dropdown-menu widgetsList pull-right" style="min-width:100%;text-align:left;">
                                    {assign var="MINILISTWIDGET" value=""}
                                    {foreach from=$WIDGETS item=WIDGET}
                                            {if $WIDGET->getName() eq 'MiniList'}
                                                    {assign var="MINILISTWIDGET" value=$WIDGET} {* Defer to display as a separate group *}
                                            {elseif $WIDGET->getName() eq 'Notebook'}
                                                    {assign var="NOTEBOOKWIDGET" value=$WIDGET} {* Defer to display as a separate group *}
                                            {else}
                                            <li>
                                                    <a onclick="Vtiger_DashBoard_Js.addWidget(this, '{$WIDGET->getUrl()}')" href="javascript:void(0);"
                                                            data-linkid="{$WIDGET->get('linkid')}" data-name="{$WIDGET->getName()}" data-width="{$WIDGET->getWidth()}" data-height="{$WIDGET->getHeight()}">
                                                            {vtranslate($WIDGET->getTitle(), $MODULE_NAME)}</a>
                                            </li>
                                            {/if}
                                    {/foreach}

                                    {if $MINILISTWIDGET && $MODULE_NAME == 'Home'}
                                    <li class="divider"></li>
                                    <li>
                                            <a onclick="Vtiger_DashBoard_Js.addMiniListWidget(this, '{$MINILISTWIDGET->getUrl()}')" href="javascript:void(0);"
                                                    data-linkid="{$MINILISTWIDGET->get('linkid')}" data-name="{$MINILISTWIDGET->getName()}" data-width="{$MINILISTWIDGET->getWidth()}" data-height="{$MINILISTWIDGET->getHeight()}">
                                                    {vtranslate($MINILISTWIDGET->getTitle(), $MODULE_NAME)}</a>
                                    </li>
                                    <li>
                                            <a onclick="Vtiger_DashBoard_Js.addNoteBookWidget(this, '{$NOTEBOOKWIDGET->getUrl()}')" href="javascript:void(0);"
                                                    data-linkid="{$NOTEBOOKWIDGET->get('linkid')}" data-name="{$NOTEBOOKWIDGET->getName()}" data-width="{$NOTEBOOKWIDGET->getWidth()}" data-height="{$NOTEBOOKWIDGET->getHeight()}">
                                                    {vtranslate($NOTEBOOKWIDGET->getTitle(), $MODULE_NAME)}</a>
                                    </li>
                                    {/if}

                            </ul>
                            {else if $MODULE_PERMISSION}
                                    <button class='btn addButton dropdown-toggle' disabled="disabled" data-toggle='dropdown'>
                                            <strong>{vtranslate('LBL_ADD_WIDGET')}</strong> &nbsp;&nbsp;
                                    <i class="caret"></i>
                            </button>
                            {/if}
                    </span>
                </div>
              </div>
         </div>
    </div>
</div>
