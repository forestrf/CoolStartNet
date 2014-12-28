<?php

require_once __DIR__.'/php/defaults.php';
require_once __DIR__.'/php/functions/generic.php';


$useInterval = false;


$ini = 'Digital 24H.ini';
$skin = 'isteve_by_minhtrimatrix-d4dojjk/Skins/iSteve/Clock/Digital';
/*
$ini = 'Clock.ini';
$skin = 'Black_Vintage_Clock_/Skins/Vintage Clock';
*/
$path = '../rainmeter tests/';

$folder = $path . $skin . '/';
$ini_file = $folder . $ini;





$operate = array();
$interval = array();
$update_interval = 1000;

function add_to_operate($elem) {
	global $operate;
	if (!in_array($elem, $operate)) {
		$operate[] = $elem;
	}
}
function add_to_interval($elem) {
	global $interval;
	$interval[] = $elem;
}


$previous = array(
	'x' => 0,
	'y' => 0
);





$sub_ini = file_get_contents($ini_file);

$sub_ini = parse_ini($sub_ini);
print_r($sub_ini);

$update_interval = isset_and_default($sub_ini['Rainmeter'], 'UPDATE', 1000);
$dragableBox = isset_and_default($sub_ini['Rainmeter'], 'DRAGMARGINS', '0,0,0,0');




foreach ($sub_ini as $a => $b) {
	// $operate
	if (isset($b['METER'])) {
		add_to_operate("G['{$a}'] = API.widget.create();");
	} else {
		add_to_operate("G['{$a}'] = {};");
	}
	
	add_to_operate("G['{$a}'].style = \"" . extractStyles($b) . '"');
	
	if (isset($b['FONTFACE'])) {
		add_to_operate("API.widget.linkExternalCSS('//fonts.googleapis.com/css?family=" . urlencode($b['FontFace']) . "');");
	}
	
	
	
	// $interval
	if (isset($b['METER'])) {
		$x = position(isset_and_default($b, 'X', '0'), 'x');
		$y = position(isset_and_default($b, 'Y', '0'), 'y');
		
		$MeterStyle = isset_and_default($b, 'METERSTYLE', false);
		$style = '';
		if ($MeterStyle) {
			$style = "G['{$MeterStyle}'].style +";
		}
		
		
		$T = "G['{$a}'].innerHTML = '';\n" .
				"G['{$a}'].setPosition(pos.left, pos.top);\n" .
				"var elem = C('div', ['class', 'rainmeter_positionable', 'style', {$style} 'left: {$x}px; top: {$y}px;']);\n";
		
		$MeasureName = isset_and_default($b, 'MEASURENAME', false);
		
		
		switch (strtoupper($b['METER'])) {
			case 'IMAGE':
				$ImageName = image(isset_and_default($b, 'IMAGENAME', false));
				$T .= "C(elem, C('img', ['src', '{$ImageName}']));\n";
				break;
			case 'STRING':
				if ($MeasureName) {
					switch (strtoupper($sub_ini[$MeasureName]['MEASURE'])) {
						case 'TIME':
							$T .= "elem.innerHTML = G['{$MeasureName}'];\n";
							break;
					}
					
				}
				break;
			case 'BITMAP':
				$BitmapImage = image(isset_and_default($b, 'BITMAPIMAGE', false));
				$imageAnalisys = analize_image($BitmapImage);
				
				$axis = $imageAnalisys['height'] > $imageAnalisys['width'] ? 'y' : 'x';
				
				$BitmapFrames = isset_and_default($b, 'BITMAPFRAMES', 1);
				$Size = array(
					'x' => $imageAnalisys['width']  / ($axis === 'x' ? $BitmapFrames : 1),
					'y' => $imageAnalisys['height'] / ($axis === 'y' ? $BitmapFrames : 1)
				);
				
				
				$BitmapExtend = isset_and_default($b, 'BITMAPEXTEND', 0);
				if ($BitmapExtend == 0) {
					
				} else {
					$T .= "elem.txt = extract_numbers(G['{$MeasureName}']);\n";
					
					$BitmapDigits = isset_and_default($b, 'BITMAPDIGITS', 0);
					
					if ($BitmapDigits != 0) {
						// !autoadjust
						$T .= "elem.txt = number_to_size(elem.txt, {$BitmapDigits});\n";
					}
					
					$T .= "for (var i = 0; i < elem.txt.length; i++) {
							C(elem, C('div', ['class', 'rainmeter_positionable', 'style', '" .
								"width: {$Size['x']}px;" .
								"height: {$Size['y']}px;" .
								"background-image: url(\'{$BitmapImage}\');";
					if ($axis === 'x') {
						$T .= "left: ' + (i * {$Size['x']}) + 'px;" .
									"top: 0px;" .
									"background-position: -' + ({$Size['x']} * +elem.txt[i]) + 'px 0;";
					} else {
						$T .= "left: 0px;" .
									"top: ' + (i * {$Size['y']}) + 'px;" .
									"background-position: 0 -' + ({$Size['y']} * +elem.txt[i]) + 'px;";
					}
									
							$T .= "']));
						}\n";
					
					
				}
				
				
				
				print_r($imageAnalisys);
				break;
		}
		$T .= "G['{$a}'].appendChild(elem);\n";
		add_to_interval($T);
	}
	if (isset($b['MEASURE'])) {
		switch (strtoupper($b['MEASURE'])) {
			case 'TIME':
				$Format = isset_and_default($b, 'FORMAT', '%H:%M:%S');
				$T = "G['{$a}'] = '{$Format}';\n" .
						"G['{$a}'] = (new Date()).format('{$Format}');\n";
				if (isset($b['SUBSTITUTE'])) {
					$subtitutions = json_decode('{' . $b['SUBSTITUTE'] . '}', true);
					foreach($subtitutions as $from => $to) {
						$T .= "G['{$a}'] = G['{$a}'].replace('{$from}', '{$to}');\n";
					}
				}
				add_to_interval($T);
				break;
		}
	}
	
}





// No soporta operaciones en el color o colores hex con alfa
function toRGBA($color) {
	switch (substr_count($color, ',')) {
		case 2:
			return "rgb({$color})";
		case 3:
			return "rgba({$color})";
		default:
			return "#{$color}";
	}
}

// No soporta ClipString, StringEffect (?), StringCase (?), StringAlign (Vertical, ?)
function extractStyles($b) {
	$styles = '';
	
	$styles .= "font-family: '" . isset_and_default($b, 'FontFace', 'Arial') . "';";
	
	$styles .= "font-size: " . isset_and_default($b, 'FontSize', 10) . "pt;";
	
	$styles .= "color: " . toRGBA(isset_and_default($b, 'FontColor', '0,0,0,255')) . ";";
	
	$StringAlign = isset_and_default($b, 'StringAlign', 'Left');
	if (strpos($StringAlign, 'Left') === 0) $styles .= "text-align: left;";
	if (strpos($StringAlign, 'Right') === 0) $styles .= "text-align: right;";
	if (strpos($StringAlign, 'Center') === 0) $styles .= "text-align: center;";
	if (strpos($StringAlign, 'Top') > 0) $styles .= "vertical-align: top;";
	if (strpos($StringAlign, 'Bottom') > 0) $styles .= "vertical-align: bottom;";
	if (strpos($StringAlign, 'Center') > 0) $styles .= "vertical-align: middle;";
	
	$StringStyle = isset_and_default($b, 'StringStyle', 'Normal');
	if (strpos($StringStyle, 'Bold') === 0) $styles .= "font-weight: bold;";
	if (strpos($StringStyle, 'Italic') !== false) $styles .= "font-style: italic;";
	
	$StringCase = isset_and_default($b, 'StringCase', 'Normal');
	switch ($StringCase) {
		case 'Upper':$styles .= "text-transform: uppercase;";break;
		case 'Lower':$styles .= "text-transform: lowercase;";break;
		case 'Proper':$styles .= "font-variant: small-caps;";break;
	}
	
	$StringCase = isset_and_default($b, 'StringCase', 'Normal');
	switch ($StringCase) {
		case 'Upper':$styles .= "text-transform: uppercase;";break;
		case 'Lower':$styles .= "text-transform: lowercase;";break;
		case 'Proper':$styles .= "font-variant: small-caps;";break;
	}
	
	$StringEffect = isset_and_default($b, 'StringEffect', false);
	$FontEffectColor = toRGBA(isset_and_default($b, '$FontEffectColor', '0,0,0,255'));
	switch ($StringEffect) {
		case 'Shadow':$styles .= "text-shadow: 2px 2px 3px {$FontEffectColor};";break;
		case 'Border':$styles .= "text-shadow: 1px 1px 0 {$FontEffectColor};";break;
	}
	
	//ClipString  (0)
	$Angle = isset_and_default($b, 'Angle', false);
	if ($Angle) {
		$styles .= "transform: rotate({$Angle}deg);";
	}
	
	
	return $styles;
}

function image($filename) {
	return strpos($filename, '.') === false ? $filename . '.png' : $filename;
}

function position($value, $axis) {
	global $previous;
	if (strpos($value, 'r') !== false) {
		echo substr($value, 0, -1);
		$value = substr($value, 0, -1) + $previous[$axis];
	}
	$previous[$axis] = $value;
	return $value;
}









function analize_image($filename) {
	global $folder;
	$arr = getimagesize($folder . $filename);
	return array(
		'width' => $arr[0],
		'height' => $arr[1]
	);
}








$widget_content = 'var C = crel2;

API.widget.linkExternalJS("js/rainmeter.js", operate);
API.widget.linkExternalCSS("css/rainmeter.css");

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
});



var G = {};
';

$widget_content .= "function operate() {\n";
foreach ($operate as $op) {
	$widget_content .= $op."\n";
}
if ($useInterval) {
	$widget_content .= "setInterval(function() { interval(position, false) }, " . $update_interval . ");\n}\n";
} else {
	$widget_content .= "interval(position, false);\n}\n";
}



$widget_content .= "function interval(pos, moving) {\nif (settingPosition && !moving) return;\n";
foreach ($interval as $op) {
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



file_put_contents($path . 'widget.js', $widget_content);














function separators($path) {
	return str_replace('\\', '/', $path);
}




function parse_ini($contents) {
	$contents = str_replace("\r", '', $contents);
	$contents = explode("\n", $contents);
	
	$result = array();
	$last = '';
	
	foreach ($contents as $elem) {
		if (preg_match('#^\[(.+?)\]#', $elem, $matches)) {
			$result[$last = $matches[1]] = array();
		} else if (preg_match('#(.+?)=(.*)#', $elem, $matches)) {
			if (substr_count($matches[2], '"') === 2 && $matches[2][0] === '"') {
				$matches[2] = substr($matches[2], 1, -1);
			}
			$result[$last][strtoupper($matches[1])] = trim($matches[2]);
		}
	}
	
	return $result;
}
	