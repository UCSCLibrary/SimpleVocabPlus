<?php
class Table_SvpVocab extends Omeka_Db_Table
{
	public function createVocab($name, $url) 
	{
		$vocab = new SvpVocab();
		$vocab->name = $name;
		$vocab->url = $url;
		$save = $vocab->save();
		return $vocab;
	}
	
	public function deleteVocab($vocab_id) 
	{
		$db = $this->_db;
		$sql1 = "DELETE FROM `{$db->SvpTerm}` WHERE vocab_id = $vocab_id";
		$db->query($sql1);
		$sql2 = "DELETE FROM `{$db->SvpVocab}` WHERE id = $vocab_id";
		$db->query($sql2);
	}
	
	public function updateAll()	
	{
		foreach($this->findAll() as $vocab) {
			$vocab->update();
		}
	}
}