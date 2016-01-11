<?php

namespace Choult\Enamel\Feature\Extractor;

use \Choult\Enamel\Document;
use \Choult\Enamel\Feature\Extractor;

use \Porter;

/**
 * A class to represent an NGram Feature Extractor
 *
 * @license http://opensource.org/licenses/MIT
 * @package Enamel
 * @author Christopher Hoult <chris@choult.com>
 */
class NGram implements Extractor
{

    private $key = '';
    private $gramLength = 1;
    private $stopwordFile;
    private $caseSensitive = false;
    private $stopwords = [];

    public function __construct($key, $stopwordFile, $gramLength = 1, $caseSensitive = false)
    {
        if (!is_integer($gramLength) || $gramLength < 1) {
            throw new Exception('The gramLength parameter must be a positive integer');
        }
        $this->key = $key;
        $this->stopwordFile = $stopwordFile;
        $this->gramLength = $gramLength;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * {@inheritdoc}
     */
    public function extract(Document $document)
    {
        $content = $document->getContent();
        if (!$this->caseSensitive) {
            $content = strtolower($content);
        }
        $stemmed = $this->stem($this->removeStopwords($this->tokenize($content)));
        $result = [];
        for ($i = 0; $i <= (count($stemmed) - $this->gramLength); $i++) {
            $tokens = array_slice($stemmed, $i, $this->gramLength);
            $key = (($this->key) ? $this->key . '.' : '') . implode('.', $tokens);
            $result[$key] = (isset($result[$key])) ? $result[$key] + 1 : 1;
        }

        ksort($result);

        return $result;
    }

    protected function tokenize($str)
    {
        $str = str_replace('-', '', $str);
        $str = preg_replace('[^A-z]', '', $str);
        return explode(' ', $str);
    }

    protected function removeStopwords(array &$tokens)
    {
        $stopwords = $this->getStopwords();
        foreach ($tokens as $idx => $token) {
            if (in_array($token, $stopwords)) {
                unset($tokens[$idx]);
            }
        }
        return $tokens;
    }

    protected function getStopwords()
    {
        if (!$this->stopwords) {
            $cont = file_get_contents($this->stopwordFile);
            $cont = preg_replace('/^#.*$/', '', $cont);
            $this->stopwords = explode("\n", $cont);
        }
        return $this->stopwords;
    }

    protected function stem(array &$tokens)
    {
        foreach ($tokens as $idx => $token) {
            if (trim($token)) {
                $tokens[$idx] = \Porter::stem($token);
            } else {
                unset($tokens[$idx]);
            }
        }
        return $tokens;
    }
}
