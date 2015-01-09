<?php


class Rainmeter {
	private $errors = array();
	
	
	
	private $folder;
	private $tmp_path;
	function set_tmp_path($tmp_path) {
		if ($tmp_path[strlen($tmp_path) - 1] === '/') {
			$tmp_path = substr($tmp_path, 0, strlen($tmp_path) - 1);
		}
		$this->tmp_path = $tmp_path;
	}
	
	function unpack_skin($filename, $file_contents = false) {
		if ($file_contents === false) {
			if(!is_file($filename)){
				$errors[] = "The file does not exists ($filename)";
				return false;
			}
			
			preg_match('#/([^/]+?\.rmskin)#', $filename, $matches);
			$this->folder = $matches[1];
		} else {
			// $file_contents contains the binary
			$this->folder = $filename;
			$filename = $this->tmp_path . '/' . $filename;
			if ($fd = fopen($filename, 'w+')) {
				fwrite($fd, $file_contents);
				fclose($fd);
			}
		}
		
		$full_folder = $this->tmp_path . '/' . $this->folder;
		if (!is_dir($full_folder)) {
			mkdir($full_folder, 0777);
		}
		
		Rm_basic_utils::extractZip($filename, $full_folder);
		
		$this->generate_ini_list();
	}
	
	
	
	
	
	private $ini_list;
	
	private function generate_ini_list($inner_path = '') {
		$p = $this->tmp_path . '/' . $this->folder . '/' . $inner_path;
		$d = dir($p);
		
		while (false !== ($entry = Rm_basic_utils::filter_directory($d, true, true))) {
			$file = $inner_path . $entry;
			
			if (is_dir($p . $entry)) {
				$this->generate_ini_list($file . '/');
			} else {
				if (preg_match('#skins.+\.ini$#i', $file)) {
					$this->ini_list[] = $file;
				}
			}
		}
		$d->close();
	}
	
	function get_ini_list() {
		return $this->ini_list;
	}
	
	
	
	function generate_widget($ini, $output_folder, $output_widget_name) {
		$output_path = $output_folder . '/' . $output_widget_name;
		if (is_dir($output_path)) {
			Rm_basic_utils::rrmdir($output_path);
		}
		mkdir($output_path, 0777);
		
		
		
		$Util = new Rm_parse_utils();
		$Util->output_path = $output_path;
		
		
		$useInterval = true;
		$globalG = false;
		
		
		var_dump($ini);
		$ini_file = $this->tmp_path . '/' . $this->folder . '/' . $ini;
		$folder = substr($ini_file, 0, strrpos($ini_file, '/'));
		$Util->folder = $folder;
		
		
		
		
		
		// Search @Resources folder (it can exists)
		$resources = array();
		
		var_dump($Util->folder);
		$resourcesFolder = $Util->folder;
		while (false !== ($pos = strrpos($resourcesFolder, '/'))) {
			$resourcesFolder = substr($resourcesFolder, 0, $pos);
			if (is_dir($resourcesFolder . '/@Resources')) {
				$resourcesFolder = $resourcesFolder . '/@Resources/';
				
				// There is a Resources folder. Load fonts (other files will be added manually from the ini file speecifying @Resources. In this case use $resourcesFolder 
				
				$p = $resourcesFolder . 'Fonts/';
				
				if (is_dir($p)) {
					$d = dir($p);
					while (false !== ($entry = Rm_basic_utils::filter_directory($d, false, true))) {
						if (is_file($p . $entry)) {
							$name = strtolower($entry);
							$name = substr($name, 0, -4);
							$name = str_replace('_', ' ', $name);
							$resources['Fonts/' . $name] = $p . $entry;
						}
					}
					$d->close();
				}
				
				break;
			}
		}
		
		foreach ($resources as $resource) {
			$Util->add_file_to_widget($resource, false, true);
		}
		var_dump($resources);
		
		
		
		
		
		$sub_ini = file_get_contents($ini_file);
		
		$sub_ini = $Util->parse_ini($sub_ini);
		$Util->sub_ini = $sub_ini;
		print_r($sub_ini);
		
		$update_interval = $Util->isset_default($sub_ini['RAINMETER'], 'UPDATE', 1000);
		$dragableBox = $Util->isset_default($sub_ini['RAINMETER'], 'DRAGMARGINS', '0,0,0,0');
		
		
		
		// No supported:  local filesystem or @Resources folder
		foreach ($sub_ini as $a => $b) {
			// $operate
			if (isset($b['METER'])) {
				$Util->add_to_operate("G['{$a}'] = API.widget.create();");
			} else {
				$Util->add_to_operate("G['{$a}'] = {};");
			}
			
			$Util->add_to_operate("G['{$a}'].styletxt = \"" . $Util->extractStyles($b) . "\";");
			if (isset($b['METER'])) {
				// Prevent text collapse
				$Util->add_to_operate("G['{$a}'].setSize(100, 0);");
			}
			
			if (isset($b['FONTFACE'])) {
				var_dump('Fonts/' . $b['FONTFACE']);
				$fontface = strtolower($b['FONTFACE']);
				if (isset($resources['Fonts/' . $fontface])) {
					$Util->add_to_operate(
					"API.widget.InlineCSS('".
						"@font-face {" .
							"font-family: \'{$fontface}\';" .
							"src: url(\'' + API.url('{$fontface}.ttf') + '\') format(\'truetype\');" .
						"}'" .
					");");
				} else {
					$Util->add_to_operate("API.widget.linkExternalCSS('//fonts.googleapis.com/css?family=" . urlencode($b['FONTFACE']) . "');");
				}
			}
			
			
			
			// $interval
			if (isset($b['METER'])) {
				$x = $Util->position($Util->isset_default($b, 'X', '0'), 'x');
				$y = $Util->position($Util->isset_default($b, 'Y', '0'), 'y');
				$w = $Util->position($Util->isset_default($b, 'W', 'auto'), 'w'); // 0 will collapse text
				$h = $Util->position($Util->isset_default($b, 'H', 'auto'), 'h'); // 0 will collapse text
				
				$MeterStyle = $Util->isset_default($b, 'METERSTYLE', false);
				$style = '';
				if ($MeterStyle) {
					$style = "G['{$MeterStyle}'].styletxt +";
				} else {
					$style = "G['{$a}'].styletxt +";
				}
				
				
				$T = "G['{$a}'].innerHTML = '';\n" .
						"G['{$a}'].setPosition(pos.left, pos.top);\n" .
						"var elem = C('div', ['class', 'rainmeter_pos', 'style', {$style} 'left: {$x}px; top: {$y}px; width: {$w}px; height: {$h}px;']);\n";
				
				$MeasureName = $Util->isset_default($b, 'MEASURENAME', false);
				
				
				$case_found = true;
				switch (strtoupper($b['METER'])) {
					case 'IMAGE':
						$ImageName = $Util->image($Util->isset_default($b, 'IMAGENAME', false));
						$T .= "C(elem, C('img', ['src', API.url('{$ImageName}')]));\n";
						$Util->add_file_to_widget($ImageName);
						break;
					case 'STRING':
						// No supported: Text, multiple MeasureName. Everything except one MeasureName
						if ($MeasureName) {
							$T .= "elem.innerHTML = G['{$MeasureName}'].txt;\n";
						}
						break;
					case 'BITMAP':
						// No supported: BitmapAlign, BitmapSeparation, BitmapExtend = 0, BitmapZeroFrame, BitmapTransitionFrames
						$BitmapImage = $Util->image($Util->isset_default($b, 'BITMAPIMAGE', false));
						$imageAnalisys = $Util->analize_image($BitmapImage);
						$Util->add_file_to_widget($BitmapImage);
						
						$axis = $imageAnalisys['height'] > $imageAnalisys['width'] ? 'y' : 'x';
						
						$BitmapFrames = $Util->isset_default($b, 'BITMAPFRAMES', 1);
						$Size = array(
							'x' => $imageAnalisys['width']  / ($axis === 'x' ? $BitmapFrames : 1),
							'y' => $imageAnalisys['height'] / ($axis === 'y' ? $BitmapFrames : 1)
						);
						
						
						$BitmapExtend = $Util->isset_default($b, 'BITMAPEXTEND', 0);
						if ($BitmapExtend == 0) {
							
						} else {
							$T .= "elem.txt = extract_numbers(G['{$MeasureName}']);\n";
							
							$BitmapDigits = $Util->isset_default($b, 'BITMAPDIGITS', 0);
							
							if ($BitmapDigits != 0) {
								// !autoadjust
								$T .= "elem.txt = number_to_size(elem.txt, {$BitmapDigits});\n";
							}
							
							$T .= "for (var i = 0; i < elem.txt.length; i++) {\n" .
									"C(elem, C('div', ['class', 'rainmeter_pos', 'style', 'width: {$Size['x']}px; height: {$Size['y']}px; background-image: url(\'' + API.url('{$BitmapImage}') + '\');";
										
										if ($axis === 'x')
										$T .= "left: ' + (i * {$Size['x']}) + 'px; top: 0px;" .
											"background-position: -' + ({$Size['x']} * +elem.txt[i]) + 'px 0;";
										else
										$T .= "left: 0px; top: ' + (i * {$Size['y']}) + 'px;" .
											"background-position: 0 -' + ({$Size['y']} * +elem.txt[i]) + 'px;";
										
									$T .= "']));
								}\n";
						}
						break;
					case 'ROUNDLINE':
						// No supported: Solid
						$LineWidth = $Util->isset_default($b, 'LINEWIDTH', 1);
						$LineColor = $Util->toRGBA($Util->isset_default($b, 'LINECOLOR', '255,255,255,255'));
						$LineLength = $Util->isset_default($b, 'LINELENGTH', 0);
						$ValueRemainder = $Util->isset_default($b, 'VALUEREMAINDER', false); //modulo on the value
						$StartAngle = $Util->isset_default($b, 'STARTANGLE', 0); // start point in rad. 0 = pointing to right
						$RotationAngle = $Util->isset_default($b, 'ROTATIONANGLE', deg2rad(360)); // rad
						
						$T .= "elem.angle = extract_numbers(G['{$MeasureName}']);\n";
						
						if ($ValueRemainder !== false) {
							$T .= "elem.angle %= {$ValueRemainder};\n";
							$T .= "elem.angle /= {$ValueRemainder};\n";
						}
						$T .= "elem.angle = elem.angle * {$RotationAngle};\n";
						
						$T .= "elem.style.transform = 'rotate(' + ({$StartAngle} + elem.angle) + 'rad)';\n";
						
						$T .= "var div = C('div', ['class', 'rainmeter_pos', 'style', 'top:" . ($h / 2) . "px; left: " . ($w / 2) . "px;" .
								"background-color: {$LineColor}; width: {$LineLength}px; height: {$LineWidth}px;']);\n";
						$T .= "elem.appendChild(div);\n";
								
						
						break;
					default:
						$case_found = false;
						break;
				}
				
				if ($case_found) {
					$T .= "G['{$a}'].appendChild(elem);\n";
					$Util->add_to_interval($T);
				}
			}
			if (isset($b['MEASURE'])) {
				// MinValue and MaxValue are not dynamic. Also not in use
				$MinValue = $Util->isset_default($b, 'MINVALUE', 0);
				$MaxValue = $Util->isset_default($b, 'MAXVALUE', 1);
				
				$T = '';
				
				$case_found = true;
				switch (strtoupper($b['MEASURE'])) {
					case 'TIME':
						$Format = $Util->isset_default($b, 'FORMAT', '%H:%M:%S');
						$T = "G['{$a}'] = {};\n";
						$T = "G['{$a}'].txt = (new Date()).format('{$Format}');\n";
						if ($Format === '%H:%M:%S') {
							$T .= "G['{$a}'].number = local_timestamp();\n";
						}
						break;
					case 'CALC':
						break;
					default:
						$case_found = false;
						break;
				}
				
				if ($case_found) {
					if (isset($b['SUBSTITUTE'])) {
						$subtitutions = json_decode('{' . $b['SUBSTITUTE'] . '}', true);
						foreach($subtitutions as $from => $to) {
							$T .= "while(G['{$a}'].txt.indexOf('{$from}') !== -1) G['{$a}'].txt = G['{$a}'].txt.replace('{$from}', '{$to}');\n";
						}
					}
					$T .= "G['{$a}'].txt = resolve_formula_no_calc(G['{$a}'].txt);\n";
					$T .= "G['{$a}'].txt = G['{$a}'].txt.replace(/ /g, '&nbsp;');\n";
					$Util->add_to_interval($T);
				}
			}
			
		}
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		$widget_content = 'var C = crel2;
		
		API.widget.linkExternalJS("js/rainmeter.js", operate);
		API.widget.linkExternalCSS("css/rainmeter.css");
		
		var operated = false;
		
		var position = {
			"top":0,
			"left":0
		};
		API.storage.remoteStorage.get("pos", function(entrada){
			if(entrada){
				position = entrada;
			} else {
				position = {
					"left" : 0,
					"top"  : 0
				}
			}
			if (operated) {
				interval(position, false);
			}
		});
		
		
		' . (!$globalG ? 'var ' : '') . "G = {};\n";
		
		$widget_content .= "function operate() {\n";
		$widget_content .= "operated = true;\n";
		foreach ($Util->operate as $op) {
			$widget_content .= $op."\n";
		}
		if ($useInterval) {
			$widget_content .= "setInterval(function() { interval(position, false) }, " . $update_interval . ");\n}\n";
		} else {
			$widget_content .= "interval(position, false);\n}\n";
		}
		
		
		
		$widget_content .= "function interval(pos, moving) {\nif (settingPosition && !moving) return;\n";
		foreach ($Util->interval as $op) {
			$widget_content .= $op."\n";
		}
		$widget_content .= "}\n";
		
		
		
		
		
		
		
		
		$widget_content .= '// Function for the config widgetID. It returns the html object to append on the config window
		var settingPosition = false;
		var CONFIG_function = function(functions){
			
			return C("div",
				C("button", ["onclick", setPosition], "Set position")
			);
			
			
			
			function realTimeMove(data){
				interval(data, true);
			}
			
			function setPosition(){
				settingPosition = true;
				functions.positioning(
					{
						"left"    : position.left,
						"top"     : position.top,
						"show_bg" : false,
						"realtime": realTimeMove
					},
					function(data){
						if(data){
							position = {
								left: data.left,
								top: data.top
							};
							API.storage.remoteStorage.set("pos", position, function(entrada){
								if(!entrada){
									alert("data not saved");
								}
							});
						}
						settingPosition = false;
					}
				);
			}
			
			
		}';
		
		
		
		file_put_contents($output_path . '/main.js', $widget_content);
		
		
		$infotxt = array(
			"name" => $output_widget_name,
			"description" => $output_widget_name . " - Widget generated with the rainmeter parser.",
			"fulldescription" => $output_widget_name . " - Widget generated with the rainmeter parser.",
			//"images":["capture 1.png", "capture 2.png", "capture 3.png", "capture 4.png", "capture 5.png"],
			//"autor":"Andrés Leone",
			//"date":"30/11/2014 12:00:00", /* DD/MM/YYYY HH:MM:SS*/
			//"staticfiles":["capture 1.png", "capture 2.png", "capture 3.png", "capture 4.png", "capture 5.png"],
			"visible" => 1
		);
		$infotxt = json_encode($infotxt);
		
		
		file_put_contents($output_path . '/info.txt', $infotxt);
	}
	
	
	
}


class Rm_basic_utils {
	static function extractZip($filename = '', $extractFolder = '') {
		$zip = zip_open($filename);
		
		if ($zip) {
			while ($zip_entry = zip_read($zip)) {
				$completePath = dirname(zip_entry_name($zip_entry));
				$completeName = zip_entry_name($zip_entry);
				
				// Walk through path to create non existing directories
				// This won't apply to empty directories ! They are created further below
				if (!file_exists($completePath)) {
					$tmp = $extractFolder . '/';
					foreach (explode('/',$completePath) AS $k) {
						$tmp .= $k.'/';
						if (!file_exists($tmp)) {
							mkdir($tmp, 0777);
						}
					}
				}
			
				if (zip_entry_open($zip, $zip_entry, 'r')) {
					$f = $extractFolder . '/' . $completeName;
					if (!is_file($f) && $fd = @fopen($f, 'w+')) {
						fwrite($fd, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
						fclose($fd);
					}
					zip_entry_close($zip_entry);
				}
			}
			zip_close($zip);
			return true;
		}
		return false;
	}

	static function filter_directory(&$directory_resource, $show_folders = true, $show_files = true) {
		if (false !== $entry = $directory_resource->read()) {
			if ($entry === '.' || $entry === '..') {
				return self::filter_directory($directory_resource, $show_folders, $show_files);
			} else {
				if ($show_folders && is_dir($directory_resource->path . $entry) ||
						$show_files && is_file($directory_resource->path . $entry)) {
					return $entry;
				}
			}
		} else {
			return false;
		}
	}
	
	static function separators($path) {
		return str_replace('\\', '/', $path);
	}
	
	static function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != '.' && $object != '..') {
 					if (filetype($dir . '/' . $object) == 'dir')
 						rrmdir($dir . '/' . $object);
 					else
 						unlink($dir . '/' . $object);
				}
			}
			reset($objects); 
			rmdir($dir); 
		} 
	}
}

class Rm_parse_utils {
	
	var $operate = array();
	var $interval = array();
	function add_to_operate($elem) {
		global $operate;
		if (!in_array($elem, $this->operate)) {
			$this->operate[] = $elem;
		}
	}
	function add_to_interval($elem) {
		$this->interval[] = $elem;
	}
	
	function parse_ini($contents) {
		$contents = str_replace("\r", '', $contents);
		$contents = explode("\n", $contents);
		
		$result = array();
		$last = '';
		
		foreach ($contents as $elem) {
			if (preg_match('#^\[(.+?)\]#', $elem, $matches)) {
				$result[$last = $matches[1]] = array();
			} else if (preg_match('#^([^;]+?)=(.*)#', $elem, $matches)) {
				if (substr_count($matches[2], '"') === 2 && $matches[2][0] === '"') {
					$matches[2] = substr($matches[2], 1, -1);
				}
				$matches[1] = trim(strtoupper($matches[1]));
				if ($matches[1] === 'VALUEREMINDER') $matches[1] = 'VALUEREMAINDER';
				$result[$last][strtoupper($matches[1])] = trim($matches[2]);
			}
		}
		
		return $result;
	}
	
	var $sub_ini;
	function isset_default(&$array, $param, $default) {
		return isset($array[$param]) && $array[$param] !== '' ? $array[$param] : (
			isset($array['METERSTYLE']) ? $this->isset_default($this->sub_ini[$array['METERSTYLE']], $param, $default) : $default
		);
	}
	
	var $folder;
	function analize_image($filename) {
		$arr = getimagesize($this->folder . '/' . $filename);
		return array(
			'width' => $arr[0],
			'height' => $arr[1]
		);
	}
	
	function image($filename) {
		return strpos($filename, '.') === false ? $filename . '.png' : $filename;
	}
	
	var $previous = array(
		'x' => 0,
		'y' => 0,
		'w' => 0,
		'h' => 0
	);
	
	function position($value, $axis) {
		if (strpos($value, 'r') !== false) {
			$value = substr($value, 0, -1) + $this->previous[$axis];
		}
		$this->previous[$axis] = $value;
		return $value;
	}
	
	// No soporta operaciones en el color o colores hex con alfa
	function toRGBA($color) {
		switch (substr_count($color, ',')) {
			case 2:
				return "rgb({$color})";
			case 3:
				$color = explode(',', $color);
				$color[3] /= 255;
				$color = implode(',', $color);
				return "rgba({$color})";
			default:
				return "#{$color}";
		}
	}
	
	// No soporta ClipString, StringEffect (?), StringCase (?), StringAlign (Vertical)
	function extractStyles($b) {
		$styles = '';
		
		$styles .= "font-family: '" . $this->isset_default($b, 'FONTFACE', 'Arial') . "';";
		
		$styles .= "font-size: " . $this->isset_default($b, 'FONTSIZE', 10) . "pt;";
		
		$styles .= "color: " . $this->toRGBA($this->isset_default($b, 'FONTCOLOR', '0,0,0,255')) . ";";
		
		$StringAlign = strtoupper($this->isset_default($b, 'STRINGALIGN', 'LEFT'));
		if (strpos($StringAlign, 'LEFT') === 0) $styles .= "text-align: left;";
		if (strpos($StringAlign, 'RIGHT') === 0) $styles .= "text-align: right;width: 100%;height: 0;margin-left:-100%;";
		if (strpos($StringAlign, 'CENTER') === 0) $styles .= "text-align: center;width: 100%;height: 0;margin-left:-50%;";
		if (strpos($StringAlign, 'TOP') > 0) $styles .= "vertical-align: top;";
		if (strpos($StringAlign, 'BOTTOM') > 0) $styles .= "vertical-align: bottom;";
		if (strpos($StringAlign, 'CENTER') > 0) $styles .= "vertical-align: middle;";
		
		$StringStyle = strtoupper($this->isset_default($b, 'STRINGSTYLE', 'NORMAL'));
		if (strpos($StringStyle, 'BOLD') === 0) $styles .= "font-weight: bold;";
		if (strpos($StringStyle, 'ITALIC') !== false) $styles .= "font-style: italic;";
		
		$StringCase = strtoupper($this->isset_default($b, 'STRINGCASE', 'NORMAL'));
		switch ($StringCase) {
			case 'UPPER':$styles .= "text-transform: uppercase;";break;
			case 'LOWER':$styles .= "text-transform: lowercase;";break;
			case 'PROPER':$styles .= "font-variant: small-caps;";break;
		}
		
		$StringEffect = strtoupper($this->isset_default($b, 'STRINGEFFECT', false));
		$FontEffectColor = $this->toRGBA($this->isset_default($b, 'FONTEFFECTCOLOR', '0,0,0,255'));
		switch ($StringEffect) {
			case 'SHADOW':$styles .= "text-shadow: 2px 2px 3px {$FontEffectColor};";break;
			case 'BORDER':$styles .= "text-shadow: 1px 1px 0 {$FontEffectColor};";break;
		}
		
		//ClipString  (0)
		$Angle = $this->isset_default($b, 'ANGLE', false);
		if ($Angle) {
			$styles .= "transform: rotate({$Angle}deg);";
		}
		
		
		return $styles;
	}
	
	var $output_path;
	function add_file_to_widget($filename, $relative_to_widget = true, $correctResourceName = false) {
		if ($relative_to_widget) {
			$from = $this->folder . '/' . $filename;
		} else {
			$from = $filename;
			$filename = substr($filename, strrpos($filename, '/') + 1);
		}
		if ($correctResourceName) {
			$filename = strtolower($filename);
			$filename = str_replace('_', ' ', $filename);
		}
		var_dump($from);
		var_dump($this->output_path . '/' . $filename);
		copy($from, $this->output_path . '/' . $filename);
	}
}


return;



	