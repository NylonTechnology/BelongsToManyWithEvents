<?php
namespace NylonTechnology;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany as EloquentBelongsToMany;
		
class BelongsToManyWithEvents extends EloquentBelongsToMany {

		public function attach($ids, array $attributes = [], $touch = true) {
			$returnVal = parent::attach($ids, $attributes, $touch);

			if ($ids instanceof Model) {
				$ids = $ids->getKey();
			}
			if ($ids instanceof Collection) {
				$ids = $ids->modelKeys();
			}

			$this->fireParentEvent("attached.{$this->getRelationName()}", $ids, false);

			return $returnVal;
		}

		public function detach($ids = [], $touch = true) {
			$returnVal = parent::detach($ids, $touch);

			if ($ids instanceof Model) {
				$ids = $ids->getKey();
			}
			if ($ids instanceof Collection) {
				$ids = $ids->modelKeys();
			}

			if (!is_array($ids)) $ids = (array)$ids;
			
      if (!(count($ids) == 1 && empty($ids[0]))) {
				$this->fireParentEvent("detached.{$this->getRelationName()}", $ids, false);
      }

			return $returnVal;
		}

    public function sync($ids, $detaching = true) {
			$returnVal = parent::sync($ids, $detaching);

			$this->fireParentEvent("synced.{$this->getRelationName()}", $returnVal, false);

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
