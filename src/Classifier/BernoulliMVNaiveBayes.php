<?php

namespace Choult\Enamel\Classifier;

use \Choult\Enamel\Feature\Extractor;
use \Choult\Enamel\Feature\Vector;
use \Choult\Enamel\Classifier;
use \Choult\Enamel\Document;
use \Choult\Enamel\Model;

/**
 * A Bernoulli Multi-Variate Naive Bayes classifier
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 */
class BernoulliMVNaiveBayes extends MultiVariateNaiveBayes
{

    /**
     * {@inheritdoc}
     */
    public function predict(Document $document, $useLog = 0)
    {
        if ($this->model->isEmpty()) {
            $this->generateModel();
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
        foreach ($this->model->getLabels() as $label) {
            $predictions[$label] = $this->predictLabel($label, $features, $useLog);
        }

        return $predictions;
    }

    /**
     * Generates a prediction for the given label
     *
     * @param string   $label
     * @param array    $features
     * @param boolean  $useLog
     *
     * @return float
     */
    protected function predictLabel($label, array $features, $useLog)
    {
        if ($useLog) {
            $score = log($this->model->getLabelCount($label) / $this->model->getDocCount());

            foreach ($this->model->getLabelModel($label) as $feature => $probability) {
                if (isset($features[$feature])) {
                    $score +=
                        ($features[$feature])
                            ? log($probability)
                            : log(1 - $probability);
                }
            }
        } else {
            $score = $this->model->getLabelCount($label) / $this->model->getDocCount();

            foreach ($this->model->getLabelModel($label) as $feature => $probability) {
                if (isset($features[$feature])) {
                    $score *=
                        ($features[$feature])
                            ? $probability
                            : (1 - $probability);
                }
            }
        }

        return $score;
    }
}
