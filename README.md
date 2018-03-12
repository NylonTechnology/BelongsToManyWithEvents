# BelongsToManyWithEvents
Override of Laravel Eloquent BelongsToMany relationship to fire events on attach/detach operations

## Install

    $ composer require nylontechnology/belongstomanywithevents
    
## Usage

1. Import into your model
```php
use NylonTechnology\BelongsToManyWithEvents as BelongsToMany;

class User extends Model {
....
```

2. In your model, define what relationship events you want to observe
```php
class User extends Model {

    protected $observables = [
        'attached.roles',
        'detached.roles'
    ];

	public function roles()
	{
		return $this->belongsToMany('App\Role');
	}

	...
```

3. Create an observer and handle events however you wish
```php
namespace App\Observers;

class ModelAuditObserver { 

    private function attached($observer_callback, $model, $ids) {}

		private function detached($observer_callback, $model, $ids) {}
		
		private function synced($observer_callback, $model, $ids) {}

		...
```

4. Register your observer in AppServiceProvider boot() method
```php
class AppServiceProvider extends ServiceProvider {

    public function boot() {
        App\User::observe(ModelAuditObserver::class);

    ...
```

## Notes

sync() is a wrapper around attach() and detach() so generally you should only observe sync events or attach/detach, but not both unless you don't mind redundant events.

Inspired by [@andyberry88's gist](https://gist.github.com/andyberry88/be3c45380568fc359cb61e00c4249704)
