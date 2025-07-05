<?php
$statusLabels = [
    0 => ['label' => 'Chưa giao', 'color' => 'red'],
    1 => ['label' => 'Đang giao', 'color' => 'blue'],
    2 => ['label' => 'Đã giao', 'color' => 'green']
];

$status = $statusLabels[$value] ?? ['label' => 'Không xác định', 'color' => 'gray'];
?>
<span style="color: <?= $status['color'] ?>; font-weight: 500;">
    <?= $status['label'] ?>
</span>