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

| Parameter       | Description |
| ---------       | ----------- |
| `url`           | The URL for the given website |
| `entry`         | A pattern for the article containers |
| `link`          | A pattern for an anchor within the entry that will be used as the entry's link |
| `[title]`       | A pattern for an element within the entry whose text content will be used for as the entry's title. Default is the link anchor. |
| `[descritpion]` | A pattern for an element within the entry whose raw content will be used as the entry's description. Default is the entry container. |
| `[feedtitle]`   | Override the title of the feed |
