<?php
$pageTitle = $title ?? 'Glossary';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/training') ?>">Training</a></li>
                <li class="breadcrumb-item active">Glossary</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-book me-2"></i>Glossary of Terms</h1>
        <p class="text-muted mb-0">Definitions of key terms used in the system</p>
    </div>
    <a href="<?= url('/training') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
</div>

<!-- Search -->
<div class="card mb-4">
    <div class="card-body">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" id="glossarySearch" class="form-control" placeholder="Search terms...">
        </div>
    </div>
</div>

<!-- Alphabet Navigation -->
<div class="card mb-4">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap justify-content-center gap-1">
            <?php
            $letters = array_unique(array_map(function($t) {
                return strtoupper(substr($t['term'], 0, 1));
            }, $terms));
            sort($letters);
            foreach (range('A', 'Z') as $letter):
            ?>
            <a href="#letter-<?= $letter ?>"
               class="btn btn-sm <?= in_array($letter, $letters) ? 'btn-outline-primary' : 'btn-light disabled' ?>">
                <?= $letter ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Terms List -->
<div class="card">
    <div class="card-body">
        <?php
        $currentLetter = '';
        foreach ($terms as $term):
            $firstLetter = strtoupper(substr($term['term'], 0, 1));
            if ($firstLetter !== $currentLetter):
                $currentLetter = $firstLetter;
        ?>
        <h4 class="text-primary mt-4 mb-3" id="letter-<?= $currentLetter ?>">
            <?= $currentLetter ?>
        </h4>
        <?php endif; ?>

        <div class="glossary-term mb-3 pb-3 border-bottom">
            <h5 class="mb-1">
                <i class="bi bi-bookmark text-primary me-2"></i>
                <?= e($term['term']) ?>
            </h5>
            <p class="text-muted mb-0 ms-4"><?= e($term['definition']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.getElementById('glossarySearch').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.glossary-term').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
