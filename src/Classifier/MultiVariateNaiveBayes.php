<?php

namespace Choult\Enamel\Classifier;

use \Choult\Enamel\Feature\Extractor;
use \Choult\Enamel\Feature\Vector;
use \Choult\Enamel\Classifier;
use \Choult\Enamel\Document;
use \Choult\Enamel\Model;

/**
 * A Multi-Variate Naive Bayes classifier
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 */
class MultiVariateNaiveBayes implements Classifier
{

    /**
     * The Feature Extractor for this Classifier
     *
     * @var \Choult\Enamel\Feature\Extractor
     */
    protected $extractor;

    /**
     * A list of features modelled in this Classifier
     *
     * @var array
     */
    protected $featureList = [];

    /**
     * A list of features describing each label
     *
     * @var array
     */
    protected $labelFeatureList = [];

    /**
     * The number of documents this classifier has been trained with
     *
     * @var int
     */
    protected $docCount = 0;

    /**
     * An array of labels and the number of occurrences
     *
     * @var array
     */
    protected $labelCounts = [];

    /**
     * The model this classifier is to build
     *
     * @var \Choult\Enamel\Model
     */
    protected $model;

    /**
     * Constructs a new Multi-Variate NB classifier
     *
     * @param Extractor $extractor
     * @param Model $model
     */
    public function __construct(Extractor $extractor, Model $model)
    {
        $this->extractor = $extractor;
        $this->setModel($model);
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
     * {@inheritdoc}
     */
    public function train(Document $document)
    {
        $this->docCount++;
        $features = $this->extractor->extract($document);
        $this->addFeatures($features);
        foreach ($document->getLabels() as $label) {
            $this->labelCounts[$label] = (isset($this->labelCounts[$label])) ? $this->labelCounts[$label] + 1 : 1;
            $this->addLabelFeatures($label, $features);
        }

        return $features;
    }

    /**
     * {@inheritdoc}
     */
    public function predict(Document $document, $useLog = 0)
    {
        if ($this->model->isEmpty()) {
            $this->generateModel();
        }

        $features = $this->extractor->extract($document);

        $predictions = [];
        foreach ($this->model->getLabels() as $label) {
            $predictions[$label] = $this->predictLabel($label, $features, $useLog);
        }

        return $predictions;
    }

    /**
     * {@inheritdoc}
     */
    public function generateModel($labelCount = 0)
    {
        $this->model->reset();
        if (!$labelCount || $labelCount > count($this->labelCounts)) {
            $labelCount = count($this->labelCounts);
        }

        arsort($this->labelCounts);

        $labelList = array_slice(array_keys($this->labelCounts), 0, $labelCount);

        foreach ($labelList as $label) {
            $labelFeatures = $this->labelFeatureList[$label];
            $model = array_fill_keys(array_keys($this->featureList), 0);
            $model = array_merge($model, $labelFeatures);
            foreach ($this->featureList as $feature => $count) {
                $model[$feature] = ($model[$feature] + 1) / ($count + 1);
            }
            $this->model->setLabelModel($label, $this->labelCounts[$label], $model);
        }
        $this->model->setDocCount($this->docCount);
    }


    /**
     * Generates a prediction for the given label
     *
     * @param $label
     * @param array $features
     * @return float
     */
    protected function predictLabel($label, array $features, $useLog)
    {
        if ($useLog) {
            $score = log($this->model->getLabelCount($label) / $this->model->getDocCount());

            foreach ($features as $feature => $count) {
                if ($this->model->labelModelsFeature($label, $feature)) {
                    $probability = $this->model->getLabelFeatureModel($label, $feature);
                    $score += log($probability);
                }
            }

            return $score;
        } else {
            $score = $this->model->getLabelCount($label) / $this->model->getDocCount();

            foreach ($features as $feature => $count) {
                if ($this->model->labelModelsFeature($label, $feature)) {
                    $probability = $this->model->getLabelFeatureModel($label, $feature);
                    $score *= $probability;
                }
            }

            return $score;
        }
    }

    /**
     * Adds a list of features and their counts to this classifier
     *
     * @param array $features
     */
    protected function addFeatures(array $features)
    {
        foreach (array_keys($features) as $feature) {
            $this->featureList[$feature] = (isset($this->featureList[$feature])) ? $this->featureList[$feature] + 1 : 1;
        }
    }

    /**
     * Adds a list of features and their counts for a given label
     *
     * @param $label
     * @param array $features
     */
    protected function addLabelFeatures($label, array $features)
    {
        if (!isset($this->labelFeatureList[$label])) {
            $this->labelFeatureList[$label] = [];
        }

        $flist = &$this->labelFeatureList[$label];

        foreach (array_keys($features) as $feature) {
            $flist[$feature] = (isset($flist[$feature])) ? $flist[$feature] + 1 : 1;
        }
    }
}
