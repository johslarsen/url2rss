<?php
require_once("ganon/ganon.php");

foreach (array('url') as $required) {
  if (!isset($_GET[$required])) {
    header('Content-Type: text/markdown; charset=UTF-8; variant=GFM');
    header('Location: README.md');
    exit();
  }
}
$http_opts = array();
if (isset($_GET['user_agent'])) $http_opts['user_agent'] = $_GET['user_agent'];
$context = stream_context_create(array('http' => $http_opts));

$title = isset($_GET['title']) ? $_GET['title'] : "";

$rss = file_get_dom($_GET['url'], true, false, $context);
foreach($rss->select("item", false, true, true) as $item) {
    if ($title != "" && stripos($item("title",0)->getPlainText(), $title) !== False) {
        continue;
    };
    $item->delete();
}

header('Content-Type: text/xml');
print($rss);
