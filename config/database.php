<?php
// =============================================
// Database Configuration
// =============================================

$db_host = 'localhost';
$db_name = 'devtrack';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Database helper functions
function query($sql, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function fetchAll($sql, $params = []) {
    return query($sql, $params)->fetchAll();
}

function fetchOne($sql, $params = []) {
    return query($sql, $params)->fetch();
}

function fetchColumn($sql, $params = []) {
    return query($sql, $params)->fetchColumn();
}

function insert($sql, $params = []) {
    query($sql, $params);
    global $pdo;
    return $pdo->lastInsertId();
}

function update($sql, $params = []) {
    return query($sql, $params)->rowCount();
}

function delete($sql, $params = []) {
    return query($sql, $params)->rowCount();
}

function beginTransaction() {
    global $pdo;
    return $pdo->beginTransaction();
}

function commitTransaction() {
    global $pdo;
    return $pdo->commit();
}

function rollbackTransaction() {
    global $pdo;
    return $pdo->rollBack();
}
?>