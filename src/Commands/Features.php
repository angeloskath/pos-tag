<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInteface;
use Symfony\Component\Console\Output\OutputInteface;

/**
 * Show the features that fire for a specific token. Usefull for debugging
 * purposes to understand for example why a word has been assigned such an
 * inappropriate tag.
 */
class Features extends Command
{
	protected function configure() {
		$this
			->setName("features")
			->setDescription("Show the features that fire for a given doc and their value")
			->addArgument(
				"input",
				InputArgument::OPTIONAL,
				"The sentence to create the document set from (read from stdin if missing)"
			)->addOption(
				"model",
				"m",
				InputOption::VALUE_REQUIRED,
				"The model to use (default is model.bin)"
			)->addOption(
				"index",
				"i",
				InputOption::VALUE_REQUIRED,
				"The index of the word whose features to show"
			)->setHelp("Show the features that fire for a specific token.

Usefull for debugging and understanding why a token has been assigned a certain tag.
");
	}

	protected function execute($in, $out) {
		// new tagger
		$tagger = new \PosTagger();

		// which model
		$model = $in->getOption("model");
		if (!$model)
			$model = "model.bin";

		// could not read or find the model
		if (!file_exists($model) || !is_readable($model))
			throw new \RuntimeException("Could not locate or read the model file");

		// read the text
		// in contrast with the tag command in this command
		// the text is read *at once* until eof since we need to show the
		// features of a token a certain offset from the start
		$text = $in->getArgument("input");
		if (!$text)
			$text = file_get_contents("php://stdin");

		// tokenize
		$tok = new \NlpTools\Tokenizers\WhitespaceTokenizer();
		$tokens = $tok->tokenize($text);

		// offset from the start
		$idx = $in->getOption("index");
		if (!$idx)
			$idx = 0;

		// get the features and their weights
		$f = $tagger->getFeaturesFired($model, $tokens, $idx);

		$this->outputFeatures($f, $out);
	}

	/**
	 * Output the features that fired in a nice indented manner much like
	 * a yaml file.
	 */
	 protected function outputFeatures($f, $out) {
	 	if (count($f)==0)
		{
			$out->writeln("No features fired");
		}
	 	$classes = call_user_func_array(
			'array_merge',
			array_map(
				'array_keys',
				$f
			)
		);
		$len = function ($s) { return mb_strlen($s,"utf-8"); };
		$column_len = max(array_map($len,$classes));
		foreach ($f as $feature=>$classes) {
			$out->writeln("<info>$feature</info> :");
			foreach ($classes as $c=>$v) {
				$out->writeln("    ".$c.str_repeat(" ",$column_len-$len($c))." : ".$v);
			}
		}
	 }
}
