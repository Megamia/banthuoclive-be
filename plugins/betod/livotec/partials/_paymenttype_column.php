<?php
$statusLabels = [
    1 => ['label' => 'Chuyển khoản'],
    2 => ['label' => 'Tiền mặt']
];

$status = $statusLabels[$value] ?? ['label' => 'Không xác định'];
?>
<span style="font-weight: 500;">
    <?= $status['label'] ?>
</span>