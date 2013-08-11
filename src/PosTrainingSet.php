<?php

use NlpTools\Documents\TrainingSet;
use NlpTools\Documents\WordDocument;

class PosTrainingSet extends TrainingSet
{
	/**
	 * Read word-tag pairs from a file and create a TrainingSet.
	 * The file should have the following format
	 * <word> <space> <tag> <new line>
	 *
	 * @param string|Iterator $file The filename that contains the tagged words or an iterator returninthe above formatted lines
	 * @return PosTrainingSet The training set from the file
	 */
	public static function fromFile($file, $context=1) {
		if (!($file instanceof Iterator)) {
			$file = new SplFileObject($file);
		}
		$lines = array_filter(
			array_map(
				function ($line) {
					return array_map("trim",explode(" ", $line));
				},
				iterator_to_array($file, false)
			)
		);
		$words = array_map(
			function ($l) {
				return $l[0];
			},
			$lines
		);

		$tset = new PosTrainingSet();
		foreach ($lines as $idx=>$l) {
			if (count($l)<2)
				continue;
			$tset->addDocument(
				$l[1], // the tag
				new WordDocument(
					$words,
					$idx,
					$context
				)
			);
		}

		return $tset;
	}
}
