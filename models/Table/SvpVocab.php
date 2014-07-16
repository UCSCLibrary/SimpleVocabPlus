<?php
class Table_SvpVocab extends Omeka_Db_Table {

  public function createVocab($name,$url) {
    $vocab = new SvpVocab();
    $vocab->name = $name;
    $vocab->url = $url;
    $save = $vocab->save();
    return($vocab);
  }


  public function updateAll() {
    foreach($this->findAll() as $vocab) {
      $vocab->update();
    }
  }

}

?>