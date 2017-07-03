<?php

/*

		Derived from: https://gist.github.com/andyberry88/be3c45380568fc359cb61e00c4249704

*/

namespace NylonTechnology;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;

class BelongsToManyWithEvents extends EloquentBelongsToMany {


		/*
				attach() fires for every relation added
		*/
		public function attach($id, array $attributes = [], $touch = true) {
			$returnVal = parent::attach($id, $attributes, $touch);

			if ($id instanceof Model) {
				$id = $id->getKey();
			}
			if ($id instanceof Collection) {
				$id = $id->modelKeys();
			}

			$this->fireParentEvent("attached.{$this->relationName}", $id, false);

			return $returnVal;
		}

		/*
				detach() fires only once for all removed relations
		*/
		public function detach($ids = [], $touch = true) {
			$returnVal = parent::detach($ids, $touch);

			if ($ids instanceof Model) {
				$ids = $ids->getKey();
			}
			if ($ids instanceof Collection) {
				$ids = $ids->modelKeys();
			}

      if (!(count($ids) == 1 && empty($ids[0]))) {
				$this->fireParentEvent("detached.{$this->relationName}", $ids, false);
      }

			return $returnVal;
		}


		/*
				sync() is a wrapper around attach() and detach() so generally you should 
				only observe sync events or attach/detach, but not both unless you don't
				mind redundant events.
		*/
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
