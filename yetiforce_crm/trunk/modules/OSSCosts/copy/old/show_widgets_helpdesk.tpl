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
{strip}
{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET']}
	{if ($DETAIL_VIEW_WIDGET->getLabel() eq 'Documents') }
		{assign var=DOCUMENT_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'ModComments')}
		{assign var=COMMENTS_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{elseif ($DETAIL_VIEW_WIDGET->getLabel() eq 'LBL_UPDATES')}
		{assign var=UPDATES_WIDGET_MODEL value=$DETAIL_VIEW_WIDGET}
	{/if}
{/foreach}

<div class="row-fluid">
	<div class="span7">
		{* Module Summary View*}
			<div class="summaryView row-fluid">
				{$MODULE_SUMMARY}
			</div>
		{* Module Summary View Ends Here*}

		{* Summary View Comments Widget*}
		{if $COMMENTS_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_comments" data-url="{$COMMENTS_WIDGET_MODEL->getUrl()}" data-name="{$COMMENTS_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="{$COMMENTS_WIDGET_MODEL->get('linkName')}" />
						<span class="span9 margin0px"><h4>{vtranslate($COMMENTS_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class="pull-right">
								{if $COMMENTS_WIDGET_MODEL->get('action')}
									<button class="btn pull-right addButton createRecord" type="button" data-url="{$COMMENTS_WIDGET_MODEL->get('actionURL')}">
										<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
									</button>
								{/if}
							</span>
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Comments Widget Ends Here*}
	</div>

	<div class='span5' style="overflow: hidden">
		{* Summary View Related Activities Widget*}
			<div id="relatedActivities">
				{$RELATED_ACTIVITIES}
			</div>
		{* Summary View Related Activities Widget Ends Here*}

		{* Summary View Documents Widget*}
		{if $DOCUMENT_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_documents" data-url="{$DOCUMENT_WIDGET_MODEL->getUrl()}" data-name="{$DOCUMENT_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="{$DOCUMENT_WIDGET_MODEL->get('linkName')}" />
						<span class="span9 margin0px"><h4 class="textOverflowEllipsis">{vtranslate($DOCUMENT_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class="pull-right">
								{if $DOCUMENT_WIDGET_MODEL->get('action')}
									<button class="btn pull-right addButton createRecord" type="button" data-url="{$DOCUMENT_WIDGET_MODEL->get('actionURL')}">
										<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
									</button>
								{/if}
							</span>
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Documents Widget Ends Here*}

		{* Summary View Updates Widget*}
		{if $UPDATES_WIDGET_MODEL}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_updates" data-url="{$UPDATES_WIDGET_MODEL->getUrl()}" data-name="{$UPDATES_WIDGET_MODEL->getLabel()}">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="{$UPDATES_WIDGET_MODEL->get('linkName')}" />
						<span class="span9 margin0px"><h4 class="textOverflowEllipsis">{vtranslate($UPDATES_WIDGET_MODEL->getLabel(),$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class="pull-right">
								{if $UPDATES_WIDGET_MODEL->get('action')}
									<button class="btn pull-right addButton createRecord" type="button" data-url="{$UPDATES_WIDGET_MODEL->get('actionURL')}">
										<strong>{vtranslate('LBL_ADD',$MODULE_NAME)}</strong>
									</button>
								{/if}
							</span>
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Updates Widget Ends Here*}
	</div>
</div>
{/strip}