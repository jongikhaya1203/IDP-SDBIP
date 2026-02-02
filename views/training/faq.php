<?php
$pageTitle = $title ?? 'FAQ';
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= url('/training') ?>">Training</a></li>
                <li class="breadcrumb-item active">FAQ</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0"><i class="bi bi-question-circle me-2"></i>Frequently Asked Questions</h1>
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
            <input type="text" id="faqSearch" class="form-control" placeholder="Search FAQs...">
        </div>
    </div>
</div>

<!-- Category Filter -->
<div class="mb-4">
    <button class="btn btn-primary btn-sm me-1 category-btn active" data-category="all">All</button>
    <?php foreach ($categories as $cat): ?>
    <button class="btn btn-outline-primary btn-sm me-1 category-btn" data-category="<?= e($cat) ?>">
        <?= e($cat) ?>
    </button>
    <?php endforeach; ?>
</div>

<!-- FAQ Accordion -->
<div class="accordion" id="faqAccordion">
    <?php
    $currentCategory = '';
    foreach ($faqs as $i => $faq):
        if ($faq['category'] !== $currentCategory):
            $currentCategory = $faq['category'];
    ?>
    <h5 class="mt-4 mb-3 faq-category" data-category="<?= e($faq['category']) ?>">
        <i class="bi bi-folder me-2"></i><?= e($faq['category']) ?>
    </h5>
    <?php endif; ?>

    <div class="accordion-item faq-item" data-category="<?= e($faq['category']) ?>">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button"
                    data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                <?= e($faq['question']) ?>
            </button>
        </h2>
        <div id="faq<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
                <?= e($faq['answer']) ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Still Need Help -->
<div class="card mt-4 bg-light">
    <div class="card-body text-center py-4">
        <i class="bi bi-envelope text-primary" style="font-size: 2.5rem;"></i>
        <h5 class="mt-3">Still have questions?</h5>
        <p class="text-muted">Contact your system administrator for additional support.</p>
    </div>
</div>

<script>
// Category filter
document.querySelectorAll('.category-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.category-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.category-btn').forEach(b => b.classList.replace('btn-primary', 'btn-outline-primary'));
        this.classList.add('active');
        this.classList.replace('btn-outline-primary', 'btn-primary');

        const category = this.dataset.category;
        document.querySelectorAll('.faq-item, .faq-category').forEach(item => {
            if (category === 'all' || item.dataset.category === category) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

// Search
document.getElementById('faqSearch').addEventListener('input', function() {
    const query = this.value.toLowerCase();
    document.querySelectorAll('.faq-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(query) ? '' : 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
include VIEWS_PATH . '/layouts/main.php';
?>
