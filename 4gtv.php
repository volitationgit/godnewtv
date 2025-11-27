<?php
$phpurl=is_https().$_SERVER['HTTP_HOST'].strtok($_SERVER["REQUEST_URI"],'?');
$type = '';
$str = <<<EOF
*=====================================================
*
* by 餃子
* {$phpurl}?type=m3u	m3u格式
* {$phpurl}?type=txt	txt格式列表
*
*=====================================================
EOF;
$q = @$_SERVER['QUERY_STRING'];

if(!$q){
	header("Content-Type: text/json; charset=UTF-8");
	echo $str;
	exit;
}else{
	parse_str($q,$qo);
	foreach($qo as $qk=>$qv){
		$$qk = $qv;
	}
}
if($type){
	$url = 'https://twproxy03.svc.litv.tv/cdi/v2/rpc';
	
	$pd = array(
		"jsonrpc"=>"2.0",
		"method"=>"CCCService.GetLineup",
		"id"=> -1765180716,
		"params"=>array(
			"client_id"=>"2A009F444D700685",
			"deviceId"=>"4A0211827DA6",
			"swver"=>"LTIOS0330191LEP20200922190000",
			"project_num"=>"LTIOS03",
			"headend_id"=>"TW00100",
			"version"=>"3.0"
		)
	);
	$data = json_encode($pd);
	$out = postdata($url,$data,0);
	file_put_contents('1stout.txt',$out);
	$od = json_decode($out,true);
	$txt = 'LiTV-';
	$m3u = "#EXTM3U\r\n";
	foreach($od['result']['data']['channels'] as $row){
		$name = $row['title'];
		$id = $row['content_id'];
		$no0 =  $row['no'];
		$content_type = $row['content_type'];
		$picture = 'https://p-cdnstatic.svc.litv.tv/'.$row['picture'];
		$genre = $row['genres'][0]['name'];
		if($content_type == 'channel'){
			$temp[$genre][] = $name.",".$phpurl."?id=".$id."&no=".$no0;
			if($type == 'm3u'){
				$m3u .= "#EXTINF:-1 tvg-logo=".'"'.$picture.'"'."group-title=".'"'.$genre.'",'.$name."\r\n".$phpurl."?id=".$id."&no=".$no0."\r\n";	
			}		
		}
	}
	if($type == 'txt'){
		$tg = '';
		foreach($temp as $key=>$value){
			if($tg !== $key){
				$txt .= $key.",#genre#\r\n";
				$tg = $key;
			}
			foreach($value as $row1){
				$txt .= $row1."\r\n";
			}
			
		}
		header("Content-Type: text/json; charset=UTF-8");
		echo $txt;
		exit;

	}elseif($type == 'm3u'){
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header('Content-Type: application/vnd.apple.mpegurl'); 
		header("Pragma: no-cache");
		header('Content-Transfer-Encoding: chunked'); 
		header('Content-Length: ' . strlen($m3u)); 
		header("Content-Disposition: attachment; filename=index.m3u");
		echo $m3u;
		exit;
	}
}elseif(isset($id)){
	$n=array(
		'4gtv-4gtv001' => [1, 6],//民视台湾台
		'4gtv-4gtv002' => [1, 10],//民视
		'4gtv-4gtv003' => [1, 6],//民视第一台
		'4gtv-4gtv004' => [1, 8],//民视综艺
		'4gtv-4gtv006' => [1, 9],//猪哥亮歌厅秀
		'4gtv-4gtv009' => [2, 7],//中天新闻
		'4gtv-4gtv010' => [1, 2],//非凡新闻
		'4gtv-4gtv011' => [1, 6],//影迷數位電影台
		'4gtv-4gtv013' => [1, 2],//視納華仁紀實頻道
		'4gtv-4gtv014' => [1, 5],//时尚运动X
		'4gtv-4gtv016' => [1, 6],//GLOBETROTTER
		'4gtv-4gtv017' => [1, 6],//amc电影台
		'4gtv-4gtv018' => [1, 10],//达文西频道
		'4gtv-4gtv034' => [1, 6],//八大精彩台
		'4gtv-4gtv039' => [1, 7],//八大综艺台
		'4gtv-4gtv040' => [1, 6],//中视
		'4gtv-4gtv041' => [1, 6],//华视
		'4gtv-4gtv042' => [1, 6],//公视戏剧
		'4gtv-4gtv043' => [1, 6],//客家电视台
		'4gtv-4gtv044' => [1, 8],//靖天卡通台
		'4gtv-4gtv045' => [1, 6],//靖洋戏剧台
		'4gtv-4gtv046' => [1, 8],//靖天综合台
		'4gtv-4gtv047' => [1, 8],//靖天日本台
		'4gtv-4gtv048' => [1, 2],//非凡商业
		'4gtv-4gtv049' => [1, 8],//采昌影剧
		'4gtv-4gtv051' => [1, 6],//台视新闻
		'4gtv-4gtv052' => [1, 2],//华视新闻
		'4gtv-4gtv053' => [1, 8],//GinxTV
		'4gtv-4gtv054' => [1, 8],//靖天欢乐台
		'4gtv-4gtv055' => [1, 8],//靖天映画
		'4gtv-4gtv056' => [1, 2],//台视财经
		'4gtv-4gtv057' => [1, 8],//靖洋卡通台
		'4gtv-4gtv058' => [1, 8],//靖天戏剧台
		'4gtv-4gtv059' => [1, 6],//古典音乐台
		'4gtv-4gtv061' => [1, 7],//靖天电影台
		'4gtv-4gtv062' => [1, 8],//靖天育乐台
		'4gtv-4gtv063' => [1, 6],//靖天国际台
		'4gtv-4gtv064' => [1, 8],//中视菁采
		'4gtv-4gtv065' => [1, 8],//靖天资讯台
		'4gtv-4gtv066' => [1, 2],//台视
		'4gtv-4gtv067' => [1, 8],//tvbs精采台
		'4gtv-4gtv068' => [1, 7],//tvbs欢乐台
		'4gtv-4gtv070' => [1, 7],//爱尔达娱乐
		'4gtv-4gtv072' => [1, 2],//tvbs新闻台
		'4gtv-4gtv073' => [1, 2],//tvbs
		'4gtv-4gtv074' => [1, 2],//中视新闻
		'4gtv-4gtv075' => [1, 6],//镜新闻
		'4gtv-4gtv076' => [1, 2],//CATCHPLAY电影台
		'4gtv-4gtv077' => [1, 7],//TRACE SPORTS STARS
		'4gtv-4gtv079' => [1, 2],//阿里郎
		'4gtv-4gtv080' => [1, 5],//中视经典
		'4gtv-4gtv082' => [1, 7],//TRACE URBAN
		'4gtv-4gtv083' => [1, 6],//MEZZO LIVE
		'4gtv-4gtv084' => [1, 6],//国会频道1
		'4gtv-4gtv085' => [1, 5],//国会频道2
		'4gtv-4gtv101' => [1, 6],//智林体育台
		'4gtv-4gtv104' => [1, 7],//国际财经
		'4gtv-4gtv109' => [1, 7],//第1商業台
		'4gtv-4gtv152' => [1, 6],//东森新闻
		'4gtv-4gtv153' => [1, 6],//东森财经新闻
		'4gtv-4gtv155' => [1, 6],//民视
		'4gtv-4gtv156' => [1, 2],//民视台湾台
		'litv-ftv03' => [1, 7],//美国之音
		'litv-ftv07' => [1, 7],//民视旅游
		'litv-ftv09' => [1, 7],//民视影剧
		'litv-ftv10' => [1, 7],//半岛新闻
		'litv-ftv13' => [1, 7],//民视新闻台
		'litv-ftv15' => [1, 7],//爱放动漫
		'litv-ftv16' => [1, 2],//好消息
		'litv-ftv17' => [1, 2],//好消息2台
		'litv-longturn01' => [4, 2],//龙华卡通
		'litv-longturn03' => [5, 2],//龙华电影
		'litv-longturn04' => [5, 2],//博斯魅力
		'litv-longturn05' => [5, 2],//博斯高球1
		'litv-longturn06' => [5, 2],//博斯高球2
		'litv-longturn07' => [5, 2],//博斯运动1
		'litv-longturn08' => [5, 2],//博斯运动2
		'litv-longturn09' => [5, 2],//博斯网球
		'litv-longturn10' => [5, 2],//博斯无限
		'litv-longturn11' => [5, 2],//龙华日韩
		'litv-longturn12' => [5, 2],//龙华偶像
		'litv-longturn13' => [4, 2],//博斯无限2
		'litv-longturn14' => [1, 6],//寰宇新闻台
		'litv-longturn15' => [5, 2],//寰宇新闻台湾台
		'litv-longturn17' => [5, 2],//亚洲旅游台
		'litv-longturn18' => [5, 2],//龙华戏剧
		'litv-longturn19' => [5, 2],//Smart知识台
		'litv-longturn20' => [5, 2],//生活英语台
		'litv-longturn21' => [5, 2],//龙华经典
		'litv-longturn22' => [5, 2],//台湾戏剧台

		
	);
	if(!array_key_exists($id,$n)){
		$n[$id] = array(5,2);
	}
	$timestamp = intval(time()/4-355017625);
	//$ts = substr(time(),4)+125500;//127200
	$ts = intval(time()/4-429083608);
	$t=$timestamp*4;
	$current = "#EXTM3U"."\r\n";
	$current.= "#EXT-X-VERSION:3"."\r\n";
	$current.= "#EXT-X-TARGETDURATION:4"."\r\n";
	if(preg_match('/fast12/',$id)){
		$current.= "#EXT-X-MEDIA-SEQUENCE:{$ts}"."\r\n";
	}else{
		$current.= "#EXT-X-MEDIA-SEQUENCE:{$timestamp}"."\r\n";
	}
	for ($i=0; $i<3; $i++) {
    	$current.= "#EXTINF:4,"."\r\n";
		if(preg_match('/fast12/',$id)){
			$current.= "https://lifastssaimobile.akamaized.net/video/ch".$no."/playlist_1080p_{$ts}.ts"."\n";
			$ts = $ts+1;
		}else{
			$current.= "https://litvpc-hichannel.cdn.hinet.net/live/pool/{$id}/litv-pc/{$id}-avc1_6000000={$n[$id][0]}-mp4a_134000_zho={$n[$id][1]}-begin={$t}0000000-dur=40000000-seq={$timestamp}.ts"."\n";
		}
    	$timestamp = $timestamp + 1;
		$t=$t+4;
    }
   	$m3u8=$current;
	header('Content-Type: application/vnd.apple.mpegurl'); 
	header('Content-Disposition: inline; filename='.$id.'.m3u8');
	header('Content-Length: ' . strlen($m3u8)); 
	echo $m3u8;
}


function postdata($url,$data,$header){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_HEADER,$header);
    curl_setopt($ch, CURLOPT_POST,1 );
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Accept: application/json',
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data),
		'Accept-Language: zh-Hant-TW;q=1, en-TW;q=0.9, zh-Hans-TW;q=0.8, ja-TW;q=0.7'
	));
	curl_setopt($ch, CURLOPT_USERAGENT,'LTIOS03/3.1.91 (iPhone; iOS 14.0.1; Scale/2.00)');
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    $response = curl_exec($ch);
	curl_close ($ch);
	return $response;
}

function is_https(){
	return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https' : 'http'.'://';
}
?>