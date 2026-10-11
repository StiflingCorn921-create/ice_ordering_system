<?php
/** Public layout footer. Optional: $pageJs (filename in js/) */
?>
<footer class="text-center text-muted small py-4 mt-5">
    &copy; <?= date('Y') ?> <?= e(config('name')) ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('js/app.js') ?>"></script>
<?php if (!empty($pageJs)): ?>
    <script src="<?= url('js/' . $pageJs) ?>"></script>
<?php endif; ?>
</body>
</html>

