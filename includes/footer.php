<?php
declare(strict_types=1);

/** @var array $site */
?>
    <footer class="site-footer">
        <div class="container site-footer__inner">
            <div class="site-footer__brand">
                <p class="site-footer__name"><?= e($site['name']) ?></p>
                <p class="site-footer__tag"><?= e($site['tagline']) ?></p>
            </div>
            <div class="site-footer__links">
                <a href="<?= e($site['linkedin']) ?>" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                <a href="<?= e($site['github']) ?>" target="_blank" rel="noopener noreferrer">GitHub</a>
                <?php if (!empty($site['deviantart'])): ?>
                <a href="<?= e($site['deviantart']) ?>" target="_blank" rel="noopener noreferrer">DeviantArt</a>
                <?php endif; ?>
                <a href="<?= e($site['blog']) ?>" target="_blank" rel="noopener noreferrer">Medium</a>
                <a href="mailto:<?= e($site['email']) ?>">Email</a>
            </div>
            <p class="site-footer__copy">&copy; <span id="year"></span> <?= e($site['name']) ?></p>
        </div>
    </footer>

    <button type="button" class="back-to-top" id="back-to-top" aria-label="Back to top" hidden>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 19V5M5 12l7-7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>

    <dialog class="project-lightbox" id="project-lightbox" aria-label="Image preview">
        <div class="project-lightbox__panel">
            <button type="button" class="project-lightbox__close" data-lightbox-close aria-label="Close">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
            <div class="project-lightbox__media">
                <img src="" alt="" data-lightbox-image />
            </div>
            <div class="project-lightbox__bar">
                <p class="project-lightbox__title" data-lightbox-heading></p>
                <div class="project-lightbox__actions" data-lightbox-actions>
                    <a class="btn btn--soft" href="#" target="_blank" rel="noopener noreferrer" data-lightbox-live hidden>Open link</a>
                    <a class="btn btn--ghost" href="#" target="_blank" rel="noopener noreferrer" data-lightbox-code hidden>Code</a>
                </div>
            </div>
        </div>
    </dialog>

    <script src="<?= e(asset('js/site.js')) ?>"></script>
</body>
</html>
