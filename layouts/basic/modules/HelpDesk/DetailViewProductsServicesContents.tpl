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
	<div>
		{* Summary View Products Widget*}
		{if \App\Module::isModuleActive('Products')}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_products hideActionImages" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_PRODUCTS">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="Products" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{vtranslate('Interested products',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							<div class="pull-right">
								<button class="btn btn-default showModal" type="button" data-url="index.php?module=Products&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
									<span class="glyphicon glyphicon-zoom-in" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</div>
						</div>
					</div>
					<div class="widget_contents">
					</div>
				</div>
				<div class="widgetContainer_productsCategory" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedTree" data-name="LBL_RELATED_PRODUCTS">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="Products" />
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Assets Widget*}
		{if \App\Module::isModuleActive('Assets')}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_assets2" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Assets&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_ASSETS">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="Assets" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{vtranslate('LBL_RELATED_ASSETS',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							<span class="pull-right">
								<button class="btn btn-default showModal" type="button" data-url="index.php?module=Assets&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
									<span class="glyphicon glyphicon-zoom-in" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</span>
						</div>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{* Summary View Services Widget Ends Here*}
		{if \App\Module::isModuleActive('Services')}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_service hideActionImages" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_SERVICES">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="Services" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{vtranslate('Interested services',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							<span class="pull-right">
								<button class="btn btn-default showModal" type="button" data-url="index.php?module=Services&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
									<span class="glyphicon glyphicon-zoom-in" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</span>
						</div>
					</div>
					<div class="widget_contents">
					</div>
				</div>
				<div class="widgetContainer_productsCategory" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedTree" data-name="LBL_RELATED_SERVICES">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="Services" />
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
		{if \App\Module::isModuleActive('OSSSoldServices')}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSSoldServices&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OSSSS">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="OSSSoldServices" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{vtranslate('LBL_RELATED_OSSSS',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							<span class="pull-right">
								<button class="btn btn-default showModal" type="button" data-url="index.php?module=OSSSoldServices&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
									<span class="glyphicon glyphicon-zoom-in" title="{vtranslate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</span>
						</div>
					</div>
					<div class="widget_contents">
					</div>
				</div>
			</div>
		{/if}
	</div>
{/strip}
