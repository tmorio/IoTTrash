<?php
function routeSort($list, $lat, $lng){
	for($i=0; $i<count($list); $i++){
		for($j=0; $j<count($list); $j++){
			if(isset($dist[$i][$j])) continue;
			$dist[$i][$j] = ($list[$i]["Lat"]-$list[$j]["Lat"])**2 + ($list[$i]["Lng"]-$list[$j]["Lng"])**2;
			$dist[$j][$i] = $dist[$i][$j];
		}
	}
	//print_r( $dist);
	return $list;
}
?>
