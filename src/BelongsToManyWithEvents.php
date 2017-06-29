<?php

/*

		Derived from: https://gist.github.com/andyberry88/be3c45380568fc359cb61e00c4249704

*/

namespace NylonTechnology;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;

class BelongsToManyWithEvents extends EloquentBelongsToMany {


		public function attach($ids, array $attributes = [], $touch = true) {
			$returnVal = parent::attach($ids, $attributes, $touch);

			if ($ids instanceof EloquentCollection) {
				$ids = $ids->modelKeys();
			} else {
				$ids = [$ids];
			}

			foreach($ids as $id) {
				$attached = $id instanceof Model ? $this->find($id = $id->getKey()) : $this->find($id);
				if ($attached) {
					$this->fireParentEvent("attached.{$this->relationName}", $attached, false);
				}
			}
			return $returnVal;
		}


		public function detach($ids = [], $touch = true) {
			$returnVal = parent::detach($ids, $touch);

			if ($ids instanceof EloquentCollection) {
				$ids = $ids->modelKeys();
			} else {
				$ids = [$ids];
			}

			foreach($ids as $id) {
				$id = $id instanceof Model ? $id->getKey() : $id;
				$this->fireParentEvent("detached.{$this->relationName}", $id, false);
			}

			return $returnVal;
		}



    public function sync($ids, $detaching = true) {
			$returnVal = parent::sync($ids, $detaching);

			$this->fireParentEvent("synced.{$this->relationName}", $returnVal, false);

			return $returnVal;
    }



		protected function fireParentEvent($event, $records, $halt = true) {
			$dispatcher = $this->getParent()->getEventDispatcher();

			if (! $dispatcher) {
				return true;  
			}

			$event = "eloquent.{$event}: ".get_class($this->getParent());

			$method = $halt ? 'until' : 'fire';

			return $dispatcher->$method($event, [$this->getParent(), $records]);
	}

}
