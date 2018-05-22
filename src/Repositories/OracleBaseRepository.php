<?php
/**
 * Created by darlane https://github.com/darlane
 */
namespace OracleLib\Repositories;

/**
 * Class OracleBaseRepository
 * @package OracleLib\Repositories
 */
trait OracleBaseRepository
{
    /**
     * @return ExampleRepository
     */
    public function exampleRepository()
    {
        return app(ExampleRepository::class);
    }
}