<?php

namespace Choult\Enamel\Classifier;

use \Choult\Enamel\Feature\Extractor;
use \Choult\Enamel\Feature\Vector;
use \Choult\Enamel\Classifier;
use \Choult\Enamel\Document;

class BernoulliNaiveBayes implements Classifier
{

    private $extractor;

    private $dirty = 0;

    private $featureList = [];

    private $tagFeatureList = [];

    private $model;

    public function __construct(Extractor $extractor)
    {
        $this->extractor = $extractor;
    }

    /**
     * Adds a Document to the Training set
     *
     * @param Document $document
     *
     * @return
     */
    public function train(Document $document)
    {
        $this->model = null;
        $features = $this->extractor->extract($document);
        $this->addFeatures($features);
        foreach ($document->getTags() as $tag) {
            $this->addTagFeatures($tag, $features);
        }

        return $features;
    }

    /**
     * Gets a list of tags for the passed document
     *
     * @param Document $document
     *
     * @return array
     */
    public function predict(Document $document)
    {
        if ($this->model == null) {
            $this->calculateModel();
        }

        $base = array_fill_keys(array_keys($this->featureList), 0);
        $features = $this->extractor->extract($document);
        array_walk(
            $features,
            function (&$item) {
                $item = 1;
            }
        );

        $features = array_merge($base, $features);

        $predictions = [];
        foreach ($this->model as $tag => $model) {
            $predictions[$tag] = $this->predictTag($model, $features);
        }

        return $predictions;
    }

    private function predictTag(array $model, array $features)
    {
        $num = 1;

        foreach ($model as $tag => $probability) {
            $num *= pow(1 - $probability, (1 - $features[$tag]));
        }

        return $num;
    }

    private function calculateModel()
    {
        $this->model = [];
        foreach ($this->tagFeatureList as $tag => $tagFeatures) {
            $model = array_fill_keys(array_keys($this->featureList), 0);
            $model = array_merge($model, $tagFeatures);
            foreach ($this->featureList as $feature => $count) {
                $model[$feature] = $model[$feature] / $count;
            }
            $this->model[$tag] = $model;
        }
    }

    private function addFeatures(array $features)
    {
        foreach (array_keys($features) as $feature) {
            $this->featureList[$feature] = (isset($this->featureList[$feature])) ? $this->featureList[$feature] + 1 : 1;
        }
    }

    private function addTagFeatures($tag, array $features)
    {
        if (!isset($this->tagFeatureList[$tag])) {
            $this->tagFeatureList[$tag] = [];
        }

        $flist = &$this->tagFeatureList[$tag];

        foreach (array_keys($features) as $feature) {
            $flist[$feature] = (isset($flist[$feature])) ? $flist[$feature] + 1 : 1;
        }
    }
}