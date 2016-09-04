<?php

namespace smn\pheeca;

use \smn\pheeca\Database\Query;
use \smn\pheeca\Database\Rowset;
use \smn\pheeca\Database\Transaction;

class Database {

    /**
     * Contiene tutte le resource di connessione ai database
     * @var Database\Adapter\AbstractAdapter[]
     */
    protected static $_connections = [];

    /**
     * Constante che indica il database di default sul quale eseguire le query
     */
    const DEFAULT_RESOURCE = 'default';

    /**
     * Namespace di base delle clausole
     */
    const DEFAULT_CLAUSE_NS = '\smn\pheeca\Database\Clause\\';

    /**
     * Namespace dell'adapter di base
     */
    const DEFAULT_ADAPTER_CLASS = '\smn\pheeca\Database\Adapter\BaseAdapter';

    /**
     * Nome dell'adapter di base
     */
    const BASIC_ADAPTER = 'basic';

    /**
     * Contiene tutte le informazioni sui driver
     * @var Array 
     */
    protected static $_drivers = [
        self::BASIC_ADAPTER => [
            'adapters' => self::DEFAULT_ADAPTER_CLASS,
            'clauseNS' => self::DEFAULT_CLAUSE_NS
        ]
    ];

    /**
     * 
     * @param Array $database_connections
     */
    public static function initialize($database_info)
    {
        // vedo prima se vanno aggiunti nuovi drivers
        if (array_key_exists('adapters', $database_info))
        {
            $adapters_options = $database_info['adapters'];
            foreach ($adapters_options as $name => $adapter)
            {
                if (!array_key_exists('adapter', $adapter))
                {
                    throw new \Exception('Non ètato indicato l\'adapter');
                }
                $clauseNS = (array_key_exists('clauseNS', $adapter)) ? $adapter['clauseNS'] : self::DEFAULT_CLAUSE_NS;
                self::addDriver($name, $adapter['adapter'], $clauseNS);
            }
        }
        if (array_key_exists('connections', $database_info))
        {
            $connections = $database_info['connections'];
            foreach ($connections as $connection_name => $connection_info)
            {
                $dsn = $connection_info['dsn'];
                $user = $connection_info['user'];
                $pass = $connection_info['pass'];
                $options = $connection_info['options'];
                $driver = $connection_info['driver'];

                $instance = self::createAdapterInstanceByDriverName($driver, $dsn, $user, $pass, $options);
                self::$_connections[$connection_name] = $instance;
            }
        }
    }

    /**
     * Prepara uno statement che si adatta al driver associato all'identificativo di connessione
     * @param Array $clause_list
     * @param type $connection_name
     * @return Database\StatementInterface
     */
    public static function getStatement(Array $clause_list, $connection_name = self::DEFAULT_RESOURCE)
    {
        return new Database\Statement($clause_list, $connection_name);
    }

    /**
     * Restituisce una istanza adapter che effettua la connessione al database
     * @param type $driver
     * @param type $dsn
     * @param type $user
     * @param type $pass
     * @param type $options
     * @return \smn\pheeca\class
     */
    public static function createAdapterInstanceByDriverName($driver, $dsn, $user, $pass, $options)
    {
        $class = self::getAdapterNSByDriverName($driver);
        return new $class($dsn, $user, $pass, $options);
    }

    /**
     * Restituisce l'adapter instanziato per l'identificativo di connessione indicato
     * @param String $connection_name
     * @return \PDO|Database\Adapter\AbstractAdapter
     */
    public static function getAdapterInstanceByConnectionName($connection_name = self::DEFAULT_RESOURCE)
    {
        if (array_key_exists($connection_name, self::$_connections))
        {
            return self::$_connections[$connection_name];
        }
        return false;
    }

    /**
     * Restituisce il NS dell'adapter utilizzato per il driver $driver_name
     * @param String $driver_name
     */
    public static function getAdapterNSByDriverName($driver_name)
    {
        $index = (array_key_exists($driver_name, self::$_drivers)) ? $driver_name : self::BASIC_ADAPTER;
        return self::$_drivers[$index]['adapter'];
    }
    
    
    /**
     * Restituisce il namespace disponibile per una clausola in base al driver
     * @param type $driver_name
     * @return String
     */
    public static function getClauseNSByDriverName($driver_name = self::BASIC_ADAPTER) {
        $index = (array_key_exists($driver_name, self::$_drivers)) ? $driver_name : self::BASIC_ADAPTER;
        return self::$_drivers[$index]['clauseNS'];
    }
    
    
    /**
     * 
     * @param type $name
     * @param type $args
     * @param type $connection_name
     * @return Database\Clause
     * @throws \Exception
     */
    public static function getClauseInstanceByConnectionName($name, $args = [], $connection_name = self::DEFAULT_RESOURCE) {
         $ns = Database::getClauseNSByDriverName(Database::getAdapterInstanceByConnectionName($connection_name)->getAdapterName());
         $instance_name = sprintf('%s%s',$ns, $name);
         $instance_name_default = sprintf('%s%s', self::DEFAULT_CLAUSE_NS, $name);
         if (!class_exists($instance_name) && !class_exists($instance_name_default)) {
             throw new \Exception('Clause inesistente');
         }
         else if (!class_exists($instance_name) && class_exists($instance_name_default)) {
             $instance = new \ReflectionClass($instance_name_default);
         }
         else if (class_exists($instance_name)) {
             $instance = new \ReflectionClass($instance_name);
         }
         return $instance->newInstanceArgs($args);
    }
    
    

    public static function addDriver($name, $adapter, $clauseNS = self::DEFAULT_CLAUSE_NS)
    {
        $info = [
            'adapter' => $adapter,
            'clauseNS' => $clauseNS
        ];
        self::$_drivers[$name] = $info;
    }
    
    
    
    public static function execute($statement, $params = array(), $connection_name = self::DEFAULT_RESOURCE) {
        if (($adapter = self::getAdapterInstanceByConnectionName($connection_name)) === false) {
            throw new \Exception('Non esiste l\'adapter');
        }
        return $adapter->execute($statement, $params);
        
    }

}

