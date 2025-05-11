<?php
require_once("ganon/ganon.php");

if (!(isset($_GET["url"]) && (isset($_GET["entry"]) || isset($_GET["entryRegexp"])))) {
  header('Content-Type: text/markdown; charset=UTF-8; variant=GFM');
  header('Location: README.md');
  exit();
}
$http_opts = array();
if (isset($_GET['user_agent'])) $http_opts['user_agent'] = $_GET['user_agent'];
$context = stream_context_create(array('http' => $http_opts));
$doc = file_get_dom($_GET['url'], false, false, $context);

function authority($url_components) {
    $port = isset($url_components['port']) ? ":".$url_components['port'] : "";
    $pwd = isset($url_components['pass']) ? ":".$url_components['pass'] : "";
    $login = isset($url_components['user']) ? $url_components['user'].$pwd."@" : "";
    return $login.$url_components['host'].$port;
}
function absolute($relative_url) {
    if (empty($relative_url)) return "";
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
function absolutify_attrs($root, array $attrs) {
  foreach($root->getChildrenByCallback(function($n) {return true;}, true, true) as $n) {
    foreach($attrs as $a) {
      if ($n->hasAttribute($a)) $n->setAttribute($a, absolute(html_entity_decode($n->getAttribute($a))));
    }
  }
}
function blacklist($root, array $elem_attrs)
{
  foreach($elem_attrs as $elem_attr) {
    $e_a = explode("$", $elem_attr);
    $matches = empty($e_a[0])
      ? $root->getChildrenByCallback(function($n) {return true;}, true, true)
      : $root->select($e_a[0], false, true, true);
    foreach ($matches as $n) {
      if (sizeof($e_a) == 1) {
        $n->delete();
      } else {
        $n->deleteAttribute($e_a[1]);
      }
    }
  }
}
function elem_attr($root, $elem_attr, $default_elem, $default_attr) {
  $e_a = explode("$", $elem_attr);
  $e = empty($e_a[0]) ? $default_elem : $root($e_a[0], 0);
  if (empty($e)) return null;
  $a_gsub = explode("/", sizeof($e_a) == 1 ? $default_attr : $e_a[1]);
  $a = $a_gsub[0];
  $c = empty($a) ? $e->getPlainText() : $e->getAttribute($a);
  if (sizeof($a_gsub) != 1) {
    $c = preg_replace("/".$a_gsub[1]."/", $a_gsub[2], $c);
  }
  return array($e, $c);
}
function defaulted(&$value, $default = "") {
  return isset($value) ? $value : $default;
}

function entries($doc) {
    if (isset($_GET['entryRegexp'])) {
        preg_match_all("#".$_GET['entryRegexp']."#", $doc->doc, $matches, PREG_SET_ORDER);
        $unique_entries = [];
        foreach ($matches as $match) {
            $unique_entries[$match[0]] = $match;
        }
        return $unique_entries;
    }
    return ($doc->root)($_GET['entry']);
}

header('Content-Type: text/xml');
?>
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
  <channel>
    <title><?=htmlspecialchars(defaulted($_GET['feedtitle'], ($doc->root)('title', 0)->getPlainText()))?></title>
    <link href="<?=htmlspecialchars($_GET['url'])?>" rel="self"/>
    <lastBuildDate><?=date(DateTime::RFC3339, time())?></lastBuildDate>
    <generator uri="https://github.com/johslarsen/url2rss">URL2RSS/0.2</generator>

<?php foreach(entries($doc) as $e) {
  if (isset($_GET['entryRegexp'])) {
    $fill_in_groups = function($groups) use ($e) {
        return $e[(int)$groups[1]];
    };
    $t = preg_replace_callback("/\\\\([0-9]+)/", $fill_in_groups, defaulted($_GET['title'], "\\0"));
    $l = preg_replace_callback("/\\\\([0-9]+)/", $fill_in_groups, defaulted($_GET['link'], "\\0"));
    $g = preg_replace_callback("/\\\\([0-9]+)/", $fill_in_groups, defaulted($_GET['guid'], "\\0"));
    $d = preg_replace_callback("/\\\\([0-9]+)/", $fill_in_groups, defaulted($_GET['description'], "\\0"));
  } else {
    absolutify_attrs($e, array("href", "src"));
    if (isset($_GET['blacklist'])) blacklist($e, explode(",", $_GET['blacklist']));
    if (isset($_GET['grep']) && preg_match("/".$_GET['grep']."/", $e->toString()) == 0) continue;
    list($le, $l) = elem_attr($e, defaulted($_GET['link']), str_get_dom('<a href=""/>', true), "href");
    if (empty($le)) continue;
    list($te, $t) = elem_attr($e, defaulted($_GET['title']), $le, "");
    list($ge, $g) = elem_attr($e, defaulted($_GET['guid']), $le, "href");
    $d = isset($_GET['description']) ? $e($_GET['description'], 0) : $e;
    $d = empty($d) ? "" : $d->toString();
  }
  ?>
    <item>
      <title><?=htmlspecialchars($t)?></title>
      <link><?=htmlspecialchars($l)?></link>
      <guid><?=htmlspecialchars($g)?></guid>
      <description><?="<![CDATA[".html_entity_decode($d)."]]>"?></description>
    </item>
<?php ; } ?>

  </channel>
</rss>
