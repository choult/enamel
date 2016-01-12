<?php

namespace Choult\Enamel\Classifier;

use \Choult\Enamel\Feature\Extractor;
use \Choult\Enamel\Feature\Vector;
use \Choult\Enamel\Classifier;
use \Choult\Enamel\Document;

class MultiVariateNaiveBayes implements Classifier
{

    private $extractor;

    private $dirty = 0;

    private $featureList = [];

    private $tagFeatureList = [];

    private $docCount = 0;

    private $tagCounts = [];

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
        $this->docCount++;
        $features = $this->extractor->extract($document);
        $this->addFeatures($features);
        foreach ($document->getTags() as $tag) {
            $this->tagCounts[$tag] = (isset($this->tagCounts[$tag])) ? $this->tagCounts[$tag] + 1 : 1;
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

        $features = $this->extractor->extract($document);

        $predictions = [];
        foreach (array_keys($this->model) as $tag) {
            $predictions[$tag] = $this->predictTag($tag, $features);
        }

        return $predictions;
    }

    private function predictTag($tag, array $features)
    {
        $score = log($this->tagCounts[$tag] / $this->docCount);
        $featureCount = 1;

        foreach ($features as $feature => $count) {
            if (isset($this->model[$tag][$feature])) {
                $probability = $this->model[$tag][$feature];
                $score += ($count * log($probability));
                $featureCount += $count;
            }
        }

        return $score / $featureCount;
    }

    private function calculateModel()
    {
        $this->model = [];
        foreach ($this->tagFeatureList as $tag => $tagFeatures) {
            $model = array_fill_keys(array_keys($this->featureList), 0);
            $model = array_merge($model, $tagFeatures);
            foreach ($this->featureList as $feature => $count) {
                $model[$feature] = ($model[$feature] + 1) / ($count + 2);
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