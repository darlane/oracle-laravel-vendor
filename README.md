# oracle-laravel-vendor
Vendor for oracle at laravel 


Add to app.php at 'providers' block:

    \OracleLib\Providers\OracleServiceProvider::class

    
Next:    
    
    php artisan vendor:publish --provider="\OracleLib\Providers\OracleServiceProvider" --tag="config"
    
Create trait, like at example:

    trait OracleRepositoryList
    {
        
    }


Next all new repository add at this trait and AppServiceProvider

    /**
     * @return FooRepository
     */
    public function fooRepository()
    {
        return app(FooRepository::class);
    }

At AppServiceProvider


    public function boot()
    {
        $this->app->singleton(FooRepository::class, function () {
            return new FooRepository(app('oracle'));
        }); 
    }
        
        
At class, where you wanna use this repo add trait and use it like this:

    class FooClass
    {
       use OracleRepositoryList;
             
       public function method()
       {
           $this->testRepository()->test()
       }
    }
    
    
    
Example call function with cursor:

    $result = $this->execute(
            [
                'function'    => 'devdb.get_stat',
                'return_type' => 'cursor',
            ],
            [
                'p_id_user' => 1,
            ]);
    
    $stats  = array_keys_rename($result['cursor'], [
        'EXPERIENCE'     => 'experience',
        'STATUS'         => 'status',
        'DT              => 'date',
    ]);
    $stats  = array_keys_set_type($stats, [
        'experience'                => 'integer',
        'status'                    => 'integer',
        'credit_date'               => 'date',
    ]);

