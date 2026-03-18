<?php

/**
 * Supported Database Drivers
 * @author coder
 *
 * Created on: May 3, 2025 at 9:28:25 AM
 */

namespace shani\persistence {

    enum DatabaseDriver: string
    {

        /**
         * MySQL drivers
         */
        case MYSQL = 'mysql';

        /**
         * Postgres SQL drivers
         */
        case POSTGRES = 'pgsql';

        /**
         * Sybase drivers
         */
        case SYBASE = 'sybase';

        /**
         * Microsoft Access drivers
         */
        case MSSQL = 'mssql';

        /**
         * DBLIB drivers for SQL Serve & Sybase
         */
        case DBLIB = 'dblib';

        /**
         * OCI drivers for oracle
         */
        case ORACLE = 'oci';

        /**
         * SQLite drivers
         */
        case SQLITE = 'sqlite';

        /**
         * SQL Server drivers
         */
        case SQL_SERVER = 'sqlsrv';

        /**
         * ODBC drivers for SQL Server
         */
        case ODBC = 'odbc';
    }

}
