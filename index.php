<?php
require_once("ganon/ganon.php");

foreach (array('url', 'entry') as $required) {
  if (!isset($_GET[$required])) {
    header('Location: README.md');
    exit();
  }
  $$required = $_GET[$required];
}
$h = file_get_dom($url);
?>
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title><?=isset($_GET['feedtitle']) ? $_GET['feedtitle'] : $h('title', 0)->getPlainText()?></title>
    <link href="<?=htmlentities($url)?>" rel="self"/>
    <lastBuildDate><?=date(DateTime::RFC3339, time())?></lastBuildDate>
    <generator uri="https://github.com/johslarsen/url2rss">URL2RSS/0.1</generator>

<?php foreach($h($entry) as $e) {
  $l = isset($_GET['link']) ? $e($_GET['link'], 0) : str_get_dom('<a href=""/>', true);
  $d = isset($_GET['description']) ? $e($_GET['description'], 0) : $e;
  $t = isset($_GET['title']) ? $e($_GET['title'], 0) : $l;?>
    <item>
      <title><?=$t->getPlainText()?></title>
      <link><?=html_entity_decode($l->href)?></link>
      <description><?="<![CDATA[".html_entity_decode($d->toString())."]]>"?></description>
    </item>
<?php } ?>

  </channel>
</rss>
