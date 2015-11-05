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

<div class="row">
	{* Summary View Products Widget*}
	{if vtlib_isModuleActive('Products')}
		<div class="summaryWidgetContainer">
			<div class="widgetContainer_products" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_PRODUCTS">
				<div class="widget_header row">
					<input type="hidden" name="relatedModule" value="Products" />
					<span class="col-md-9 margin0px"><h4>{vtranslate('Interested products',$MODULE_NAME)}</h4></span>
					<span class="col-md-3">
						<span class="pull-right">
							<button class="btn btn-default addButton selectRelation" type="button" data-modulename="Products" >
								<span class="glyphicon glyphicon-zoom-in" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}"></span>
							</button>
						</span>
					</span>
				</div>
				<div class="widget_contents">
				</div>
			</div>
		</div>
	{/if}

	{* Summary View OutsourcedProducts Widget*}
	{if vtlib_isModuleActive('OutsourcedProducts')}
		<div class="summaryWidgetContainer">
			<div class="widgetContainer_assets" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OutsourcedProducts&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OP">
				<div class="widget_header row">
					<input type="hidden" name="relatedModule" value="OutsourcedProducts" />
					<span class="col-md-9 margin0px"><h4>{vtranslate('LBL_RELATED_OP',$MODULE_NAME)}</h4></span>
					<span class="col-md-3">
						{if {Users_Privileges_Model::isPermitted('OutsourcedProducts', 'EditView')}}
							<span class="pull-right">
								<button class="btn btn-default createRecord" type="button" data-url="index.php?module=OutsourcedProducts&view=QuickCreateAjax">
									<span class="glyphicon glyphicon-plus-sign" title="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
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

	{* Summary View Assets Widget*}
	{if $MODULE_NAME != 'Leads' && vtlib_isModuleActive('Assets')}
		<div class="summaryWidgetContainer">
			<div class="widgetContainer_assets2" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Assets&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_ASSETS">
				<div class="widget_header row">
					<input type="hidden" name="relatedModule" value="Assets" />
					<span class="col-md-9 margin0px"><h4>{vtranslate('LBL_RELATED_ASSETS',$MODULE_NAME)}</h4></span>
					<span class="col-md-3">
						{if {Users_Privileges_Model::isPermitted('Assets', 'EditView')} }
							<span class="pull-right">
								<button class="btn btn-default createRecord" type="button" data-url="index.php?module=Assets&view=QuickCreateAjax">
									<span class="glyphicon glyphicon-plus-sign" title="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
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

	{* Summary View Services Widget Ends Here*}
	{if vtlib_isModuleActive('Services')}
		<div class="summaryWidgetContainer">
			<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_SERVICES">
				<div class="widget_header row">
					<input type="hidden" name="relatedModule" value="Services" />
					<span class="col-md-9 margin0px"><h4>{vtranslate('Interested services',$MODULE_NAME)}</h4></span>
					<span class="col-md-3">
						<span class="pull-right">
							<button class="btn btn-default addButton selectRelation" style="margin:0 auto;" type="button" data-modulename="Services" >
								<span class="glyphicon glyphicon-zoom-in" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}"></span>
							</button>
						</span>
					</span>
				</div>
				<div class="widget_contents">
				</div>
			</div>
		</div>
	{/if}
	
	{* Summary View OSSOutsourcedServices Widget Start Here*}
	{if vtlib_isModuleActive('OSSOutsourcedServices')}
	<div class="summaryWidgetContainer">
		<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSOutsourcedServices&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OSSOS">
			<div class="widget_header row">
				<input type="hidden" name="relatedModule" value="OSSOutsourcedServices" />
				<span class="col-md-9 margin0px"><h4>{vtranslate('LBL_RELATED_OSSOS',$MODULE_NAME)}</h4></span>
				<span class="col-md-3">
					{if {Users_Privileges_Model::isPermitted('OSSOutsourcedServices', 'EditView')} }
						<span class="pull-right">
							<button class="btn btn-default createRecord" type="button" data-url="index.php?module=OSSOutsourcedServices&view=QuickCreateAjax">
								<span class="glyphicon glyphicon-plus-sign" title="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
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
	{if $MODULE_NAME != 'Leads' && vtlib_isModuleActive('OSSSoldServices')}
		<div class="summaryWidgetContainer">
			<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSSoldServices&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OSSSS">
				<div class="widget_header row">
					<input type="hidden" name="relatedModule" value="OSSSoldServices" />
					<span class="col-md-9 margin0px"><h4>{vtranslate('LBL_RELATED_OSSSS',$MODULE_NAME)}</h4></span>
					<span class="col-md-3">
						{if {Users_Privileges_Model::isPermitted('OSSSoldServices', 'EditView')} }
							<span class="pull-right">
								<button class="btn btn-default createRecord" type="button" data-url="index.php?module=OSSSoldServices&view=QuickCreateAjax">
									<span class="glyphicon glyphicon-plus-sign" title="{vtranslate('LBL_ADD',$MODULE_NAME)}"></span>
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
