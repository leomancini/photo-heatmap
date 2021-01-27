<?php
	$directory = 'photos/'.$_GET["city"]."/";
	$photos = scandir($directory);
	
	$index = 0;
	
	foreach($photos as $photo) {
		$exif = exif_read_data($directory."/".$photo, 0, true);
		
		if($exif) {
			$index++;
			
			foreach ($exif as $key => $section) {
				foreach ($section as $name => $val) {	
					if($name == "GPSLatitude" || $name == "GPSLongitude") {
						$type = str_replace("GPS", "", $name);
				
						$degrees = $val[0];
						$degrees = explode("/", $degrees);
						$degrees = $degrees[0]/$degrees[1];
			
						$minutes = $val[1];
						$minutes = explode("/", $minutes);
						$minutes = $minutes[0]/$minutes[1];
				
						$seconds = $val[2];
						$seconds = explode("/", $seconds);
						$seconds = $seconds[0]/$seconds[1];
						
						if($type == "Latitude") {
							$latitude_reference = $section["GPSLatitudeRef"];
							$latitude_reference = str_replace("N", "+", $latitude_reference);
							$latitude_reference = str_replace("S", "-", $latitude_reference);
							$reference = $latitude_reference;
						} elseif($type == "Longitude") {
							$longitude_reference = $section["GPSLongitudeRef"];
							$longitude_reference = str_replace("E", "+", $longitude_reference);
							$longitude_reference = str_replace("W", "-", $longitude_reference);
							$reference = $longitude_reference;
						}
						
						$coordinates[$index][$type]["Value"] = $reference . ($degrees + (($seconds / 60) + $minutes) / 60);
					}
				}
			}
		}
	}
?>		

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Heatmaps</title>
	<style>
		#map {
			height: 100%;
		}
		html, body {
			height: 100%;
			margin: 0;
			padding: 0;
		}
		#floating-panel {
			position: absolute;
			top: 10px;
			left: 25%;
			z-index: 5;
			background-color: #fff;
			padding: 5px;
			border: 1px solid #999;
			text-align: center;
			font-family: 'Roboto','sans-serif';
			line-height: 30px;
			padding-left: 10px;
		}
		#floating-panel {
			background-color: #fff;
			border: 1px solid #999;
			left: 25%;
			padding: 5px;
			position: absolute;
			top: 10px;
			z-index: 5;
		}
		</style>
	</head>
	<body>
		<div id="map"></div>
		<script>
			var map, heatmap;

			function initMap() {
				map = new google.maps.Map(document.getElementById('map'), {
					zoom: 13,
					center: {lat: 39.9042, lng: 116.4074},
					mapTypeId: 'terrain',
					styles: 
					[
						{
							"featureType": "water",
							"elementType": "geometry",
							"stylers": [
								{
									"color": "#e9e9e9"
								},
								{
									"lightness": 17
								}
							]
						},
						{
							"featureType": "landscape",
							"elementType": "geometry",
							"stylers": [
								{
									"color": "#f5f5f5"
								},
								{
									"lightness": 20
								}
							]
						},
						{
							"featureType": "road.highway",
							"elementType": "geometry.fill",
							"stylers": [
								{
									"color": "#ffffff"
								},
								{
									"lightness": 17
								}
							]
						},
						{
							"featureType": "road.highway",
							"elementType": "geometry.stroke",
							"stylers": [
								{
									"color": "#ffffff"
								},
								{
									"lightness": 29
								},
								{
									"weight": 0.2
								}
							]
						},
						{
							"featureType": "road.arterial",
							"elementType": "geometry",
							"stylers": [
								{
									"color": "#ffffff"
								},
								{
									"lightness": 18
								}
							]
						},
						{
							"featureType": "road.local",
							"elementType": "geometry",
							"stylers": [
								{
									"color": "#ffffff"
								},
								{
									"lightness": 16
								}
							]
						},
						{
							"featureType": "poi",
							"elementType": "geometry",
							"stylers": [
								{
									"color": "#f5f5f5"
								},
								{
									"lightness": 21
								}
							]
						},
						{
							"featureType": "poi.park",
							"elementType": "geometry",
							"stylers": [
								{
									"color": "#dedede"
								},
								{
									"lightness": 21
								}
							]
						},
						{
							"elementType": "labels.text.stroke",
							"stylers": [
								{
									"visibility": "on"
								},
								{
									"color": "#ffffff"
								},
								{
									"lightness": 16
								}
							]
						},
						{
							"elementType": "labels.text.fill",
							"stylers": [
								{
									"saturation": 36
								},
								{
									"color": "#333333"
								},
								{
									"lightness": 40
								}
							]
						},
						{
							"elementType": "labels.icon",
							"stylers": [
								{
									"visibility": "off"
								}
							]
						},
						{
							"featureType": "transit",
							"elementType": "geometry",
							"stylers": [
								{
									"color": "#f2f2f2"
								},
								{
									"lightness": 19
								}
							]
						},
						{
							"featureType": "administrative",
							"elementType": "geometry.fill",
							"stylers": [
								{
									"color": "#fefefe"
								},
								{
									"lightness": 20
								}
							]
						},
						{
							"featureType": "administrative",
							"elementType": "geometry.stroke",
							"stylers": [
								{
									"color": "#fefefe"
								},
								{
									"lightness": 17
								},
								{
									"weight": 1.2
								}
							]
						}
					]
				});
				
				heatmap = new google.maps.visualization.HeatmapLayer({
					data: getPoints(),
					map: map,
					radius: 80,
					opacity: 0.8,
					dissipating: true
				});
			}

			function getPoints() {
				return [
					<?php
					foreach($coordinates as $coordinate_set => $coordinate) {
						echo "{location: new google.maps.LatLng(".$coordinate["Latitude"]["Value"].", ".$coordinate["Longitude"]["Value"]."), weight: 10000},
						";
					}
					?>
				];
			}
		</script>
		<script async defer src="https://maps.googleapis.com/maps/api/js?key=API_KEY&libraries=visualization&callback=initMap"></script>
	</body>
</html>
