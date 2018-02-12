{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div>
		{* Summary View Products Widget*}
		{if isset($RELATIONS[\App\Module::getModuleId('Products')])}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_products hideActionImages" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_PRODUCTS">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="Products" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{\App\Language::translate('Interested products',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							<div class="float-right">
								<button class="btn btn-light showModal" type="button" data-modalid="ProductsModal" data-url="index.php?module=Products&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
									<span class="fas fa-search-plus" title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</div>
						</div>
					</div>
					<div class="widget_contents"></div>
				</div>
				{if $RELATIONS[\App\Module::getModuleId('Products')]->isTreeRelation()}
					<div class="widgetContainer_productsCategory" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedTree" data-name="LBL_RELATED_PRODUCTS">
						<div class="widget_header row">
							<input type="hidden" name="relatedModule" value="Products" />
						</div>
						<div class="widget_contents"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View OutsourcedProducts Widget*}
		{if isset($RELATIONS[\App\Module::getModuleId('OutsourcedProducts')])}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_assets" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OutsourcedProducts&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OP">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="OutsourcedProducts" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{\App\Language::translate('LBL_RELATED_OP',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							<div class="float-right">
								<button class="btn btn-light showModal" type="button" data-modalid="OutsourcedProductsModal" data-module="OutsourcedProducts" data-url="index.php?module=OutsourcedProducts&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
									<span class="fas fa-search-plus" title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</div>
						</div>
					</div>
					<div class="widget_contents"></div>
				</div>
				{if $RELATIONS[\App\Module::getModuleId('OutsourcedProducts')]->isTreeRelation()}
					<div class="widgetContainer_productsCategory" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OutsourcedProducts&mode=showRelatedTree" data-name="LBL_RELATED_OP">
						<div class="widget_header row">
							<input type="hidden" name="relatedModule" value="OutsourcedProducts" />
						</div>
						<div class="widget_contents"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View Assets Widget*}
		{if isset($RELATIONS[\App\Module::getModuleId('Assets')])}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_assets2" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Assets&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_ASSETS">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="Assets" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{\App\Language::translate('LBL_RELATED_ASSETS',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							{if {\App\Privilege::isPermitted('Assets', 'CreateView')} }
								<span class="float-right">
									<button class="btn btn-light createRecord" type="button" data-url="index.php?module=Assets&view=QuickCreateAjax">
										<span class="fas fa-plus-circle" title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}"></span>
									</button>
								</span>
							{/if}
						</div>
					</div>
					<div class="widget_contents"></div>
				</div>
			</div>
		{/if}
		{* Summary View Services Widget Ends Here*}
		{if isset($RELATIONS[\App\Module::getModuleId('Services')])}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_service hideActionImages" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_SERVICES">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="Services" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{\App\Language::translate('Interested services',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							<span class="float-right">
								<button class="btn btn-light showModal" type="button" data-modalid="ServicesModal" data-url="index.php?module=Services&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
									<span class="fas fa-search-plus" title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</span>
						</div>
					</div>
					<div class="widget_contents"></div>
				</div>
				{if $RELATIONS[\App\Module::getModuleId('Services')]->isTreeRelation()}
					<div class="widgetContainer_productsCategory" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedTree" data-name="LBL_RELATED_SERVICES">
						<div class="widget_header row">
							<input type="hidden" name="relatedModule" value="Services" />
						</div>
						<div class="widget_contents"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View OSSOutsourcedServices Widget Start Here*}
		{if isset($RELATIONS[\App\Module::getModuleId('OSSOutsourcedServices')])}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSOutsourcedServices&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OSSOS">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="OSSOutsourcedServices" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{\App\Language::translate('LBL_RELATED_OSSOS',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							<div class="float-right">
								<button class="btn btn-light showModal" type="button" data-modalid="OSSOutsourcedServicesModal" data-module="OSSOutsourcedServices" data-url="index.php?module=OSSOutsourcedServices&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
									<span class="fas fa-search-plus" title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
								</button>
							</div>
						</div>
					</div>
					<div class="widget_contents"></div>
				</div>
				{if $RELATIONS[\App\Module::getModuleId('OSSOutsourcedServices')]->isTreeRelation()}
					<div class="widgetContainer_productsCategory" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSOutsourcedServices&mode=showRelatedTree" data-name="LBL_RELATED_OSSOS">
						<div class="widget_header row">
							<input type="hidden" name="relatedModule" value="OSSOutsourcedServices" />
						</div>
						<div class="widget_contents"></div>
					</div>
				{/if}
			</div>
		{/if}
		{if isset($RELATIONS[\App\Module::getModuleId('OSSSoldServices')])}
			<div class="summaryWidgetContainer">
				<div class="widgetContainer_service" data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSSoldServices&mode=showRelatedRecords&page=1&limit={$LIMIT}" data-name="LBL_RELATED_OSSSS">
					<div class="widget_header row">
						<input type="hidden" name="relatedModule" value="OSSSoldServices" />
						<div class="col-xs-10 col-sm-10 col-md-9 margin0px"><h4>{\App\Language::translate('LBL_RELATED_OSSSS',$MODULE_NAME)}</h4></div>
						<div class="col-xs-1 col-md-3 summaryWidgetIcon">
							{if {\App\Privilege::isPermitted('OSSSoldServices', 'CreateView')} }
								<span class="float-right">
									<button class="btn btn-light createRecord" type="button" data-url="index.php?module=OSSSoldServices&view=QuickCreateAjax">
										<span class="fas fa-plus-circle" title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}"></span>
									</button>
								</span>
							{/if}
						</div>
					</div>
					<div class="widget_contents"></div>
				</div>
			</div>
		{/if}
	</div>
{/strip}
