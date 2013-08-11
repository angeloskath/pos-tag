<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInteface;
use Symfony\Component\Console\Output\OutputInteface;

/**
 * This command does the part of speech tagging.
 */
class Tag extends Command
{
	/**
	 * Configure the command (set name, help, ...)
	 */
	protected function configure()
	{
		$this
			->setName("tag")
			->setDescription("Tag a sequence of tokens")
			->addArgument(
				"input",
				InputArgument::OPTIONAL,
				"The sentence to tag (read from stdin if missing)"
			)->addOption(
				"model",
				"m",
				InputOption::VALUE_REQUIRED,
				"The model to use (default is model.bin)"
			)->addOption(
				"output-format",
				"o",
				InputOption::VALUE_REQUIRED,
				"The format with which to echo the output (<w>=word, <t>=tag, <n>=new line)"
			)->setHelp("Tag a sequence of tokens. Each token should be separated by whitespace.

This command takes two paramaters, the model and the output format.
The model is a base64 encoded serialized NlpTools\Models\Maxent model.
The output format is a string containing anything and will be written
to stdout once for each token with the strings '<w>', '<t>', '<n>'
replaced with the token, tag, new line respectively.

Examples:

./post tag -m greek.bin 'Η καλή μας αγελάδα βόσκει κάτω στην λιακάδα'
Η/article καλή/adjective μας/pronoun αγελάδα/noun βόσκει/verb κάτω/adverb στην/article λιακάδα/noun 

./post tag -o '<t> ' 'Η καλή μας αγελάδα βόσκει κάτω στην λιακάδα'
article adjective pronoun noun verb adverb article noun 
");
	}

	/**
	 * Execute the command
	 */
	protected function execute($in, $out)
	{
		// initialize a tagger
		$tagger = new \PosTagger();

		// determine which file is the serialized model
		$model = $in->getOption("model");
		if (!$model)
			$model = "model.bin";

		// oops! couldn't find it or read it
		if (!file_exists($model) || !is_readable($model))
			throw new \RuntimeException("Could not locate or read the model file");

		// this will throw an exception if the unserialized model is not
		// an instance of Maxent
		$tagger->loadModelFromFile($model);

		// determine the output format
		// see help text for explanation regarding what the format is
		$format = $in->getOption("output-format");
		if (!$format)
			$format = "<w>/<info><t></info> ";

		// determine the input source and/or input text
		$text = $in->getArgument("input");
		if (!$text)
			$text = new \NoRewindIterator(new \SplFileObject("php://stdin"));
		else
			$text = array($text);

		// create a whitespace tokenizer for splitting the tokens in each line
		$tok = new \NlpTools\Tokenizers\WhitespaceTokenizer();

		// foreach line
		foreach ($text as $line) {

			// split and tag the tokens
			$tokens = $tok->tokenize($line);
			$tags = $tagger->tag($tokens);

			// augment the output-format with the token, tag, new line strings
			// and combine one for each token
			$rs = implode(
				"",
				array_map(
					function ($word, $tag) use($format) {
						return str_replace(
							array("<w>","<t>","<n>"),
							array($word,$tag,"\n"),
							$format
						);
					},
					$tokens,
					$tags
				)
			);

			// finally echo the tagged pairs
			$out->writeln($rs);
		}
	}
}

