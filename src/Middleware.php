<?php

namespace Axllent\LinksToBlank;

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\Core\Config\Configurable;

class Middleware implements HTTPMiddleware
{
    use Configurable;

    /**
     * Ignore hosts will skip external links for these hostnames
     * unless they are download links. The current host gets added
     * to this automatically.
     *
     * @var array
     */
    private static $ignore_hosts = [];

    /**
     * Additional download extensions will be added to the list of
     * default_file_extensions.
     *
     * @var array
     */
    private static $add_file_extensions = [];

    /**
     * Ignore links with the following class from parsing
     *
     * @var string
     */
    private static $ignore_class = false;

    /**
     * Add rel="nofollow" to external links links.
     * Indicates that the current document's original author or publisher does not endorse the referenced document.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Attributes/rel#nofollow
     *
     * @var bool
     */
    private static $nofollow = false;

    /**
     * Add rel="noreferrer" to external links, preventing the referring website
     * information from being sent to the target website.
     * Additionally, has the same effect as noopener, so if this is set then noopener if not added.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Attributes/rel/noreferrer
     *
     * @var bool
     */
    private static $noreferrer = false;

    /**
     * Add a css class to download links.
     *
     * @var null|string
     */
    private static $add_css_files;

    /**
     * Add a css class to external links.
     *
     * @var null|string
     */
    private static $add_css_external;

    /**
     * File extensions to be considered to be download links.
     * These get a target="_blank" appended to them.
     *
     * @var array
     */
    private const DEFAULT_FILE_EXTENSIONS = [
        '.7z',
        '.avi',
        '.bz2',
        '.doc',
        '.docx',
        '.flac',
        '.flv',
        '.gz',
        '.m4a',
        '.mkv',
        '.mov',
        '.mp3',
        '.mp4',
        '.mpeg',
        '.mpg',
        '.ods',
        '.odt',
        '.ogg',
        '.ogv',
        '.pages',
        '.pdf',
        '.pps',
        '.ppt',
        '.pptx',
        '.rar',
        '.tar',
        '.tgz',
        '.wav',
        '.webm',
        '.wmv',
        '.wpd',
        '.xls',
        '.xlsx',
        '.zip',
    ];

    /**
     * Regular expression to match links in HTML.
     */
    private const LINK_TAG_REGEX = '<a\s[^>]*href=("??)([^" >]*?)\1[^>]*>';

    /**
     * Internal array of compiled hosts.
     *
     * @var array
     */
    private $compiled_hosts = [];

    /**
     * Internal array of file extensions.
     *
     * @var array
     */
    private $file_extensions = [];

    /**
     * Filter executed AFTER a request.
     *
     * @param HTTPRequest $request HTTP request
     *
     * @return void
     */
    public function process(HTTPRequest $request, callable $delegate)
    {
        $response = $delegate($request);

        if ($response
            && preg_match('/text\/html/', (string) $response->getHeader('Content-Type'))
            && !preg_match('/^(admin|dev)\//', (string) $request->getURL())
        ) {
            $this->compiled_hosts = static::config()->get('ignore_hosts') ?? [];
            // add current host to list of self::$ignore_hosts
            $host = Director::host();
            if (!in_array($host, $this->compiled_hosts)) {
                array_push($this->compiled_hosts, $host);
            }

            $response->setBody(
                $this->updateLinks((string) $response->getBody())
            );
        }

        return $response;
    }

    /**
     * Update links setting rel="nofollow" to external links
     *
     * @param string $html HTML body
     */
    private function updateLinks(string $html): string
    {
        $regexp                = static::LINK_TAG_REGEX;
        $this->file_extensions = static::DEFAULT_FILE_EXTENSIONS;
        $ignore_class          = static::config()->get('ignore_class');
        $nofollow              = static::config()->get('nofollow');
        $noreferrer            = static::config()->get('noreferrer');
        $add_css_files         = static::config()->get('add_css_files');
        $add_css_external      = static::config()->get('add_css_external');
        $add_file_extensions   = static::config()->get('add_file_extensions');

        if (!is_bool($nofollow)) {
            throw new \InvalidArgumentException('nofollow must be a boolean');
        }

        if (!is_bool($noreferrer)) {
            throw new \InvalidArgumentException('noreferrer must be a boolean');
        }

        if (is_array($add_file_extensions) && count($add_file_extensions) > 0) {
            // merge default download extensions with configured ones
            $this->file_extensions = array_merge($this->file_extensions, $add_file_extensions);
        }

        if (preg_match_all("/{$regexp}/siU", $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                // we're only interested in the first part, so close off the <a> tag
                $dom = \SimpleHtmlDom\str_get_html($match[0] . '</a>');
                // use SimpleHtmlDom to parse the link which is more reliable than regex
                // and extremely efficient
                $a = $dom->find('a', 0);

                if ($a) {
                    $href    = strtolower(trim((string) $a->href));
                    $rel     = strtolower((string) $a->rel);
                    $target  = strtolower((string) $a->target);
                    $classes = preg_split('/\s+/', (string) $a->class, -1, PREG_SPLIT_NO_EMPTY);

                    // skip if this has the ignore class
                    if ($ignore_class && in_array($ignore_class, $classes)) {
                        continue;
                    }

                    $is_file_link     = $this->isDownloadLink($href);
                    $is_external_link = $this->isExternalLink($href);

                    // ignore link if neither external nor file link
                    if (!$is_external_link && !$is_file_link) {
                        continue;
                    }

                    if ($is_file_link && $add_css_files) {
                        // add file CSS class
                        $classes[] = $add_css_files;
                    }

                    if ($is_external_link && $add_css_external) {
                        // add external CSS class
                        $classes[] = $add_css_external;
                    }

                    // if link does not have a target set, add target="_blank"
                    if (empty($target)) {
                        $a->target = '_blank';
                    }

                    if ($is_external_link) {
                        $relParts = $this->updateRelParts($rel, $nofollow, $noreferrer);
                        if (count($relParts) > 0) {
                            // set the rel attribute
                            $a->rel = implode(' ', $relParts);
                        }
                    }

                    if (count($classes) > 0) {
                        // set the class attribute
                        $a->class = implode(' ', array_unique($classes));
                    }

                    // replace the original link with the updated one, removing the closing </a> tag
                    // as this was artificially added to the original tag for parsing.
                    $html = str_replace($match[0], str_replace('</a>', '', (string) $a), $html);
                }
            }
        }

        return $html;
    }

    /**
     * Detect if a link is external.
     * $href is already lowercased.
     */
    private function isExternalLink(string $href): bool
    {
        $host = parse_url($href, PHP_URL_HOST);

        return $host
            && preg_match('/^https?:\/\//', (string) $href)
            && !in_array($host, $this->compiled_hosts);
    }

    /**
     * Detect if a link is a file link.
     * $href is already lowercased.
     */
    private function isDownloadLink(string $href): bool
    {
        $urlParts = parse_url($href);
        if (empty($urlParts['path'])) {
            return false;
        }

        foreach ($this->file_extensions as $dl) {
            if (str_ends_with($urlParts['path'], $dl)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Update rel parts based on the current configuration.
     */
    private function updateRelParts(string $rel, bool $nofollow, bool $noreferrer): array
    {
        $relParts = preg_split('/\s+/', (string) $rel, -1, PREG_SPLIT_NO_EMPTY);
        if ($nofollow && !in_array('nofollow', $relParts) && !in_array('follow', $relParts)) {
            // add nofollow if it is enabled and not already set
            $relParts[] = 'nofollow';
        }

        if ($noreferrer && !in_array('noreferrer', $relParts)) {
            // add noreferrer if it is enabled and not already set
            $relParts[] = 'noreferrer';
        } elseif (!in_array('noopener', $relParts)) {
            // noopener is added by default if noreferrer is not set
            // as noreferrer has the same effect as noopener
            $relParts[] = 'noopener';
        }

        return $relParts;
    }
}
