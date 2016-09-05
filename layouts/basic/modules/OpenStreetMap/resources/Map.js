/* {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} */
jQuery.Class("OpenStreetMap_Map_Js", {}, {
	container: false,
	mapInstance: false,
	selectedParams: false,
	setSelectedParams: function (params) {
		this.selectedParams = params;
	},
	registerMap: function (startCoordinate, startZoom) {
		var myMap = L.map('mapid').setView(startCoordinate, startZoom);
		L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'

		}).addTo(myMap);
		this.mapInstance = myMap;
		return myMap;
	},
	setMarkersByResponse: function (response) {
		var markerArray = [];
		var coordinates = response.result.coordinates;
		var map = this.mapInstance;
		var markers = L.markerClusterGroup({
			spiderfyOnMaxZoom: true,
			showCoverageOnHover: true,
			zoomToBoundsOnClick: true,
			maxClusterRadius: 10
		});
		coordinates.forEach(function (e) {
			markerArray.push([e.lat, e.lon]);
			var marker = L.marker([e.lat, e.lon], {
				icon: L.AwesomeMarkers.icon({
					icon: 'home',
					markerColor: 'blue',
					prefix: 'fa',
					iconColor: e.color
				})
			}).bindPopup(e.label);
			markers.addLayer(marker);
		});
		map.addLayer(markers);
		
		if (typeof response.result.legend != 'undefined') {
			var footer = this.container.find('.modal-footer');
			var html = '';
			var legend = response.result.legend;
			legend.forEach(function (e) {
				html += '<div class="pull-left"><span class="leegendIcon" style="background:' + e.color + '"></span> '+e.value+'</div>'
			});
			footer.html(html);
		}
		map.fitBounds(markerArray);
	},
	registerBasicModal: function () {
		var thisInstance = this;
		var container = this.container;
		container.find('.groupBy').on('click', function () {
			var progressIndicatorElement = jQuery.progressIndicator({
				'position': container,
				'blockInfo': {
					'enabled': true
				}
			});
			var params = {
				module: 'OpenStreetMap',
				action: 'GetMarkers',
				srcModule: app.getModuleName(),
				groupBy: container.find('.fieldsToGroup').val()
			};
			$.extend(params, thisInstance.selectedParams);
			AppConnector.request(params).then(function (response) {
				progressIndicatorElement.progressIndicator({'mode': 'hide'});
				thisInstance.setMarkersByResponse(response);
			});
		});
	},
	registerModalView: function (container) {
		var thisInstance = this;
		var progressIndicatorElement = jQuery.progressIndicator({
			'position': container,
			'blockInfo': {
				'enabled': true
			}
		});
		this.container = container;
		var startCoordinate = [0, 0];
		var startZoom = 2;
		$('#mapid').css({
			height: 500
		});
		var myMap = this.registerMap(startCoordinate, startZoom);
		var params = {
			module: 'OpenStreetMap',
			action: 'GetMarkers',
			srcModule: app.getModuleName(),
		};
		$.extend(params, this.selectedParams);
		AppConnector.request(params).then(function (response) {
			progressIndicatorElement.progressIndicator({'mode': 'hide'});
			thisInstance.setMarkersByResponse(response);
			thisInstance.registerBasicModal();
		});
	},
	registerDetailView: function (container) {
		this.container = container;
		var coordinates = container.find('#coordinates').val();
		coordinates = JSON.parse(coordinates);
		var startCoordinate = [0, 0];
		var startZoom = 2;
		if (coordinates.length) {
			startCoordinate = coordinates[0];
			startZoom = 6;
		}
		var postionTop = $('#mapid').position();
		var positionBottom = $('.footerContainer ').position();
		$('#mapid').css({
			height: positionBottom.top - postionTop.top - 281
		});
		var myMap = this.registerMap(startCoordinate, startZoom);
		var markers = L.markerClusterGroup({
			spiderfyOnMaxZoom: true,
			showCoverageOnHover: true,
			zoomToBoundsOnClick: true,
			maxClusterRadius: 10
		});
		coordinates.forEach(function (e) {
			var marker = L.marker([e.lat, e.lon], {
				icon: L.AwesomeMarkers.icon({
					icon: 'home',
					markerColor: 'blue',
					prefix: 'fa',
					iconColor: e.color
				})
			}).bindPopup(e.label);
			markers.addLayer(marker);
		});
		myMap.addLayer(markers);
	}
});

