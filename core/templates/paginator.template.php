<ul class="paginator">
    <?php if ($firstPage !== null): ?>
    <li><a href="<?php echo $firstPage; ?>">&lt;&lt;</a></li>
    <?php endif; ?>
    <?php foreach ($lowerPages as $page => $url): ?>
    <li><a href="<?php echo $url; ?>"><?php echo $page; ?></a></li>
    <?php endforeach; ?>
    <li><?php echo $currentPage; ?></li>
    <?php foreach ($higherPages as $page => $url): ?>
    <li><a href="<?php echo $url; ?>"><?php echo $page; ?></a></li>
    <?php endforeach; ?>
    <?php if ($lastPage !== null): ?>
    <li><a href="<?php echo $lastPage; ?>">&gt;&gt;</a></li>
    <?php endif; ?>
</ul>

