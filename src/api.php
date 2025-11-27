<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = 'db';
$db   = 'iv_db';
$user = 'user';
$pass = 'password';
$port = '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $pdo->exec("SET time_zone = '-03:00'");

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Erro de Conexão: " . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'medicos') {
    $sql = "
        SELECT DISTINCT psv_a.PSV_COD, psv_a.psv_apel
        FROM agm
        JOIN psv psv_a ON agm.agm_med = psv_a.psv_cod
        WHERE DATE(agm.agm_hini) = CURDATE()
          AND agm.AGM_CTF = '3203'
          AND agm.AGM_STAT != 'C'
    ";
    
    try {
        $stmt = $pdo->query($sql);
        $medicos = $stmt->fetchAll();
        echo json_encode($medicos);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erro na query medicos: " . $e->getMessage()]);
    }
    exit;
}

if ($action === 'dados') {
    $doctorCode = $_GET['cod'] ?? 'TODOS';
    
    $sql = "
        SELECT 
            DATE_FORMAT(A.DATA_AGENDAMENTO, '%d/%m/%Y %H:%i') AS DATA,
            A.REGISTRO_PACIENTE AS REG,
            A.NOME_PACIENTE AS PACIENTE,
            A.CIRURGIA AS CIRURGIA,
            A.OLHO AS OLHO,
            A.CONVENIO AS CONVENIO,
            COALESCE(A.LENTE, '-') AS LENTE,
            CASE 
                WHEN I.REGISTRO_PACIENTE IS NOT NULL THEN 'ADMITIDO'
                ELSE 'AGENDADO'
            END AS STATUS
        FROM Agendamentos A
        LEFT JOIN Admissoes I ON A.REGISTRO_PACIENTE = I.REGISTRO_PACIENTE
        WHERE DATE(A.DATA_AGENDAMENTO) = CURDATE()
    ";

    $params = [];
    if ($doctorCode !== 'TODOS') {
        $sql .= " AND A.COD_MEDICO = ? ";
        $params[] = $doctorCode;
    }

    $sql .= " ORDER BY A.DATA_AGENDAMENTO DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll());
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Erro na query dados: " . $e->getMessage()]);
    }
    exit;
}
?>