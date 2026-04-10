<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Listing Details | Landly Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-900: #0f1b1b;
            --green-800: #14312c;
            --green-700: #1f4f48;
            --green-600: #2a6c62;
            --cream-100: #f5f5dc;
            --cream-200: #efe7d8;
            --accent: #d2b48c;
            --accent-dark: #bea074;
            --sidebar-width: 280px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: "Inter", system-ui, sans-serif; }
        body { background-color: var(--green-900); color: var(--cream-100); min-height: 100vh; overflow-x: hidden; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .sidebar { width: var(--sidebar-width); background: linear-gradient(180deg, rgba(45, 106, 79, 0.95), rgba(13, 40, 24, 0.98)); padding-top: 20px; position: fixed; height: 100vh; }
        .sidebar .brand { margin: 0 20px 18px; display: flex; gap: 12px; align-items: center; text-decoration: none; color: var(--cream-100); }
        .brand-badge { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--accent), var(--accent-dark)); color: var(--green-900); display: grid; place-items: center; font-weight: 700; }
        .brand-text { font-weight: 700; font-size: 1.25rem; letter-spacing: -0.5px; }
        .sidebar-nav { margin-top: 16px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 12px 18px; color: rgba(254, 250, 224, 0.75); text-decoration: none; transition: background 0.2s ease; }
        .nav-item:hover, .nav-item.active { background: rgba(149, 213, 178, 0.2); color: var(--accent); }
        .main-content { margin-left: var(--sidebar-width); padding: 28px; width: calc(100% - var(--sidebar-width)); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 24px; }
        .page-title h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; font-style: italic; color: var(--cream-100); }
        .page-title p { color: rgba(245,245,220,0.85); margin-top: 6px; }
        .content-card { background: linear-gradient(145deg, rgba(255,255,255,.08), rgba(255,255,255,.02)); border: 1px solid rgba(149,213,178,.15); border-radius: 18px; padding: 24px; margin-bottom: 20px; }
        .content-card h2 { margin-bottom: 16px; color: var(--cream-100); }
        .back-btn { padding: 10px 16px; border: 1px solid rgba(210, 180, 140, 0.4); border-radius: 8px; color: var(--accent); background: transparent; cursor: pointer; text-decoration: none; font-weight: 600; transition: all .2s ease; }
        .back-btn:hover { background: rgba(210, 180, 140, 0.1); }
        .listing-header { display: flex; gap: 20px; margin-bottom: 24px; }
        .listing-image { width: 300px; height: 300px; border-radius: 12px; object-fit: cover; background: rgba(149,213,178,.1); }
        .listing-basic-info h1 { font-size: 1.8rem; margin-bottom: 12px; }
        .listing-basic-info p { color: rgba(254,250,224,.75); margin-bottom: 8px; line-height: 1.6; }
        .info-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .info-item { background: rgba(255,255,255,.03); padding: 12px; border-radius: 8px; border: 1px solid rgba(149,213,178,.1); }
        .info-label { font-size: .85rem; color: rgba(254,250,224,.6); text-transform: uppercase; letter-spacing: .5px; }
        .info-value { font-size: 1.1rem; font-weight: 600; color: var(--cream-100); margin-top: 6px; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .gallery-item { width: 100%; aspect-ratio: 1; border-radius: 12px; object-fit: cover; background: rgba(149,213,178,.1); cursor: pointer; transition: transform .2s ease; }
        .gallery-item:hover { transform: scale(1.05); }
        .documents { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-top: 12px; }
        .doc-item { background: rgba(255,255,255,.05); padding: 12px; border-radius: 8px; border: 1px solid rgba(149,213,178,.15); text-align: center; }
        .doc-link { color: var(--accent); text-decoration: none; word-break: break-all; padding: 6px; display: block; transition: color .2s ease; }
        .doc-link:hover { color: #95d5b2; }
        .seller-info { background: rgba(255,255,255,.03); padding: 16px; border-radius: 12px; border: 1px solid rgba(149,213,178,.1); }
        .seller-info h3 { margin-bottom: 12px; }
        .action-buttons { display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { border: none; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all .2s ease; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 0 12px rgba(0,0,0,.15); }
        .btn-success { background: rgba(46,204,113,.3); color: #2ecc71; border: 1px solid rgba(46,204,113,.3); }
        .btn-danger { background: rgba(231, 76, 60,.3); color: #e74c3c; border: 1px solid rgba(231, 76, 60,.3); }
        .btn-warning { background: rgba(241,196,15,.3); color: #f1c40f; border: 1px solid rgba(241,196,15,.3); }

        @media (max-width: 980px) {
            .dashboard-container { display: block; }
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content { margin-left: 0; width: 100%; padding: 16px; }
            .page-title h1 { font-size: 1.6rem; }
            .listing-header { flex-direction: column; }
            .listing-image { width: 100%; height: auto; }
            .info-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <a class="brand" href="/admin/dashboard">
                <div class="brand-badge">A</div>
                <div>
                    <div class="brand-text">Landly</div>
                    <div style="font-size:.65rem;color:var(--accent);letter-spacing:1.2px;">Admin Panel</div>
                </div>
            </a>
            <nav class="sidebar-nav">
                <a class="nav-item" href="/admin/dashboard">Dashboard</a>
                <a class="nav-item" href="/admin/dashboard/#users">Users</a>
                <a class="nav-item active" href="/admin/dashboard/#listings">Land Listings</a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Listing Details</h1>
                    <p>Complete property information and documents</p>
                </div>
                <a href="/admin/dashboard/#listings" class="back-btn">← Back to Listings</a>
            </div>

            <?php if (! empty($listing)): ?>
                <div class="content-card">
                    <div class="listing-header">
                        <?php 
                            if (! empty($images)): 
                                $firstImage = $images[0];
                        ?>
                            <img src="<?= esc($firstImage['image_path']) ?>" alt="<?= esc($listing['title']) ?>" class="listing-image"/>
                        <?php else: ?>
                            <div class="listing-image">No Image Available</div>
                        <?php endif; ?>
                        
                        <div class="listing-basic-info">
                            <h1><?= esc($listing['title']) ?></h1>
                            <p><?= esc($listing['description'] ?? 'No description provided') ?></p>
                            <div style="margin-top: 20px;">
                                <div style="font-size: 1.5rem; color: #2ecc71; font-weight: 700;">₱<?= number_format((float) ($listing['price'] ?? 0), 0) ?></div>
                            </div>
                        </div>
                    </div>

                    <h2>Basic Information</h2>
                    <div class="info-row">
                        <div class="info-item">
                            <div class="info-label">Listing ID</div>
                            <div class="info-value"><?= esc($listing['listing_id']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value"><?= esc(ucfirst($listing['listing_status'] ?? 'pending')) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Verification Status</div>
                            <div class="info-value"><?= esc(ucfirst($listing['is_verified_listing'] ?? 'pending')) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Land Area</div>
                            <div class="info-value"><?= esc($listing['land_area'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">City</div>
                            <div class="info-value"><?= esc($listing['city'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Province</div>
                            <div class="info-value"><?= esc($listing['province'] ?? 'Not provided') ?></div>
                        </div>
                    </div>

                    <?php if (! empty($images) && count($images) > 1): ?>
                        <h2>Gallery</h2>
                        <div class="gallery">
                            <?php foreach ($images as $image): ?>
                                <img src="<?= esc($image['image_path']) ?>" alt="Listing image" class="gallery-item"/>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($documents)): ?>
                        <h2>Supporting Documents</h2>
                        <div class="documents">
                            <?php foreach ($documents as $doc): ?>
                                <div class="doc-item">
                                    <a href="<?= esc($doc['document_path']) ?>" target="_blank" class="doc-link" download>
                                        📄 <?= basename($doc['document_path']) ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (! empty($seller)): ?>
                        <h2>Seller Information</h2>
                        <div class="seller-info">
                            <h3><?= esc(trim(($seller['first_name'] ?? '') . ' ' . ($seller['last_name'] ?? ''))) ?></h3>
                            <p><strong>Email:</strong> <?= esc($seller['email']) ?></p>
                            <p><strong>Role:</strong> <?= esc(ucfirst($seller['roles'] ?? 'seller')) ?></p>
                        </div>
                    <?php endif; ?>

                    <h2 style="margin-top: 24px;">Actions</h2>
                    <div class="action-buttons">
                        <form action="/admin/listings/<?= esc($listing['listing_id']) ?>/verify" method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <button class="btn btn-success" type="submit">✓ Approve</button>
                        </form>
                        <form action="/admin/listings/<?= esc($listing['listing_id']) ?>/reject" method="post" style="display:inline">
                            <?= csrf_field() ?>
                            <button class="btn btn-danger" type="submit">✗ Reject</button>
                        </form>
                        <form action="/admin/listings/<?= esc($listing['listing_id']) ?>/delete" method="post" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this listing?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-warning" type="submit">🗑 Delete</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="content-card" style="text-align: center;">
                    <p>Listing not found.</p>
                    <a href="/admin/dashboard/#listings" class="back-btn">← Back to Listings</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
