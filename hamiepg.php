<?php
date_default_timezone_set("Asia/Taipei");
error_reporting(1);
error_reporting(2);
error_reporting(3);
$ch = strtoupper($_GET['ch']);
$date = $_GET['date'];
$url = 'https://apl-hamivideo.cdn.hinet.net/HamiVideo/getMainMenu.php?deviceType=2';
$out = download($url,0);
	$data = json_decode($out,true);
	foreach($data['menuInfo'][1]['categoryInfo'] as $row){
		$genre = $row['name'];
		$menuId = $row['menuId'];
		if(!preg_match('/全部/',$genre)){
			
			$url = 'https://apl-hamivideo.cdn.hinet.net/HamiVideo/getUILayoutById.php?appVersion=7.12.104&deviceType=2&appOS=android&menuId='.$menuId.'&filterType=new&getStr=0';
			$o1 = download($url,0);
			$data1 = json_decode($o1,true);
			foreach($data1['UIInfo'][0]['elements'] as $row1){
				$title = strtoupper($row1['title']);
				$contentPk = $row1['contentPk'];
				$alldata[$title]=$contentPk;
				
			}
		}
	}
$fch = $alldata[$ch];
$url = "https://apl-hamivideo.cdn.hinet.net/HamiVideo/getEpgByContentIdAndDate.php?deviceType=1&Date={$date}&contentPk={$fch}";
$d1 = download($url,0);	
$data1 = json_decode($d1,true);
$od = '';
$od = array(
	'date'=>$date,
	'channel_name'=>$ch,
	'url'=>'tvland'
	);

foreach($data1['UIInfo'][0]['elements'] as $row){
	foreach($row['programInfo']as $row1){
		$pn = $row1['programName'];
		$st = date("H:i",$row1['startTime']);
		$et = date("H:i",$row1['endTime']);
		$od['epg_data'][] = 
		array(
			'title'=>$pn,
			'start'=>$st,
			'end'=>$et
		);
	}
}
$output = json_encode($od);
echo $output;


function download($url,$header){
	$ch = curl_init();
	curl_setopt($ch,CURLOPT_USERAGENT,'ExoPlayer/5.3.0 (Linux;Android 4.4.4) ExoPlayerLib/1.5.16');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Connection: Keep-Alive'
	));
	curl_setopt($ch,CURLOPT_HEADER,$header);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result= curl_exec ($ch);
	curl_close ($ch);
    return $result;
}
?>