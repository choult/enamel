<?php

namespace Choult\Enamel;

/**
 * An interface to represent a Feature extractor
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 */
interface Document
{
    /**
     * @return array
     */
    public function getTags();

    /**
     * @return string
     */
    public function getContent();
}
