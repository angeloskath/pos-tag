Pos-tag
=======

Pos-tag aims at providing the world with a cli application for part of speech tagging
using Maxent models from [NlpTools](https://github.com/angeloskath/php-nlp-tools).

It is the product of [these series of posts](http://php-nlp-tools.com/blog/category/greek-pos-tagger/)
and the original aim was only for the Greek language.

Usage
-----

    bin/pos-tag tag "Η καλή μας αγελάδα βόσκει κάτω στην λιακάδα"
	Η/article καλή/adjective μας/pronoun αγελάδα/noun βόσκει/verb κάτω/adverb στην/article λιακάδα/noun

Installation
------------

Simply download the zip and install with composer. Alternatively a phar archive will be
provided at the final blog post of the aforementioned series.

Prebuilt Models
---------------

In order to do what is shown above in the usage example a pre build model for the greek
language is required. Prebuilt models will be provided at the final blog post of the
aforementioned series.

