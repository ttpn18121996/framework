<?php if ($paginator->hasPages()): ?>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-end mb-0">
            <?php if (1 < $paginator->currentPage): ?>
            <li class="page-item">
                <a class="page-link" href="<?=$paginator->prevPageUrl()?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php endif; ?>
            <?php foreach ($paginator->elements() as $key => $value): ?>
                <?php if (is_string($value)): ?>
                    <li class="page-item" aria-current="page"><span class="page-link">...</span></li>
                <?php else: ?>
                    <?php foreach ($value as $page): ?>
                        <?php if ($page == $paginator->currentPage): ?>
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">
                                    <?=$page?>
                                </span>
                            </li>
                        <?php else: ?>
                            <li class="page-item">
                                <a class="page-link" href="<?=$paginator->url($page)?>"><?=$page?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if ($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link" href="<?=$paginator->nextPageUrl()?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>
