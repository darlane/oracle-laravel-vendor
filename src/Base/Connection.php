<?php
/**
 * Created by PhpStorm.
 * User: darlane
 * Date: 22.05.18
 * https://github.com/darlane
 */
namespace OracleLib\Base;

use OracleLib\Exceptions\OracleBaseException;

/**
 * Created by PhpStorm.
 * User: cookie
 * Date: 28.11.16
 * Time: 11:08
 */
class Connection
{
    protected $connections = [];
    protected $config;

    /**
     * OracleConnection constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get connection.
     * @param $type
     * @param bool $force
     * @return mixed
     * @throws OracleBaseException
     */
    public function getConnection($type, $force = false)
    {
        if ($force || \count($this->connections) === 0) {
            $this->createConnections('read');
            $this->createConnections('write');
        }
        return $this->connections[$type];
    }

    /**
     * Get read or write connection to oracle.
     * @param string $type
     * @throws OracleBaseException
     */
    private function createConnections($type)
    {
        $host               = $this->config[$type]['host'];
        $connectionFunction = 'oci_connect';
        if (env('DB_EXTERNAL_BILLING_HOST_CONNECTION_TYPE_PERSISTENT')) {
            $connectionFunction = 'oci_pconnect';
        }
        $countError = 0;
        $maxTryReconnect = env('BILLING_RECONNECT_COUNT', 1);
        while ($countError < $maxTryReconnect) {
            try {
                $connection = $connectionFunction(
                    $this->config['username'],
                    $this->config['password'],
                    "(DESCRIPTION=(CONNECT_TIMEOUT={$this->config['timeout']})
                    (ADDRESS=(PROTOCOL=TCP)
                        (HOST={$host})
                            (PORT={$this->config['port']}))
                    (CONNECT_DATA=(SERVER={$this->config['server']})
                        (SERVICE_NAME={$this->config['database']})))",
                    $this->config['charset']
                );
                $countError = $maxTryReconnect;
            } catch (\Throwable $e) {
                $countError++;
            }
        }
        if (isset($connection) && $connection) {
            $this->connections[$type] = $connection;
        } else {
            $error = oci_error();
            throw new OracleBaseException($error['message'], $error['code']);
        }
    }
}