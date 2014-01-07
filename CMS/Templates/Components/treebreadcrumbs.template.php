<div class="breadcrumbs">
    <?php foreach ($breadcrumbs as &$link): ?>
        <?=' &gt; ' . $link->parse(); ?>
    <?php endforeach; ?>
</div>
