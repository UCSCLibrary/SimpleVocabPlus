<?php
/**
 * Simple Vocab Plus
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @copyright Copyright 2021 Daniele Binaghi
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * A simple_vocab_plus element assignment row.
 *
 * @package SimpleVocabPlus
 */
class SvpAssign extends Omeka_Record_AbstractRecord
{
	public $id;
	public $element_id;
	public $type;
	public $enforced;
	public $vocab_id;
	public $sources_id;
}
