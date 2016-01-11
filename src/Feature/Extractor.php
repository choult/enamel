<?php

namespace Choult\Enamel\Feature;

use \Choult\Enamel\Document;

/**
 * An interface to represent a Feature extractor
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 */
interface Extractor
{

    /**
     * Extracts a Feature Vector from the passed Document
     *
     * @param Document $document
     *
     * @return \Choult\Enamel\Feature\Vector
     *
     * @throws \Choult\Enamel\Feature\Extractor\Exception
     */
    public function extract(Document $document);
}
