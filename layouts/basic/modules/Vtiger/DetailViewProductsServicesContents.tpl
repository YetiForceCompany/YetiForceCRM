{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	{* Summary View Products Widget*}
	<div class="tpl-DetailViewProductsServicesContents">
		{assign var=PRODUCTS value=\App\Module::getModuleId('Products')}
		{if isset($RELATIONS[$PRODUCTS])}
			<div class="c-detail-widget js-detail-widget u-mb-13px" data-js="container">
				<div class="widgetContainer_products hideActionImages"
					 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					 data-name="LBL_RELATED_PRODUCTS">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="Products"/>
							<div class="col-10 col-sm-10 col-md-9 margin0px"><h5
										class="mb-0">{\App\Language::translate('Interested products',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								<div class="float-right">
									<button class="btn btn-light showModal" type="button"
											data-modalid="ProductsModal"
											data-url="index.php?module=Products&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
											<span class="fas fa-search-plus"
												  title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
									</button>
								</div>
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content">
						<table class="table mb-0 mt-1 border-0">
							<thead>
							<tr>
								<th class="py-0 border-0">
									<h6 class="py-1 my-0">{\App\Language::translate('Products',$MODULE_NAME)}</h6>
								</th>
							</tr>
							</thead>
						</table>
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
				{if $RELATIONS[$PRODUCTS]->isTreeRelation()}
					<div class="widgetContainer_productsCategory"
						 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Products&mode=showRelatedTree"
						 data-name="LBL_RELATED_PRODUCTS">
						<div class="c-detail-widget__content">
							<table class="table mb-0 mt-1 border-0">
								<thead>
								<tr>
									<th class="py-0 border-0">
										<h6 class="py-1 my-0">{\App\Language::translate('LBL_CATEGORIES', $MODULE_NAME)}</h6>
									</th>
								</tr>
								</thead>
							</table>
						</div>
						<div class="c-detail-widget__header js-detail-widget-header form-row"
							 data-js="container|value">
							<input type="hidden" name="relatedModule" value="Products"/>
						</div>
						<div class="c-detail-widget__content js-detail-widget-content"
							 data-js="container|value"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View OutsourcedProducts Widget*}
		{assign var=OUTSOURCEDPRODUCTS value=\App\Module::getModuleId('OutsourcedProducts')}
		{if isset($RELATIONS[$OUTSOURCEDPRODUCTS])}
			<div class="c-detail-widget js-detail-widget u-mb-13px" data-js="container">
				<div class="widgetContainer_assets"
					 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OutsourcedProducts&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					 data-name="LBL_RELATED_OP">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="OutsourcedProducts"/>
							<div class="col-10 col-sm-10 col-md-9 margin0px"><h5
										class="mb-0">{\App\Language::translate('LBL_RELATED_OP',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								<div class="float-right">
									<button class="btn btn-light showModal" type="button"
											data-modalid="OutsourcedProductsModal" data-module="OutsourcedProducts"
											data-url="index.php?module=OutsourcedProducts&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
											<span class="fas fa-search-plus"
												  title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
									</button>
								</div>
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content">
						<table class="table mb-0 mt-1 border-0">
							<thead>
							<tr>
								<th class="py-0 border-0">
									<h6 class="py-1 my-0">{\App\Language::translate('Products',$MODULE_NAME)}</h6>
								</th>
							</tr>
							</thead>
						</table>
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
				{if $RELATIONS[$OUTSOURCEDPRODUCTS]->isTreeRelation()}
					<div class="widgetContainer_productsCategory"
						 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OutsourcedProducts&mode=showRelatedTree"
						 data-name="LBL_RELATED_OP">
						<div class="c-detail-widget__content">
							<table class="table mb-0 mt-1 border-0">
								<thead>
								<tr>
									<th class="py-0 border-0">
										<h6 class="py-1 my-0">{\App\Language::translate('LBL_CATEGORIES',$MODULE_NAME)}</h6>
									</th>
								</tr>
								</thead>
							</table>
						</div>
						<div class="c-detail-widget__header js-detail-widget-header form-row"
							 data-js="container|value">
							<input type="hidden" name="relatedModule" value="OutsourcedProducts"/>
						</div>
						<div class="c-detail-widget__content js-detail-widget-content"
							 data-js="container|value"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View Assets Widget*}
		{assign var=ASSETS value=\App\Module::getModuleId('Assets')}
		{if isset($RELATIONS[$ASSETS])}
			<div class="c-detail-widget js-detail-widget u-mb-13px" data-js="container">
				<div class="widgetContainer_assets2"
					 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Assets&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					 data-name="LBL_RELATED_ASSETS">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="Assets"/>
							<div class="col-10 col-sm-10 col-md-9 margin0px"><h5
										class="mb-0">{\App\Language::translate('LBL_RELATED_ASSETS',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								{if {\App\Privilege::isPermitted('Assets', 'CreateView')} }
									<span class="float-right">
										<button class="btn btn-light createRecord" type="button"
												data-url="index.php?module=Assets&view=QuickCreateAjax">
											<span class="fas fa-plus-circle"
												  title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}"></span>
										</button>
									</span>
								{/if}
							</div>
						</div>
						<hr class="widgetHr">
					</div>

					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
			</div>
		{/if}
		{* Summary View Services Widget Ends Here*}
		{assign var=SERVICES value=\App\Module::getModuleId('Services')}
		{if isset($RELATIONS[$SERVICES])}
			<div class="c-detail-widget js-detail-widget u-mb-13px" data-js="container">
				<div class="widgetContainer_service hideActionImages"
					 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					 data-name="LBL_RELATED_SERVICES">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="Services"/>
							<div class="col-10 col-sm-10 col-md-9 margin0px"><h5
										class="mb-0">{\App\Language::translate('Interested services',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								<span class="float-right">
									<button class="btn btn-light showModal" type="button" data-modalid="ServicesModal"
											data-url="index.php?module=Services&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
										<span class="fas fa-search-plus"
											  title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
									</button>
								</span>
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content">
						<table class="table mb-0 mt-1 border-0">
							<thead>
							<tr>
								<th class="py-0 border-0">
									<h6 class="py-1 my-0">{\App\Language::translate('Services',$MODULE_NAME)}</h6>
								</th>
							</tr>
							</thead>
						</table>
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
				{if $RELATIONS[$SERVICES]->isTreeRelation()}
					<div class="widgetContainer_productsCategory"
						 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=Services&mode=showRelatedTree"
						 data-name="LBL_RELATED_SERVICES">
						<div class="c-detail-widget__content">
							<table class="table mb-0 mt-1 border-0">
								<thead>
								<tr>
									<th class="py-0 border-0">
										<h6 class="py-1 my-0">{\App\Language::translate('LBL_CATEGORIES',$MODULE_NAME)}</h6>
									</th>
								</tr>
								</thead>
							</table>
						</div>
						<div class="c-detail-widget__header js-detail-widget-header form-row"
							 data-js="container|value">
							<input type="hidden" name="relatedModule" value="Services"/>
						</div>
						<div class="c-detail-widget__content js-detail-widget-content"
							 data-js="container|value"></div>
					</div>
				{/if}
			</div>
		{/if}
		{* Summary View OSSOutsourcedServices Widget Start Here*}
		{assign var=OSSOUTSOURCEDSERVICES value=\App\Module::getModuleId('OSSOutsourcedServices')}
		{if isset($RELATIONS[$OSSOUTSOURCEDSERVICES])}
			<div class="c-detail-widget js-detail-widget u-mb-13px" data-js="container">
				<div class="widgetContainer_service"
					 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSOutsourcedServices&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					 data-name="LBL_RELATED_OSSOS">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="OSSOutsourcedServices"/>
							<div class="col-10 col-sm-10 col-md-9 margin0px"><h5
										class="mb-0">{\App\Language::translate('LBL_RELATED_OSSOS',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								<div class="float-right">
									<button class="btn btn-light showModal" type="button"
											data-modalid="OSSOutsourcedServicesModal"
											data-module="OSSOutsourcedServices"
											data-url="index.php?module=OSSOutsourcedServices&view=TreeCategoryModal&src_module={$MODULE_NAME}&src_record={$RECORDID}">
											<span class="fas fa-search-plus"
												  title="{\App\Language::translate('LBL_SELECT',$MODULE_NAME)}"></span>
									</button>
								</div>
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content">
						<table class="table mb-0 mt-1 border-0">
							<thead>
							<tr>
								<th class="py-0 border-0">
									<h6 class="py-1 my-0">{\App\Language::translate('Services',$MODULE_NAME)}</h6>
								</th>
							</tr>
							</thead>
						</table>
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
				{if $RELATIONS[$OSSOUTSOURCEDSERVICES]->isTreeRelation()}
					<div class="widgetContainer_productsCategory"
						 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSOutsourcedServices&mode=showRelatedTree"
						 data-name="LBL_RELATED_OSSOS">
						<div class="c-detail-widget__content">
							<table class="table mb-0 mt-1 border-0">
								<thead>
								<tr>
									<th class="py-0 border-0">
										<h6 class="py-1 my-0">{\App\Language::translate('LBL_CATEGORIES',$MODULE_NAME)}</h6>
									</th>
								</tr>
								</thead>
							</table>
						</div>
						<div class="c-detail-widget__header js-detail-widget-header form-row"
							 data-js="container|value">
							<input type="hidden" name="relatedModule" value="OSSOutsourcedServices"/>
						</div>
						<div class="c-detail-widget__content js-detail-widget-content"
							 data-js="container|value"></div>
					</div>
				{/if}
			</div>
		{/if}
		{assign var=OSSSOLDSERVICES value=\App\Module::getModuleId('OSSSoldServices')}
		{if isset($RELATIONS[$OSSSOLDSERVICES])}
			<div class="c-detail-widget js-detail-widget u-mb-13px" data-js="container">
				<div class="widgetContainer_service"
					 data-url="module={$MODULE_NAME}&view=Detail&record={$RECORDID}&relatedModule=OSSSoldServices&mode=showRelatedRecords&page=1&limit={$LIMIT}"
					 data-name="LBL_RELATED_OSSSS">
					<div class="c-detail-widget__header js-detail-widget-header" data-js="container|value">
						<div class="form-row align-items-center py-1">
							<input type="hidden" name="relatedModule" value="OSSSoldServices"/>
							<div class="col-10 col-sm-10 col-md-9"><h5
										class="mb-0">{\App\Language::translate('LBL_RELATED_OSSSS',$MODULE_NAME)}</h5>
							</div>
							<div class="col-1 col-md-3 summaryWidgetIcon">
								{if {\App\Privilege::isPermitted('OSSSoldServices', 'CreateView')} }
									<span class="float-right">
									<button class="btn btn-light createRecord" type="button"
											data-url="index.php?module=OSSSoldServices&view=QuickCreateAjax">
										<span class="fas fa-plus-circle"
											  title="{\App\Language::translate('LBL_ADD',$MODULE_NAME)}"></span>
										</button>
									</span>
								{/if}
							</div>
						</div>
						<hr class="widgetHr">
					</div>
					<div class="c-detail-widget__content js-detail-widget-content" data-js="container|value"></div>
				</div>
			</div>
		{/if}
	</div>
{/strip}
