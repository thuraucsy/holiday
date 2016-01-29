<?php
$config = array(
	## Sqlite
	"dbdriver" => "sqlite",
	"db" => "data/db.sqlite",

	## MySQL
	# "dbdriver" => "mysql",
	# "dbhost" => "127.0.0.1",
	# "dbuser" => "root",
	# "dbpass" => "",
	# "dbname" => "tinynote",

	"default-controller" => "home"
);


# example.com/controller/action/id
$route = array("controller", "action", "id");

# example.com/controller/action/view/cat/id
# $route = array("controller", "action", "view", "cat", "id");

# route
function init() {
	global $config;

	$root = str_replace("/index.php", "", $_SERVER["SCRIPT_FILENAME"]);

	$protocol = (!empty($_SERVER['HTTPS']) 
					&& $_SERVER['HTTPS'] !== 'off' 
					|| $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
	$subdir = str_replace("/index.php", "", $_SERVER['SCRIPT_NAME']);
	$path = $protocol . $host . $subdir;
	
	$config["root"] = $root;
	$config["path"] = $path;

	routing();
}

function routing() {
	global $config;

	$requests = requests();
	$controller = get_controller();

	# Routing
	if(!$controller) {
		$controller = $config["default-controller"];

		if(!$controller) {
			echo "<span style='color:#900'>No default controller set!</span><br />";
		} else {
			load_controller($controller);
		}
	} else {
		load_controller($controller);
	}
}

function requests() {
	$uri = explode('/', $_SERVER['REQUEST_URI']);

	# Trimming sub folders name in case project is in sub folders
	$path = explode('/',$_SERVER['SCRIPT_NAME']);
	for($i = 0; $i < sizeof($path); $i++) {
		if ($uri[$i] == $path[$i]) {
			unset($uri[$i]);
		}
	}

	# Filter request array
	$uri = array_filter($uri, "filter_request_array");

	# Re-Index request array
	$requests = array_values($uri);
	return $requests;
}

function get_controller() {
	global $config;

	$requests = requests();
	
	if($requests[0])
		return $requests[0];
	else
		return $config['default-controller'];
}

## Array Filter Call Back
function filter_request_array($element) {
	# Remove everything excepts Alphu-Num, dash and underscore
	return preg_replace('/\W\-\_/si', '', $element);
}

function load_controller($controller) {
	global $config, $route;

	$script = "controllers/{$controller}.php";

	# Create up routing variables
	$requests = requests();
	for($i = 0; $i < count($route); $i++) {
		$$route[$i] = $requests[$i];
	}

	if(file_exists($script)) {
		$model = "models/{$controller}.php";
		if(file_exists($model)) {
			include($model);
		}

		include($script);
	} else {
		include("404.html");
	}
}

function render($template, $data = array()) {
	global $config, $route;

	# Create routing variables
	$requests = requests();
	$controller = get_controller();

	$template = "views/{$controller}/{$template}.php";

	# Setting route variables
	for($i = 0; $i < count($route); $i++) {
		$$route[$i] = $requests[$i];
	}

	# Setting data params variables
	foreach($data as $key => $value) {
		$$key = $value;
	}

	if(file_exists($template)) {
		include("template/index.php");
	} else {
		echo "<span style='color:#900'>View missing! Create - $template</span><br />";
	}
}

function redirect($url) {
	global $config;

	if(!preg_match("/^https?:\/\//", $url))
		$url = "{$config['path']}/$url";

	if (headers_sent()) {
		echo "<script>document.location.href='$url';</script>\n";
	} else {
		@ob_end_clean();				# clear output buffer
		header( "Location: ". $url );
	}
	exit(0);
}

function load_model($model) {
	global $config;

	$file = "{$config['root']}/models/{$model}.php";

	if(file_exists($file)) {
		include_once($file);
	} else {
		echo "Cannot load model - $model";
	}
}

#control
function include_js($file) {
	global $config;

	if(preg_match("/^https?:\/\//", $file))
		return "<script src='{$file}'></script>\n";

	return "<script src='{$config['path']}/template/js/{$file}'></script>\n";
}

function include_css($file) {
	global $config;

	if(preg_match("/^https?:\/\//", $file))
		return "<link rel='stylesheet' href='{$file}' />";

	return "<link rel='stylesheet' href='{$config['path']}/template/css/{$file}' />";
}

function link_to($url, $text, $title='', $id='', $class='', $attrs='') {
	global $config;

	if(!$text) $text = $url;

	if(preg_match("/^https?:\/\//", $url))
		return "<a href='$url' title='$title' id='$id' class='$class' $attrs>$text</a>";

	return "<a href='{$config['path']}/{$url}' title='$title' id='$id' class='$class' $attrs>$text</a>";
}

function image($url, $alt='', $id='', $class='', $attrs='') {
	global $config;

	if(preg_match("/^https?:\/\//", $url))
		return "<img src='$url' alt='$alt' id='$id' class='$class' $attrs>";

	return "<img src='{$config['path']}/{$url}' alt='$alt' id='$id' class='$class' $attrs>";
}

function form($action, $method='post', $attrs='') {
	global $config;

	if(preg_match("/^https?:\/\//", $url))
		return "<form action='$action' mehtod='$method' $attrs>";

	return "<form action='{$config['path']}/{$action}' method='$method' $attrs>";
}

#db
# Database Wrapper, Supporting MySQL and Sqlite
# Check config.php for database configuration
# Usage:
#   $db = new db();
#
#   // table, data
#   $db->create('users', array(
#     'fname' => 'john',
#     'lname' => 'doe'
#   ));
#   
#   // table, where, where-bind
#   $db->read('users', "fname LIKE :search", array(
#     ':search' => 'j%'
#   ));
#
#	// table, data, where, where-bind
#   $db->update('users', array(
#     'fname' => 'jame'
#   ), 'gender = :gender', array(
#     ':gender' => 'female'
#   ));
#   
#   // table, where, where-bind
#   $db->delete('users', 'lname = :lname', array(
#     ':lname' => 'doe'
#   ));

class db
{
	function db() {
		global $config;
		$dbuser = $config['dbuser'];
		$dbpass = $config['dbpass'];

		$options = array(
			PDO::ATTR_PERSISTENT => true, 
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
		);

		try {
			switch($config["dbdriver"]) {
				case "sqlite":
					$conn = "sqlite:{$config['root']}/{$config['db']}";
					break;
				case "mysql":
					$conn = "mysql:host={$config['dbhost']};dbname={$config['dbname']}";
					break;
				default:
					echo "Unsuportted DB Driver! Check the configuration.";
					exit(1);
			}

			$this->db = new PDO($conn, $dbuser, $dbpass, $options);
			
		} catch(PDOException $e) {
			echo $e->getMessage(); exit(1);
		}
	}

	function run($sql, $bind=array()) {
		$sql = trim($sql);
		
		try {

			$result = $this->db->prepare($sql);
			$result->execute($bind);
			return $result;

		} catch (PDOException $e) {
			echo $e->getMessage(); exit(1);
		}
	}

	function create($table, $data) {
		$fields = $this->filter($table, $data);

		$sql = "INSERT INTO " . $table . " (" . implode($fields, ", ") . ") VALUES (:" . implode($fields, ", :") . ");";

		$bind = array();
		foreach($fields as $field)
			$bind[":$field"] = $data[$field];

		$result = $this->run($sql, $bind);
		return $this->db->lastInsertId();
	}

	function read($table, $where="", $bind=array(), $fields="*") {
		$sql = "SELECT " . $fields . " FROM " . $table;
		if(!empty($where))
			$sql .= " WHERE " . $where;
		$sql .= ";";

		$result = $this->run($sql, $bind);
		$result->setFetchMode(PDO::FETCH_ASSOC);

		$rows = array();
		while($row = $result->fetch()) {
			$rows[] = $row;
		}

		return $rows;
	}

	function update($table, $data, $where, $bind=array()) {
		$fields = $this->filter($table, $data);
		$fieldSize = sizeof($fields);

		$sql = "UPDATE " . $table . " SET ";
		for($f = 0; $f < $fieldSize; ++$f) {
			if($f > 0)
				$sql .= ", ";
			$sql .= $fields[$f] . " = :update_" . $fields[$f]; 
		}
		$sql .= " WHERE " . $where . ";";

		foreach($fields as $field)
			$bind[":update_$field"] = $data[$field];
		
		$result = $this->run($sql, $bind);
		return $result->rowCount();
	}

	function delete($table, $where, $bind="") {
		$sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
		$result = $this->run($sql, $bind);
		return $result->rowCount();
	}

	private function filter($table, $data) {
		global $config;
		$driver = $config['dbdriver'];

		if($driver == 'sqlite') {
			$sql = "PRAGMA table_info('" . $table . "');";
			$key = "name";
		} elseif($driver == 'mysql') {
			$sql = "DESCRIBE " . $table . ";";
			$key = "Field";
		} else {	
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
			$key = "column_name";
		}	

		if(false !== ($list = $this->run($sql))) {
			$fields = array();
			foreach($list as $record)
				$fields[] = $record[$key];
			return array_values(array_intersect($fields, array_keys($data)));
		}

		return array();
	}
}

#util
function truncate($str, $crop, $trail='...') {
	mb_internal_encoding('UTF-8');

	if(strlen($str) <= $crop or $crop < 1) {
		return $str;
	} else {
		$str = mb_substr($str, 0, ($crop - (count($trail)+1)));
		return $str . " " . $trail;
	}
}

#filter
function f($str, $strip=false) {
	if($strip) {
		return htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8', false);
	}

	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', false);
}

#model > global
function is_auth() {
	return $_SESSION["auth"];
}

function get_user_data() {
	if(!is_auth) return false;

	$id = $_SESSION['id'];
	$db = new db();

	$result = $db->read("users", "id = :id", array(
		":id" => $id
	));

	return $result[0];
}