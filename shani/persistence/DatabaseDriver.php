<?php

/**
 * Description of DatabaseDriver
 * @author coder
 *
 * Created on: May 3, 2025 at 9:28:25 AM
 */

namespace shani\persistence {

    enum DatabaseDriver: string
    {

        case MYSQL = 'mysql';
        case POSTGRES = 'pgsql';
        case SYBASE = 'sybase';
        case MSSQL = 'mssql';
        case DBLIB = 'dblib'; //for sqlserver & sybase
        case ORACLE = 'oci'; //for oracle
        case SQLITE = 'sqlite';
        case SQL_SERVER = 'sqlsrv'; //for sqlserver
        case ODBC = 'odbc'; //for sqlserver
    }

}
