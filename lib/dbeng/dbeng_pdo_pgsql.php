<?php
class dbeng_pdo_pgsql extends dbeng_pdo {
	protected $_conn;

	function __construct() {
		/* 
		 * arbitrarily chosen because some insert statements might
		 * be very large.
		 */
		$this->_batchInsertChunks = 250;
	}

	function connect($host, $usr, $pass, $db, $port) {
		if (!$this->_conn instanceof PDO) {
			if ($port == '' || !isset($port)) {$port = '5432';}
			$db_conn = "host=" . $host.";port=".$port;
			try {
				$this->_conn = new PDO('pgsql:' . $db_conn . ';dbname=' . $db, $usr, $pass);
			} catch (PDOException $e) {
				throw new DatabaseConnectionException($e->getMessage(), -1);
			} # catch

			$this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} # if
	} # connect()

    function connectRoot($host, $pass, $port) {
		if (!$this->_conn instanceof PDO) {
            $db_conn = "host=" . $host.";port=".$port;
			
			try {
				$this->_conn = new PDO('pgsql:' . $db_conn , 'postgres', $pass);
			} catch (PDOException $e) {
				throw new DatabaseConnectionException($e->getMessage(), -1);
			} # catch

			$this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } # if
    }

    function createDb($db, $usr, $pass) {
        $rowsFound = $this->exec("select * from pg_database where datname = :dbid",
                                    array(':dbid' => array($db, PDO::PARAM_STR)))->rowCount();  
        if ($rowsFound == 0) {
            $this->exec("CREATE DATABASE ".$db);
        } //$rowsFound == 0
        try {
                $usrfound = $this->exec('SELECT 1 FROM pg_roles WHERE rolname = :usr',array(':usr' => array($usr, PDO::PARAM_STR)))->rowCount();
                if ($usrfound == 0) {
                    $this->exec("CREATE USER ".$usr." WITH PASSWORD '".$pass."'");
                }
                $this->exec("GRANT ALL privileges ON DATABASE ".$db." TO ".$usr."");
            }
            catch (Exception $e) {
                $this->exec("DROP DATABASE ".$db);                           
                throw $e;
            }

    }

} # class
