<?php

/** Создавалось под использование с MariaDB */
class Database
{
    private mysqli $mysqli;

    private string $dbName;

    private array $neededTables = [];

    private array $exceptionTables = [];

    private array $usingColumns = [];

    private array $updateOrders = [];

    private array $orderBy = [];

    public function __construct(array $settings)
    {
        $this->mysqli = new mysqli($settings['HOST'], $settings['USER'], $settings['PASSWORD'], $settings['DATABASE']);

        $this->dbName = $settings['DATABASE'];
    }

    public function setNeededTables(string $tables) : void
    {
        $this->neededTables = $this->trimTablesString($tables);
    }

    /**
     * Если указаны Needed, Exception Tables работать не будет
     * @param $tables
     * @return void
     */
    public function setExceptionTables(string $tables) : void
    {
        if (empty($this->neededTables)) {
            $this->exceptionTables = $this->trimTablesString($tables);
        }
    }

    public function setUsingColumns(array $using) : void
    {
        $this->usingColumns = $using;
    }

    public function setUpdateOrder(array $orders) : void
    {
        foreach ($orders as $table => $columns) {
            $this->updateOrders += [$table => explode(",", $columns)];
        }
    }

    public function setOrderBy($orderBy) : void
    {
        $this->orderBy = $orderBy;
    }

    private function trimTablesString($tables) : array
    {
        $tables = trim($tables, "\n\r\t");
        $tables = str_replace(["\n", "\t", "\r", " "], '', $tables);

        return explode(",", $tables);
    }

    public function selectTablesName()
    {
        $mysqli = $this->mysqli;

        $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = '{$this->dbName}';";

        $request = $mysqli->query($query);

        if (mysqli_num_rows($request) > 0) {
            $tables = [];

            while (['table_name' => $table] = mysqli_fetch_array($request, MYSQLI_ASSOC)) {
                if (count($this->neededTables) > 0 and empty($this->exceptionTables)) {
                    if (in_array($table, $this->neededTables)) {
                        $tables[] = $table;
                    }

                } elseif (empty($this->neededTables) and count($this->exceptionTables) > 0) {
                    if (!in_array($table, $this->exceptionTables)) {
                        $tables[] = $table;
                    }
                }
            }

            return $tables;
        }

        return [];
    }

    public function selectTablePointer(string $table, string|bool $orderColumn = 'id', int $offset = 0) : ?mysqli_result
    {
        $mysqli = $this->mysqli;

        if (!empty($this->orderBy)) {
            if (isset($this->orderBy[$table])) {
                $orderColumn = $this->orderBy[$table];
            }
        } else {
            $orderQuery = '`id` ASC';
        }

        $limit = 250;
        $offset = $limit * $offset;

        $orderQuery = $this->setOrder($orderColumn);

        $query = "SELECT * FROM `$table` $orderQuery  LIMIT $limit OFFSET $offset ";

        $request = $mysqli->query($query);

        if (mysqli_num_rows($request) > 0) {
            return $request;
        }

        return null;
    }

    public function getTableObject(string $table, array $object) : array|false|null
    {
        $mysqli = $this->mysqli;

        $columns = [$table => ["id"]];

        if (!empty($this->updateOrders)) {
            $columns = $this->updateOrders;
        }

        $columnsQuery = implode('`, `', $columns[$table]);

        $whereColumns = [];

        foreach ($columns[$table] as $column) {
            $whereColumns[] = "`$column` = '{$object[$column]}'";
        }

        $whereColumns = implode(' AND ', $whereColumns);

        $query = "SELECT `$columnsQuery` FROM `$table` WHERE $whereColumns ";

        $request = $mysqli->query($query);

        if (mysqli_num_rows($request) > 0) {
            return mysqli_fetch_array($request, MYSQLI_ASSOC);
        }

        return null;
    }

    private function setOrder(string $column = 'id') : string
    {
        return " ORDER BY $column";
    }

    public function executeQuery(string $query) : void
    {
        $this->mysqli->query($query);

        echo "Запрос выполнен.\n";
    }

    public function updateObject(string $table, array $object) : void
    {
        if (count($this->neededTables) > 0 and empty($this->exceptionTables)) {
            if (in_array($table, $this->neededTables)) {
                $this->updateObjectExecute($table, $object);
            }

        } elseif (empty($this->neededTables) and count($this->exceptionTables) > 0) {
            if (!in_array($table, $this->exceptionTables)) {
                $this->updateObjectExecute($table, $object);
            }
        }
    }

    private function updateObjectExecute($table, $object) : void
    {
        $mysqli = $this->mysqli;

        $setColumns = [];

        foreach ($object as $column => $value) {
            $value = mysqli_escape_string($mysqli, $value);
            $setColumns[] = "`$column` = '$value'";
        }

        $whereQuery = "";
        $whereColumns = [];

        if (!empty($this->updateOrders)) {
            if (isset($this->updateOrders[$table])) {
                foreach ($this->updateOrders[$table] as $column) {
                    $object[$column] = mysqli_escape_string($mysqli, $object[$column]);

                    $whereColumns[] = "`$column` = '{$object[$column]}'";
                }
            }
        }

        $setQuery = implode(', ', $setColumns);

        if (!empty($whereColumns)) {
            $whereQuery = " WHERE ". implode(' AND ', $whereColumns);
        }

        $query = "UPDATE `$table` SET $setQuery $whereQuery";

        $this->executeQuery($query);
    }

    public function insertObject($table, $object) : void
    {
        $into = $values = array();

        foreach ($object as $column => $value) {
            $into[] = $column;
            $values[] = $value;
        }

        echo "Часть запроса составлена\n";

        $intoQuery = "(`". implode("`, `", $into) . "`)";

        echo "\tВставляемые поля: $intoQuery\n";

        $valuesQuery = "('". implode("', '", $values) . "')";

        echo "\tВставляемые значения: $valuesQuery\n";

        $query = "INSERT INTO `$table` $intoQuery VALUES $valuesQuery";

        $this->executeQuery($query);
    }
}
