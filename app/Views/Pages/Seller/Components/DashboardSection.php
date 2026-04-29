<?php
$sellerListings = is_array($sellerListings ?? null) ? $sellerListings : [];
$sellerInquiries = is_array($sellerInquiries ?? null) ? $sellerInquiries : [];

$totalListings = count($sellerListings);
$totalViews = 0;

$listingInquiryCounts = [];
$listingAcceptedCounts = [];

$inquiriesTotal = count($sellerInquiries);
$inquiriesAccepted = 0;
$inquiriesReserved = 0;
$inquiriesClosed = 0;

$responseSecondsTotal = 0;
$responseSamples = 0;

$dayKeys = [];
$dayLabels = [];
$dailyInquiries = [];
$dailyResolved = [];

for ($i = 6; $i >= 0; $i--) {
    $key = date('Y-m-d', strtotime("-$i days"));
    $dayKeys[] = $key;
    $dayLabels[] = date('M d', strtotime($key));
    $dailyInquiries[$key] = 0;
    $dailyResolved[$key] = 0;
}

foreach ($sellerListings as $listing) {
    $listingId = (int) ($listing['listing_id'] ?? 0);
    $totalViews += (int) ($listing['view_count'] ?? 0);

    if ($listingId > 0) {
        $listingInquiryCounts[$listingId] = 0;
        $listingAcceptedCounts[$listingId] = 0;
    }
}

foreach ($sellerInquiries as $inquiry) {
    $listingId = (int) ($inquiry['listing_id'] ?? 0);
    $status = strtolower(trim((string) ($inquiry['status_label'] ?? 'pending')));

    if ($listingId > 0) {
        if (! isset($listingInquiryCounts[$listingId])) {
            $listingInquiryCounts[$listingId] = 0;
        }

        if (! isset($listingAcceptedCounts[$listingId])) {
            $listingAcceptedCounts[$listingId] = 0;
        }

        $listingInquiryCounts[$listingId]++;
    }

    if ($status === 'accepted') {
        $inquiriesAccepted++;
        if ($listingId > 0) {
            $listingAcceptedCounts[$listingId]++;
        }
    }

    if ($status === 'reserved') {
        $inquiriesReserved++;
    }

    if ($status === 'closed') {
        $inquiriesClosed++;
    }

    $rawCreatedAt = (string) ($inquiry['created_at'] ?? '');
    $rawUpdatedAt = (string) ($inquiry['updated_at'] ?? '');

    if ($rawCreatedAt !== '') {
        $createdDay = date('Y-m-d', strtotime($rawCreatedAt));
        if (isset($dailyInquiries[$createdDay])) {
            $dailyInquiries[$createdDay]++;
        }
    }

    if ($rawUpdatedAt !== '' && in_array($status, ['accepted', 'rejected', 'reserved', 'closed'], true)) {
        $updatedDay = date('Y-m-d', strtotime($rawUpdatedAt));
        if (isset($dailyResolved[$updatedDay])) {
            $dailyResolved[$updatedDay]++;
        }
    }

    $createdTs = $rawCreatedAt !== '' ? strtotime($rawCreatedAt) : false;
    $updatedTs = $rawUpdatedAt !== '' ? strtotime($rawUpdatedAt) : false;

    if (
        $createdTs !== false
        && $updatedTs !== false
        && $updatedTs > $createdTs
        && in_array($status, ['accepted', 'rejected', 'reserved', 'closed'], true)
    ) {
        $responseSecondsTotal += ($updatedTs - $createdTs);
        $responseSamples++;
    }
}

$avgResponseHours = $responseSamples > 0 ? ($responseSecondsTotal / $responseSamples) / 3600 : 0;
$avgResponseLabel = $responseSamples > 0
    ? ($avgResponseHours < 24
        ? number_format($avgResponseHours, 1) . 'h'
        : number_format($avgResponseHours / 24, 1) . 'd')
    : 'N/A';

$conversionInquiryRate = $totalViews > 0 ? ($inquiriesTotal / $totalViews) * 100 : 0;
$conversionAcceptRate = $inquiriesTotal > 0 ? ($inquiriesAccepted / $inquiriesTotal) * 100 : 0;
$conversionCloseRate = $inquiriesTotal > 0 ? (($inquiriesReserved + $inquiriesClosed) / $inquiriesTotal) * 100 : 0;

$performanceRows = [];
foreach ($sellerListings as $listing) {
    $listingId = (int) ($listing['listing_id'] ?? 0);
    $views = (int) ($listing['view_count'] ?? 0);
    $inquiryCount = (int) ($listingInquiryCounts[$listingId] ?? 0);
    $acceptedCount = (int) ($listingAcceptedCounts[$listingId] ?? 0);

    $score = ($views * 0.35) + ($inquiryCount * 12) + ($acceptedCount * 20);

    $performanceRows[] = [
        'title' => (string) ($listing['title'] ?? 'Untitled Listing'),
        'location_label' => (string) ($listing['location_label'] ?? 'Location unavailable'),
        'views' => $views,
        'inquiries' => $inquiryCount,
        'accepted' => $acceptedCount,
        'score' => (int) round($score),
    ];
}

usort($performanceRows, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);
$topPerformanceRows = array_slice($performanceRows, 0, 5);

$maxSeriesValue = max(1, max($dailyInquiries), max($dailyResolved));
$pointCount = max(1, count($dayKeys) - 1);

$inquiryPoints = [];
$resolvedPoints = [];

foreach ($dayKeys as $index => $key) {
    $x = (int) round(($index / $pointCount) * 100);
    $inquiryY = (int) round(100 - (($dailyInquiries[$key] / $maxSeriesValue) * 100));
    $resolvedY = (int) round(100 - (($dailyResolved[$key] / $maxSeriesValue) * 100));

    $inquiryPoints[] = $x . ',' . $inquiryY;
    $resolvedPoints[] = $x . ',' . $resolvedY;
}
?>

<style>
    .seller-analytics-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
        margin-top: 24px;
    }

    .seller-analytics-card {
        background: rgba(20, 30, 45, 0.65);
        border: 1px solid rgba(210, 180, 140, 0.22);
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.22);
    }

    .seller-analytics-card h3 {
        margin: 0;
        font-size: 1.05rem;
        color: #fefae0;
        font-weight: 700;
    }

    .seller-analytics-subtitle {
        margin-top: 6px;
        color: rgba(254, 250, 224, 0.7);
        font-size: 0.82rem;
    }

    .seller-kpi-row {
        margin-top: 14px;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .seller-kpi {
        background: rgba(11, 22, 34, 0.78);
        border: 1px solid rgba(254, 250, 224, 0.12);
        border-radius: 14px;
        padding: 12px;
    }

    .seller-kpi-label {
        color: rgba(254, 250, 224, 0.68);
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .seller-kpi-value {
        margin-top: 8px;
        font-size: 1.2rem;
        font-weight: 700;
        color: #fefae0;
    }

    .seller-funnel {
        margin-top: 14px;
        display: grid;
        gap: 10px;
    }

    .seller-funnel-item {
        display: grid;
        grid-template-columns: 116px 1fr auto;
        align-items: center;
        gap: 10px;
    }

    .seller-funnel-label,
    .seller-funnel-value {
        color: rgba(254, 250, 224, 0.85);
        font-size: 0.8rem;
    }

    .seller-funnel-track {
        height: 10px;
        border-radius: 999px;
        background: rgba(254, 250, 224, 0.1);
        overflow: hidden;
    }

    .seller-funnel-bar {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #d2b48c, #c9a86c);
    }

    .seller-line-chart-wrap {
        margin-top: 14px;
        background: rgba(11, 22, 34, 0.78);
        border: 1px solid rgba(254, 250, 224, 0.12);
        border-radius: 14px;
        padding: 12px;
    }

    .seller-line-legend {
        display: flex;
        gap: 16px;
        font-size: 0.78rem;
        color: rgba(254, 250, 224, 0.8);
        margin-bottom: 8px;
    }

    .seller-line-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        display: inline-block;
        margin-right: 6px;
    }

    .seller-line-dot.inquiries {
        background: #d2b48c;
    }

    .seller-line-dot.resolved {
        background: #5fc9a8;
    }

    .seller-line-axis {
        margin-top: 8px;
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 4px;
    }

    .seller-line-axis span {
        color: rgba(254, 250, 224, 0.66);
        font-size: 0.7rem;
        text-align: center;
    }

    .seller-response-main {
        margin-top: 14px;
        display: flex;
        align-items: end;
        gap: 10px;
    }

    .seller-response-main .value {
        font-size: 2rem;
        font-weight: 800;
        color: #fefae0;
        line-height: 1;
    }

    .seller-response-main .meta {
        color: rgba(254, 250, 224, 0.72);
        font-size: 0.82rem;
    }

    .seller-performance-table-wrap {
        width: 100%;
        max-width: 100%;
        max-height: 100px;
        overflow-x: hidden;
        overflow-y: auto;
        margin-top: 14px;
        padding-right: 6px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: rgba(210, 180, 140, 0.65) rgba(254, 250, 224, 0.08);
    }

    .seller-performance-table-wrap::-webkit-scrollbar {
        width: 8px;
    }

    .seller-performance-table-wrap::-webkit-scrollbar-track {
        background: rgba(254, 250, 224, 0.08);
        border-radius: 999px;
    }

    .seller-performance-table-wrap::-webkit-scrollbar-thumb {
        background: rgba(210, 180, 140, 0.65);
        border-radius: 999px;
    }

    .seller-performance-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .seller-performance-table th,
    .seller-performance-table td {
        padding: 9px 8px;
        text-align: left;
        border-bottom: 1px solid rgba(254, 250, 224, 0.1);
    }

    .seller-performance-table th {
        color: rgba(254, 250, 224, 0.72);
        font-size: 0.72rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .seller-performance-table td {
        color: rgba(254, 250, 224, 0.9);
    }

    .seller-score-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 48px;
        padding: 4px 8px;
        border-radius: 999px;
        background: rgba(210, 180, 140, 0.16);
        border: 1px solid rgba(210, 180, 140, 0.38);
        color: #fefae0;
        font-weight: 700;
    }

    @media (max-width: 1200px) {
        .seller-analytics-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .seller-funnel-item {
            grid-template-columns: 1fr;
            gap: 6px;
        }

        .seller-kpi-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<section id="section-dashboard" class="content-section active">
    <div class="seller-analytics-grid">
        <article class="seller-analytics-card">
            <h3>Inquiry Conversion Funnel</h3>
            <p class="seller-analytics-subtitle">Seller lead flow from views to closed outcomes.</p>

            <div class="seller-kpi-row">
                <div class="seller-kpi">
                    <div class="seller-kpi-label">Inquiry Rate</div>
                    <div class="seller-kpi-value"><?= number_format($conversionInquiryRate, 1) ?>%</div>
                </div>
                <div class="seller-kpi">
                    <div class="seller-kpi-label">Accept Rate</div>
                    <div class="seller-kpi-value"><?= number_format($conversionAcceptRate, 1) ?>%</div>
                </div>
                <div class="seller-kpi">
                    <div class="seller-kpi-label">Close/Reserve Rate</div>
                    <div class="seller-kpi-value"><?= number_format($conversionCloseRate, 1) ?>%</div>
                </div>
            </div>

            <?php
            $funnelMax = max(1, $totalViews, $inquiriesTotal, $inquiriesAccepted, ($inquiriesReserved + $inquiriesClosed));
            ?>
            <div class="seller-funnel">
                <div class="seller-funnel-item">
                    <span class="seller-funnel-label">Views</span>
                    <div class="seller-funnel-track"><div class="seller-funnel-bar" style="width: <?= ($totalViews / $funnelMax) * 100 ?>%"></div></div>
                    <span class="seller-funnel-value"><?= number_format($totalViews) ?></span>
                </div>
                <div class="seller-funnel-item">
                    <span class="seller-funnel-label">Inquiries</span>
                    <div class="seller-funnel-track"><div class="seller-funnel-bar" style="width: <?= ($inquiriesTotal / $funnelMax) * 100 ?>%"></div></div>
                    <span class="seller-funnel-value"><?= number_format($inquiriesTotal) ?></span>
                </div>
                <div class="seller-funnel-item">
                    <span class="seller-funnel-label">Accepted</span>
                    <div class="seller-funnel-track"><div class="seller-funnel-bar" style="width: <?= ($inquiriesAccepted / $funnelMax) * 100 ?>%"></div></div>
                    <span class="seller-funnel-value"><?= number_format($inquiriesAccepted) ?></span>
                </div>
                <div class="seller-funnel-item">
                    <span class="seller-funnel-label">Reserved/Closed</span>
                    <div class="seller-funnel-track"><div class="seller-funnel-bar" style="width: <?= (($inquiriesReserved + $inquiriesClosed) / $funnelMax) * 100 ?>%"></div></div>
                    <span class="seller-funnel-value"><?= number_format($inquiriesReserved + $inquiriesClosed) ?></span>
                </div>
            </div>
        </article>

        <article class="seller-analytics-card">
            <h3>7-Day Engagement Trend</h3>
            <p class="seller-analytics-subtitle">Line chart for incoming inquiries and resolved actions.</p>

            <div class="seller-line-chart-wrap">
                <div class="seller-line-legend">
                    <span><span class="seller-line-dot inquiries"></span>Inquiries</span>
                    <span><span class="seller-line-dot resolved"></span>Resolved (accepted/rejected/reserved/closed)</span>
                </div>
                <svg viewBox="0 0 100 100" width="100%" height="210" preserveAspectRatio="none" role="img" aria-label="Seller analytics line chart">
                    <defs>
                        <linearGradient id="sellerInquiriesStroke" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stop-color="#d2b48c" />
                            <stop offset="100%" stop-color="#f3d9b5" />
                        </linearGradient>
                        <linearGradient id="sellerResolvedStroke" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0%" stop-color="#5fc9a8" />
                            <stop offset="100%" stop-color="#93e7cf" />
                        </linearGradient>
                    </defs>
                    <g stroke="rgba(254, 250, 224, 0.14)" stroke-width="0.5">
                        <line x1="0" y1="0" x2="100" y2="0" />
                        <line x1="0" y1="25" x2="100" y2="25" />
                        <line x1="0" y1="50" x2="100" y2="50" />
                        <line x1="0" y1="75" x2="100" y2="75" />
                        <line x1="0" y1="100" x2="100" y2="100" />
                    </g>
                    <polyline fill="none" stroke="url(#sellerInquiriesStroke)" stroke-width="2.2" points="<?= esc(implode(' ', $inquiryPoints)) ?>" />
                    <polyline fill="none" stroke="url(#sellerResolvedStroke)" stroke-width="2.2" points="<?= esc(implode(' ', $resolvedPoints)) ?>" />
                </svg>
                <div class="seller-line-axis">
                    <?php foreach ($dayLabels as $label): ?>
                        <span><?= esc($label) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>

        <article class="seller-analytics-card">
            <h3> Seller Summary Analytics </h3>
            <p class="seller-analytics-subtitle">Time from inquiry created to your first status action.</p>

          

            <div class="seller-kpi-row">
                <div class="seller-kpi">
                    <div class="seller-kpi-label">Total Listings</div>
                    <div class="seller-kpi-value"><?= number_format($totalListings) ?></div>
                </div>
                <div class="seller-kpi">
                    <div class="seller-kpi-label">Total Views</div>
                    <div class="seller-kpi-value"><?= number_format($totalViews) ?></div>
                </div>
                <div class="seller-kpi">
                    <div class="seller-kpi-label">Total Inquiries</div>
                    <div class="seller-kpi-value"><?= number_format($inquiriesTotal) ?></div>
                </div>
            </div>
        </article>

        <article class="seller-analytics-card">
            <h3>Listing Performance Score</h3>
            <p class="seller-analytics-subtitle">Top-performing listings based on views, inquiries, and accepted leads.</p>

            <?php if ($topPerformanceRows === []): ?>
                <div class="seller-analytics-subtitle" style="margin-top: 16px;">No listings available yet to compute a score.</div>
            <?php else: ?>
                <div class="seller-performance-table-wrap">
                    <table class="seller-performance-table" aria-label="Top listing performance table">
                        <thead>
                            <tr>
                                <th>Listing</th>
                                <th>Views</th>
                                <th>Inquiries</th>
                                <th>Accepted</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topPerformanceRows as $row): ?>
                                <tr>
                                    <td>
                                        <div><?= esc($row['title']) ?></div>
                                        <div style="color: rgba(254, 250, 224, 0.58); font-size: 0.72rem; margin-top: 2px;"><?= esc($row['location_label']) ?></div>
                                    </td>
                                    <td><?= number_format($row['views']) ?></td>
                                    <td><?= number_format($row['inquiries']) ?></td>
                                    <td><?= number_format($row['accepted']) ?></td>
                                    <td><span class="seller-score-badge"><?= number_format($row['score']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>
    </div>
</section>
