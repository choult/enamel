<?php

namespace Choult\Enamel\Classifier;

use \Choult\Enamel\Feature\Extractor;
use \Choult\Enamel\Feature\Vector;
use \Choult\Enamel\Classifier;
use \Choult\Enamel\Document;
use \Choult\Enamel\Model;

class MultiVariateNaiveBayes implements Classifier
{

    private $extractor;

    private $dirty = 0;

    private $featureList = [];

    private $tagFeatureList = [];

    private $docCount = 0;

    private $tagCounts = [];

    private $model;

    public function __construct(Extractor $extractor, Model $model)
    {
        $this->extractor = $extractor;
        $this->setModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function train(Document $document)
    {
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
     * {@inheritdoc}
     */
    public function predict(Document $document)
    {
        if ($this->model->isEmpty()) {
            $this->generateModel();
        }

        $features = $this->extractor->extract($document);

        $predictions = [];
        foreach ($this->model->getLabels() as $label) {
            $predictions[$label] = $this->predictLabel($label, $features);
        }

        return $predictions;
    }

    /**
     * {@inheritdoc}
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $label
     * @param array $features
     * @return float
     */
    private function predictLabel($label, array $features)
    {
        $score = log($this->model->getLabelCount($label) / $this->model->getDocCount());
        $featureCount = 1;

        foreach ($features as $feature => $count) {
            if ($this->model->labelModelsFeature($label, $feature)) {
                $probability = $this->model->getLabelFeatureModel($label, $feature);
                $score += ($count * log($probability));
                $featureCount += $count;
            }
        }

        return $score / $featureCount;
    }

    public function generateModel()
    {
        $this->model->reset();
        $cnt = 0;
        foreach ($this->tagFeatureList as $label => $labelFeatures) {
            $model = array_fill_keys(array_keys($this->featureList), 0);
            $model = array_merge($model, $labelFeatures);
            foreach ($this->featureList as $feature => $count) {
                $model[$feature] = ($model[$feature] + 1) / ($count + 1);
            }
            $this->model->setLabelModel($label, $this->tagCounts[$label], $model);
        }
        $this->model->setDocCount($this->docCount);
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