<?php

namespace Axllent\LinksToBlank;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\View\Requirements;

/**
 * Create target="_blank" links
 * ==============================
 *
 * Extension to inline JavaScript to all pages to add target="_blank"
 * to all outgoing links, as well as all links to PDF, DOC, and Excel files
 *
 * License: MIT-style license http://opensource.org/licenses/MIT
 * Authors: Techno Joy development team (www.technojoy.co.nz)
 */

class LinksToBlank extends Extension
{
    private $inline = true;

    /*
     * Automatically initiate the code
     */
    public function onAfterInit()
    {
        if (Config::inst()->get('Axllent\LinksToBlank\LinksToBlank', 'inline')) {
            $script = $this->Compress(
                file_get_contents(Director::getAbsFile($this->getModuleBase() . '/javascript/linkstoblank.js'))
            );
            Requirements::customScript($script);
        } else {
            Requirements::javascript(
                $this->getModuleBase() . '/javascript/linkstoblank.js'
            );
        }
    }

    /*
     * Compress inline JavaScript
     * @param str data
     * @return str
     */
    protected function Compress($data)
    {
        $repl = array(
            '/(\n|\t)/' => '',
            '/\s?=\s?/' => '=',
            '/\s?==\s?/' => '==',
            '/\s?!=\s?/' => '!=',
            '/\s?;\s?/' => ';',
            '/\s?:\s?/' => ':',
            '/\s?\+\s?/' => '+',
            '/\s?\?\s?/' => '?',
            '/\s?&&\s?/' => '&&',
            '/\s?\(\s?/' => '(',
            '/\s?\)\s?/' => ')',
            '/\s?\|\s?/' => '|',
            '/\s<\s?/' => '<',
            '/\s>\s?/' => '>',
            '/(\s{2,})/' => '' // tabs / multiple spaces
        );
        return preg_replace(array_keys($repl), array_values($repl), $data);
    }

    /**
    * Returns the base directory of this module
    * @return string
    */
    private function getModuleBase()
    {
        return basename(dirname(dirname(__FILE__)));
    }
}
