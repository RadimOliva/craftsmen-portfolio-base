<?php namespace ProcessWire;
$trust=Craft::trust($s);
if($trust['enabled'] && $trust['items']): ?>
<div class="trust-strip"><div class="wrap trust-inner" style="--trust-count:<?= count($trust['items']) ?>">
<?php foreach($trust['items'] as $item): ?>
<div><span class="trust-icon"><?= Craft::trustIcon($item['icon']) ?></span><p><?= $e($item['title']) ?><?php if($item['subtitle']!==''):?><small><?= $e($item['subtitle']) ?></small><?php endif;?></p></div>
<?php endforeach; ?>
</div></div>
<?php endif; ?>
