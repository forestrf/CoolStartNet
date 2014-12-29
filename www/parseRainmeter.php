<?php


$useInterval = true;
$globalG = true;



$ini = 'Calendar.ini';
$skin = 'isteve_by_minhtrimatrix-d4dojjk/Skins/iSteve/Calendar';
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
	'y' => 0,
	'w' => 0,
	'h' => 0
);





$sub_ini = file_get_contents($ini_file);

$sub_ini = parse_ini($sub_ini);
print_r($sub_ini);

$update_interval = isset_and_default($sub_ini['RAINMETER'], 'UPDATE', 1000);
$dragableBox = isset_and_default($sub_ini['RAINMETER'], 'DRAGMARGINS', '0,0,0,0');



// No supported:  local filesystem or @Resources folder
foreach ($sub_ini as $a => $b) {
	// $operate
	if (isset($b['METER'])) {
		add_to_operate("G['{$a}'] = API.widget.create();");
	} else {
		add_to_operate("G['{$a}'] = {};");
	}
	
	add_to_operate("G['{$a}'].styletxt = \"" . extractStyles($b) . "\";");
	if (isset($b['METER'])) {
		// Prevent text collapse
		add_to_operate("G['{$a}'].setSize(100, 0);");
	}
	
	if (isset($b['FONTFACE'])) {
		add_to_operate("API.widget.linkExternalCSS('//fonts.googleapis.com/css?family=" . urlencode($b['FONTFACE']) . "');");
	}
	
	
	
	// $interval
	if (isset($b['METER'])) {
		$x = position(isset_and_default($b, 'X', '0'), 'x');
		$y = position(isset_and_default($b, 'Y', '0'), 'y');
		$w = position(isset_and_default($b, 'W', 'auto'), 'w'); // 0 will collapse text
		$h = position(isset_and_default($b, 'H', 'auto'), 'h'); // 0 will collapse text
		
		$MeterStyle = isset_and_default($b, 'METERSTYLE', false);
		$style = '';
		if ($MeterStyle) {
			$style = "G['{$MeterStyle}'].styletxt +";
		} else {
			$style = "G['{$a}'].styletxt +";
		}
		
		
		$T = "G['{$a}'].innerHTML = '';\n" .
				"G['{$a}'].setPosition(pos.left, pos.top);\n" .
				"var elem = C('div', ['class', 'rainmeter_pos', 'style', {$style} 'left: {$x}px; top: {$y}px; width: {$w}px; height: {$h}px;']);\n";
		
		$MeasureName = isset_and_default($b, 'MEASURENAME', false);
		
		
		switch (strtoupper($b['METER'])) {
			case 'IMAGE':
				$ImageName = image(isset_and_default($b, 'IMAGENAME', false));
				$T .= "C(elem, C('img', ['src', '{$ImageName}']));\n";
				break;
			case 'STRING':
				// No supported: Text, multiple MeasureName. Everything except one MeasureName
				if ($MeasureName) {
					$T .= "elem.innerHTML = G['{$MeasureName}'].txt;\n";
				}
				break;
			case 'BITMAP':
				// No supported: BitmapAlign, BitmapSeparation, BitmapExtend = 0, BitmapZeroFrame, BitmapTransitionFrames
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
					
					$T .= "for (var i = 0; i < elem.txt.length; i++) {\n" .
							"C(elem, C('div', ['class', 'rainmeter_pos', 'style', 'width: {$Size['x']}px; height: {$Size['y']}px; background-image: url(\'{$BitmapImage}\');";
								
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
				$LineWidth = isset_and_default($b, 'LINEWIDTH', 1);
				$LineColor = toRGBA(isset_and_default($b, 'LINECOLOR', '255,255,255,255'));
				$LineLength = isset_and_default($b, 'LINELENGTH', 0);
				$ValueRemainder = isset_and_default($b, 'VALUEREMAINDER', false); //modulo on the value
				$StartAngle = isset_and_default($b, 'STARTANGLE', 0); // start point in rad. 0 = pointing to right
				$RotationAngle = isset_and_default($b, 'ROTATIONANGLE', deg2rad(360)); // rad
				
				$T .= "elem.angle = extract_numbers(G['{$MeasureName}']);\n";
				
				if ($ValueRemainder !== false) {
					$T .= "elem.angle %= {$ValueRemainder};\n";
					$T .= "elem.angle /= {$ValueRemainder};\n";
				}
				$T .= "elem.angle = elem.angle * {$RotationAngle};\n";
				$T .= "console.log(elem.angle * (180/Math.PI));\n";
				
				$T .= "elem.style.transform = 'rotate(' + ({$StartAngle} + elem.angle) + 'rad)';\n";
				
				$T .= "var div = C('div', ['class', 'rainmeter_pos', 'style', 'top:" . ($h / 2) . "px; left: " . ($w / 2) . "px;" .
						"background-color: {$LineColor}; width: {$LineLength}px; height: {$LineWidth}px;']);\n";
				$T .= "elem.appendChild(div);\n";
						
				
				break;
		}
		$T .= "G['{$a}'].appendChild(elem);\n";
		add_to_interval($T);
	}
	if (isset($b['MEASURE'])) {
		// MinValue and MaxValue are not dynamic. Also not in use
		$MinValue = isset_and_default($b, 'MINVALUE', 0);
		$MaxValue = isset_and_default($b, 'MAXVALUE', 1);
		
		switch (strtoupper($b['MEASURE'])) {
			case 'TIME':
				$Format = isset_and_default($b, 'FORMAT', '%H:%M:%S');
				$T = "G['{$a}'] = {};\n";
				$T = "G['{$a}'].txt = (new Date()).format('{$Format}');\n";
				if ($Format === '%H:%M:%S') {
					$T .= "G['{$a}'].number = local_timestamp();\n";
				}
				break;
		}
		if (isset($b['SUBSTITUTE'])) {
			$subtitutions = json_decode('{' . $b['SUBSTITUTE'] . '}', true);
			foreach($subtitutions as $from => $to) {
				$T .= "while(G['{$a}'].txt.indexOf('{$from}') !== -1) G['{$a}'].txt = G['{$a}'].txt.replace('{$from}', '{$to}');\n";
			}
		}
		$T .= "G['{$a}'].txt = G['{$a}'].txt.replace(/ /g, '&nbsp;');\n";
		add_to_interval($T);
	}
	
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
	
	$styles .= "font-family: '" . isset_and_default($b, 'FONTFACE', 'Arial') . "';";
	
	$styles .= "font-size: " . isset_and_default($b, 'FONTSIZE', 10) . "pt;";
	
	$styles .= "color: " . toRGBA(isset_and_default($b, 'FONTCOLOR', '0,0,0,255')) . ";";
	
	$StringAlign = strtoupper(isset_and_default($b, 'STRINGALIGN', 'LEFT'));
	if (strpos($StringAlign, 'LEFT') === 0) $styles .= "text-align: left;";
	if (strpos($StringAlign, 'RIGHT') === 0) $styles .= "text-align: right;width: 100%;margin-left:-100%;";
	if (strpos($StringAlign, 'CENTER') === 0) $styles .= "text-align: center;width: 100%;margin-left:-50%;";
	if (strpos($StringAlign, 'TOP') > 0) $styles .= "vertical-align: top;";
	if (strpos($StringAlign, 'BOTTOM') > 0) $styles .= "vertical-align: bottom;";
	if (strpos($StringAlign, 'CENTER') > 0) $styles .= "vertical-align: middle;";
	
	$StringStyle = strtoupper(isset_and_default($b, 'STRINGSTYLE', 'NORMAL'));
	if (strpos($StringStyle, 'BOLD') === 0) $styles .= "font-weight: bold;";
	if (strpos($StringStyle, 'ITALIC') !== false) $styles .= "font-style: italic;";
	
	$StringCase = strtoupper(isset_and_default($b, 'STRINGCASE', 'NORMAL'));
	switch ($StringCase) {
		case 'UPPER':$styles .= "text-transform: uppercase;";break;
		case 'LOWER':$styles .= "text-transform: lowercase;";break;
		case 'PROPER':$styles .= "font-variant: small-caps;";break;
	}
	
	$StringEffect = strtoupper(isset_and_default($b, 'STRINGEFFECT', false));
	$FontEffectColor = toRGBA(isset_and_default($b, 'FONTEFFECTCOLOR', '0,0,0,255'));
	switch ($StringEffect) {
		case 'SHADOW':$styles .= "text-shadow: 2px 2px 3px {$FontEffectColor};";break;
		case 'BORDER':$styles .= "text-shadow: 1px 1px 0 {$FontEffectColor};";break;
	}
	
	//ClipString  (0)
	$Angle = isset_and_default($b, 'ANGLE', false);
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


' . (!$globalG ? 'var ' : '') . "G = {};\n";

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

function isset_and_default(&$array, $param, $default) {
	global $sub_ini;
	return isset($array[$param]) && $array[$param] !== '' ? $array[$param] : (
		isset($array['METERSTYLE']) ? isset_and_default($sub_ini[$array['METERSTYLE']], $param, $default) : $default
	);
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
	