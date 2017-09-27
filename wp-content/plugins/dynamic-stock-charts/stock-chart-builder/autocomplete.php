<?php
header('Content-Type: application/javascript; charset=utf-8');
$url = isset($_GET['url']) ? $_GET['url'] : '';
if ($url && strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST'])!==FALSE) {
  $output = @file_get_contents(str_replace(' ', '%20', urldecode($url)));
  // try to decode output as some queries return gzipped string
  $decoded_output = @gzdecode($output);
  print $decoded_output ? $decoded_output : $output;
}
?>