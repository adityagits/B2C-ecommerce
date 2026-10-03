<?php
/** @var array|null $shipment */
$steps = ['processing' => 'Processing', 'shipped' => 'Shipped', 'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered'];
$rank = ['processing' => 0, 'shipped' => 1, 'in_transit' => 1, 'out_for_delivery' => 2, 'delivered' => 3];
$cur = $rank[$shipment['status']] ?? null;
?>
<h3>Delivery</h3>
<?php if ($shipment['status'] === 'cancelled' || $shipment['status'] === 'returned' || $cur === null): ?>
    <p><?= status_tag($shipment['status']) ?></p>
<?php else: ?>
    <ol class="steps">
        <?php $n = 0; foreach ($steps as $key => $label): ?>
            <li class="<?= $n <= $cur ? 'done' : '' ?>"><?= e($label) ?></li>
        <?php $n++; endforeach; ?>
    </ol>
<?php endif; ?>
<?php if ($shipment['carrier']): ?><p class="small">Carrier: <strong><?= e($shipment['carrier']) ?></strong><?php if ($shipment['tracking_number']): ?> · Tracking: <strong><?= e($shipment['tracking_number']) ?></strong><?php endif; ?></p><?php endif; ?>
<?php if ($shipment['estimated_delivery'] && $shipment['status'] !== 'delivered'): ?><p class="small">Estimated delivery: <?= e(date('M j, Y', strtotime($shipment['estimated_delivery']))) ?></p><?php endif; ?>
<?php if ($shipment['delivered_at']): ?><p class="small">Delivered <?= e(date('M j, Y g:i A', strtotime($shipment['delivered_at']))) ?></p><?php endif; ?>
