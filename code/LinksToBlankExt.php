<?php
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

class LinksToBlankExt extends Extension
{

    /*
     * Automatically initiate the code
     */
    public function onAfterInit()
    {
        if (Config::inst()->get('LinksToBlank', 'inline')) {
            $script = $this->Compress(
                @file_get_contents(dirname(dirname(__FILE__)) . '/javascript/linkstoblank.js')
            );
            Requirements::customScript($script);
        } else {
            Requirements::javascript(
                basename(dirname(dirname(__FILE__))) . '/javascript/linkstoblank.js'
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
        );
        return preg_replace(array_keys($repl), array_values($repl), $data);
    }
}
