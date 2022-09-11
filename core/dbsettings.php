<?php

require_once  __DIR__ ."/../classlib/DBSettings.class.php";
require_once  __DIR__ ."/../classlib/NestedPdo.class.php";
require_once  __DIR__ ."/../classlib/NestedPdoStatement.class.php";

$dbsettings = new DBSettings;
$db = new NestedPDO(
    $dbsettings->getConnectionString(),
    $dbsettings->getUser(),
    $dbsettings->getPass(),
    [
        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_SILENT,
        \PDO::ATTR_PERSISTENT         => false,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ]
);

?>