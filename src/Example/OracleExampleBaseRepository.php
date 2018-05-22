<?php
/**
 * Created by darlane https://github.com/darlane
 */
namespace OracleLib\Repositories;

/**
 * Class OracleBaseRepository
 * @package OracleLib\Repositories
 */
trait OracleExampleBaseRepository
{
    /**
     * @return ExampleRepository
     */
    public function exampleRepository()
    {
        return app(ExampleRepository::class);
    }
}