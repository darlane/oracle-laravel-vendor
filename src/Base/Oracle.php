<?php
/**
 * Created by PhpStorm.
 * User: darlane
 * Date: 22.05.18
 * https://github.com/darlane
 */


namespace OracleLib\Base;

use OracleLib\Exceptions\OracleBaseException;

class Oracle
{
    /**
     * @var resource
     */
    protected $connection;
    private $returnTypes;
    private $connectionType = 'read';
    private $returnType;
    private $clobs;

    public function __construct(Connection $connections)
    {
        $this->connection  = $connections;
        $this->clobs       = [];
        $this->returnTypes = [
            'cursor',
            'ret_val',
            'clob',
        ];
    }

    /**
     * @param $type
     * @param bool $force
     * @return mixed
     * @throws OracleBaseException
     */
    public function connection($type, $force = false)
    {
        return $this->connection->getConnection($type, $force);
    }

    /**
     * @param $type
     * @param $sql
     * @return array
     * @throws OracleBaseException
     */
    private function getConnectionAndStatement($type, $sql)
    {
        $connection = $this->connection($type);
        try {
            $statement = oci_parse($connection, $sql);
        } catch (\ErrorException $e) {
            if ($e->getMessage() !== 'oci_parse(): ORA-03114: not connected to ORACLE') {
                throw new OracleBaseException($e->getMessage());
            }
            $connection = $this->connection($type, true);
            $statement  = oci_parse($connection, $sql);
        }

        return [$connection, $statement];
    }


    /**
     * @param $select
     * @param array $args
     * @param string $type
     * @return mixed
     * @throws OracleBaseException
     */
    protected function select($select, array $args = [], $type = 'read')
    {
        $sql = $select;
        foreach ($args as $arg_name => $arg_val) {
            $sql = str_replace(':'.$arg_name, $arg_val, $sql);
        }

        [$connection, $statement] = $this->getConnectionAndStatement($type, $sql);

        oci_execute($statement, OCI_COMMIT_ON_SUCCESS);
        $result = [];
        while ($row = oci_fetch_array($statement, OCI_BOTH)) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * @param $connectionParams array
     * @param array $params
     * @return mixed
     * @throws OracleBaseException
     */
    public function execute(array $connectionParams, array $params = [])
    {
        $this->validateConnectionConfig([
            'function'    => 'required',
            'return_type' => 'required',
        ], $connectionParams);
        $this->setConnectionConfig($connectionParams);
        $sql = $this->prepareSql($connectionParams['return_type'], $connectionParams['function'], $params);

        [$connection, $statement] = $this->getConnectionAndStatement($this->connectionType, $sql);
        [$statement, $result, $customStorage] = $this->bindParams($connection, $statement, $params);

        return $this->getResult($statement, $result, $customStorage);
    }

    private function prepareSql(string $returnType, string $function, array $params = [])
    {
        $sql = "begin :$returnType := $function(";
        foreach ($params as $name => $value) {
            $key = ':'.$name.',';
            if (array_get($value, 'data_type') === 'date') {
                $key = 'to_date(:'.$key.", 'dd.mm.yyyy hh24:mi:ss'),";
            }
            $sql .= $key;
        }

        return rtrim($sql, ',').'); end;';
    }

    private function bindParams($connection, $statement, array $params)
    {
        $result        = null;
        $customStorage = null;
        if ($this->returnType === 'cursor') {
            $customStorage = oci_new_cursor($connection);
        }
        if ($this->returnType === 'clob') {
            $customStorage = oci_new_descriptor($connection, OCI_DTYPE_LOB);
        }
        foreach ($params as $name => $data) {
            if (array_get($data, 'param_type') === 'out') {
                oci_bind_by_name($statement, ':'.$name, $result[$name], 4100);
                continue;
            }
            if (array_get($data, 'data_type') === 'clob') {
                $clob = oci_new_descriptor($connection, OCI_DTYPE_LOB);
                $clob->write($result[$name]['value'], OCI_TEMP_BLOB);
                oci_bind_by_name($statement, ':'.$name, $clob, -1, OCI_B_CLOB);
                $this->clobs[] = $clob;
                continue;
            }

            $value = \is_array($data) ? $data['value'] : $data;
            oci_bind_by_name($statement, ':'.$name, $value, \strlen($value));
            unset($value);
        }

        if ($this->returnType === 'ret_val') {
            oci_bind_by_name($statement, ':ret_val', $result['ret_val'], 512);
        }

        if ($this->returnType === 'cursor') {
            oci_bind_by_name($statement, ':cursor', $customStorage, -1, OCI_B_CURSOR);
        }

        if ($this->returnType === 'clob') {
            oci_bind_by_name($statement, ':clob', $customStorage, -1, OCI_B_CLOB);
        }

        return [$statement, $result, $customStorage];
    }


    /**
     * @param $statement
     * @param $customStorage
     * @param $result
     * @return mixed
     * @throws OracleBaseException
     */
    private function getResult($statement, $result, $customStorage)
    {
        try {
            if (!oci_execute($statement, OCI_COMMIT_ON_SUCCESS)) {
                $err = oci_error($statement);
                throw new OracleBaseException($err['message'], $err['code']);
            }
            if ($this->returnType === 'cursor') {
                oci_fetch_all($customStorage, $result['cursor'], null, null, OCI_FETCHSTATEMENT_BY_ROW);
            }
            if ($this->returnType === 'clob') {
                $result['clob'] = $customStorage->load();
                $customStorage->free();
            }
            foreach ($this->clobs as $clob) {
                $clob->free();
            }
        } catch (\ErrorException $e) {
            throw new OracleBaseException($e->getMessage(), $e->getCode());
        }
        return $result;
    }


    /**
     * @param $rules array
     * @param $params array
     * @throws OracleBaseException
     */
    private function validateConnectionConfig(array $rules, array $params)
    {
        foreach ($rules as $key => $rule) {
            if ($rule === 'required' && !isset($params[$key])) {
                throw new OracleBaseException("Field $key is required.");
            }
            if ($key === 'return_type' && !\in_array($params[$key], $this->returnTypes, true)) {
                throw new OracleBaseException('Return argument type should belong to the list');
            }
        }
    }

    /**
     * @param $params
     */
    private function setConnectionConfig($params)
    {
        $this->connectionType = array_get($params, 'connection_type', 'read');
        $this->returnType     = array_get($params, 'return_type');
    }

}
