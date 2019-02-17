<?php
// 0<=(h)<=360, 0<=(s,v)<=255
// return:string
function hsv2code($h,$s,$v){
	$max = $v;
	$min = ($max-(($s/255)*$max));
	$r=0;
	$g=0;
	$b=0;
	if(0<=$h && $h<60){
		$r=$max;
		$g=(int)(($h/60)*($max-$min)+$min);
		$b=$min;
	}
	if(60<=$h && $h<120){
		$r=(int)(((120-$h)/60)*($max-$min)+$min);
		$g=$max;
		$b=$min;
	}
	if(120<=$h && $h<180){
		$r=$min;
		$g=$max;
		$b=(int)((($h-120)/60)*($max-$min)+$min);
	}
	if(180<=$h && $h<240){
		$r=$min;
		$g=(int)(((240-$h)/60)*($max-$min)+$min);
		$b=$max;
	}
	if(240<=$h && $h<300){
		$r=(int)((($h-240)/60)*($max-$min)+$min);
		$g=$min;
		$b=$max;
	}
	if(300<=$h && $h<=360){
		$r=$max;
		$g=$min;
		$b=(int)(((360-$h)/60)*($max-$min)+$min);
	}
	//echo $h.",".$s.",".$v."<br>\n";
	//echo $r.",".$g.",".$b."<br>\n";
	//echo sprintf('%06x',$r*0x10000+$g*0x100+$b);
	return sprintf('%06x',$r*0x10000+$g*0x100+$b);
}
function map($x, $iMin, $iMax, $oMin, $oMax){
	return ((1.0*$x-1.0*$iMin)*(1.0*$oMax-1.0*$oMin)/(1.0*$iMax-1.0*$iMin)+1.0*$oMin);
}
?>

