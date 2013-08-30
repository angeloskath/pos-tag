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

Simply download the zip and install with composer. Alternatively you can download a
[phar archive](http://php-nlp-tools.com/files/pos-tag/pos-tag.phar).

Prebuilt Models
---------------

In order to do what is shown above in the usage example a pre built model for the greek
language is required. You can use any of the following models and you can read more about their usage
[here](http://php-nlp-tools.com/posts/pos-tag-application.html).

* [model.bin (complete)](/files/pos-tag/models/model.bin)
* [model_thre_0.09.bin](/files/pos-tag/models/model_thre_0.09.bin)
* [model_thre_0.49.bin (recommended)](/files/pos-tag/models/model_thre_0.49.bin)
* [model_thre_0.99.bin](/files/pos-tag/models/model_thre_0.99.bin)

License
-------

The contents of this repository are all published under the license present in the LICENSE file.

