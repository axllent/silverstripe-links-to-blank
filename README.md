# Automatically set `target="_blank"` for outgoing & file links in Silverstripe

This module enhances your HTML output by automatically adding `target="_blank"` to all **outgoing** links and internal "download links" (e.g., PDF, ZIP, TAR, DOC, PPT, and Excel files).

Earlier versions of this module used JavaScript for similar functionality, but that approach caused compatibility issues with tools like the Wayback Machine.

## Features

-   Automatically adds `target="_blank"` to outgoing and internal file links.
-   Includes `rel="noopener"` to outgoing links to prevent [cross-site exploits](https://mathiasbynens.github.io/rel-noopener/).
-   Optionally adds `rel="nofollow"` and `rel="noreferrer"` to outgoing links.
-   Allows adding custom CSS classes to file and external links for styling.
-   Supports adding additional file extensions for custom file types.

## Requirements

-   Silverstripe ^4 || ^5 || ^6

## Installation

```shell
composer require axllent/silverstripe-links-to-blank
```

After installation ensure you have done a `?flush`. No further configuration is required.

## Usage

To customise the module, you can define your own configuration:

```yaml
Axllent\LinksToBlank\Middleware:
    ## Specify additional external hosts to exclude from processing.
    ## The current host is automatically excluded.
    ignore_hosts:
        - "www.example.com"

    ## Exclude links with this CSS class from being parsed (default: none).
    ignore_css_class: "ignore-link"

    ## Add a CSS class to all external links (default: none).
    add_css_external: "external-link"

    ## Add a CSS class to all file links (default: none).
    add_css_files: "download-link"

    ## Define custom file types to be treated as file links.
    ## Each file type should include the leading dot.
    add_file_extensions:
        - ".ext"

    ## Add rel="nofollow" to all external links (default: false).
    nofollow: true

    ## Add rel="noreferrer" to all external links (default: false).
    noreferrer: true
```
