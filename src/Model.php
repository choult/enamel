<?php

namespace Choult\Enamel;

/**
 * A class to store the inner state of a Classifier
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 */
class Model
{

    /**
     * A list of labels modelled
     *
     * @var array
     */
    private $labels = [];

    /**
     * A list of label models, indexed by label
     *
     * @var array
     */
    private $model = [];

    /**
     * The total number of Documents that this model was trained with
     *
     * @var int
     */
    private $docCount = 0;

    /**
     * Wipes this Model's data
     */
    public function reset()
    {
        $this->setLabels([]);
        $this->setModel([]);
        $this->setDocCount(0);
    }

    /**
     * Gets the document count for this model
     *
     * @return integer
     */
    public function getDocCount()
    {
        return $this->docCount;
    }

    /**
     * Sets the document count for this model
     *
     * @param integer $docCount
     */
    public function setDocCount($docCount)
    {
        $this->docCount = $docCount;
    }

    /**
     * Gets the list of labels from this Model
     *
     * @return array
     */
    public function getLabels()
    {
        return array_keys($this->labels);
    }

    /**
     * Gets a list of labels and their counts
     *
     * @return array
     */
    public function getLabelCounts()
    {
        return $this->labels;
    }

    /**
     * Gets the count of documents the passed label represents
     *
     * @param $label
     *
     * @return integer
     */
    public function getLabelCount($label)
    {
        return $this->labels[$label];
    }

    /**
     * Sets the labels this Model codes for
     *
     * @param array $labels
     */
    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * Adds a label to this Model
     *
     * @param $label
     */
    public function addLabel($label, $count)
    {
        $this->labels[$label] = $count;
    }

    /**
     * Sets the model for the passed label
     *
     * @param string $label
     * @param integer $labelCount
     * @param array $model
     */
    public function setLabelModel($label, $labelCount, array $model)
    {
        $this->addLabel($label, $labelCount);
        $this->model[$label] = $model;
    }

    /**
     * Sets the model for the passed label
     *
     * @param string $label
     *
     * @return array
     */
    public function getLabelModel($label)
    {
        return $this->model[$label];
    }

    /**
     * Returns true if the passed feature is modelled for the given label
     *
     * @param string $label
     * @param string $feature
     *
     * @return boolean
     */
    public function labelModelsFeature($label, $feature)
    {
        return (isset($this->model[$label], $this->model[$label][$feature]));
    }

    /**
     * Gets the model for this label/feature pair
     *
     * @param $label
     * @param $feature
     *
     * @return mixed
     */
    public function getLabelFeatureModel($label, $feature)
    {
        if ($this->labelModelsFeature($label, $feature)) {
            return $this->model[$label][$feature];
        }
    }

    /**
     * Sets the model data
     *
     * @param array $model
     */
    public function setModel(array $model)
    {
        $this->model = $model;
    }

    /**
     * Gets this model's data
     *
     * @return array
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Returns whether this Model is empty or not
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return (count($this->model)) ? false : true;
    }
}
