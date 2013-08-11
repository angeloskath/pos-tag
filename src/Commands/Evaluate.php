<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInteface;
use Symfony\Component\Console\Output\OutputInteface;

/**
 * This command evaluates the accuracy of the tagger on a
 * preannotated corpus
 */
class Evaluate extends Command
{
	protected function configure() {
		$this
			->setName("evaluate")
			->setDescription("Evaluate the tagger given a preannotated corpus")
			->addOption(
				"model",
				"m",
				InputOption::VALUE_REQUIRED,
				"The model to use (default is model.bin)"
			)->addOption(
				"corpus",
				"c",
				InputOption::VALUE_REQUIRED,
				"The file to read the word tag pairs from (one word-tag pair per line separated by space)"
			)->setHelp("This is a convenience command for evaluating a model file on a preannotated corpus.

The corpus is assumed to be in the following format. One token-tag pair per line separated
by a single space. If the parameter --corpus is not used then the corpus is read from the
standard input.
");
	}

	protected function execute($in, $out) {
		// new tagger instance
		$tagger = new \PosTagger();

		// which model
		$model = $in->getOption("model");
		if (!$model)
			$model = "model.bin";

		// model not found or unreadable
		if (!file_exists($model) || !is_readable($model))
			throw new \RuntimeException("Could not locate or read the model file");

		// determine the source of the corpus
		$corpus = $in->getOption("corpus");
		if (!$corpus)
			$corpus = new \NoRewindIterator(new \SplFileObject("php://stdin"));

		// create a training set from a file
		// see help text for the file's necessary format
		$tset = \PosTrainingSet::fromFile($corpus);

		// load the model
		$tagger->loadModelFromFile($model);

		// evaluate
		$acc = $tagger->evaluate($tset);
		$out->writeln("<info>Accuracy:</info> $acc");
	}
}
