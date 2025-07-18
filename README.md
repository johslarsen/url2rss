# URL2RSS

URL2RSS is a very basic PHP application that generates a RSS-feed for a given
website. It fetches the given url to the server and uses user-supplied [CSS
Selectors](https://www.w3schools.com/cssref/css_selectors.asp) patterns to
extract the relevant article structure and data items.

## Installation

Clone this repository and its submodules somewhere on a webserver that serves
PHP with privileges to fetch remote URLs.

## Usage

Do a GET request on the served directory with the following GET-parameters:

### Parameters

| Parameter            | Description |
| ---------            | ----------- |
| `url`                | The URL for the given website |
| `entry`              | A pattern for the article containers. NOTE: all `href`(URLs) and `src`(images) attributes within this will be absolutified. |
| `[entryRegexp]`      | Alternative `entry` pattern extracting the article content with regexp instead of the DOM. When set, link etc. are sed replacements string from the matched groups here. |
| `link[$attr]`        | A pattern for an element whose href (or $attr) content will be used as the entry's link. |
| `[title[$attr]]`     | A pattern for an element (default is link) within the entry whose text (or $attr) content will be used for as the entry's title. |
| `[guid[$attr]]`      | A pattern for an element (default is link) within the entry whose text (or $attr) content will be used as the entry's guid. |
| `[descritpion]`      | A pattern for an element within the entry whose raw content will be used as the entry's description. Default is the entry container. |
| `[blacklist[$attr]]` | A comma separated list of elements (or $attr) patterns to delete. |
| `[feedtitle]`        | Override the title of the feed |
| `[user_agent]`       | Override the user agent used for the request |
| `[grep]`             | Only include entries matching this pattern |

The `$attr[/regex/replace]` syntax is a literal `$` followed by an empty string or an attribute name used to specify that the either the inner text content or the given attribute value should be used to fill that particular field. If followed by a literal `/` it will also do a sed-like search and replace on the content.

## Filter

There is also a `filter.php` script that fetches a RSS feed, and only outputs those items that matches a given set of filters.

### Parameters

| Parameter | Description |
| --------- | ----------- |
| `url`     | The URL for the given website |
| `title`   | A case-insensitive pattern for the title of items to include |

## YouTube

There is also a `youtube.php` that converts a youtube page into an RSS-feed (in case they ever turn [videos.xml](https://www.youtube.com/feeds/videos.xml))

### Parameters

| Parameter | Description |
| --------- | ----------- |
| `user`    | The username to create a feed for (i.e. `https://www.youtube.com/<user>/videos`) |
