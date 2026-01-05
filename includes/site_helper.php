<?php
function getUserSites(PDO $pdo, int $userId, string $role): array
{
    if ($role === 'ADMIN') {
        return $pdo->query("SELECT * FROM sites")->fetchAll();
    }

    if ($role === 'OPERATOR') {
        $stmt = $pdo->prepare("SELECT * FROM sites WHERE operator_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // STAFF
    $stmt = $pdo->prepare("
        SELECT s.*
        FROM sites s
        JOIN site_staff ss ON ss.site_id = s.id
        WHERE ss.staff_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
