<?php

namespace Choult\Enamel\Feature\Extractor;

use Choult\Enamel\Document;
use \Choult\Enamel\Feature\Extractor;

class Collection implements Extractor
{
    /** @var Extractor[] */
    private $extractors = [];

    /**
     * @param array  $extractors
     */
    public function __construct(array $extractors)
    {
        $this->setExtractors($extractors);
    }


    /**
     * Sets the extractors.
     *
     * @param array  $extractors
     */
    public function setExtractors(array $extractors)
    {
        $this->extractors = [];
        foreach ($extractors as $extractor) {
            $this->addExtractor($extractor);
        }
    }


    /**
     * @param Extractor $extractor
     */
    public function addExtractor(Extractor $extractor)
    {
        $this->extractors[] = $extractor;
    }

    public function extract(Document $document)
    {
        $features = [];
        foreach ($this->extractors as $extractor) {
            $features = array_merge($features, $extractor->extract($document));
        }

        ksort($features);
        return $features;
    }
}