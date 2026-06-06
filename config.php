<?php

$user="root";
$pass="";
$server="localhost";
$dbname="sproutsync";
$plantnetApiKey = getenv('PLANTNET_API_KEY') ?: '2b10LOj10EaYTa0QpeJ2MQx2H';

if (!function_exists('plant_local_image')) {
	function plant_local_image($plantName = '') {
		$name = strtolower(trim($plantName ?? ''));
		$aliases = [
			'aloe' => 'aloe-vera',
			'aloe vera' => 'aloe-vera',
			'basil' => 'basil',
			'rose' => 'rose',
			'sunflower' => 'sunflower',
			'tomato' => 'tomato',
		];
		$slug = $aliases[$name] ?? preg_replace('/[^a-z0-9]+/', '-', $name);
		$slug = trim($slug ?: 'default-plant', '-');
		$path = __DIR__ . '/assets/plants/' . $slug . '.jpg';

		if (is_file($path)) {
			return 'assets/plants/' . $slug . '.jpg';
		}

		return 'assets/plants/default-plant.jpg';
	}
}

if (!function_exists('plant_image_src')) {
	function plant_image_src($imageUrl = '', $plantName = '') {
		$imageUrl = trim($imageUrl ?? '');

		if ($imageUrl !== '' && !preg_match('/^https?:\/\//i', $imageUrl)) {
			return $imageUrl;
		}

		return plant_local_image($plantName);
	}
}

try {
	$conn = new PDO("mysql:host=$server;dbname=$dbname", $user, $pass);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	echo "error: " . $e->getMessage();
}

?>