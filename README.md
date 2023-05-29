# Append target="_blank" with JavaScript for Silverstripe

An extension to automatically add JavaScript on all pages to add target="_blank" to all **outgoing** links,
as well as "download links" such as PDF, ZIP, TAR, DOC, PPT and Excel files.

For security, it also adds `rel="[noopener]` to the outbound links to prevent cross-site exploit ([see here](https://mathiasbynens.github.io/rel-noopener/)).

## Requirements

- Silverstripe ^4 || ^5

For Silverstripe 3, please refer to the [Silverstripe3 branch](https://github.com/axllent/silverstripe-links-to-blank/tree/silverstripe3).

## Installation

Installation can be done either by composer or by manually downloading a release.

### Via composer

```
composer require axllent/silverstripe-links-to-blank
```

## Usage

By default the module will automatically include some compressed inline JavaScript into your page.
If you prefer to include this as a separate JavaScript asset instead then you can define this in a yaml config
(eg: `app/_config/config.yml`):

```
Axllent\LinksToBlank\LinksToBlank:
  inline: false
```
