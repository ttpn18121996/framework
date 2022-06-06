<?php if ($paginator->hasMorePages() || $paginator->prevPageUrl()): ?>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-end mb-0">
            <?php if ($paginator->currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="<?=$paginator->prevPageUrl()?>" aria-label="Previous">
                    <span aria-hidden="true">Previous</span>
                </a>
            </li>
            <?php endif; ?>
            <?php if ($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link" href="<?=$paginator->nextPageUrl()?>" aria-label="Next">
                        <span aria-hidden="true">Next</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>