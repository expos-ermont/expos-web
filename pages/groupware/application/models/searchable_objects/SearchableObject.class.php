<?php

  /**
  * SearchableObject class
  * Generated on Tue, 13 Jun 2006 12:15:44 +0200 by DataObject generation tool
  *
  * @author Ilija Studen <ilija.studen@gmail.com>
  */
  class SearchableObject extends BaseSearchableObject {
  
  	
  	public function save()
  	{
  		if (!(defined('LUCENE_SEARCH') && LUCENE_SEARCH))
  			return parent::save();
  		else {
  			LuceneDB::AddToIndex($this);
  			parent::save();
  		}
  	}
  } // SearchableObject 

?>