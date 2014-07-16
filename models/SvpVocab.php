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
    
    public function updateNow() {
      
      $id = $this->id;
      $url = $this->url;
      if($url == "local")
	return;
      
      $newText = $this->_curlFetch($url);
      //die($newText);
      $newTerms = get_db()->getTable('SvpTerm')->updateFromText($id,$newText);
    }
    

    private function _curlFetch($url) {
      // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);  
	return($output);

    }
}

?>