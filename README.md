# Append target="_blank" with JavaScript for SilverStripe

Extension to automatically add JavaScript on all pages to add target="_blank" to all **outgoing** links,
as well as "download links" such as PDF, ZIP, TAR, DOC, PPT and Excel files.

For security, it also adds `rel="[noopener]` to the links to prevent cross-site exploit ([see here](https://mathiasbynens.github.io/rel-noopener/)).

## Requirements

- SilverStripe 3+

## Installation

Installation can be done either by composer or by manually downloading a release.

### Via composer

`composer require "axllent/silverstripe-links-to-blank:*"`

### Manually

1. Download the module from [the releases page](https://github.com/axllent/silverstripe-links-to-blank/releases).
2. Extract the folder with contents into site's root directory. This is the one with framework and cms in it.
3. Do a ?flush of your site

## Usage

By default the module will automatically include some compressed inline JavaScript into your page.
If you prefer to include this as a resource instead then you can define this in a yaml config
(eg: `mysite/_config/config.yml`):

```
LinksToBlank:
  inline: false
```
