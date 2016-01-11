<?php

namespace Choult\Enamel;

use \Choult\Enamel\Document;

/**
 * An interface to model a Classifier
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 */
interface Classifier
{

    /**
     * Adds a Document to the Training set
     *
     * @param Document $document
     *
     * @return
     */
    public function train(Document $document);

    /**
     * Gets a list of tags for the passed document
     *
     * @param Document $document
     *
     * @return array
     */
    public function predict(Document $document);

}
