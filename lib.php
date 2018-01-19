<?php



function get_table($array) {
	$out = "<table class='table table-condensed  table-bordered'>";
	
	$out .= "<tr>";
	foreach (array_values($array)[0] as $attr => $vl) {
		$out .= "<th>" . $attr . "</th>";
	}
	$out .= "</tr>";
	
	foreach ($array as $id => $element) {
		$out .= "<tr>";
	
		//$out .= print_r($course, true);
	
		foreach($element as $value) {
			$out .= "<td>" . $value . "</td>";
		}
		$out .= "</tr>";
	}
	$out .= "<table>";
	
	return $out;
}