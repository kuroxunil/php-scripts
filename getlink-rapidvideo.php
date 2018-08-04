<?php
set_time_limit(0);
$url = isset($_GET['url']) ? $_GET['url'] : null;
if (filter_var($url, FILTER_VALIDATE_URL)) {
	$videos = get_video_without_api($url);
	echo json_encode($videos);
}

function get_video_without_api($url) {
	$download_url = 'https://www.rapidvideo.com/d/' . get_video_id($url);
	$source = get_page_source($download_url);
	$videos = array();
	if (preg_match_all('/<a href="(.*)" id="button-download" class="(.*)" style="(.*)">/', $source, $a)) {
		preg_match_all('/<span style="(.*)">Download (.*)<\/span>/', $source, $span);
		foreach ($a[1] as $index => $href)
			array_push($videos, array(
				'file' => $href,
				'type' => 'video/mp4',
				'label' => $span[2][$index]
			));
		$videos[count($videos) - 1]['default'] = true;
	}
	return $videos;
}
function get_video_id($url) {
	preg_replace('/&q=\w+/', '', $url);
	preg_match('/\w+$/', $url, $match);
	return $match[0];
}
function get_page_source($url) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_CONNECTTIMEOUT => 0,
		CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36',
		CURLOPT_FOLLOWLOCATION => true
	));
	$source = curl_exec($curl);
	curl_close($curl);
	return $source;
}