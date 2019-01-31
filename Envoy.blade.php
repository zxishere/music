@servers(['localhost' => '127.0.0.1'])

@task('task', ['on' => 'localhost'])
	git fetch --all && git reset --hard origin/master
	chmod -R 777 storage bootstrap/cache public
	php artisan horizon:purge
	php artisan horizon:terminate
	php artisan queue:flush
	php artisan queue:restart
@endtask

@task('cu', ['on' => 'localhost'])
	chmod -R 777 storage bootstrap/cache public
	composer update --no-dev
@endtask