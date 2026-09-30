<?php
$role = $_SESSION['role'] ?? '';
$activeMenu = $activeMenu ?? '';
$navigationItems = [
    ['key' => 'dashboard', 'label' => 'Dashboard', 'path' => '/index.php']
];

if ($role === 'admin') {
    $navigationItems[] = ['key' => 'kategori', 'label' => 'Kategori', 'path' => '/modules/kategori/index.php'];
    $navigationItems[] = ['key' => 'buku', 'label' => 'Buku', 'path' => '/modules/buku/index.php'];
    $navigationItems[] = ['key' => 'user', 'label' => 'User', 'path' => '/modules/user/index.php'];
}

if (in_array($role, ['admin', 'petugas'], true)) {
    $navigationItems[] = ['key' => 'peminjaman', 'label' => 'Peminjaman', 'path' => '/modules/peminjaman/index.php'];
    $navigationItems[] = ['key' => 'laporan', 'label' => 'Laporan', 'path' => '/modules/laporan/index.php'];
}
?>
<aside class="sidebar" aria-label="Navigasi Bibliotech">
    <a class="brand" href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES, 'UTF-8') ?>/index.php" aria-label="Bibliotech, Dashboard">
        <span class="brand-mark" aria-hidden="true">B</span>
        <span class="brand-copy">
            <span class="brand-name">Bibliotech</span>
            <span class="brand-caption">Perpustakaan</span>
        </span>
    </a>

    <nav class="desktop-nav nav-links" aria-label="Menu utama">
        <?php foreach ($navigationItems as $item): ?>
            <?php $isActive = $activeMenu === $item['key']; ?>
            <a class="nav-link<?= $isActive ? ' is-active' : '' ?>" href="<?= htmlspecialchars($appBaseUrl . $item['path'], ENT_QUOTES, 'UTF-8') ?>"<?= $isActive ? ' aria-current="page"' : '' ?>>
                <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <details class="mobile-nav">
        <summary class="nav-toggle">Menu</summary>
        <nav class="nav-links" aria-label="Menu utama mobile">
            <?php foreach ($navigationItems as $item): ?>
                <?php $isActive = $activeMenu === $item['key']; ?>
                <a class="nav-link<?= $isActive ? ' is-active' : '' ?>" href="<?= htmlspecialchars($appBaseUrl . $item['path'], ENT_QUOTES, 'UTF-8') ?>"<?= $isActive ? ' aria-current="page"' : '' ?>>
                    <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </details>

    <div class="sidebar-bottom">
        <a class="logout-link" href="<?= htmlspecialchars($appBaseUrl, ENT_QUOTES, 'UTF-8') ?>/logout.php">Logout</a>
    </div>
</aside>
