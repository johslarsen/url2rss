<?php
require_once("ganon/ganon.php");

foreach (array('url', 'entry') as $required) {
  if (!isset($_GET[$required])) {
    header('Location: README.md');
    exit();
  }
}
$h = file_get_dom($_GET['url']);

function authority($url_components) {
    $port = isset($url_components['port']) ? ":".$url_components['port'] : "";
    $pwd = isset($url_components['pass']) ? ":".$url_components['pass'] : "";
    $login = isset($url_components['user']) ? $url_components['user'].$pwd."@" : "";
    return $login.$url_components['host'].$port;
}
function absolute($relative_url) {
    $r = parse_url($_GET['url']);
    $u = parse_url($relative_url);

    $url = isset($u['scheme']) ? $u['scheme'] : $r['scheme'];
    $url .= "://";
    $url .= isset($u['host']) ? authority($u) : authority($r);
    if (!isset($u['path'])) {
        $url .= $r['path'];
    } else {
        $url .= ($u['path'][0] != "/" ? dirname($r['path'])."/" : "") . $u['path'];
    }
    if (isset($u['query'])) $url .= "?${u['query']}";
    if (isset($u['fragment'])) $url .= "?${u['fragment']}";
    return $url;
}

?>
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title><?=htmlspecialchars(isset($_GET['feedtitle']) ? $_GET['feedtitle'] : $h('title', 0)->getPlainText())?></title>
    <link href="<?=htmlspecialchars($_GET['url'])?>" rel="self"/>
    <lastBuildDate><?=date(DateTime::RFC3339, time())?></lastBuildDate>
    <generator uri="https://github.com/johslarsen/url2rss">URL2RSS/0.1</generator>

<?php foreach($h($_GET['entry']) as $e) {
  $l = isset($_GET['link']) ? $e($_GET['link'], 0) : str_get_dom('<a href=""/>', true);
  $d = isset($_GET['description']) ? $e($_GET['description'], 0) : $e;
  $t = isset($_GET['title']) ? $e($_GET['title'], 0) : $l;?>
    <item>
      <title><?=html_entity_decode($t->getPlainText())?></title>
      <link><?=htmlspecialchars(absolute(html_entity_decode($l->href)))?></link>
      <description><?="<![CDATA[".html_entity_decode($d->toString())."]]>"?></description>
    </item>
<?php } ?>

  </channel>
</rss>
