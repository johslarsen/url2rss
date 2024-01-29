<?php 
$user = $_GET["user"];
$url = "https://www.youtube.com/$user/videos";

$html = file_get_contents($url);
preg_match("/ytInitialData = (.*}}});/", $html, $matches);
$ytInitialData = json_decode($matches[1]);

function find_videos($array, &$result = array()) {
    foreach($array as $key => $value) {
        if ($key == "videoRenderer") {
            array_push($result, $value);
        } elseif (is_array($value) || is_object($value)) {
            find_videos($value, $result);
        }
    }
    return $result;
}
$videos = find_videos($ytInitialData);
header('Content-Type: text/xml');
?>
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title><?=$user?></title>
    <link href="<?=htmlspecialchars($url)?>" rel="self"/>
    <lastBuildDate><?=date(DateTime::RFC3339, time())?></lastBuildDate>
    <generator uri="https://github.com/johslarsen/url2rss">URL2RSS/0.2</generator>

<?php foreach($videos as $v) {
  $l = "https://www.youtube.com/watch?v={$v->videoId}";
  $t = $v->title->runs[0]->text;
  $i = end($v->thumbnail->thumbnails)->url;
  $d = $v->descriptionSnippet->runs[0]->text;
  ?>
    <item>
      <title><?=htmlspecialchars($t)?></title>
      <link><?=htmlspecialchars($l)?></link>
      <guid><?=htmlspecialchars($l)?></guid>
      <description><?="<![CDATA[<img src=\"".htmlspecialchars($i)."\"/><p>".html_entity_decode($d)."</p>]]>"?></description>
    </item>
<?php ; } ?>

  </channel>
</rss>
