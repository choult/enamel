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
     * Gets a list of labels describing this Document
     *
     * @return array
     */
    public function getLabels();

    /**
     * Gets the content of this Document to have features extracted from
     *
     * @return string
     */
    public function getContent();
}
