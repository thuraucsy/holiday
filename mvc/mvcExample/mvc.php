<?php
# Generating Absolute Path
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$subdir = str_replace("/mvc.php", "", $_SERVER['SCRIPT_NAME']);
$PATH = "http://" . $host . $subdir;
	
# Render view function
function render( $template, $data = array() ) {
	global $PATH;
	
	$controller = $_GET['controller'];

	$view_file = "views/${controller}/${template}.php";

	if(file_exists( $view_file )
		 and !is_dir( $view_file )) {

		$view = $view_file;
		include("index.php");

	} else {
		exit("View not found -> $view_file");
	}
}

# Setting URL Varaibles
$controller = $_GET['controller'];
$action = isset($_GET['action']) ? $_GET['action'] : "";
$id = isset($_GET['id']) ? $_GET['id'] : "";

# Loading model
$model_file = "models/${controller}.php";

if(file_exists( $model_file )
	 and !is_dir( $model_file )) {

	include( $model_file );

} else {
	exit("Model not found -> $model_file");
}

# Loading controller
$controller_file = "controllers/${controller}.php";

if(file_exists( $controller_file )
	 and !is_dir($controller_file)) {

	include( $controller_file );

} else {
	exit("Controller not found -> $controller_file");
}
?>
