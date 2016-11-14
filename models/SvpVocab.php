<?php
/**
 * A simple_vocab_plus term entry row.
 *
 * @package SimpleVocabPlus
 */
class SvpVocab extends Omeka_Record_AbstractRecord
{
    public $id;
    public $name;
    public $url;

    public function updateNow()
    {
      $id = $this->id;
      $url = $this->url;
      if ($url == "local") {
          return;
      }
      //$newText = $this->_streamFetch($url);
      $newText = $this->_curlFetch($url);
      //die($newText);
      $newTerms = get_db()->getTable('SvpTerm')->updateFromText($id,$newText);
    }

    private function _streamFetch($Url)
    {
        ob_start();
        $context_options = array(
            'http' => array(
                'method '=> 'GET',
                'header' => 'Accept-language: en\r\n'
            )
        );
        $context = stream_context_create($context_options);
        $contents = file_get_contents($Url, NULL, $context);
        ob_end_clean();
        //$contents = file_get_contents($Url);
        return $contents;
    }

    private function _curlFetch($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return($output);
    }
}
