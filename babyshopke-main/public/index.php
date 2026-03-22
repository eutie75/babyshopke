<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Family.php';
require_once __DIR__ . '/../models/Wishlist.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../controllers/wishlist_controller.php';

handleCartAction();
handleWishlistAction();

$category = trim((string)($_GET['cat'] ?? ''));
$search = trim((string)($_GET['q'] ?? ''));
$ageKey = trim((string)($_GET['age'] ?? ''));
$ageMap = getAgeFilterMap();
$activeAgeRange = getActiveAgeRange();
$activeAgeLabel = $ageKey !== '' ? $ageKey : null;

if ($activeAgeRange === null) {
    $activeAgeRange = $ageMap['6-12'];
    $activeAgeLabel = '6-12';
} elseif ($activeAgeLabel === null && !empty($_SESSION['active_child_age_months'])) {
    $activeAgeLabel = 'active-child';
}

$activeChild = null;
if (!empty($_SESSION['active_child_id']) && isLoggedIn()) {
    $activeChild = Family::getChildForUser((int)$_SESSION['active_child_id'], (int)currentUserId());
}

$products = Product::getFiltered(
    $category !== '' ? $category : null,
    $search !== '' ? $search : null,
    $activeAgeRange,
    24
);

if (empty($products) && ($category !== '' || $search !== '')) {
    $products = Product::getFiltered(
        $category !== '' ? $category : null,
        $search !== '' ? $search : null,
        null,
        24
    );
}

$categories = Product::getCategories();
$wishlistProductIds = isLoggedIn() ? Wishlist::getProductIdsByUser((int)currentUserId()) : [];
$wishlistLookup = array_fill_keys($wishlistProductIds, true);

$pageTitle = 'Home';
include __DIR__ . '/../includes/header.php';
?>

<section class="hero">
    <div class="container hero-content">
        <h1>Everything your baby needs in one place</h1>
        <p>Shop trusted baby and kids products delivered across Kenya.</p>
        <div class="button-row">
            <a href="#products" class="btn btn-accent">Shop Now</a>
            <a href="<?= e(siteUrl('getstarted.php')) ?>" class="btn btn-outline">Get Started</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2>Shop by Category</h2>
        <div class="category-grid">
            <?php
            $allParams = [];
            if ($search !== '') {
                $allParams['q'] = $search;
            }
            if ($ageKey !== '') {
                $allParams['age'] = $ageKey;
            }
            ?>
            <a href="<?= e(siteUrl('index.php' . (!empty($allParams) ? '?' . http_build_query($allParams) : ''))) ?>" class="category-card <?= ($category === '') ? 'active' : '' ?>">
                All
            </a>
            <?php foreach ($categories as $cat): ?>
                <?php
                $params = ['cat' => $cat];
                if ($search !== '') {
                    $params['q'] = $search;
                }
                if ($ageKey !== '') {
                    $params['age'] = $ageKey;
                }
                ?>
                <a href="<?= e(siteUrl('index.php?' . http_build_query($params))) ?>" class="category-card <?= ($category === $cat) ? 'active' : '' ?>">
                    <?= e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="products" class="section">
    <div class="container">
        <div class="section-header">
            <div>
                <h2>
                    <?php if ($activeChild && $activeAgeLabel === 'active-child'): ?>
                        Recommended for <?= e($activeChild['child_name']) ?> (<?= (int)($_SESSION['active_child_age_months'] ?? 0) ?> months)
                    <?php else: ?>
                        Top Picks by Age
                    <?php endif; ?>
                </h2>
                <p class="muted">Use age, category, and search filters together.</p>
            </div>
            <?php if (isLoggedIn()): ?>
                <a class="btn btn-outline btn-sm" href="<?= e(siteUrl('account.php')) ?>">Manage Child Profiles</a>
            <?php endif; ?>
        </div>

        <div class="tabs">
            <?php foreach ($ageMap as $label => $range): ?>
                <?php
                $params = ['age' => $label];
                if ($category !== '') {
                    $params['cat'] = $category;
                }
                if ($search !== '') {
                    $params['q'] = $search;
                }
                ?>
                <a href="<?= e(siteUrl('index.php?' . http_build_query($params))) ?>" class="<?= ($ageKey === $label) ? 'active' : '' ?>">
                    <?= e($label) ?> months
                </a>
            <?php endforeach; ?>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <article class="product-card">
                    <a href="<?= e(siteUrl('product.php?id=' . (int)$p['id'])) ?>">
                        <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>">
                    </a>
                    <h3><?= e($p['name']) ?></h3>
                    <p class="muted"><?= e($p['category']) ?></p>
                    <p class="price">KSH <?= number_format((float)$p['price'], 0) ?></p>
                    <p class="muted"><?= (int)$p['age_min_months'] ?>-<?= (int)$p['age_max_months'] ?> months</p>

                    <div class="button-row">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="cart_action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="redirect_to" value="index.php<?= (!empty($_SERVER['QUERY_STRING'])) ? '?' . e((string)$_SERVER['QUERY_STRING']) : '' ?>">
                            <button type="submit" class="btn btn-accent btn-sm" <?= ((int)$p['stock'] <= 0) ? 'disabled' : '' ?>>
                                <?= ((int)$p['stock'] <= 0) ? 'Out of Stock' : 'Add to Cart' ?>
                            </button>
                        </form>

                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="wishlist_action" value="toggle">
                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="redirect_to" value="index.php<?= (!empty($_SERVER['QUERY_STRING'])) ? '?' . e((string)$_SERVER['QUERY_STRING']) : '' ?>">
                            <button type="submit" class="btn btn-outline btn-sm">
                                <?= isset($wishlistLookup[(int)$p['id']]) ? 'Wishlisted' : 'Wishlist' ?>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if (empty($products)): ?>
            <div class="card">
                <p>No products matched your filters.</p>
                <a href="<?= e(siteUrl('index.php')) ?>" class="btn btn-primary">Reset Filters</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
