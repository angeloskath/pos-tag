<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInteface;
use Symfony\Component\Console\Output\OutputInteface;
use NlpTools\Optimizers\ExternalMaxentOptimizer;

/**
 * This command trains a model and produces a file suitable
 * for use with the tag command
 */
class Train extends Command
{
	protected function configure() {
		$this
			->setName("train")
			->setDescription("Train a tagger model")
			->addOption(
				"optimizer",
				null,
				InputOption::VALUE_REQUIRED,
				"The optimizer executable (default gradient-descent)"
			)->addOption(
				"model",
				"m",
				InputOption::VALUE_REQUIRED,
				"The file to output the model to (default model.bin)"
			)->addOption(
				"corpus",
				"c",
				InputOption::VALUE_REQUIRED,
				"The annotated corpus to train on (default stdin)"
			)->setHelp("Train on a corpus and produce a model suitable for use with the tag command.

The corpus should have the following format. One token-tag pair per line separated
by a single space.

This command depends on an external optimizer to run. You can find and download
binaries in this blog post
<http://php-nlp-tools.local/posts/sentiment-detection-maxent.html>.
");
	}

	protected function execute($in, $out) {
		// new tagger instance
		$tagger = new \PosTagger();

		// which model
		$model = $in->getOption("model");
		if (!$model)
			$model = "model.bin";

		// determine the source of the corpus
		$corpus = $in->getOption("corpus");
		if (!$corpus)
			$corpus = new \NoRewindIterator(new \SplFileObject("php://stdin"));

		// create a training set from a file
		// see help text for the file's necessary format
		$tset = \PosTrainingSet::fromFile($corpus);

		// create the optimizer instant
		// see help text for external dependency
		$optimizer_exe = $in->getOption("optimizer");
		if (!$optimizer_exe)
			$optimizer_exe = "gradient-descent";
		$optimizer = new ExternalMaxentOptimizer($optimizer_exe);

		// train
		$m = $tagger->train($tset, $optimizer);

		// save
		file_put_contents($model, base64_encode(serialize($m)));
	}
}
