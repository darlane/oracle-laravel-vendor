<?php
/**
 * Created by PhpStorm.
 * User: darlane
 * Date: 22.05.18
 * Time: 15:07
 */

namespace OracleLib\Repositories;


use OracleLib\Base\Oracle;
use OracleLib\Exceptions\OracleBaseException;

class ExampleRepository extends Oracle
{
    /**
     * Check connection to oracle DB
     * @return bool
     * @throws OracleBaseException
     */
    public function test()
    {
        return $this->select('select 1 from dual');
    }
}