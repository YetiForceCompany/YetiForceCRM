<?php

/**
 * Map Modal Class
 * @package YetiForce.ModalView
 * @license licenses/License.html
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */
class OpenStreetMap_MapModal_View extends Vtiger_BasicModal_View {

	public function getSize(Vtiger_Request $request) {
		return 'modal-full';
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$this->preProcess($request);
		$viewer = $this->getViewer($request);
		$viewer->view('MapModal.tpl', $moduleName);
		$this->postProcess($request);
	}

	public function getModalScripts(Vtiger_Request $request) {
		$jsFileNames = array(
			'~libraries/leaflet/leaflet.js',
			'~libraries/leaflet-osm/leaflet-osm.js',
			"modules.OpenStreetMap.resources.Map",
		);
		return $this->checkAndConvertJsScripts($jsFileNames);
	}

	public function getModalCss(Vtiger_Request $request) {
		$cssFileNames = [
			'~libraries/leaflet/leaflet.css'
		];
		return $this->checkAndConvertCssStyles($cssFileNames);
	}

}
