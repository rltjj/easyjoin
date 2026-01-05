<?php

function getContractStats(PDO $pdo, int $siteId): array
{
    $stmt = $pdo->prepare("
        SELECT 
            t.title,
            t.category,
            SUM(c.status = 'IN_PROGRESS') AS in_progress,
            SUM(c.status = 'COMPLETED') AS completed
        FROM templates t
        LEFT JOIN contracts c ON c.template_id = t.id
        WHERE t.site_id = ? AND t.is_deleted = 0
        GROUP BY t.id
    ");
    $stmt->execute([$siteId]);
    return $stmt->fetchAll();
}

function getInquiries(PDO $pdo, int $siteId, int $limit = 5): array
{
    $stmt = $pdo->prepare("
        SELECT i.id, i.content, i.status, i.created_at, u.name
        FROM inquiries i
        LEFT JOIN users u ON u.id = i.requester_id
        WHERE i.site_id = ?
        ORDER BY i.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $siteId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
