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

<div class="row-fluid">
		{* Summary View Products Widget*}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_products" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_PRODUCTS">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="Products" />
						<span class="span9 margin0px"><h4>{vtranslate('Interested products',$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class="pull-right">
								<button class="btn addButton selectRelation" type="button" data-modulename="Products" >
									<span class="icon-zoom-in" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</span>
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{* Summary View Products Widget Ends Here*}
		{* Summary View OutsourcedProducts Widget*}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_assets" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OutsourcedProducts&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OP">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="OutsourcedProducts" />
						<span class="span9 margin0px"><h4>{vtranslate('LBL_RELATED_OP',$MODULE_NAME)}</h4></span>
						<span class="span3">
							{if {Users_Privileges_Model::isPermitted('OutsourcedProducts', 'EditView')}}
								<span class="pull-right">
									<button class="btn createRecord" type="button" data-url="index.php?module=OutsourcedProducts&view=QuickCreateAjax">
										<span class="icon-plus-sign" title="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
									</button>
								</span>
							{/if}	
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>		
		{* Summary View OutsourcedProducts Widget Ends Here*}
		{* Summary View Assets Widget*}
		{if $MODULE_NAME != 'Leads'}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_assets2" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Assets&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_ASSETS">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="Assets" />
						<span class="span9 margin0px"><h4>{vtranslate('LBL_RELATED_ASSETS',$MODULE_NAME)}</h4></span>
						<span class="span3">
							{if {Users_Privileges_Model::isPermitted('Assets', 'EditView')} }
								<span class="pull-right">
									<button class="btn createRecord" type="button" data-url="index.php?module=Assets&view=QuickCreateAjax">
										<span class="icon-plus-sign" title="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
									</button>
								</span>
							{/if}
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}

		{* Summary View Assets Widget Ends Here*}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_SERVICES">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="Services" />
						<span class="span9 margin0px"><h4>{vtranslate('Interested services',$MODULE_NAME)}</h4></span>
						<span class="span3">
							<span class="pull-right">
								<button class="btn addButton selectRelation" style="margin:0 auto;" type="button" data-modulename="Services" >
									<span class="icon-zoom-in icon-2x" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</span>
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
			{* Summary View OSSOS Widget Start Here*}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSOutsourcedServices&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OSSOS">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="OSSOutsourcedServices" />
						<span class="span9 margin0px"><h4>{vtranslate('LBL_RELATED_OSSOS',$MODULE_NAME)}</h4></span>
						<span class="span3">
							{if {Users_Privileges_Model::isPermitted('OSSOutsourcedServices', 'EditView')} }
								<span class="pull-right">
									<button class="btn createRecord" type="button" data-url="index.php?module=OSSOutsourcedServices&view=QuickCreateAjax">
										<span class="icon-plus-sign" title="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
									</button>
								</span>
							{/if}
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{if $MODULE_NAME != 'Leads'}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSSoldServices&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OSSSS">
					<div class="widget_header row-fluid">
						<input type="hidden" name="relatedModule" value="OSSSoldServices" />
						<span class="span9 margin0px"><h4>{vtranslate('LBL_RELATED_OSSSS',$MODULE_NAME)}</h4></span>
						<span class="span3">
							{if {Users_Privileges_Model::isPermitted('OSSSoldServices', 'EditView')} }
								<span class="pull-right">
									<button class="btn createRecord" type="button" data-url="index.php?module=OSSSoldServices&view=QuickCreateAjax">
										<span class="icon-plus-sign" title="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
									</button>
								</span>
							{/if}
						</span>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
</div>
{/strip}