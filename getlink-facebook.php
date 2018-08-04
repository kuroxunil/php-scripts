<?php
set_time_limit(0);
$token = 'EDIT_HERE';
$url = isset($_GET['url']) ? $_GET['url'] : null;
if (filter_var($url, FILTER_VALIDATE_URL)) {
	$videos = get_public_video($url);
	if (!$videos[0]['file'] || !$videos['file'])
		$videos = get_private_video($url, $token);
	echo json_encode($videos);
}

function get_public_video($url) {
	$source = get_page_source($url);
	$hd = array(
		'file' => explode_by('hd_src_no_ratelimit:"', '"', $source),
		'type' => 'video/mp4',
		'label' => '720p',
		'default' => true
	);
	$hd_limit = array(
		'file' => explode_by('hd_src:"', '"', $source),
		'type' => 'video/mp4',
		'label' => '720p',
		'default' => true
	);
	$sd = array(
		'file' => explode_by('sd_src_no_ratelimit:"', '"', $source),
		'type' => 'video/mp4',
		'label' => '360p'
	);
	return $hd['file'] ? array($hd, $sd) : $hd_limit['file'] ? array($hd_limit, $sd) : $sd;
}
function get_private_video($url, $token) {
	$api_url = 'https://graph.facebook.com/' . get_video_id($url) . '?fields=source&access_token=' . $token;
	$source = get_page_source($api_url);
	$json = json_decode($source);
	return ($json->error) ? $json->error->message :
	array(
		'file' => $json->source,
		'type' => 'video/mp4',
		'label' => '720p'
	);
}
function get_video_id($url) {
	if (preg_match('/videos\/\w+\./', $url)) {
		$id = explode('/', $url);
		return $id[count($id) - 2];
	} elseif (strpos($url, 'videos/')) {
		return explode_by('videos/', '/', $url);
	} else return null;
}
function explode_by($begin, $end, $data) {
	$data = explode($begin, $data);
	$data = explode($end, $data[1]);
	return $data[0];
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