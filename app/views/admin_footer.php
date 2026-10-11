        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('js/app.js') ?>"></script>
<script src="<?= url('js/admin.js') ?>"></script>
<?php if (!empty($pageJs)): ?>
    <script src="<?= url('js/' . $pageJs) ?>"></script>
<?php endif; ?>
</body>
</html>

