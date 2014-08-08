<?php
class Table_SvpTerm extends Omeka_Db_Table
{
  public function addFromText($vocab_id,$text) {
    $terms = $this->_parseText($text);
    foreach($terms as $term) {
      $term = trim($term);
      $svpTerm = new SvpTerm();
      $svpTerm->vocab_id = $vocab_id;
      $svpTerm->term = $term;
      $svpTerm->save();
    }
    return true;
  }
  
  public function updateFromText($vocab_id,$text) {
    //set this to all rows
    $deleted = array();
    $added = array();
    $termObjs = $this->fetchObjects("Select `term` from omeka_svp_terms where vocab_id = ".$vocab_id);

    foreach($termObjs as $termObj) {
      $deleted[]=trim($termObj['term']);
    }
    
    $terms = $this->_parseText($text);

    foreach($terms as $term) {
      $i=array_search(trim($term),$deleted);
      if($i!==false) {
	unset($deleted[$i]);
      } else {
	$added[]=$term;
      }
    }

    foreach($deleted as $delete) {
      $sql = 'delete from omeka_svp_terms where vocab_id = "'.$vocab_id.'" and term = "'.trim($delete).'"';
      //die($sql);
      get_db()->query($sql);
    }

    foreach($added as $add) {
      $term = trim($term);
      $svpTerm = new SvpTerm();
      $svpTerm->vocab_id = $vocab_id;
      $svpTerm->term = $add;
      $svpTerm->save();
    }
   
    if ( !empty($added) && !empty($deleted) ) {
      return(array('add'=>array_values($added),'delete'=>array_values($deleted)));
    }
   
    return false;
  }
  
  private function _parseText($text) {
    $text = str_replace("\n\r","\n",$text);
    $text = str_replace("\r","",$text);
    $terms = explode("\n",$text);
    $terms = array_map('trim',$terms);
    return($terms);
  }

}

?>