<?php

use NlpTools\Models\Maxent;
use NlpTools\Optimizers\MaxentOptimizer;
use NlpTools\Classifiers\FeatureBasedLinearClassifier;
use NlpTools\FeatureFactories\FunctionFeatures;
use NlpTools\Documents\WordDocument;
use NlpTools\Documents\TrainingSet;

/**
 * This class is going to implement part of speech tagging using
 * a maxent model by training one or loading one from a file.
 */
class PosTagger
{
	// configuration variables
	protected $classes;
	protected $context_size;

	// objects used for classification
	protected $cls;
	protected $ff;

	public function __construct() {
		$this->classes = array(
			'other',
			'punctuation',
			'verb',
			'article',
			'noun',
			'preposition',
			'adjective',
			'conjunction',
			'adverb',
			'particle',
			'pronoun',
			'numeral'
		);
		$this->context_size = 1;

		$this->ff = new FunctionFeatures(
			$this->getFeatureFunctions()
		);
	}

	/**
	 * @return array An array of features to be used for this classification task.
	 */
	protected function getFeatureFunctions() {
		return array(
			function ($class, $doc) {
				list($w, $prev, $next) = $doc->getDocumentData();
				$features = array();
				$len = mb_strlen($w, "utf-8");

				// the actual word in lower case
				$features[] = "$class ^ ".mb_strtolower($w,"utf-8");

				if ($len>3) {
					// the word's suffixes
					$features[]="$class ^ sub(-1)=".mb_strtolower(mb_substr($w,-1, 3, "utf-8"), "utf-8");
					$features[]="$class ^ sub(-2)=".mb_strtolower(mb_substr($w,-2, 3, "utf-8"), "utf-8");
					$features[]="$class ^ sub(-3)=".mb_strtolower(mb_substr($w,-3, 3, "utf-8"), "utf-8");
				}

				// the words without the suffixes
				if ($len>5)
					$features[] = "$class ^ pre(-3)=".mb_strtolower(mb_substr($w, 0, -3, "utf-8"), "utf-8");
				if ($len>4)
					$features[] = "$class ^ pre(-2)=".mb_strtolower(mb_substr($w, 0, -2, "utf-8"), "utf-8");
				if ($len>3)
					$features[] = "$class ^ pre(-1)=".mb_strtolower(mb_substr($w, 0, -1, "utf-8"), "utf-8");

				// the previous word
				if (isset($prev[0]))
					$features[] = "$class ^ ctx(-1)=".mb_strtolower($prev[0],"utf-8");

				// the next word
				if (isset($next[0]))
					$features[] = "$class ^ ctx(1)=".mb_strtolower($next[0],"utf-8");
				
				if (preg_match("/\d/u",$w))
					$features[] = "$class ^ has_number";

				if (mb_strlen($w,"utf-8")==1)
					$features[] = "$class ^ one_letter";

				return $features;
			}
		);
	}

	/**
	 * @throws RuntimeException
	 * @param string $file The filename of the serialized model
	 * @return Maxent The model
	 */
	protected function unserializeModel($file) {
		$m = unserialize(base64_decode(file_get_contents($file)));
		if (!($m instanceof Maxent))
			throw new RuntimeException("Invalid or corrupt model file");
		return $m;
	}

	/**
	 * Build the classifier from a model and the feature factory.
	 */
	protected function buildClassifier(Maxent $m) {
		$this->cls = new FeatureBasedLinearClassifier(
			$this->ff,
			$m
		);
	}

	/**
	 * Load the model from the file and instatiate a new classifier.
	 *
	 * @param string $file A file that is a serialized and base64 encoded maxent model
	 */
	public function loadModelFromFile($file) {
		$m = $this->unserializeModel($file);
		$this->buildClassifier($m);
	}

	/**
	 * Create a new empty model and instatiate a new classifier.
	 * If weights are passed as a parameter then the modl is not empty.
	 *
	 * @param array $w The maxent weights to be used for instatiating the new Maxent model
	 */
	public function newModel($w=array()) {
		$m = new Maxent($w);
		$this->buildClassifier($m);
	}

	/**
	 * Tag a sequence of tokens.
	 *
	 * @param array $tokens The sequence of tokens to be tagged
	 * @return array The sequence of tags that correspond to this seequence of tokens
	 */
	public function tag(array $tokens) {
		$tags = array();
		$c = count($tokens);
		for ($i=0;$i<$c;$i++) {
			$d = new WordDocument($tokens, $i, $this->context_size);
			$tags[] = $this->cls->classify($this->classes, $d);
		}
		return $tags;
	}

	/**
	 * @return array The classes that this tagger recognizes
	 */
	public function getClasses() {
		return $this->classes;
	}

	/**
	 * @return Classifier The classifier used for tagging
	 */
	public function getClassifier() {
		return $this->cls;
	}

	/**
	 * @return FeatureFactory The feature factory used for tagging
	 */
	public function getFeatureFactory() {
		return $this->ff;
	}

	/**
	 * Train a maxent model and build a classifier. The TrainingSet should
	 * have WordDocuments because that is what the feature factory expects.
	 *
	 * @return Maxent The trained model
	 */
	public function train(TrainingSet $tset, MaxentOptimizer $op) {
		$this->classes = $tset->getClassSet();
		$m = new Maxent(array());
		$m->train(
			$this->ff,
			$tset,
			$op
		);
		$this->buildClassifier($m);
		return $m;
	}

	/**
	 * Show which features fire and their value for a given word
	 *
	 * @param string $modelfile The file containing the serialized model
	 * @param array $tokens The tokens to create the TrainingSet from
	 * @param integer $idx The index of the token list for which we will show the features
	 */
	public function getFeaturesFired($modelfile, array $tokens, $idx=0) {
		$d = new WordDocument($tokens, $idx, $this->context_size);
		$w = $this->unserializeModel($modelfile)->getWeights();
		$feats = array();
		foreach ($this->classes as $c) {
			foreach ($this->ff->getFeatureArray($c, $d) as $f) {
				list($_, $feat) = explode(' ^ ',$f);
				if (!isset($feats[$feat]))
					$feats[$feat] = array();
				if (isset($w[$f]))
					$feats[$feat][$c] = $w[$f];
			}
		}
		return $feats;
	}

	/**
	 * Compute the prediction accuracy of the model for the given test set.
	 *
	 * @param $tset TrainingSet The document set to classify and evaluate the accuracy on
	 * @return float Accuracy
	 */
	 public function evaluate(TrainingSet $tset) {
		$c = 0;
		foreach ($tset as $d) {
			$p = $this->cls->classify($this->classes, $d);
			$c += (int)($p==$d->getClass());
		}
		return  $c/count($tset);
	 }
}
