<?php
$sidebarCounts = $sidebarCounts ?? [
    'saved_properties' => 0,
    'accepted_inquiries' => 0,
    'unread_messages' => 0,
];
$userProfile = $userProfile ?? [
    'full_name' => 'Buyer',
    'email' => 'N/A',
    'avatar_url' => '',
    'initials' => 'NA',
    'status_label' => 'Inactive Buyer',
    'status_class' => 'inactive',
];
$buyerFullName = trim((string) ($userProfile['full_name'] ?? 'Buyer'));
$buyerFirstName = trim((string) strtok($buyerFullName, ' '));
$buyerFirstName = $buyerFirstName !== '' ? $buyerFirstName : 'there';
$geoapifyApiKey = trim((string) ($geoapifyApiKey ?? ''));
$jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buyer Dashboard | Landly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --green-900: #0d2818;
            --green-800: #1a4d2e;
            --green-700: #2d6a4f;
            --green-600: #40916c;
            --cream-100: #fefae0;
            --cream-200: #f5f0d6;
            --accent: #95d5b2;
            --accent-dark: #74c69d;
            --text-dark: #0d2818;
            --sidebar-width: 280px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: "Inter", system-ui, sans-serif;
        }

        body {
            background-color: var(--green-900);
            color: var(--cream-100);
            line-height: 1.6;
            overflow-x: hidden;
            min-height: 100vh;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 30%, rgba(149, 213, 178, 0.03) 0%, transparent 25%),
                radial-gradient(circle at 80% 70%, rgba(149, 213, 178, 0.025) 0%, transparent 30%),
                radial-gradient(circle at 50% 50%, rgba(45, 106, 79, 0.15) 0%, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%2395d5b2' fill-opacity='0.015'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 0;
        }

        /* === ANIMATIONS === */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInLeft {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* === LAYOUT === */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* === SIDEBAR === */
        .sidebar {
            --bs-offcanvas-width: var(--sidebar-width);
            width: var(--sidebar-width);
            background: linear-gradient(180deg, rgba(45, 106, 79, 0.95) 0%, rgba(13, 40, 24, 0.98) 100%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(149, 213, 178, 0.15);
            border-top: none;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1045;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar.offcanvas {
            padding: 0;
            color: var(--cream-100);
        }

        .sidebar .offcanvas-body {
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        .sidebar .offcanvas-header {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .sidebar .btn-close {
            filter: invert(1);
            opacity: 0.9;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100px;
            max-width: 100px;
            height: auto;
            max-height: 100px;
            overflow: hidden;
            animation: pulse 3s ease-in-out infinite;
        }

        .brand-badge .brand-logo {
            width: 100px;
            height: 100%;
            max-height: 100px;
            object-fit: contain;
            display: block;
        }

        .brand-text {
            font-weight: 700;
            font-size: 1.3rem;
            letter-spacing: -0.5px;
        }

        .brand-subtitle {
            font-size: 0.7rem;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }

        /* User Profile */
        .user-profile {
            padding: 20px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            display: grid;
            place-items: center;
            font-weight: 600;
            color: var(--green-900);
            font-size: 1.1rem;
        }

        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }

        .user-details h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .user-details span {
            font-size: 0.75rem;
            color: rgba(254, 250, 224, 0.6);
        }

        .member-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: rgba(149, 213, 178, 0.2);
            border: 1px solid rgba(149, 213, 178, 0.4);
            border-radius: 12px;
            font-size: 0.65rem;
            color: var(--accent);
            margin-top: 6px;
        }

        .member-badge.inactive {
            background: rgba(229, 57, 53, 0.2);
            border-color: rgba(229, 57, 53, 0.4);
            color: #ffb4a9;
        }

        /* Navigation */
        .sidebar-nav {
            flex: 1 1 auto;
            min-height: 0;
            padding: 20px 0;
            overflow-y: auto;
        }

        .nav-section {
            margin-bottom: 25px;
        }

        .nav-section-title {
            padding: 0 20px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(254, 250, 224, 0.4);
            margin-bottom: 10px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            color: rgba(254, 250, 224, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .nav-item::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--accent);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-item:hover {
            background: rgba(149, 213, 178, 0.08);
            color: var(--cream-100);
        }

        .nav-item:hover::before {
            transform: scaleY(1);
        }

        .nav-item.active {
            background: rgba(149, 213, 178, 0.12);
            color: var(--accent);
        }

        .nav-item.active::before {
            transform: scaleY(1);
        }

        .nav-item svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
        }

        .nav-item span {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .nav-badge {
            margin-left: auto;
            padding: 2px 8px;
            background: var(--accent);
            color: var(--green-900);
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            flex-shrink: 0;
            margin-top: auto;
            padding: 20px;
            border-top: 1px solid rgba(149, 213, 178, 0.1);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 12px;
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.3);
            border-radius: 12px;
            color: #e74c3c;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(231, 76, 60, 0.25);
            border-color: rgba(231, 76, 60, 0.5);
        }

        .logout-btn svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        /* === MAIN CONTENT === */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
            min-width: 0;
            width: 100%;
        }

        .mobile-topbar {
            display: none;
            margin-bottom: 16px;
            padding: 8px 0;
            border-bottom: 1px solid rgba(149, 213, 178, 0.2);
            background: rgba(13, 40, 24, 0.75);
            backdrop-filter: blur(10px);
        }

        .mobile-topbar .container-fluid {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .mobile-topbar-brand {
            color: var(--cream-100);
            text-decoration: none;
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 1.1rem;
            font-weight: 600;
            line-height: 1.1;
        }

        .mobile-topbar-brand span {
            display: block;
            font-family: "Inter", system-ui, sans-serif;
            font-size: 0.68rem;
            font-style: normal;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--accent);
            margin-top: 3px;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            animation: fadeInUp 0.5s ease;
        }

        .page-title h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 2rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .page-title p {
            color: rgba(254, 250, 224, 0.6);
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(149, 213, 178, 0.2);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(149, 213, 178, 0.1);
        }

        .search-box input {
            background: none;
            border: none;
            color: var(--cream-100);
            font-size: 0.9rem;
            width: 200px;
            outline: none;
        }

        .search-box input::placeholder {
            color: rgba(254, 250, 224, 0.4);
        }

        .search-box svg {
            width: 18px;
            height: 18px;
            stroke: rgba(254, 250, 224, 0.5);
            stroke-width: 2;
            fill: none;
        }

        .notification-btn {
            position: relative;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(149, 213, 178, 0.2);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            background: rgba(149, 213, 178, 0.1);
            border-color: var(--accent);
        }

        .notification-btn svg {
            width: 20px;
            height: 20px;
            stroke: var(--cream-100);
            stroke-width: 2;
            fill: none;
        }

        .notification-dot {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 8px;
            height: 8px;
            background: #e74c3c;
            border-radius: 50%;
            animation: pulse 2s ease infinite;
        }

        .notification-wrapper {
            position: relative;
        }

        .notification-count {
            position: absolute;
            top: 6px;
            right: 4px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 999px;
            background: #e74c3c;
            color: #fff;
            font-size: 0.68rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .notification-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: min(420px, 92vw);
            max-height: 70vh;
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid rgba(149, 213, 178, 0.25);
            background: linear-gradient(160deg, rgba(16, 33, 28, 0.98), rgba(23, 48, 40, 0.97));
            backdrop-filter: blur(10px);
            box-shadow: 0 18px 44px rgba(0, 0, 0, 0.42);
            z-index: 1100;
        }

        .notification-dropdown[hidden] {
            display: none;
        }

        .notification-dropdown-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.15);
        }

        .notification-dropdown-title {
            color: var(--cream-100);
            font-size: 0.9rem;
            font-weight: 600;
            margin: 0;
        }

        .notification-action-btn {
            border: 1px solid rgba(149, 213, 178, 0.25);
            border-radius: 10px;
            background: rgba(149, 213, 178, 0.08);
            color: var(--cream-100);
            padding: 6px 10px;
            font-size: 0.75rem;
            cursor: pointer;
        }

        .notification-action-btn:hover {
            background: rgba(149, 213, 178, 0.18);
        }

        .notification-list {
            max-height: 58vh;
            overflow-y: auto;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .notification-item {
            border: 1px solid rgba(149, 213, 178, 0.18);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.03);
            padding: 10px;
            cursor: pointer;
            transition: border-color 0.2s ease, background 0.2s ease;
        }

        .notification-item:hover {
            border-color: rgba(149, 213, 178, 0.45);
            background: rgba(149, 213, 178, 0.08);
        }

        .notification-item.unread {
            border-color: rgba(231, 76, 60, 0.55);
        }

        .notification-item-main {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: flex-start;
        }

        .notification-message {
            color: var(--cream-100);
            font-size: 0.86rem;
            line-height: 1.35;
            margin: 0;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
            color: rgba(254, 250, 224, 0.65);
            font-size: 0.72rem;
        }

        .notification-type-pill {
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid rgba(149, 213, 178, 0.3);
            background: rgba(149, 213, 178, 0.1);
            font-size: 0.68rem;
            text-transform: capitalize;
        }

        .notification-detail {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed rgba(149, 213, 178, 0.2);
            color: rgba(254, 250, 224, 0.78);
            font-size: 0.74rem;
            display: none;
        }

        .notification-item.expanded .notification-detail {
            display: block;
        }

        .notification-empty,
        .notification-loading {
            text-align: center;
            color: rgba(254, 250, 224, 0.7);
            padding: 18px 10px;
            font-size: 0.82rem;
        }

        /* === DASHBOARD STATS === */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
            animation: fadeInUp 0.6s ease;
        }

        .stat-card {
            padding: 24px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            transition: all 0.4s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(149, 213, 178, 0.3);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(149, 213, 178, 0.2), rgba(149, 213, 178, 0.05));
            border: 1px solid rgba(149, 213, 178, 0.3);
            display: grid;
            place-items: center;
        }

        .stat-icon svg {
            width: 24px;
            height: 24px;
            stroke: var(--accent);
            stroke-width: 1.8;
            fill: none;
        }

        .stat-trend {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stat-trend.up {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
        }

        .stat-trend.down {
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--cream-100);
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: rgba(254, 250, 224, 0.6);
        }

        /* === CONTENT SECTIONS === */
        .content-section {
            display: none;
            animation: fadeInUp 0.5s ease;
        }

        .content-section.active {
            display: block;
        }

        .content-section .table-responsive {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .content-section table {
            width: 100%;
        }

        .content-section img {
            max-width: 100%;
        }

        button,
        .btn-primary,
        .btn-secondary,
        .inquiry-btn,
        .chat-send-btn {
            touch-action: manipulation;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }

        /* Cards */
        .content-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .card-header-action {
            padding: 6px 14px;
            background: rgba(149, 213, 178, 0.1);
            border: 1px solid rgba(149, 213, 178, 0.3);
            border-radius: 8px;
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .card-header-action:hover {
            background: rgba(149, 213, 178, 0.2);
        }

        .card-body {
            padding: 20px 24px;
        }

        /* Property Listings */
        .listing-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(149, 213, 178, 0.08);
        }

        .listing-item:last-child {
            border-bottom: none;
        }

        .listing-thumb {
            width: 80px;
            height: 60px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--green-700), var(--green-800));
            overflow: hidden;
        }

        .listing-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .listing-info {
            flex: 1;
        }

        .listing-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
            margin-bottom: 4px;
        }

        .listing-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.5);
        }

        .listing-meta span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .listing-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent);
        }

        .listing-status {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .listing-status.available {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
        }

        .listing-status.pending {
            background: rgba(241, 196, 15, 0.15);
            color: #f1c40f;
        }

        .listing-status.contacted {
            background: rgba(52, 152, 219, 0.15);
            color: #3498db;
        }

        /* Activity Feed */
        .activity-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(149, 213, 178, 0.08);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(149, 213, 178, 0.1);
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .activity-icon svg {
            width: 16px;
            height: 16px;
            stroke: var(--accent);
            stroke-width: 2;
            fill: none;
        }

        .activity-content p {
            font-size: 0.85rem;
            color: var(--cream-100);
            margin-bottom: 2px;
        }

        .activity-content span {
            font-size: 0.75rem;
            color: rgba(254, 250, 224, 0.4);
        }

        /* === BROWSE LISTINGS SECTION === */
        .browse-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .toolbar-filters {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(149, 213, 178, 0.2);
            border-radius: 10px;
            color: rgba(254, 250, 224, 0.7);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: rgba(149, 213, 178, 0.15);
            border-color: var(--accent);
            color: var(--accent);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            border: none;
            border-radius: 12px;
            color: var(--green-900);
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(149, 213, 178, 0.4);
        }

        .btn-primary svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .listings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
        }

        .listing-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .listing-card:hover {
            transform: translateY(-8px);
            border-color: rgba(149, 213, 178, 0.3);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .listing-card-image {
            height: 180px;
            background: linear-gradient(135deg, var(--green-700), var(--green-800));
            position: relative;
            overflow: hidden;
        }

        .listing-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .listing-card-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .listing-card-actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 8px;
        }

        .listing-card-action {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(13, 40, 24, 0.8);
            backdrop-filter: blur(10px);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .listing-card-action:hover {
            background: var(--accent);
        }

        .listing-card-action svg {
            width: 16px;
            height: 16px;
            stroke: var(--cream-100);
            stroke-width: 2;
            fill: none;
        }

        .listing-card-action:hover svg {
            stroke: var(--green-900);
        }

        .listing-card-action.saved {
            background: var(--accent);
        }

        .listing-card-action.saved svg {
            stroke: var(--green-900);
            fill: var(--green-900);
        }

        .listing-card-content {
            padding: 20px;
        }

        .listing-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--cream-100);
            margin-bottom: 8px;
        }

        .listing-card-location {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: rgba(254, 250, 224, 0.6);
            margin-bottom: 12px;
        }

        .listing-card-location svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .listing-card-details {
            display: flex;
            gap: 20px;
            padding: 12px 0;
            border-top: 1px solid rgba(149, 213, 178, 0.1);
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
            margin-bottom: 12px;
        }

        .listing-card-detail {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.5);
        }

        .listing-card-detail strong {
            color: var(--cream-100);
            font-weight: 600;
        }

        .listing-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .listing-card-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent);
        }

        .listing-card-seller {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.5);
        }

        .listing-card-seller .seller-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: grid;
            place-items: center;
            font-size: 0.6rem;
            font-weight: 600;
            color: var(--green-900);
        }

        /* === SAVED PROPERTIES === */
        .saved-empty {
            text-align: center;
            padding: 60px 20px;
        }

        .saved-empty svg {
            width: 80px;
            height: 80px;
            stroke: rgba(149, 213, 178, 0.3);
            stroke-width: 1;
            fill: none;
            margin-bottom: 20px;
        }

        .saved-empty h3 {
            font-size: 1.2rem;
            color: var(--cream-100);
            margin-bottom: 8px;
        }

        .saved-empty p {
            color: rgba(254, 250, 224, 0.5);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        /* === MY INQUIRIES SECTION === */
        .inquiries-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .inquiry-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .inquiry-card:hover {
            border-color: rgba(149, 213, 178, 0.3);
        }

        .inquiry-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .inquiry-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .inquiry-user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green-700), var(--green-900));
            color: var(--cream-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            border: 1px solid rgba(149, 213, 178, 0.25);
        }

        .inquiry-user-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
            margin: 0;
        }

        .inquiry-user-info span {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.6);
        }

        .inquiry-property {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
            padding: 10px 12px;
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 10px;
            background: rgba(0, 0, 0, 0.18);
        }

        .inquiry-property-title {
            font-size: 0.9rem;
            color: rgba(254, 250, 224, 0.9);
            font-weight: 600;
        }

        .inquiry-property-detail {
            font-size: 0.85rem;
            color: var(--accent);
            font-weight: 600;
        }

        .inquiry-property-thumb {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--green-700), var(--green-800));
            overflow: hidden;
        }

        .inquiry-property-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .inquiry-property-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .inquiry-property-info span {
            font-size: 0.8rem;
            color: var(--accent);
        }

        .inquiry-status {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .inquiry-status.pending {
            background: rgba(241, 196, 15, 0.15);
            color: #f1c40f;
        }

        .inquiry-status.replied {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
        }

        .inquiry-status.viewed {
            background: rgba(52, 152, 219, 0.15);
            color: #3498db;
        }

        .inquiry-message {
            font-size: 0.9rem;
            color: rgba(254, 250, 224, 0.8);
            line-height: 1.6;
            padding: 12px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .inquiry-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .inquiry-date {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.5);
        }

        .inquiry-actions {
            display: flex;
            gap: 10px;
        }

        .inquiry-btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .inquiry-btn.view {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            border: none;
            color: var(--green-900);
        }

        .inquiry-btn.view:hover {
            box-shadow: 0 5px 15px rgba(149, 213, 178, 0.3);
        }

        .inquiry-btn.cancel {
            background: transparent;
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

        .inquiry-btn.cancel:hover {
            background: rgba(231, 76, 60, 0.1);
        }

        /* === MESSAGES SECTION === */
        .messages-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 25px;
            height: calc(100vh - 200px);
        }

        .messages-list {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 20px;
            overflow: hidden;
        }

        .messages-list-header {
            padding: 20px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .messages-list-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .messages-list-body {
            overflow-y: auto;
            max-height: calc(100% - 70px);
        }

        .message-item {
            display: flex;
            gap: 12px;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.08);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .message-item:hover,
        .message-item.active {
            background: rgba(149, 213, 178, 0.08);
        }

        .message-item.unread {
            background: rgba(149, 213, 178, 0.05);
        }

        .message-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: grid;
            place-items: center;
            font-weight: 600;
            color: var(--green-900);
            flex-shrink: 0;
        }

        .message-content {
            flex: 1;
            min-width: 0;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .message-sender {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .message-time {
            font-size: 0.75rem;
            color: rgba(254, 250, 224, 0.4);
        }

        .message-preview {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.6);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-unread-dot {
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
            margin-left: auto;
            flex-shrink: 0;
        }

        .message-chat {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .chat-user-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .chat-user-info span {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.5);
        }

        .chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .chat-message {
            max-width: 70%;
            margin-bottom: 15px;
        }

        .chat-message.received {
            margin-right: auto;
        }

        .chat-message.sent {
            margin-left: auto;
        }

        .chat-bubble {
            padding: 12px 18px;
            border-radius: 18px;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .chat-message.received .chat-bubble {
            background: rgba(255, 255, 255, 0.1);
            color: var(--cream-100);
            border-bottom-left-radius: 4px;
        }

        .chat-message.sent .chat-bubble {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: var(--green-900);
            border-bottom-right-radius: 4px;
        }

        .chat-time {
            font-size: 0.7rem;
            color: rgba(254, 250, 224, 0.4);
            margin-top: 5px;
        }

        .chat-message.sent .chat-time {
            text-align: right;
        }

        .chat-input {
            display: flex;
            gap: 12px;
            padding: 20px;
            border-top: 1px solid rgba(149, 213, 178, 0.1);
        }

        .chat-input input {
            flex: 1;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(149, 213, 178, 0.2);
            border-radius: 30px;
            color: var(--cream-100);
            font-size: 0.9rem;
            outline: none;
        }

        .chat-input input:focus {
            border-color: var(--accent);
        }

        .chat-send-btn {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            border: none;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(149, 213, 178, 0.4);
        }

        .chat-send-btn svg {
            width: 20px;
            height: 20px;
            stroke: var(--green-900);
            stroke-width: 2;
            fill: none;
        }

        /* === PROFILE SECTION === */
        .profile-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 25px;
        }

        .profile-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: grid;
            place-items: center;
            font-weight: 700;
            color: var(--green-900);
            font-size: 2.5rem;
            margin: 0 auto 20px;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .profile-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--cream-100);
            margin-bottom: 5px;
        }

        .profile-email {
            font-size: 0.9rem;
            color: rgba(254, 250, 224, 0.6);
            margin-bottom: 20px;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            padding-top: 20px;
            border-top: 1px solid rgba(149, 213, 178, 0.1);
        }

        .profile-stat {
            text-align: center;
        }

        .profile-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
        }

        .profile-stat-label {
            font-size: 0.75rem;
            color: rgba(254, 250, 224, 0.5);
        }

        .profile-settings {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(149, 213, 178, 0.15);
            border-radius: 20px;
        }

        .settings-section {
            padding: 24px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .settings-section:last-child {
            border-bottom: none;
        }

        .settings-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cream-100);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--cream-100);
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(149, 213, 178, 0.2);
            border-radius: 12px;
            color: var(--cream-100);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(149, 213, 178, 0.15);
        }

        .form-control::placeholder {
            color: rgba(254, 250, 224, 0.4);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .btn-secondary {
            padding: 12px 24px;
            background: transparent;
            border: 1px solid rgba(149, 213, 178, 0.3);
            border-radius: 12px;
            color: var(--cream-100);
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(149, 213, 178, 0.1);
            border-color: var(--accent);
        }

        .settings-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        /* === RESPONSIVE === */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .profile-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1024px) {
            .messages-container {
                grid-template-columns: 1fr;
            }

            .messages-list {
                max-height: 400px;
            }

            .message-chat {
                height: 500px;
            }
        }

        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .mobile-topbar {
                display: block;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .top-actions {
                width: 100%;
                justify-content: space-between;
            }

            .browse-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-filters {
                overflow-x: auto;
                padding-bottom: 10px;
            }
        }

        @media (min-width: 992px) {
            .sidebar {
                transform: translateX(0) !important;
                visibility: visible !important;
            }

            .main-content {
                margin-left: var(--sidebar-width) !important;
            }

            .mobile-topbar {
                display: none !important;
            }
        }

        .mobile-menu-btn {
            display: grid;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(149, 213, 178, 0.2);
            place-items: center;
            cursor: pointer;
        }

        .mobile-menu-btn svg {
            width: 24px;
            height: 24px;
            stroke: var(--cream-100);
            stroke-width: 2;
            fill: none;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            stroke: rgba(149, 213, 178, 0.3);
            stroke-width: 1;
            fill: none;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            color: var(--cream-100);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: rgba(254, 250, 224, 0.5);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        /* === PROPERTY DETAIL MODAL === */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            z-index: 5000;
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .property-modal {
            background: radial-gradient(circle at top left, #0d2818 0%, #fefae0 100%);
            color: #123a25;
            border: 1px solid rgba(149, 213, 178, 0.4);
            border-radius: 12px;
            position: relative;
            width: 100%;
            max-width: min(96vw, 1500px);
            max-height: none;
            overflow: visible;
            display: flex;
            flex-direction: column;
            animation: modalSlideIn 0.4s ease;
            box-shadow: 0 18px 36px rgba(12, 42, 28, 0.28);
            margin: 8px auto 24px;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 18px;
            border-bottom: 1px dotted rgba(54, 92, 71, 0.35);
            background: rgba(254, 250, 224, 0.55);
            backdrop-filter: blur(4px);
        }

        .modal-header h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 1.45rem;
            font-weight: 700;
            color: #1b3f28;
            margin: 0;
        }

        .modal-header .modal-price-section {
            text-align: right;
        }

        .modal-price {
            font-size: 1.42rem;
            color: #1b3f28;
            font-weight: 700;
            display: inline-block;
            margin-bottom: 2px;
        }

        .modal-price-sqm {
            font-size: 0.82rem;
            color: rgba(13, 40, 24, 0.68);
            display: block;
        }

        .modal-close {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.3);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(231, 76, 60, 0.3);
        }

        .modal-close svg {
            width: 20px;
            height: 20px;
            stroke: #e74c3c;
            stroke-width: 2;
            fill: none;
        }

        .modal-report-btn {
            position: absolute;
            top: 15px;
            right: 55px;
            background: none;
            border: none;
            color: #d2b48c;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 20;
            transition: color 0.3s ease;
        }

        .modal-report-btn:hover {
            color: #e74c3c;
        }

        .modal-body {
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }

        .modal-gallery {
            padding: 18px;
            border-right: 1px solid rgba(150, 196, 160, 0.35);
            background: rgba(254, 250, 224, 0.28);
        }

        .modal-content {
            background: rgba(254, 250, 224, 0.95);
            color: #113822;
        }

        .modal-gallery {
            padding: 24px;
            border-right: 1px solid rgba(149, 213, 178, 0.1);
            min-width: 0;
        }

        .gallery-main {
            width: 100%;
            height: 280px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 15px;
            background: var(--green-700);
        }

        .gallery-main img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gallery-thumbs {
            display: flex;
            gap: 10px;
        }

        .gallery-thumb {
            width: 70px;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .gallery-thumb:hover,
        .gallery-thumb.active {
            border-color: var(--accent);
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .modal-map {
            width: 100%;
            max-width: 100%;
            display: block;
            height: 200px;
            border-radius: 16px;
            overflow: hidden;
            margin-top: 0;
            background: var(--green-700);
            position: relative;
            border: none;
        }

        .modal-map iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .modal-content {
            flex: 1;
            min-height: 0;
            overflow: visible;
            padding: 24px;
            min-width: 0;
        }

        .modal-content::-webkit-scrollbar {
            width: 10px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background: rgba(149, 213, 178, 0.5);
            border-radius: 10px;
            border: 2px solid rgba(0, 0, 0, 0.2);
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background: rgba(149, 213, 178, 0.75);
        }

        .map-expand-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 8px 12px;
            background: rgba(13, 40, 24, 0.9);
            border: 1px solid rgba(149, 213, 178, 0.3);
            border-radius: 8px;
            color: var(--cream-100);
            font-size: 0.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .map-expand-btn:hover {
            background: var(--green-700);
        }

        .map-expand-btn svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .modal-details {
            padding: 24px;
            overflow-y: auto;
        }

        .detail-section {
            margin-bottom: 24px;
        }

        .detail-section:last-child {
            margin-bottom: 0;
        }

        .detail-section-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--accent);
            margin-bottom: 12px;
            font-weight: 600;
        }

        .detail-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .detail-price-sqm {
            font-size: 0.9rem;
            color: rgba(254, 250, 224, 0.6);
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .detail-item {
            padding: 12px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .detail-item-label {
            font-size: 0.75rem;
            color: rgba(254, 250, 224, 0.5);
            margin-bottom: 4px;
        }

        .detail-item-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .modal-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .modal-quick-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin: 0 0 14px 0;
            padding: 10px;
            border-radius: 10px;
            background: rgba(13, 40, 24, 0.09);
            border: 1px solid rgba(149, 213, 178, 0.4);
        }

        .quick-item {
            font-size: 0.85rem;
            color: #143f2b;
        }

        .quick-item strong {
            color: #0b3a27;
        }

        .quick-value {
            font-weight: 600;
        }

        .detail-label {
            display: block;
            font-size: 0.75rem;
            color: rgba(254, 250, 224, 0.55);
            margin-bottom: 5px;
        }

        .detail-value {
            display: block;
            font-size: 0.9rem;
            color: var(--cream-100);
            font-weight: 600;
        }

        .listing-details-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 10px 16px;
            margin-top: 8px;
        }

        .listing-details-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.18);
            border: 1px solid rgba(149, 213, 178, 0.08);
        }

        .listing-details-row span {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.6);
        }

        .listing-details-row strong {
            font-size: 0.82rem;
            color: var(--cream-100);
            text-align: right;
        }

        .modal-map-container {
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            border: 1px solid rgba(149, 213, 178, 0.2);
            width: 100%;
            max-width: 100%;
            display: block;
            min-height: 320px;
        }

        .expand-map-btn {
            position: absolute;
            right: 12px;
            bottom: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border: none;
            border-radius: 9px;
            background: rgba(13, 40, 24, 0.9);
            color: var(--cream-100);
            font-size: 0.78rem;
            cursor: pointer;
        }

        .expand-map-btn svg {
            width: 14px;
            height: 14px;
        }

        .detail-description {
            font-size: 0.9rem;
            line-height: 1.7;
            color: rgba(254, 250, 224, 0.8);
        }

        .detail-features {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .modal-main-image {
            width: 100%;
            height: 280px;
            border-radius: 16px;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(149, 213, 178, 0.15);
        }

        .modal-thumbnails {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .modal-thumb {
            width: 100%;
            height: 56px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .modal-thumb.active {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(149, 213, 178, 0.25);
        }

        .modal-title {
            line-height: 1.25;
        }

        .modal-price {
            display: block;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--accent);
        }

        .modal-price-sqm {
            display: block;
            margin-top: 2px;
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.65);
        }

        .modal-location {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(149, 213, 178, 0.08);
            border: 1px solid rgba(149, 213, 178, 0.22);
            color: rgba(254, 250, 224, 0.92);
            transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .modal-location:hover {
            transform: translateY(-1px);
            border-color: rgba(149, 213, 178, 0.5);
            background: rgba(149, 213, 178, 0.14);
        }

        .modal-location svg {
            width: 16px;
            height: 16px;
            fill: rgba(149, 213, 178, 0.2);
            stroke: var(--accent);
            stroke-width: 1.7;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .modal-location:hover svg {
            transform: scale(1.07);
        }

        .features-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .feature-tag {
            padding: 6px 12px;
            background: rgba(149, 213, 178, 0.1);
            border: 1px solid rgba(149, 213, 178, 0.2);
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--accent);
        }

        .seller-card {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px;
            background: rgba(0, 0, 0, 0.22);
            border-radius: 14px;
            border: 1px solid rgba(149, 213, 178, 0.14);
        }

        .seller-card-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            display: grid;
            place-items: center;
            font-weight: 600;
            color: var(--green-900);
        }

        .seller-card-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .seller-card-name {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 5px;
        }

        .seller-card-info span {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.5);
        }

        .seller-card-meta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: rgba(254, 250, 224, 0.7);
            font-size: 0.78rem;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: rgba(149, 213, 178, 0.2);
            border: 1px solid rgba(149, 213, 178, 0.4);
            border-radius: 12px;
            font-size: 0.65rem;
            color: var(--accent);
            letter-spacing: 0.2px;
        }

        .verified-badge svg {
            width: 12px;
            height: 12px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .seller-card-actions {
            margin-left: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 176px;
        }

        .btn-contact-seller,
        .btn-save-property {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
        }

        .btn-contact-seller svg,
        .btn-save-property svg {
            width: 16px;
            height: 16px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
            flex-shrink: 0;
        }

        .btn-contact-seller {
            background: linear-gradient(135deg, var(--green-600), var(--green-700));
            color: var(--cream-100);
            border-color: rgba(149, 213, 178, 0.35);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .btn-contact-seller:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.26);
        }

        .btn-save-property {
            background: rgba(149, 213, 178, 0.08);
            color: var(--cream-100);
            border-color: rgba(149, 213, 178, 0.35);
        }

        .btn-save-property:hover {
            transform: translateY(-1px);
            background: rgba(149, 213, 178, 0.14);
        }

        .btn-save-property.saved {
            background: rgba(149, 213, 178, 0.22);
            color: var(--accent);
            border-color: rgba(149, 213, 178, 0.7);
            box-shadow: 0 0 0 2px rgba(149, 213, 178, 0.15);
        }

        .btn-save-property.saved svg {
            fill: currentColor;
        }

        .seller-verified {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            background: rgba(46, 204, 113, 0.15);
            border-radius: 10px;
            font-size: 0.7rem;
            color: #2ecc71;
            margin-left: 8px;
        }

        .modal-footer {
            display: flex;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid rgba(149, 213, 178, 0.1);
        }

        .modal-footer .btn-primary,
        .modal-footer .btn-secondary {
            flex: 1;
            justify-content: center;
        }

        /* Full Map Modal */
        .map-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 7600;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .map-modal-overlay.active {
            display: flex;
        }

        .map-modal,
        .full-map-modal {
            width: 100%;
            max-width: min(95vw, 1400px);
            height: 84vh;
            background: var(--green-900);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(149, 213, 178, 0.2);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .map-modal-header,
        .full-map-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: var(--green-800);
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
            flex: 0 0 auto;
        }

        .map-modal-header h3,
        .full-map-header h3 {
            font-size: 1rem;
            color: var(--cream-100);
        }

        .map-modal-body {
            height: calc(100% - 60px);
            position: relative;
            flex: 1 1 auto;
            min-height: 0;
        }

        .full-map-canvas {
            width: 100%;
            height: 100%;
            border: none;
            background: var(--green-900);
            flex: 1 1 auto;
            min-height: 0;
        }

        .map-loading-state {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(13, 40, 24, 0.92);
            color: var(--cream-100);
            font-size: 0.95rem;
            letter-spacing: 0.02em;
            z-index: 2;
        }

        .map-loading-state.visible {
            display: flex;
        }

        /* Unified SweetAlert theme aligned with system Auth style */
        .swal2-popup.landly-swal {
            background: var(--green-900);
            color: var(--cream-100);
            border: 1px solid rgba(149, 213, 178, 0.35);
            border-radius: 18px;
            box-shadow: 0 20px 45px rgba(5, 18, 18, 0.45);
        }

        .swal2-popup.landly-swal .swal2-title,
        .swal2-popup.landly-swal .swal2-html-container {
            color: var(--cream-100);
        }

        .swal2-popup.landly-swal .swal2-loader {
            border-color: var(--accent) transparent var(--accent) transparent;
        }

        .swal2-popup.landly-swal .swal2-confirm {
            background: linear-gradient(135deg, var(--green-700) 0%, var(--green-800) 100%);
            color: var(--cream-100);
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(15, 27, 27, 0.25);
        }

        .swal2-popup.landly-swal .swal2-cancel {
            background: rgba(245, 240, 214, 0.14);
            color: var(--cream-100);
            border: 1px solid rgba(245, 240, 214, 0.25);
            border-radius: 10px;
        }

        .swal2-popup.landly-swal .swal2-confirm:focus,
        .swal2-popup.landly-swal .swal2-cancel:focus {
            box-shadow: 0 0 0 3px rgba(149, 213, 178, 0.35);
        }

        @keyframes landlyFadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes landlyFadeOutDown {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateY(12px) scale(0.98);
            }
        }

        .landly-swal-show {
            animation: landlyFadeInUp 0.28s ease-out;
        }

        .landly-swal-hide {
            animation: landlyFadeOutDown 0.2s ease-in;
        }

        @media (max-width: 768px) {
            .modal-body {
                grid-template-columns: 1fr;
            }

            .modal-gallery {
                border-right: none;
                border-bottom: 1px solid rgba(149, 213, 178, 0.1);
            }

            .gallery-main {
                height: 200px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .modal-footer {
                flex-direction: column;
            }

            .main-content {
                margin-left: 0;
                padding: 14px;
            }

            .top-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .top-actions {
                width: 100%;
                justify-content: space-between;
            }

            .search-box {
                width: 100%;
            }

            .content-section {
                padding: 0;
            }

            .user-profile {
                padding: 12px 10px;
            }

            .chatbot {
                width: 260px;
                right: 12px;
                bottom: 12px;
            }

            .chatbot-content {
                height: 320px;
            }
        }

        .chatbot {
            position: fixed;
            right: 20px;
            bottom: 20px;
            width: 76px;
            max-height: 480px;
            z-index: 2000;
            font-family: "Inter", system-ui, sans-serif;
            transition: width 0.24s ease;
            overflow: visible;
        }

        .swal2-container {
            z-index: 7000 !important;
        }

        .property-loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 6800;
            display: none;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        .property-loading-overlay.active {
            display: flex;
        }

        .property-loading-card {
            background: #fefae0;
            color: #123a25;
            border: 1px solid rgba(149, 213, 178, 0.65);
            border-radius: 14px;
            padding: 14px 18px;
            min-width: 220px;
            font-size: 0.92rem;
            font-weight: 600;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.28);
            text-align: center;
        }

        body.property-modal-loading .listing-card,
        body.property-modal-loading .chatbot-listing-card {
            pointer-events: none !important;
            opacity: 0.82;
        }

        .chatbot.open {
            width: 320px;
        }

        .chatbot-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--cream-100);
            border: 1px solid var(--accent);
            border-radius: 18px;
            padding: 10px 12px;
            color: var(--green-900);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            cursor: pointer;
            gap: 10px;
        }

        .chatbot-header {
            display: flex;
            align-items: center;
            gap: 10px;
            transform-origin: left;
        }

        .chatbot.open .chatbot-header {
            opacity: 1;
            visibility: visible;
        }

        .chatbot:not(.open) .chatbot-title {
            display: none;
        }

        .chatbot-header {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chatbot-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--green-900);
            font-size: 1.1rem;
            box-shadow: inset 0 0 0 1px rgba(0,0,0,.08);
        }

        .chatbot-title {
            display: flex;
            flex-direction: column;
            font-size: 0.84rem;
            line-height: 1.1;
            color: var(--green-900);
        }

        .chatbot-title strong {
            font-size: 0.93rem;
            line-height: 1.1;
        }

        .chatbot-title span {
            font-size: 0.74rem;
            color: rgba(13, 40, 24, 0.8);
        }

        .chatbot-toggle h4 {
            margin: 0;
            font-size: 0.93rem;
            display: none;
        }

        .chatbot-close {
            display: none;
            background: transparent;
            border: none;
            font-size: 1rem;
            color: var(--green-900);
            cursor: pointer;
        }

        .chatbot.open .chatbot-close {
            display: inline-flex;
        }

        .chatbot-footer {
            display: block;
            text-align: center;
            font-size: 0.72rem;
            color: rgba(13, 40, 24, 0.75);
            padding: 6px 0 5px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.6px;
        }

        .chatbot-content {
            display: none;
            flex-direction: column;
            background: var(--cream-100);
            border: 1px solid var(--accent);
            border-radius: 18px;
            margin-top: 8px;
            overflow: hidden;
            height: 400px;
            box-shadow: 0 10px 22px rgba(0,0,0,0.2);
        }

        .chatbot-content.active {
            display: flex;
        }

        .chatbot-messages {
            flex: 1;
            padding: 10px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #faf4df;
        }

        .chatbot-message {
            padding: 9px 11px;
            border-radius: 12px;
            max-width: 80%;
            line-height: 1.3;
        }

        .chatbot-message.user {
            background: #d2b48c;
            color: #0f1b1b;
            align-self: flex-end;
        }

        .chatbot-message.bot {
            background: #ece4d4;
            color: #1c3e3a;
            align-self: flex-start;
        }

        .chatbot-input-wrap {
            display: flex;
            border-top: 1px solid rgba(0,0,0,.12);
        }

        .chatbot-input {
            flex: 1;
            border: none;
            padding: 10px;
            font-size: 0.9rem;
            outline: none;
            background: #fbf7ec;
            color: #1f3f3a;
        }

        .chatbot-send {
            background: var(--green-700);
            color: var(--cream-100);
            border: none;
            padding: 10px 12px;
            cursor: pointer;
            font-weight: 600;
        }

        .chatbot-send:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .chatbot-listing-card {
            margin: 6px 0;
            padding: 8px;
            background: #fbf7ec;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid rgba(0,0,0,.1);
            transition: all 0.2s ease;
        }

        .chatbot-listing-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,.15);
            transform: translateY(-2px);
        }

        .chatbot-listing-card img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            margin-bottom: 6px;
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar offcanvas offcanvas-lg offcanvas-start" id="buyerSidebar" tabindex="-1" aria-labelledby="buyerSidebarLabel">
            <div class="offcanvas-header d-lg-none">
                <a href="<?= base_url('/') ?>" class="brand" id="buyerSidebarLabel">
                    <div class="brand-badge"><img src="<?= base_url('Logo.jpg') ?>" alt="Landly" class="brand-logo"></div>
                    <div class="brand-subtitle">Buyer Portal</div>
                </a>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" data-bs-target="#buyerSidebar" aria-label="Close"></button>
            </div>

            <div class="sidebar-header d-none d-lg-block">
                <a href="<?= base_url('/') ?>" class="brand">
                    <div class="brand-badge"><img src="<?= base_url('Logo.jpg') ?>" alt="Landly" class="brand-logo"></div>
                    <div class="brand-subtitle">Buyer Portal</div>
                </a>
            </div>

            <div class="offcanvas-body p-0 d-flex flex-column">
                <div class="user-profile">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php if (! empty($userProfile['avatar_url'])): ?>
                                <img src="<?= esc((string) $userProfile['avatar_url']) ?>" alt="<?= esc((string) $userProfile['full_name']) ?>">
                            <?php else: ?>
                                <?= esc((string) ($userProfile['initials'] ?? 'NA')) ?>
                            <?php endif; ?>
                        </div>
                        <div class="user-details">
                            <h4><?= esc((string) ($userProfile['full_name'] ?? 'Buyer')) ?></h4>
                            <span><?= esc((string) ($userProfile['email'] ?? 'N/A')) ?></span>
                            <br>
                            <div class="member-badge <?= esc((string) ($userProfile['status_class'] ?? 'inactive')) ?>">
                                <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" fill="none" stroke-width="2">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                                
                                <?= esc((string) ($userProfile['status_label'] ?? 'Inactive Buyer')) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="#" class="nav-item active" data-section="browse">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <span>Browse Listings</span>
                    </a>
                    <a href="#" class="nav-item" data-section="saved">
                        <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span>Saved Properties</span>
                        <span class="nav-badge" id="buyer-nav-saved-count"><?= (int) ($sidebarCounts['saved_properties'] ?? 0) ?></span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Communication</div>
                    <a href="#" class="nav-item" data-section="inquiries">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <span>My Inquiries</span>
                        <span class="nav-badge" id="buyer-nav-inquiries-count"><?= (int) ($sidebarCounts['accepted_inquiries'] ?? 0) ?></span>
                    </a>
                    <a href="#" class="nav-item" data-section="messages">
                        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <span>Messages</span>
                        <span class="nav-badge" id="buyer-nav-messages-count"><?= (int) ($sidebarCounts['unread_messages'] ?? 0) ?></span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="#" class="nav-item" data-section="profile">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        <span>My Profile</span>
                    </a>
                </div>
                </nav>

                <div class="sidebar-footer">
                    <button class="logout-btn" id="buyerLogoutBtn" type="button">
                        <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Logout
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <nav class="mobile-topbar navbar d-lg-none">
                <div class="container-fluid px-0">
                    <button class="mobile-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#buyerSidebar" aria-controls="buyerSidebar" aria-label="Open menu">
                        <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <a href="<?= base_url('/') ?>" class="mobile-topbar-brand">
                        Landly
                        <span>Buyer Portal</span>
                    </a>
                </div>
            </nav>

            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1 id="page-heading">Browse Listings</h1>
                    <p id="page-subheading">Find the perfect property from our curated listings.</p>
                </div>
                <div class="top-actions">
                    <div class="notification-wrapper">
                        <button class="notification-btn" id="header-notification-btn" type="button" aria-label="Open notifications" aria-expanded="false">
                            <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            <span class="notification-dot" id="header-notification-dot" hidden></span>
                            <span class="notification-count" id="header-notification-count" hidden>0</span>
                        </button>
                        <div class="notification-dropdown" id="header-notification-dropdown" hidden>
                            <div class="notification-dropdown-header">
                                <p class="notification-dropdown-title">Notifications</p>
                                <button class="notification-action-btn" id="header-notification-read-all" type="button">Mark all as read</button>
                            </div>
                            <div class="notification-list" id="header-notification-list">
                                <div class="notification-loading">Loading notifications...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Browse Listings Section -->
            <?= view('Pages/Buyer/Components/BrowseListingSection') ?>

            <!-- Saved Properties Section -->
            <?= view('Pages/Buyer/Components/SavedPropertiesSection') ?>

            <!-- My Inquiries Section -->
            <?= view('Pages/Buyer/Components/InquiriesSection', ['buyerInquiries' => $buyerInquiries ?? []]) ?>

            <!-- Messages Section -->
            <?= view('Pages/Buyer/Components/MessageSection') ?>

            <!-- Profile Section -->
            <?= view('Pages/Buyer/Components/ProfileSection') ?>
        </main>
    </div>

    <div class="chatbot" id="buyerChatbot">
        <div class="chatbot-toggle" id="buyerChatbotToggle" role="button" aria-label="Open chat with LandlyBot">
            <div class="chatbot-header">
                <div class="chatbot-icon" aria-hidden="true">🤖</div>
                <div class="chatbot-title">
                    <strong>LandlyBot</strong>
                    <span>LandlyBot assistant</span>
                </div>
            </div>
            <button class="chatbot-close" id="buyerChatbotClose" aria-label="Close chat">✕</button>
        </div>
        <div class="chatbot-content" id="buyerChatbotContent" aria-live="polite" aria-atomic="true">
            <div class="chatbot-messages" id="buyerChatbotMessages">
                <div class="chatbot-message bot">Hello <?= esc($buyerFirstName) ?>! I am LandlyBot. Need help finding land listings or tracking your inquiry?</div>
            </div>
            <div class="chatbot-input-wrap">
                <input type="text" id="buyerChatbotInput" class="chatbot-input" placeholder="Type your message..." aria-label="Type your message" />
                <button class="chatbot-send" id="buyerChatbotSend">Send</button>
            </div>
        </div>
        <div class="chatbot-footer">LandlyBot</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>
        // Navigation functionality
        const navItems = document.querySelectorAll('.nav-item[data-section]');
        const sections = document.querySelectorAll('.content-section');
        const buyerSidebarElement = document.getElementById('buyerSidebar');
        const buyerSidebarOffcanvas = (buyerSidebarElement && window.bootstrap && window.bootstrap.Offcanvas)
            ? window.bootstrap.Offcanvas.getOrCreateInstance(buyerSidebarElement)
            : null;
        const pageHeading = document.getElementById('page-heading');
        const pageSubheading = document.getElementById('page-subheading');
        const BUYER_SECTION_STORAGE_KEY = 'buyerDashboardActiveSection';
        const notificationBtn = document.getElementById('header-notification-btn');
        const notificationDropdown = document.getElementById('header-notification-dropdown');
        const notificationList = document.getElementById('header-notification-list');
        const notificationReadAllBtn = document.getElementById('header-notification-read-all');
        const notificationDot = document.getElementById('header-notification-dot');
        const notificationCount = document.getElementById('header-notification-count');
        const notificationApiBase = '<?= base_url('notifications') ?>';
        const buyerSidebarCountsApi = '<?= base_url('buyer/sidebar-counts') ?>';
        const sessionExpiredRedirectUrl = <?= json_encode(base_url('auth'), $jsonFlags) ?>;
        let hasHandledSessionExpiry = false;

        function hasSwalReady() {
            return Boolean(window.Swal && typeof window.Swal.fire === 'function');
        }

        async function handleSessionExpired() {
            if (hasHandledSessionExpiry) {
                return;
            }

            hasHandledSessionExpiry = true;

            const message = 'Your session has expired due to inactivity. Please sign in again to continue.';

            if (hasSwalReady()) {
                await window.Swal.fire({
                    icon: 'warning',
                    title: 'Session Expired',
                    text: message,
                    confirmButtonText: 'Go to Sign In',
                    allowOutsideClick: false,
                    allowEscapeKey: false
                });
            } else {
                window.alert(message);
            }

            window.location.href = sessionExpiredRedirectUrl;
        }

        const nativeFetch = window.fetch.bind(window);
        window.fetch = async (...args) => {
            const response = await nativeFetch(...args);
            if (response && response.status === 401) {
                handleSessionExpired();
            }

            return response;
        };

        const sectionInfo = {
            'browse': {
                title: 'Browse Listings',
                subtitle: 'Find the perfect property from our curated listings.'
            },
            'saved': {
                title: 'Saved Properties',
                subtitle: 'Your favorite properties saved for later.'
            },
            'inquiries': {
                title: 'My Inquiries',
                subtitle: 'Track your property inquiries and seller responses.'
            },
            'messages': {
                title: 'Messages',
                subtitle: 'Communicate with property sellers.'
            },
            'profile': {
                title: 'My Profile',
                subtitle: 'Manage your account and preferences.'
            }
        };

        const notificationState = {
            items: [],
            loading: false,
        };

        const notificationSyncState = {
            latestNotificationId: 0,
            unreadCount: 0,
        };

        const buyerRefreshState = {
            inFlight: false,
            lastRefreshAt: 0,
            minIntervalMs: 2500,
        };

        function formatNotificationType(type) {
            return String(type || 'notification').replaceAll('_', ' ');
        }

        function formatNotificationDate(dateValue) {
            if (!dateValue) return 'Just now';
            const date = new Date(dateValue.replace(' ', 'T'));
            if (Number.isNaN(date.getTime())) return String(dateValue);
            return date.toLocaleString();
        }

        function setUnreadIndicator(unreadCount) {
            const hasUnread = unreadCount > 0;
            notificationDot.hidden = !hasUnread;
            notificationCount.hidden = !hasUnread;
            notificationCount.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
        }

        function renderNotifications() {
            if (notificationState.loading) {
                notificationList.innerHTML = '<div class="notification-loading">Loading notifications...</div>';
                return;
            }

            if (!notificationState.items.length) {
                notificationList.innerHTML = '<div class="notification-empty">No notifications yet.</div>';
                setUnreadIndicator(0);
                return;
            }

            const unreadCount = notificationState.items.filter((item) => !Number(item.is_read)).length;
            setUnreadIndicator(unreadCount);

            notificationList.innerHTML = notificationState.items.map((item) => {
                const notificationId = Number(item.notification_id || 0);
                const unreadClass = Number(item.is_read) ? '' : 'unread';
                const detailParts = [];

                if (item.listing_id) detailParts.push(`Listing ID: ${item.listing_id}`);
                if (item.inquiry_id) detailParts.push(`Inquiry ID: ${item.inquiry_id}`);
                if (item.message_id) detailParts.push(`Message ID: ${item.message_id}`);

                return `
                    <div class="notification-item ${unreadClass}" data-id="${notificationId}" data-read="${Number(item.is_read) ? '1' : '0'}">
                        <div class="notification-item-main">
                            <p class="notification-message">${String(item.message || 'You have a new notification.')}</p>
                        </div>
                        <div class="notification-meta">
                            <span class="notification-type-pill">${formatNotificationType(item.notification_type)}</span>
                            <span>${formatNotificationDate(item.created_at)}</span>
                        </div>
                        <div class="notification-detail">${detailParts.length ? detailParts.join('<br>') : 'No additional details.'}</div>
                    </div>
                `;
            }).join('');
        }

        async function fetchNotifications() {
            notificationState.loading = true;
            renderNotifications();

            try {
                const response = await fetch(`${notificationApiBase}/`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                notificationState.items = Array.isArray(data.notifications) ? data.notifications : [];
                notificationSyncState.latestNotificationId = Number(notificationState.items[0]?.notification_id || 0);
                notificationSyncState.unreadCount = notificationState.items.filter((item) => !Number(item.is_read)).length;
            } catch (error) {
                notificationState.items = [];
                notificationSyncState.latestNotificationId = 0;
                notificationSyncState.unreadCount = 0;
            } finally {
                notificationState.loading = false;
                renderNotifications();
            }
        }

        async function checkNotificationChanges() {
            try {
                const lastNotificationId = encodeURIComponent(String(notificationSyncState.latestNotificationId || 0));
                const lastUnreadCount = encodeURIComponent(String(notificationSyncState.unreadCount ?? -1));
                const response = await fetch(`${notificationApiBase}/changes?last_notification_id=${lastNotificationId}&last_unread_count=${lastUnreadCount}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (!response.ok || data.status !== 'success') {
                    return false;
                }

                setUnreadIndicator(Number(data.unread_count || 0));
                notificationSyncState.latestNotificationId = Number(data.latest_notification_id || 0);
                notificationSyncState.unreadCount = Number(data.unread_count || 0);

                return Boolean(data.has_updates);
            } catch (error) {
                return false;
            }
        }

        function applyBuyerSidebarCounts(counts) {
            const savedCount = Number(counts.saved_properties || 0);
            const inquiriesCount = Number(counts.accepted_inquiries || 0);
            const messagesCount = Number(counts.unread_messages || 0);

            const savedBadge = document.getElementById('buyer-nav-saved-count');
            const inquiriesBadge = document.getElementById('buyer-nav-inquiries-count');
            const messagesBadge = document.getElementById('buyer-nav-messages-count');
            const savedSectionCount = document.getElementById('favorites-count');

            if (savedBadge) savedBadge.textContent = String(savedCount);
            if (inquiriesBadge) inquiriesBadge.textContent = String(inquiriesCount);
            if (messagesBadge) messagesBadge.textContent = String(messagesCount);
            if (savedSectionCount) savedSectionCount.textContent = String(savedCount);
        }

        async function pollNotificationsRealtime() {
            if (document.hidden) {
                return;
            }

            const hasUpdates = await checkNotificationChanges();
            if (!hasUpdates) {
                return;
            }

            await refreshBuyerSidebarCounts();

            if (notificationDropdown && !notificationDropdown.hidden) {
                await fetchNotifications();
                return;
            }
        }

        async function markNotificationRead(notificationId) {
            try {
                await fetch(`${notificationApiBase}/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            } catch (error) {
            }
        }

        async function markAllNotificationsRead() {
            try {
                await fetch(`${notificationApiBase}/read-all`, {
                    method: 'PATCH',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                notificationState.items = notificationState.items.map((item) => ({ ...item, is_read: 1 }));
                renderNotifications();
            } catch (error) {
            }
        }

        async function refreshBuyerSidebarCounts() {
            try {
                const response = await fetch(buyerSidebarCountsApi, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();
                if (!response.ok || data.status !== 'success' || !data.counts) {
                    return;
                }

                const savedCount = Number(data.counts.saved_properties || 0);
                const inquiriesCount = Number(data.counts.accepted_inquiries || 0);
                const messagesCount = Number(data.counts.unread_messages || 0);

                const savedBadge = document.getElementById('buyer-nav-saved-count');
                const inquiriesBadge = document.getElementById('buyer-nav-inquiries-count');
                const messagesBadge = document.getElementById('buyer-nav-messages-count');
                const savedSectionCount = document.getElementById('favorites-count');

                if (savedBadge) savedBadge.textContent = String(savedCount);
                if (inquiriesBadge) inquiriesBadge.textContent = String(inquiriesCount);
                if (messagesBadge) messagesBadge.textContent = String(messagesCount);
                if (savedSectionCount) savedSectionCount.textContent = String(savedCount);
            } catch (error) {
            }
        }

        function openNotificationsDropdown() {
            notificationDropdown.hidden = false;
            notificationBtn.setAttribute('aria-expanded', 'true');
        }

        function closeNotificationsDropdown() {
            notificationDropdown.hidden = true;
            notificationBtn.setAttribute('aria-expanded', 'false');
        }

        notificationBtn?.addEventListener('click', async (event) => {
            event.stopPropagation();
            const isHidden = notificationDropdown.hidden;
            if (isHidden) {
                openNotificationsDropdown();
                await fetchNotifications();
                return;
            }

            closeNotificationsDropdown();
        });

        notificationReadAllBtn?.addEventListener('click', async (event) => {
            event.stopPropagation();
            await markAllNotificationsRead();
        });

        notificationList?.addEventListener('click', async (event) => {
            const item = event.target.closest('.notification-item');
            if (!item) return;

            item.classList.toggle('expanded');

            if (item.dataset.read === '0') {
                item.dataset.read = '1';
                item.classList.remove('unread');
                const notificationId = Number(item.dataset.id || 0);
                if (notificationId > 0) {
                    await markNotificationRead(notificationId);
                    const target = notificationState.items.find((stateItem) => Number(stateItem.notification_id) === notificationId);
                    if (target) target.is_read = 1;
                    renderNotifications();
                }
            }
        });

        document.addEventListener('click', (event) => {
            if (!notificationDropdown || notificationDropdown.hidden) {
                return;
            }

            if (!event.target.closest('.notification-wrapper')) {
                closeNotificationsDropdown();
            }
        });

        async function refreshRealtimeOnBuyerSectionChange(sectionName) {
            if (buyerRefreshState.inFlight) {
                return;
            }

            const now = Date.now();
            if ((now - buyerRefreshState.lastRefreshAt) < buyerRefreshState.minIntervalMs) {
                return;
            }

            buyerRefreshState.inFlight = true;
            try {
                await refreshBuyerSidebarCounts();

                if (sectionName === 'messages' || sectionName === 'inquiries') {
                    await checkNotificationChanges();
                }

                if (notificationDropdown && !notificationDropdown.hidden) {
                    await fetchNotifications();
                }

                buyerRefreshState.lastRefreshAt = Date.now();
            } finally {
                buyerRefreshState.inFlight = false;
            }
        }

        fetchNotifications();
        refreshBuyerSidebarCounts();

        function normalizeBuyerSectionName(value) {
            return String(value || '').replace('#', '').trim().toLowerCase();
        }

        function getActiveBuyerSectionFromNav() {
            const activeNavItem = document.querySelector('.nav-item.active[data-section]');
            return normalizeBuyerSectionName(activeNavItem?.dataset?.section || '');
        }

        function initializeBuyerResponsiveContent() {
            document.querySelectorAll('.content-section table').forEach((tableElement) => {
                if (tableElement.closest('.table-responsive')) {
                    return;
                }

                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                tableElement.parentNode.insertBefore(wrapper, tableElement);
                wrapper.appendChild(tableElement);
            });
        }

        initializeBuyerResponsiveContent();

        function closeBuyerSidebarOnMobile() {
            const isMobileViewport = window.matchMedia('(max-width: 991.98px)').matches;
            const isSidebarOpen = buyerSidebarElement?.classList.contains('show');
            const activeInstance = buyerSidebarOffcanvas
                || (window.bootstrap?.Offcanvas ? window.bootstrap.Offcanvas.getInstance(buyerSidebarElement) : null);

            if (!isMobileViewport || !isSidebarOpen || !activeInstance) {
                return;
            }

            activeInstance.hide();
        }

        function syncBuyerSidebarForViewport() {
            if (!buyerSidebarElement) {
                return;
            }

            const isDesktopViewport = window.matchMedia('(min-width: 992px)').matches;
            if (!isDesktopViewport) {
                return;
            }

            buyerSidebarElement.classList.remove('show', 'hiding');
            buyerSidebarElement.removeAttribute('aria-modal');
            buyerSidebarElement.removeAttribute('role');
            buyerSidebarElement.style.removeProperty('visibility');
            buyerSidebarElement.style.removeProperty('transform');
            buyerSidebarElement.style.removeProperty('transition');

            document.querySelectorAll('.offcanvas-backdrop').forEach((backdrop) => backdrop.remove());
            document.body.classList.remove('offcanvas-open', 'modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        syncBuyerSidebarForViewport();
        window.addEventListener('resize', syncBuyerSidebarForViewport);
        window.addEventListener('pageshow', syncBuyerSidebarForViewport);

        function showSection(sectionName) {
            sectionName = normalizeBuyerSectionName(sectionName);
            if (!sectionInfo[sectionName]) {
                sectionName = 'browse';
            }

            const targetSectionId = `section-${sectionName}`;
            const hasTargetSection = Boolean(document.getElementById(targetSectionId));
            if (!hasTargetSection) {
                sectionName = 'browse';
            }

            // Update navigation
            navItems.forEach(item => {
                if (item.dataset.section === sectionName) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });

            // Update sections
            sections.forEach(section => {
                if (section.id === `section-${sectionName}`) {
                    section.classList.add('active');
                } else {
                    section.classList.remove('active');
                }
            });

            // Update page title
            if (sectionInfo[sectionName]) {
                pageHeading.textContent = sectionInfo[sectionName].title;
                pageSubheading.textContent = sectionInfo[sectionName].subtitle;
            }

            try {
                localStorage.setItem(BUYER_SECTION_STORAGE_KEY, sectionName);
                sessionStorage.setItem(BUYER_SECTION_STORAGE_KEY, sectionName);
            } catch (error) {
            }

            if (window.location.hash !== `#${sectionName}`) {
                history.replaceState(null, '', `#${sectionName}`);
            }

            window.dispatchEvent(new window.CustomEvent('buyer:section-changed', {
                detail: { sectionName }
            }));

            refreshRealtimeOnBuyerSectionChange(sectionName);

            // Close mobile offcanvas sidebar
            closeBuyerSidebarOnMobile();
        }

        window.showSection = showSection;

        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const sectionName = item.dataset.section;
                showSection(sectionName);
            });
        });

        const buyerLogoutBtn = document.getElementById('buyerLogoutBtn');
        buyerLogoutBtn?.addEventListener('click', () => {
            confirmBuyerLogout();
        });

        const initialSectionFromHash = normalizeBuyerSectionName(window.location.hash || '');
        let initialSection = sectionInfo[initialSectionFromHash] ? initialSectionFromHash : '';

        if (!initialSection) {
            try {
                const savedSection = normalizeBuyerSectionName(
                    sessionStorage.getItem(BUYER_SECTION_STORAGE_KEY)
                    || localStorage.getItem(BUYER_SECTION_STORAGE_KEY)
                    || ''
                );
                initialSection = sectionInfo[savedSection] ? savedSection : '';
            } catch (error) {
                initialSection = '';
            }
        }

        showSection(initialSection || getActiveBuyerSectionFromNav() || 'browse');

        window.addEventListener('buyer:inquiry-updated', () => {
            refreshBuyerSidebarCounts();
        });

        window.addEventListener('buyer:favorite-updated', () => {
            refreshBuyerSidebarCounts();
        });

        // Buyer Chatbot controls
        const buyerChatbot = document.getElementById('buyerChatbot');
        const buyerFirstName = <?= json_encode($buyerFirstName, $jsonFlags) ?>;
        const buyerChatbotToggle = document.getElementById('buyerChatbotToggle');
        const buyerChatbotContent = document.getElementById('buyerChatbotContent');
        const buyerChatbotClose = document.getElementById('buyerChatbotClose');
        const buyerChatbotSend = document.getElementById('buyerChatbotSend');
        const buyerChatbotInput = document.getElementById('buyerChatbotInput');
        const buyerChatbotMessages = document.getElementById('buyerChatbotMessages');

        function addBuyerChatbotMessage(text, cls) {
            const msg = document.createElement('div');
            msg.className = 'chatbot-message ' + cls;
            msg.textContent = text;
            buyerChatbotMessages.appendChild(msg);
            buyerChatbotMessages.scrollTop = buyerChatbotMessages.scrollHeight;
        }

        function updateBuyerChatbotState() {
            const isOpen = buyerChatbotContent.classList.contains('active');
            if (isOpen) {
                buyerChatbot?.classList.add('open');
            } else {
                buyerChatbot?.classList.remove('open');
            }
        }

        buyerChatbotToggle?.addEventListener('click', () => {
            buyerChatbotContent.classList.toggle('active');
            updateBuyerChatbotState();
        });

        buyerChatbotClose?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            buyerChatbotContent.classList.remove('active');
            updateBuyerChatbotState();
        });

        // init right state
        updateBuyerChatbotState();

        const chatbotApiUrl = <?= json_encode(base_url('buyer/chatbot/send-message'), $jsonFlags) ?>;
        let chatbotLoading = false;

        function addBuyerChatbotListing(listing) {
            const card = document.createElement('div');
            card.className = 'chatbot-listing-card';
            card.style.cssText = 'margin: 6px 0; padding: 8px; background: #fbf7ec; border-radius: 8px; cursor: pointer; border: 1px solid rgba(0,0,0,.1); transition: all 0.2s ease;';
            
            const imageHtml = listing.image ? `<img src="${listing.image}" style="width: 100%; height: 80px; object-fit: cover; border-radius: 6px; margin-bottom: 6px;">` : '';
            
            card.innerHTML = `
                <div>${imageHtml}</div>
                <div style="font-size: 0.85rem; font-weight: 600; color: #1c3e3a; margin: 4px 0;">${listing.title}</div>
                <div style="font-size: 0.75rem; color: #1c3e3a; margin: 2px 0;">📍 ${listing.location}</div>
                <div style="font-size: 0.78rem; color: #2d6b5f; font-weight: 600;">₱${Number(listing.price || 0).toLocaleString()} • ${listing.size} sqm</div>
            `;

            card.addEventListener('click', () => {
                showPropertyDetailsFromChatbot(listing.id);
            });

            card.addEventListener('mouseover', () => {
                card.style.boxShadow = '0 4px 8px rgba(0,0,0,.15)';
                card.style.transform = 'translateY(-2px)';
            });

            card.addEventListener('mouseout', () => {
                card.style.boxShadow = 'none';
                card.style.transform = 'translateY(0)';
            });

            buyerChatbotMessages.appendChild(card);
            buyerChatbotMessages.scrollTop = buyerChatbotMessages.scrollHeight;
        }

        async function sendBuyerChatbotMessage() {
            const text = (buyerChatbotInput?.value || '').trim();
            if (!text || chatbotLoading) return;
            
            addBuyerChatbotMessage(text, 'user');
            buyerChatbotInput.value = '';

            chatbotLoading = true;
            buyerChatbotSend.disabled = true;
            buyerChatbotSend.textContent = '...';

            try {
                const response = await fetch(chatbotApiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        message: text,
                        user_name: buyerFirstName || ''
                    })
                });

                const data = await response.json();

                if (!response.ok || data.status !== 'success') {
                    addBuyerChatbotMessage('Sorry, I encountered an error. Please try again.', 'bot');
                    return;
                }

                addBuyerChatbotMessage(data.message || 'I\'m here to help with land listings!', 'bot');

                // Display listings if available
                if (data.listings && Array.isArray(data.listings) && data.listings.length > 0) {
                    data.listings.forEach(listing => {
                        addBuyerChatbotListing(listing);
                    });
                }
            } catch (error) {
                console.error('Chatbot error:', error);
                addBuyerChatbotMessage('Sorry, something went wrong. Please try again.', 'bot');
            } finally {
                chatbotLoading = false;
                buyerChatbotSend.disabled = false;
                buyerChatbotSend.textContent = 'Send';
            }
        }

        function showPropertyDetailsFromChatbot(listingId) {
            const targetListingId = Number(listingId || 0);
            if (!targetListingId) {
                alert('Property data not available.');
                return;
            }

            if (!propertyData || !propertyData[targetListingId]) {
                alert('Property details not found.');
                return;
            }

            openPropertyModal(targetListingId);
        }

        buyerChatbotSend?.addEventListener('click', (e) => {
            e.preventDefault();
            sendBuyerChatbotMessage();
        });

        buyerChatbotInput?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendBuyerChatbotMessage();
            }
        });

        function normalizePropertyTypeKey(propertyType) {
            const raw = String(propertyType || '').trim().toLowerCase();

            if (raw === '') {
                return '';
            }

            if (raw === 'residential' || raw === 'residential_land') {
                return 'residential_land';
            }

            if (raw === 'commercial' || raw === 'commercial_land') {
                return 'commercial_land';
            }

            if (raw === 'agricultural' || raw === 'agricultural_land') {
                return 'agricultural_land';
            }

            return raw;
        }

        function applyListingFilters(toolbarElement = null) {
            const toolbars = toolbarElement ? [toolbarElement] : Array.from(document.querySelectorAll('.toolbar-filters'));

            toolbars.forEach((toolbar) => {
                const activeButton = toolbar.querySelector('.filter-btn.active');
                if (!activeButton) {
                    return;
                }

                const filterValue = normalizePropertyTypeKey(activeButton.dataset.filter || activeButton.textContent);
                const section = toolbar.closest('.content-section');
                if (!section) {
                    return;
                }

                const cards = section.querySelectorAll('.listing-card[data-property-type]');
                cards.forEach((card) => {
                    const cardType = normalizePropertyTypeKey(card.dataset.propertyType || '');
                    const isVisible = filterValue === 'all' || filterValue === 'all_saved' || filterValue === '' || cardType === filterValue;
                    card.hidden = !isVisible;
                });
            });
        }

        // Filter buttons functionality
        document.querySelectorAll('.toolbar-filters .filter-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const parent = btn.closest('.toolbar-filters');
                parent.querySelectorAll('.filter-btn').forEach((b) => b.classList.remove('active'));
                btn.classList.add('active');
                applyListingFilters(parent);
            });
        });

        applyListingFilters();

        // Save/Unsave property functionality
        document.querySelectorAll('.listing-card-action').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.querySelector('svg path[d*="20.84"]')) {
                    this.classList.toggle('saved');
                }
            });
        });

        // Message item click
        const messageItems = document.querySelectorAll('.message-item');
        messageItems.forEach(item => {
            item.addEventListener('click', () => {
                messageItems.forEach(m => m.classList.remove('active'));
                item.classList.add('active');
                item.classList.remove('unread');
                const dot = item.querySelector('.message-unread-dot');
                if (dot) dot.remove();
            });
        });

        // Property data for modal
        const propertyData = <?= json_encode($browsePropertyData ?? [], $jsonFlags) ?>;
        const browseFilterOptions = <?= json_encode($browseFilterOptions ?? [], $jsonFlags) ?>;
        const geoapifyApiKey = <?= json_encode($geoapifyApiKey, $jsonFlags) ?>;
        const logoutRedirectUrl = <?= json_encode(base_url('/#listings'), $jsonFlags) ?>;
        console.info('[Landly Map] Geoapify key loaded:', Boolean(geoapifyApiKey));
        const leafletCdn = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        const leafletCssCdn = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';

        const browseAdvancedFilterBtn = document.getElementById('browse-advanced-filter-btn');
        const browseAdvancedFilterOverlay = document.getElementById('browse-advanced-filter-overlay');
        const browseAdvancedFilterClose = document.getElementById('browse-advanced-filter-close');
        const browseAdvancedFilterForm = document.getElementById('browse-advanced-filter-form');
        const browseResultsCount = document.getElementById('browse-results-count');
        const browseFilterChips = document.getElementById('browse-filter-chips');
        const browseFilterLoading = document.getElementById('browse-filter-loading');
        const browsePagination = document.getElementById('browse-pagination');
        const browseListingsGrid = document.getElementById('browse-listings-grid');
        const browseFilterBarangay = document.getElementById('browse-filter-barangay');
        const browseFilterMinPrice = document.getElementById('browse-filter-min-price');
        const browseFilterMaxPrice = document.getElementById('browse-filter-max-price');
        const browseFilterMinSize = document.getElementById('browse-filter-min-size');
        const browseFilterMaxSize = document.getElementById('browse-filter-max-size');
        const browseFilterPropertyType = document.getElementById('browse-filter-property-type');
        const browseFilterRoadAccess = document.getElementById('browse-filter-road-access');
        const browseFilterViewType = document.getElementById('browse-filter-view-type');
        const browseFilterSort = document.getElementById('browse-filter-sort');
        const browseFilterClear = document.getElementById('browse-filter-clear');

        const browseFilterState = {
            barangay: '',
            min_price: '',
            max_price: '',
            min_size: '',
            max_size: '',
            property_type: '',
            road_access: '',
            view_type: '',
            sort: 'newest',
            page: 1,
            per_page: 12,
        };

        let browseFilterAbortController = null;
        let browseFilterDebounceTimer = null;
        let browseFilterIsInitialized = false;
        let browseFilterHasApplied = false;

        function escapeHtml(value) {
            const text = String(value ?? '');
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return text.replace(/[&<>"']/g, (character) => map[character]);
        }

        function syncBrowseFilterFormToState() {
            browseFilterState.barangay = String(browseFilterBarangay?.value || '');
            browseFilterState.min_price = String(browseFilterMinPrice?.value || '');
            browseFilterState.max_price = String(browseFilterMaxPrice?.value || '');
            browseFilterState.min_size = String(browseFilterMinSize?.value || '');
            browseFilterState.max_size = String(browseFilterMaxSize?.value || '');
            browseFilterState.property_type = String(browseFilterPropertyType?.value || '');
            browseFilterState.road_access = String(browseFilterRoadAccess?.value || '');
            browseFilterState.view_type = String(browseFilterViewType?.value || '');
            browseFilterState.sort = String(browseFilterSort?.value || 'newest');
        }

        function syncBrowseFormFromState() {
            if (browseFilterBarangay) browseFilterBarangay.value = browseFilterState.barangay;
            if (browseFilterMinPrice) browseFilterMinPrice.value = browseFilterState.min_price;
            if (browseFilterMaxPrice) browseFilterMaxPrice.value = browseFilterState.max_price;
            if (browseFilterMinSize) browseFilterMinSize.value = browseFilterState.min_size;
            if (browseFilterMaxSize) browseFilterMaxSize.value = browseFilterState.max_size;
            if (browseFilterPropertyType) browseFilterPropertyType.value = browseFilterState.property_type;
            if (browseFilterRoadAccess) browseFilterRoadAccess.value = browseFilterState.road_access;
            if (browseFilterViewType) browseFilterViewType.value = browseFilterState.view_type;
            if (browseFilterSort) browseFilterSort.value = browseFilterState.sort;
        }

        function setBrowseLoading(isLoading) {
            if (!browseFilterLoading) {
                return;
            }

            browseFilterLoading.hidden = !isLoading;
            browseFilterLoading.classList.toggle('is-visible', Boolean(isLoading));
        }

        function getBrowseDisplayValue(key, value) {
            if (key === 'property_type') {
                return {
                    residential_land: 'Residential',
                    agricultural_land: 'Agricultural',
                    commercial_land: 'Commercial',
                    beach_lot: 'Beach Lot'
                }[value] || value;
            }

            if (key === 'road_access') {
                return {
                    cemented: 'Concrete',
                    right_of_way: 'Dirt Road',
                    none: 'Highway Access'
                }[value] || value;
            }

            if (key === 'view_type') {
                return {
                    sea_view: 'Beach View',
                    mountain_view: 'Mountain View',
                    none: 'Plain'
                }[value] || value;
            }

            if (key === 'sort') {
                return {
                    price_asc: 'Price Low to High',
                    price_desc: 'Price High to Low',
                    largest_lot: 'Largest Lot',
                    newest: 'Newest Listings'
                }[value] || value;
            }

            return value;
        }

        function renderBrowseFilterChips() {
            if (!browseFilterChips) {
                return;
            }

            if (!browseFilterHasApplied) {
                browseFilterChips.innerHTML = '';
                return;
            }

            const chips = [];
            const chipMap = [
                ['barangay', 'Barangay'],
                ['min_price', 'Min Price'],
                ['max_price', 'Max Price'],
                ['min_size', 'Min Size'],
                ['max_size', 'Max Size'],
                ['property_type', 'Property Type'],
                ['road_access', 'Road Access'],
                ['view_type', 'View Type'],
                ['sort', 'Sort'],
            ];

            chipMap.forEach(([key, label]) => {
                const value = String(browseFilterState[key] ?? '').trim();
                if (!value) {
                    return;
                }

                if (key === 'sort' && value === 'newest') {
                    return;
                }

                let displayValue = value;
                if (key.includes('price')) {
                    displayValue = `₱${value}`;
                } else if (key.includes('size')) {
                    displayValue = `${value} sqm`;
                } else {
                    displayValue = getBrowseDisplayValue(key, value);
                }

                chips.push(`
                    <span class="browse-filter-chip">
                        <span>${escapeHtml(label)}: ${escapeHtml(displayValue)}</span>
                        <button type="button" aria-label="Remove ${escapeHtml(label)}" data-filter-chip-remove="${escapeHtml(key)}">&times;</button>
                    </span>
                `);
            });

            browseFilterChips.innerHTML = chips.join('');
            browseFilterChips.querySelectorAll('[data-filter-chip-remove]').forEach((button) => {
                button.addEventListener('click', () => {
                    const key = button.dataset.filterChipRemove;
                    if (!key) {
                        return;
                    }

                    if (key === 'barangay') browseFilterState.barangay = '';
                    if (key === 'min_price') browseFilterState.min_price = '';
                    if (key === 'max_price') browseFilterState.max_price = '';
                    if (key === 'min_size') browseFilterState.min_size = '';
                    if (key === 'max_size') browseFilterState.max_size = '';
                    if (key === 'property_type') browseFilterState.property_type = '';
                    if (key === 'road_access') browseFilterState.road_access = '';
                    if (key === 'view_type') browseFilterState.view_type = '';
                    if (key === 'sort') browseFilterState.sort = 'newest';

                    syncBrowseFormFromState();
                    fetchBrowseListings({ page: 1 });
                });
            });
        }

        function buildBrowseListingCard(listing) {
            const savedClass = listing.is_saved ? ' saved' : '';
            const savedTitle = listing.is_saved ? 'Remove from Saved' : 'Save Property';
            const savedPressed = listing.is_saved ? 'true' : 'false';
            return `
                <div class="listing-card" onclick="openPropertyModal(${Number(listing.listing_id || 0)})" data-property-id="${Number(listing.listing_id || 0)}" data-property-type="${escapeHtml(listing.property_type_key || '')}">
                    <div class="listing-card-image">
                        <img class="img-fluid" src="${escapeHtml(listing.image_url || '<?= base_url('default1.png') ?>')}" alt="${escapeHtml(listing.title || 'Land Listing')}" onerror="this.onerror=null;this.src='<?= base_url('default1.png') ?>';">
                        <span class="listing-card-badge listing-status ${escapeHtml(listing.status_class || 'available')}">${escapeHtml(listing.status_label || 'Available')}</span>
                        <div class="listing-card-actions">
                            <button class="listing-card-action favorite-btn${savedClass}" data-listing-id="${Number(listing.listing_id || 0)}" title="${escapeHtml(savedTitle)}" aria-pressed="${savedPressed}" onclick="toggleFavorite(event, this, ${Number(listing.listing_id || 0)})">
                                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                            </button>
                            <button class="listing-card-action" title="Contact Seller" onclick="createInquiryForListing(event, ${Number(listing.listing_id || 0)})">
                                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="listing-card-content">
                        <h4 class="listing-card-title">${escapeHtml(listing.title || 'Untitled Listing')}</h4>
                        <div class="listing-card-location">
                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            ${escapeHtml(listing.location_label || 'Location unavailable')}
                        </div>
                        <div class="listing-card-details">
                            <div class="listing-card-detail"><strong>${escapeHtml(listing.property_type_label || 'Unspecified')}</strong></div>
                            <div class="listing-card-detail"><strong>${escapeHtml(listing.document_status_label || 'Documents Pending')}</strong></div>
                            <div class="listing-card-detail"><strong>${escapeHtml(listing.road_access_label || 'Road Access N/A')}</strong></div>
                        </div>
                        <div class="listing-card-footer">
                            <span class="listing-card-price">${escapeHtml(listing.price_label || '₱0.00')}</span>
                            <div class="listing-card-seller">
                                <span class="seller-avatar">${escapeHtml(listing.seller_initials || 'NA')}</span>
                                ${escapeHtml(listing.seller_name || 'Unknown Seller')}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function renderBrowseListings(listings = [], propertyDataMap = {}, pagination = null) {
            if (propertyDataMap && typeof propertyDataMap === 'object') {
                Object.assign(propertyData, propertyDataMap);
            }

            browseFilterHasApplied = true;

            if (browseListingsGrid) {
                if (!Array.isArray(listings) || listings.length === 0) {
                    browseListingsGrid.innerHTML = `
                        <div class="listing-card" style="grid-column: 1 / -1; cursor: default;">
                            <div class="listing-card-content">
                                <h4 class="listing-card-title">No results found</h4>
                                <div class="listing-card-location">Try adjusting filters or expanding your price range.</div>
                            </div>
                        </div>
                    `;
                } else {
                    browseListingsGrid.innerHTML = listings.map(buildBrowseListingCard).join('');
                }
            }

            if (browseResultsCount && pagination) {
                const total = Number(pagination.total || 0);
                browseResultsCount.textContent = `${total} propert${total === 1 ? 'y' : 'ies'} found`;
            }

            renderBrowseFilterChips();
            renderBrowsePagination(pagination);
            applyListingFilters();
        }

        function renderBrowsePagination(pagination = null) {
            if (!browsePagination) {
                return;
            }

            const totalPages = Number(pagination?.total_pages || 1);
            const currentPage = Number(pagination?.page || 1);

            if (totalPages <= 1) {
                browsePagination.hidden = true;
                browsePagination.innerHTML = '';
                return;
            }

            const items = [];
            const addButton = (label, page, disabled = false, active = false) => {
                items.push(`<button type="button"${disabled ? ' disabled' : ''}${active ? ' class="active"' : ''} data-browse-page="${page}">${label}</button>`);
            };

            addButton('Prev', Math.max(1, currentPage - 1), currentPage === 1);

            const windowSize = 2;
            const startPage = Math.max(1, currentPage - windowSize);
            const endPage = Math.min(totalPages, currentPage + windowSize);

            if (startPage > 1) {
                addButton('1', 1, false, currentPage === 1);
                if (startPage > 2) {
                    items.push('<span style="color: rgba(239,231,216,0.6); padding: 0 4px;">...</span>');
                }
            }

            for (let page = startPage; page <= endPage; page += 1) {
                addButton(String(page), page, false, page === currentPage);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    items.push('<span style="color: rgba(239,231,216,0.6); padding: 0 4px;">...</span>');
                }
                addButton(String(totalPages), totalPages, false, currentPage === totalPages);
            }

            addButton('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages);

            browsePagination.hidden = false;
            browsePagination.innerHTML = items.join('');
            browsePagination.querySelectorAll('[data-browse-page]').forEach((button) => {
                button.addEventListener('click', () => {
                    const page = Number(button.dataset.browsePage || 1);
                    if (!Number.isFinite(page) || page < 1 || page === browseFilterState.page) {
                        return;
                    }

                    fetchBrowseListings({ page });
                });
            });
        }

        function buildBrowseFilterParams(page = 1) {
            const params = new URLSearchParams();
            params.set('page', String(page));
            params.set('per_page', String(browseFilterState.per_page));

            if (browseFilterState.barangay !== '') {
                params.set('barangay', String(browseFilterState.barangay));
            }

            if (browseFilterState.min_price !== '') {
                params.set('min_price', String(browseFilterState.min_price));
            }

            if (browseFilterState.max_price !== '') {
                params.set('max_price', String(browseFilterState.max_price));
            }

            if (browseFilterState.min_size !== '') {
                params.set('min_size', String(browseFilterState.min_size));
            }

            if (browseFilterState.max_size !== '') {
                params.set('max_size', String(browseFilterState.max_size));
            }

            if (browseFilterState.property_type !== '') {
                params.set('property_type', String(browseFilterState.property_type));
            }

            if (browseFilterState.road_access !== '') {
                params.set('road_access', String(browseFilterState.road_access));
            }

            if (browseFilterState.view_type !== '') {
                params.set('view_type', String(browseFilterState.view_type));
            }

            if (browseFilterState.sort !== 'newest') {
                params.set('sort', String(browseFilterState.sort));
            }

            return params;
        }

        function scheduleBrowseFilterFetch(page = 1) {
            window.clearTimeout(browseFilterDebounceTimer);
            browseFilterDebounceTimer = window.setTimeout(() => {
                fetchBrowseListings({ page });
            }, 400);
        }

        async function fetchBrowseListings({ page = 1 } = {}) {
            if (!browseListingsGrid || !browseResultsCount) {
                return;
            }

            syncBrowseFilterFormToState();
            browseFilterState.page = page;

            if (browseFilterAbortController) {
                browseFilterAbortController.abort();
            }

            browseFilterAbortController = new AbortController();
            setBrowseLoading(true);

            try {
                const response = await fetch(`<?= base_url('listings/filter') ?>?${buildBrowseFilterParams(page).toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    signal: browseFilterAbortController.signal
                });

                const data = await response.json();
                if (!response.ok || data.status !== 'success') {
                    throw new Error(data.message || 'Unable to filter listings.');
                }

                browseFilterState.page = Number(data.page || page);
                browseFilterState.per_page = Number(data.per_page || browseFilterState.per_page);
                renderBrowseListings(data.listings || [], data.property_data || {}, data);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    if (hasSwal()) {
                        await fireAppAlert({
                            icon: 'error',
                            title: 'Filter Failed',
                            text: 'Something went wrong. Please try again.',
                            confirmButtonText: 'Okay'
                        });
                    } else {
                        alert('Something went wrong. Please try again.');
                    }
                }
            } finally {
                setBrowseLoading(false);
            }
        }

        function openBrowseAdvancedFilter() {
            if (!browseAdvancedFilterOverlay) {
                return;
            }

            syncBrowseFormFromState();
            renderBrowseFilterChips();
            browseAdvancedFilterOverlay.classList.add('is-visible');
            browseAdvancedFilterOverlay.setAttribute('aria-hidden', 'false');
        }

        function closeBrowseAdvancedFilter() {
            if (!browseAdvancedFilterOverlay) {
                return;
            }

            browseAdvancedFilterOverlay.classList.remove('is-visible');
            browseAdvancedFilterOverlay.setAttribute('aria-hidden', 'true');
        }

        function initializeBrowseAdvancedFilters() {
            if (browseFilterIsInitialized) {
                return;
            }

            browseFilterIsInitialized = true;

            if (browseFilterBarangay) browseFilterBarangay.value = '';
            if (browseFilterSort) browseFilterSort.value = 'newest';

            syncBrowseFormFromState();
            renderBrowseFilterChips();

            browseAdvancedFilterBtn?.addEventListener('click', openBrowseAdvancedFilter);
            browseAdvancedFilterClose?.addEventListener('click', closeBrowseAdvancedFilter);

            browseAdvancedFilterOverlay?.addEventListener('click', (event) => {
                if (event.target === browseAdvancedFilterOverlay) {
                    closeBrowseAdvancedFilter();
                }
            });

            browseAdvancedFilterForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                syncBrowseFilterFormToState();
                closeBrowseAdvancedFilter();
                await fetchBrowseListings({ page: 1 });
            });

            browseFilterBarangay?.addEventListener('change', () => {
                syncBrowseFilterFormToState();
                scheduleBrowseFilterFetch(1);
            });

            [browseFilterMinPrice, browseFilterMaxPrice, browseFilterMinSize, browseFilterMaxSize].forEach((input) => {
                input?.addEventListener('input', () => {
                    syncBrowseFilterFormToState();
                    renderBrowseFilterChips();
                    scheduleBrowseFilterFetch(1);
                });
            });

            [browseFilterPropertyType, browseFilterRoadAccess, browseFilterViewType, browseFilterSort].forEach((input) => {
                input?.addEventListener('change', () => {
                    syncBrowseFilterFormToState();
                    renderBrowseFilterChips();
                    scheduleBrowseFilterFetch(1);
                });
            });

            browseFilterClear?.addEventListener('click', async () => {
                browseFilterState.barangay = '';
                browseFilterState.min_price = '';
                browseFilterState.max_price = '';
                browseFilterState.min_size = '';
                browseFilterState.max_size = '';
                browseFilterState.property_type = '';
                browseFilterState.road_access = '';
                browseFilterState.view_type = '';
                browseFilterState.sort = 'newest';
                browseFilterState.page = 1;
                syncBrowseFormFromState();
                renderBrowseFilterChips();
                await fetchBrowseListings({ page: 1 });
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeBrowseAdvancedFilter();
                }
            });
        }

        initializeBrowseAdvancedFilters();

        let fullMapInstance = null;
        let fullMapMarker = null;
        let propertyModalLoading = false;

        const defaultAlertConfig = {
            confirmButtonText: 'OK',
            customClass: {
                popup: 'landly-swal',
                confirmButton: 'landly-swal-confirm',
                cancelButton: 'landly-swal-cancel'
            },
            showClass: {
                popup: 'landly-swal-show'
            },
            hideClass: {
                popup: 'landly-swal-hide'
            }
        };

        function hasSwal() {
            return Boolean(window.Swal && typeof window.Swal.fire === 'function');
        }

        function fireAppAlert(options = {}) {
            if (!hasSwal()) {
                return Promise.resolve(null);
            }

            const mergedCustomClass = {
                ...(defaultAlertConfig.customClass || {}),
                ...(options.customClass || {})
            };

            return window.Swal.fire({
                ...defaultAlertConfig,
                ...options,
                customClass: mergedCustomClass
            });
        }

        function createMapEmbedUrl(lat, lng) {
            if (!geoapifyApiKey) {
                return '';
            }

            const safeLat = Number(lat);
            const safeLng = Number(lng);
            if (!Number.isFinite(safeLat) || !Number.isFinite(safeLng)) {
                return '';
            }

            const zoom = 16;
            const cacheBust = Date.now();
            return `https://maps.geoapify.com/v1/staticmap?style=osm-carto&width=1800&height=1100&center=lonlat:${safeLng},${safeLat}&zoom=${zoom}&marker=lonlat:${safeLng},${safeLat};type:awesome;color:%23d62828;size:large&apiKey=${encodeURIComponent(geoapifyApiKey)}&cb=${cacheBust}`;
        }

        function showDetailsLoadingState() {
            document.body.classList.add('property-modal-loading');
            const fallbackOverlay = document.getElementById('propertyLoadingOverlay');
            if (fallbackOverlay) {
                fallbackOverlay.classList.add('active');
                fallbackOverlay.setAttribute('aria-hidden', 'false');
            }

            fireAppAlert({
                title: 'Loading Details',
                text: 'Please wait while we prepare the property details.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    if (window.Swal && typeof window.Swal.showLoading === 'function') {
                        window.Swal.showLoading();
                    }
                }
            });
        }

        function closeDetailsLoadingState() {
            document.body.classList.remove('property-modal-loading');
            const fallbackOverlay = document.getElementById('propertyLoadingOverlay');
            if (fallbackOverlay) {
                fallbackOverlay.classList.remove('active');
                fallbackOverlay.setAttribute('aria-hidden', 'true');
            }

            if (window.Swal && typeof window.Swal.close === 'function') {
                window.Swal.close();
            }
        }

        async function confirmBuyerLogout() {
            if (!hasSwal()) {
                const shouldLogout = window.confirm('Do you want to logout?');
                if (shouldLogout) {
                    window.location.href = logoutRedirectUrl;
                }
                return;
            }

            const result = await fireAppAlert({
                icon: 'question',
                title: 'Do you want to logout?',
                text: 'You can log in again anytime.',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                allowOutsideClick: true,
                allowEscapeKey: true
            });

            if (result && result.isConfirmed) {
                window.location.href = logoutRedirectUrl;
            }
        }

        function loadIframeSource(iframeElement, srcUrl, timeoutMs = 10000) {
            return new Promise((resolve) => {
                if (!iframeElement || !srcUrl) {
                    resolve(false);
                    return;
                }

                let settled = false;
                const cleanup = () => {
                    iframeElement.removeEventListener('load', onLoad);
                    iframeElement.removeEventListener('error', onError);
                };

                const finish = (isLoaded) => {
                    if (settled) {
                        return;
                    }

                    settled = true;
                    cleanup();
                    resolve(isLoaded);
                };

                const onLoad = () => finish(true);
                const onError = () => finish(false);

                iframeElement.addEventListener('load', onLoad, { once: true });
                iframeElement.addEventListener('error', onError, { once: true });
                iframeElement.src = srcUrl;

                window.setTimeout(() => finish(false), timeoutMs);
            });
        }

        function buildMapFrameSrcdoc(mapUrl, altText) {
            const safeMapUrl = String(mapUrl || '').replace(/&/g, '&amp;').replace(/"/g, '&quot;');
            const safeAltText = String(altText || 'Property map').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

            return `<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            overflow: hidden;
            background: #0d2818;
        }

        img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <img src="${safeMapUrl}" alt="${safeAltText}">
</body>
</html>`;
        }

        function ensureLeaflet() {
            if (window.L && typeof window.L.map === 'function') {
                return Promise.resolve(window.L);
            }

            const existingScript = document.querySelector('script[data-leaflet="true"]');
            const existingCss = document.querySelector('link[data-leaflet="true"]');

            return new Promise((resolve) => {
                const loadScript = () => {
                    const script = existingScript || document.createElement('script');
                    if (!existingScript) {
                        script.src = leafletCdn;
                        script.async = true;
                        script.dataset.leaflet = 'true';
                        script.onload = () => resolve(window.L || null);
                        script.onerror = () => resolve(null);
                        document.head.appendChild(script);
                    } else if (window.L && typeof window.L.map === 'function') {
                        resolve(window.L);
                    } else {
                        existingScript.addEventListener('load', () => resolve(window.L || null), { once: true });
                        existingScript.addEventListener('error', () => resolve(null), { once: true });
                    }
                };

                if (!existingCss) {
                    const css = document.createElement('link');
                    css.rel = 'stylesheet';
                    css.href = leafletCssCdn;
                    css.dataset.leaflet = 'true';
                    document.head.appendChild(css);
                }

                loadScript();
            });
        }

        function setFullMapLoading(isLoading, message = 'Loading interactive map...') {
            const loadingNode = document.getElementById('fullMapLoading');
            if (!loadingNode) {
                return;
            }

            loadingNode.textContent = message;
            loadingNode.classList.toggle('visible', Boolean(isLoading));
        }

        function destroyFullMap() {
            if (fullMapInstance && typeof fullMapInstance.remove === 'function') {
                fullMapInstance.remove();
            }

            fullMapInstance = null;
            fullMapMarker = null;
        }

        async function renderFullMap(lat, lng, title) {
            const mapCanvas = document.getElementById('fullMapCanvas');
            const propertyModal = document.getElementById('propertyModal');
            if (!mapCanvas) {
                return false;
            }

            setFullMapLoading(true);
            const leaflet = await ensureLeaflet();
            if (!leaflet) {
                setFullMapLoading(true, 'Unable to load interactive map.');
                return false;
            }

            const safeLat = Number(lat);
            const safeLng = Number(lng);
            if (!Number.isFinite(safeLat) || !Number.isFinite(safeLng)) {
                setFullMapLoading(true, 'Map coordinates are unavailable.');
                return false;
            }

            destroyFullMap();

            fullMapInstance = leaflet.map(mapCanvas, {
                zoomControl: true,
                scrollWheelZoom: true,
                dragging: true,
                doubleClickZoom: true,
                touchZoom: true,
                tap: true,
            });

            leaflet.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(fullMapInstance);

            fullMapMarker = leaflet.marker([safeLat, safeLng], {
                draggable: true,
            }).addTo(fullMapInstance);

            fullMapMarker.on('dragend', () => {
                const markerPosition = fullMapMarker.getLatLng();
                if (propertyModal) {
                    propertyModal.dataset.lat = String(markerPosition.lat);
                    propertyModal.dataset.lng = String(markerPosition.lng);
                }
                fullMapInstance.setView(markerPosition, fullMapInstance.getZoom(), { animate: true });
            });

            fullMapInstance.setView([safeLat, safeLng], 16);
            window.setTimeout(() => fullMapInstance?.invalidateSize(), 250);
            setFullMapLoading(false);
            return true;
        }

        async function geocodeListingAddress(addressText) {
            if (!geoapifyApiKey || !addressText) {
                return null;
            }

            const endpoint = `https://api.geoapify.com/v1/geocode/search?text=${encodeURIComponent(addressText)}&limit=1&format=json&filter=countrycode:ph&bias=countrycode:ph&apiKey=${encodeURIComponent(geoapifyApiKey)}`;

            try {
                console.info('[Landly Map] Geoapify geocode request:', endpoint);
                const response = await fetch(endpoint, { cache: 'no-store' });
                if (!response.ok) {
                    console.warn('[Landly Map] Geoapify geocode failed:', response.status);
                    return null;
                }

                const data = await response.json();
                const result = Array.isArray(data.results) ? data.results[0] : null;
                if (!result || typeof result.lat !== 'number' || typeof result.lon !== 'number') {
                    return null;
                }

                const resultType = String(result.result_type || '').toLowerCase();
                const category = String(result.category || '').toLowerCase();
                const formatted = String(result.formatted || result.address_line1 || '').toLowerCase();
                const looksLikeWater = ['water', 'ocean', 'sea', 'bay', 'river', 'coast', 'shore']
                    .some((token) => resultType.includes(token) || category.includes(token) || formatted.includes(token));

                if (looksLikeWater) {
                    console.warn('[Landly Map] Geoapify result rejected as water-like result.');
                    return null;
                }

                console.info('[Landly Map] Geoapify geocode result:', result.lat, result.lon);
                return { lat: result.lat, lng: result.lon };
            } catch (error) {
                console.warn('[Landly Map] Geoapify request error:', error);
                return null;
            }
        }

        async function getListingCoordinates(propertyId, property) {
            const rawLat = property?.coordinates?.lat;
            const rawLng = property?.coordinates?.lng;
            const hasExplicitCoords = rawLat !== null && rawLat !== undefined && String(rawLat).trim() !== ''
                && rawLng !== null && rawLng !== undefined && String(rawLng).trim() !== '';

            const lat = Number(rawLat);
            const lng = Number(rawLng);
            const isZeroPair = lat === 0 && lng === 0;

            if (hasExplicitCoords && Number.isFinite(lat) && Number.isFinite(lng) && !isZeroPair) {
                return { lat, lng };
            }

            const geocoded = await geocodeListingAddress(property.mapAddress || property.address || property.location || '');
            if (geocoded) {
                property.coordinates = geocoded;
                if (propertyData[propertyId]) {
                    propertyData[propertyId].coordinates = geocoded;
                }
                return geocoded;
            }

            const cityProvinceAddress = [property.city, property.province, 'Philippines']
                .filter((part) => String(part || '').trim() !== '')
                .join(', ');

            const geocodedCityProvince = await geocodeListingAddress(cityProvinceAddress);
            if (geocodedCityProvince) {
                property.coordinates = geocodedCityProvince;
                if (propertyData[propertyId]) {
                    propertyData[propertyId].coordinates = geocodedCityProvince;
                }
                return geocodedCityProvince;
            }

            return { lat: 14.0664, lng: 120.6325 };
        }

        async function showInquirySentDialogAndRedirect(listingId, inquiryId = 0) {
            if (hasSwal()) {
                const result = await fireAppAlert({
                    icon: 'success',
                    title: 'Inquiry Sent',
                    text: 'Your inquiry was sent successfully. You can now continue your conversation in Messages.',
                    confirmButtonText: 'Go to Messages',
                    allowOutsideClick: false,
                    allowEscapeKey: true
                });

                if (!result?.isConfirmed) {
                    return;
                }
            } else {
                alert('Inquiry sent successfully. You will now be redirected to Messages.');
            }

            await openMessagesForListing(listingId, inquiryId);
        }

        async function showExistingInquiryDialogAndRedirect(listingId, inquiryId = 0) {
            if (hasSwal()) {
                const result = await fireAppAlert({
                    icon: 'info',
                    title: 'Conversation Already Started',
                    text: 'You already have an ongoing conversation for this listing. Click below to continue in Messages.',
                    confirmButtonText: 'Go to Messages',
                    allowOutsideClick: false,
                    allowEscapeKey: true
                });

                if (!result?.isConfirmed) {
                    return;
                }
            } else {
                alert('You already have an ongoing conversation for this listing. You will now be redirected to Messages.');
            }

            await openMessagesForListing(listingId, inquiryId);
        }

        async function openMessagesForListing(listingId, inquiryId = 0) {
            const targetListingId = Number(listingId || 0);
            const targetInquiryId = Number(inquiryId || 0);

            if (typeof closePropertyModal === 'function') {
                closePropertyModal();
            }

            if (typeof window.openBuyerConversation === 'function') {
                try {
                    await window.openBuyerConversation({
                        listingId: targetListingId,
                        inquiryId: targetInquiryId
                    });
                    return;
                } catch (error) {
                }
            }

            showSection('messages');
        }

        async function showInquiryErrorDialog(message) {
            const fallbackMessage = message || 'We could not send your inquiry right now. Please try again.';

            if (hasSwal()) {
                await fireAppAlert({
                    icon: 'error',
                    title: 'Unable to Send Inquiry',
                    text: fallbackMessage,
                    confirmButtonText: 'Okay'
                });
                return;
            }

            alert(fallbackMessage);
        }

        async function createInquiryForListing(event, listingId) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }

            const targetListingId = parseInt(listingId, 10);
            if (!targetListingId || targetListingId <= 0) {
                alert('Invalid listing.');
                return;
            }

            // Show confirmation dialog first
            const confirmResult = await fireAppAlert({
                icon: 'question',
                title: 'Send Inquiry',
                text: 'Do you want to send an inquiry to the seller for this listing?',
                showCancelButton: true,
                confirmButtonText: 'Send Message',
                cancelButtonText: 'Cancel',
                allowOutsideClick: false,
                allowEscapeKey: true
            });

            // If user clicked Cancel, return
            if (confirmResult.dismiss) {
                return;
            }

            try {
                const response = await fetch('<?= base_url('messages/inquiries') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ listing_id: targetListingId })
                });

                const data = await response.json();
                if (response.ok && data.status === 'success') {
                    window.dispatchEvent(new window.CustomEvent('buyer:inquiry-updated', {
                        detail: {
                            listingId: targetListingId,
                            inquiryId: Number(data.inquiry_id || 0),
                            inquiryStatus: String(data.inquiry_status || 'pending')
                        }
                    }));

                    const rawMessage = String(data.message || '').toLowerCase();
                    if (rawMessage.includes('already exists')) {
                        await showExistingInquiryDialogAndRedirect(targetListingId, Number(data.inquiry_id || 0));
                        return;
                    }

                    await showInquirySentDialogAndRedirect(targetListingId, Number(data.inquiry_id || 0));
                    return;
                }

                await showInquiryErrorDialog(data.message || 'Unable to send inquiry.');
            } catch (error) {
                await showInquiryErrorDialog('Unable to send inquiry right now. Please try again.');
            }
        }

        async function trackListingView(listingId) {
            const targetListingId = Number(listingId || 0);
            if (!targetListingId) {
                return;
            }

            try {
                await fetch('<?= base_url('buyer/listings/track-view') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: 'listing_id=' + encodeURIComponent(String(targetListingId))
                });
            } catch (error) {
                console.warn('Unable to track listing view:', error);
            }
        }

        // Open Property Modal
        async function openPropertyModal(propertyId) {
            if (propertyModalLoading) {
                return;
            }

            const property = propertyData[propertyId];
            if (!property) {
                alert('Sorry, property details are unavailable for this listing.');
                return;
            }

            trackListingView(propertyId);

            const modal = document.getElementById('propertyModal');
            propertyModalLoading = true;
            showDetailsLoadingState();

            try {
                const placeholderImage = '<?= base_url('default1.png') ?>';
                const imageList = Array.isArray(property.images)
                    ? property.images.filter((img) => String(img || '').trim() !== '')
                    : [];

                if (imageList.length === 0) {
                    imageList.push(placeholderImage);
                }

                // Set main image
                const primaryImage = imageList[0];
                const modalMainImage = document.getElementById('modalMainImage');
                modalMainImage.onerror = function () {
                    this.onerror = null;
                    this.src = placeholderImage;
                };
                modalMainImage.src = primaryImage || placeholderImage;

                // Set thumbnails
                const thumbsContainer = document.getElementById('modalThumbnails');
                thumbsContainer.innerHTML = '';
                imageList.forEach((img, idx) => {
                    const thumb = document.createElement('img');
                    thumb.src = img;
                    thumb.alt = `Thumbnail ${idx + 1}`;
                    thumb.className = `modal-thumb ${idx === 0 ? 'active' : ''}`;
                    thumb.onerror = function () {
                        this.onerror = null;
                        this.src = placeholderImage;
                    };
                    thumb.addEventListener('click', () => changeMainImage(img, thumb));
                    thumbsContainer.appendChild(thumb);
                });

                // Set property details
                document.getElementById('modalTitle').textContent = property.title;
                document.getElementById('modalPrice').textContent = property.price;
                document.getElementById('modalPricePerSqm').textContent = property.pricePerSqm;
                document.getElementById('modalLocation').textContent = property.location;
                setModalFieldValue('area', property.area);
                setModalFieldValue('type', property.type);
                setModalFieldValue('title_status', property.titleStatus);
                document.getElementById('modalDescription').textContent = property.description;
                document.getElementById('modalListingId').textContent = property.listingId || propertyId;
                document.getElementById('modalAddressLine').textContent = property.address || property.location || 'Address not available';
                document.getElementById('modalBarangay').textContent = property.barangay || 'N/A';
                document.getElementById('modalCity').textContent = property.city || 'N/A';
                document.getElementById('modalProvince').textContent = property.province || 'N/A';
                document.getElementById('modalRoadAccess').textContent = property.roadAccess || 'N/A';
                document.getElementById('modalViewType').textContent = property.viewType || 'Not specified';
                document.getElementById('modalInvestmentReady').textContent = property.investmentReady || 'No';
                document.getElementById('modalTaxDec').textContent = property.hasTaxDeclaration || 'No';
                document.getElementById('modalLraPlan').textContent = property.hasLraApprovedPlan || 'No';
                document.getElementById('modalMotherTitle').textContent = property.motherTitleDisclosed || 'No';
                setModalFieldValue('listing_status', property.listingStatus || 'Available');

                // Set features
                const featuresContainer = document.getElementById('modalFeatures');
                featuresContainer.innerHTML = property.features.map(feature => `
                    <span class="feature-tag">${feature}</span>
                `).join('');

                // Set map from listing address via Geoapify geocoding (if key is provided)
                const coordinates = await getListingCoordinates(propertyId, property);
                const mapIframe = document.getElementById('modalMap');
                const mapUrl = createMapEmbedUrl(coordinates.lat, coordinates.lng);

                if (mapUrl) {
                    mapIframe.removeAttribute('src');
                    console.info('[Landly Map] Geoapify static map request:', mapUrl);
                    mapIframe.srcdoc = buildMapFrameSrcdoc(mapUrl, property.title || 'Property map');
                } else {
                    mapIframe.removeAttribute('src');
                    mapIframe.srcdoc = '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><style>html,body{width:100%;height:100%;margin:0;overflow:hidden;background:#f8fafc;color:#475467;font-family:Arial,sans-serif;}body{display:flex;align-items:center;justify-content:center;}</style></head><body>Map preview unavailable.</body></html>';
                }

                // Store coordinates for full map
                modal.dataset.lat = coordinates.lat;
                modal.dataset.lng = coordinates.lng;
                modal.dataset.title = property.title;
                modal.dataset.listingId = propertyId;

                // Set seller info
                document.getElementById('sellerAvatar').textContent = property.seller.initials;
                document.getElementById('sellerName').textContent = property.seller.name;
                document.getElementById('sellerVerified').style.display = property.seller.verified ? 'inline-flex' : 'none';
                document.getElementById('sellerListings').textContent = `${property.seller.listings} listings`;
                document.getElementById('sellerMember').textContent = `Member since ${property.seller.memberSince}`;

                const modalSaveBtn = document.getElementById('modalSavePropertyBtn');
                if (modalSaveBtn) {
                    modalSaveBtn.dataset.listingId = String(propertyId);
                    const isSaved = await getFavoriteStatus(propertyId);
                    applySavedButtonState(modalSaveBtn, isSaved);
                }

                // Show modal
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';

                closeDetailsLoadingState();
            } catch (error) {
                closeDetailsLoadingState();
                if (hasSwal()) {
                    await fireAppAlert({
                        icon: 'error',
                        title: 'Unable to Load Details',
                        text: 'Something went wrong while loading this listing. Please try again.',
                        confirmButtonText: 'OK'
                    });
                }
            } finally {
                propertyModalLoading = false;
            }
        }

        function setModalFieldValue(fieldName, fieldValue) {
            const value = fieldValue || 'N/A';
            document.querySelectorAll(`[data-modal-field="${fieldName}"]`).forEach((node) => {
                node.textContent = value;
            });
        }

        function handleModalMessageSellerClick(event) {
            const listingId = Number(document.getElementById('propertyModal')?.dataset?.listingId || 0);
            createInquiryForListing(event, listingId);
        }

        function handleModalSavePropertyClick(event, buttonElement) {
            const listingId = Number(buttonElement?.dataset?.listingId || document.getElementById('propertyModal')?.dataset?.listingId || 0);
            if (!listingId || !buttonElement) {
                return;
            }

            toggleFavorite(event, buttonElement, listingId);
        }

        // Change main image in gallery
        function changeMainImage(src, thumbElement) {
            document.getElementById('modalMainImage').src = src;
            document.querySelectorAll('.modal-thumb').forEach(t => t.classList.remove('active'));
            thumbElement.classList.add('active');
        }

        // Close Property Modal
        function closePropertyModal() {
            const modal = document.getElementById('propertyModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            propertyModalLoading = false;
            closeDetailsLoadingState();
        }

        function applySavedButtonState(buttonElement, isSaved) {
            if (!buttonElement) {
                return;
            }

            buttonElement.classList.toggle('saved', Boolean(isSaved));
            buttonElement.setAttribute('aria-pressed', isSaved ? 'true' : 'false');
            buttonElement.title = isSaved ? 'Remove from Saved' : 'Save Property';

            const textNode = buttonElement.querySelector('.btn-save-property-text');
            if (textNode) {
                textNode.textContent = isSaved ? 'Saved Property' : 'Save Property';
            }
        }

        async function getFavoriteStatus(listingId) {
            try {
                const response = await fetch('<?= base_url('buyer/favorites/is-favorited') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: 'listing_id=' + encodeURIComponent(String(listingId || 0))
                });

                const data = await response.json();
                return Boolean(data && data.isFavorited);
            } catch (error) {
                return false;
            }
        }

        function syncFavoriteButtonsForListing(listingId, isSaved) {
            const selector = `.favorite-btn[data-listing-id="${listingId}"]`;
            document.querySelectorAll(selector).forEach((button) => {
                button.classList.toggle('saved', Boolean(isSaved));
                button.title = isSaved ? 'Remove from Saved' : 'Save Property';
            });

            const modalSaveBtn = document.getElementById('modalSavePropertyBtn');
            if (modalSaveBtn && Number(modalSaveBtn.dataset.listingId || 0) === Number(listingId)) {
                applySavedButtonState(modalSaveBtn, isSaved);
            }
        }

        // Toggle Favorite (Add/Remove)
        function toggleFavorite(event, buttonElement, listingId) {
            event.stopPropagation();
            
            // Send AJAX request to toggle favorite
            fetch('<?= base_url('buyer/favorites/toggle') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: 'listing_id=' + listingId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle the 'saved' class
                    if (data.action === 'added') {
                        buttonElement.classList.add('saved');
                        buttonElement.title = 'Remove from Saved';
                        syncFavoriteButtonsForListing(listingId, true);
                        showNotification('Added to favorites!', 'success');
                    } else if (data.action === 'removed') {
                        buttonElement.classList.remove('saved');
                        buttonElement.title = 'Save Property';
                        syncFavoriteButtonsForListing(listingId, false);
                        showNotification('Removed from favorites!', 'info');
                    }
                    
                    // Reload saved properties section if it exists
                    if (typeof loadSavedProperties === 'function') {
                        loadSavedProperties();
                    }

                    // Refresh sidebar counters immediately (no wait for polling interval)
                    if (typeof refreshBuyerSidebarCounts === 'function') {
                        refreshBuyerSidebarCounts();
                    }

                    window.dispatchEvent(new window.CustomEvent('buyer:favorite-updated', {
                        detail: {
                            listingId: Number(listingId || 0),
                            action: String(data.action || '')
                        }
                    }));
                } else {
                    showNotification(data.message || 'Unable to update favorite', 'error');
                }
            })
            .catch(error => {
                console.error('Error toggling favorite:', error);
                showNotification('Error updating favorite. Please try again.', 'error');
            });
        }

        // Show notification message
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 12px 20px;
                background: ${type === 'success' ? '#40916c' : type === 'error' ? '#d62828' : '#2d6a4f'};
                color: white;
                border-radius: 8px;
                z-index: 9999;
                animation: slideIn 0.3s ease-in-out;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            `;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-in-out forwards';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Add slide animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Open Full Map Modal
        function openFullMap() {
            const propertyModal = document.getElementById('propertyModal');
            const lat = propertyModal.dataset.lat;
            const lng = propertyModal.dataset.lng;
            const title = propertyModal.dataset.title;
            
            const mapModal = document.getElementById('mapModal');
            document.getElementById('fullMapTitle').textContent = title;
            setFullMapLoading(true);
            
            mapModal.classList.add('active');
            window.setTimeout(() => renderFullMap(parseFloat(lat), parseFloat(lng), title || 'Property map'), 0);
        }

        // Close Map Modal
        function closeMapModal() {
            document.getElementById('mapModal').classList.remove('active');
            destroyFullMap();
            setFullMapLoading(false);
        }

        // Close modals on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                if (e.target.id === 'propertyModal') {
                    closePropertyModal();
                } else if (e.target.id === 'mapModal') {
                    closeMapModal();
                }
            }
        });

        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePropertyModal();
                closeMapModal();
            }
        });
    </script>

    <!-- Property Detail Modal -->
    <div id="propertyModal" class="modal-overlay">
        <div class="property-modal">
            <button class="modal-report-btn" id="reportListingBtn" title="Report listing" aria-label="Report listing" onclick="openListingReportModal()">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22 6 12 13 2 6"></polyline></svg>
            </button>
            <button class="modal-close" onclick="closePropertyModal()">
                <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            
            <div class="modal-gallery">
                <img id="modalMainImage" src="" alt="Property" class="modal-main-image">
                <div id="modalThumbnails" class="modal-thumbnails"></div>
            </div>
            
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle" class="modal-title"></h2>
                    <div class="modal-price-section">
                        <span id="modalPrice" class="modal-price"></span>
                        <span id="modalPricePerSqm" class="modal-price-sqm"></span>
                    </div>
                    <div class="modal-location">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span id="modalLocation"></span>
                    </div>
                </div>

                <div class="modal-quick-info">
                    <div class="quick-item"><strong>Area:</strong> <span data-modal-field="area" class="quick-value"></span></div>
                    <div class="quick-item"><strong>Type:</strong> <span data-modal-field="type" class="quick-value"></span></div>
                    <div class="quick-item"><strong>Status:</strong> <span data-modal-field="listing_status" class="quick-value"></span></div>
                    <div class="quick-item"><strong>Title:</strong> <span data-modal-field="title_status" class="quick-value"></span></div>
                </div>

                <div class="modal-details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Land Area</span>
                        <span data-modal-field="area" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Property Type</span>
                        <span data-modal-field="type" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Title Status</span>
                        <span data-modal-field="title_status" class="detail-value"></span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Description</h3>
                    <p id="modalDescription"></p>
                </div>

                <div class="detail-section">
                    <h3>Complete Property Details</h3>
                    <div class="listing-details-list">
                        <div class="listing-details-row"><span>Listing ID</span><strong id="modalListingId"></strong></div>
                        <div class="listing-details-row"><span>Address</span><strong id="modalAddressLine"></strong></div>
                        <div class="listing-details-row"><span>Barangay</span><strong id="modalBarangay"></strong></div>
                        <div class="listing-details-row"><span>City</span><strong id="modalCity"></strong></div>
                        <div class="listing-details-row"><span>Province</span><strong id="modalProvince"></strong></div>
                        <div class="listing-details-row"><span>Road Access</span><strong id="modalRoadAccess"></strong></div>
                        <div class="listing-details-row"><span>View Type</span><strong id="modalViewType"></strong></div>
                        <div class="listing-details-row"><span>Investment Ready</span><strong id="modalInvestmentReady"></strong></div>
                        <div class="listing-details-row"><span>Tax Declaration</span><strong id="modalTaxDec"></strong></div>
                        <div class="listing-details-row"><span>LRA Approved Plan</span><strong id="modalLraPlan"></strong></div>
                        <div class="listing-details-row"><span>Mother Title Disclosed</span><strong id="modalMotherTitle"></strong></div>
                        <div class="listing-details-row"><span>Listing Status</span><strong data-modal-field="listing_status"></strong></div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Features & Amenities</h3>
                    <div id="modalFeatures" class="features-grid"></div>
                </div>
                
                <div class="detail-section">
                    <h3>Location Map</h3>
                    <div class="modal-map-container">
                        <iframe id="modalMap" class="modal-map" frameborder="0" scrolling="no"></iframe>
                        <button class="expand-map-btn" onclick="openFullMap()">
                            <svg viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                            View Full Map
                        </button>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Seller Information</h3>
                    <div class="seller-card">
                        <div class="seller-card-avatar" id="sellerAvatar"></div>
                        <div class="seller-card-info">
                            <div class="seller-card-name">
                                <span id="sellerName"></span>
                                <span id="sellerVerified" class="verified-badge">
                                    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                    Verified
                                </span>
                            </div>
                            <div class="seller-card-meta">
                                <span id="sellerListings"></span>
                                <span>•</span>
                                <span id="sellerMember"></span>
                            </div>
                        </div>
                        <div class="seller-card-actions">
                            <button class="btn-contact-seller" id="modalMessageSellerBtn" type="button" onclick="handleModalMessageSellerClick(event)">
                                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                Message Seller
                            </button>
                            <button class="btn-save-property" id="modalSavePropertyBtn" type="button" title="Save Property" aria-pressed="false" onclick="handleModalSavePropertyClick(event, this)">
                                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                <span class="btn-save-property-text">Save Property</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Map Modal -->
    <div id="mapModal" class="modal-overlay map-modal-overlay">
        <div class="full-map-modal">
            <div class="full-map-header">
                <h3 id="fullMapTitle"></h3>
                <button class="modal-close" onclick="closeMapModal()">
                    <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div id="fullMapCanvas" class="full-map-canvas"></div>
            <div id="fullMapLoading" class="map-loading-state">Loading interactive map...</div>
        </div>
    </div>

    <div id="propertyLoadingOverlay" class="property-loading-overlay" aria-hidden="true">
        <div class="property-loading-card">Loading property details...</div>
    </div>

    <!-- Report Modal -->
    <?= view('Reports/ReportModal') ?>

    <!-- Report Modal JavaScript Handler -->
    <script>
        // Listing report reasons
        const listingReportReasons = [
            'Fake listing',
            'Wrong price',
            'Misleading photos',
            'Invalid documents',
            'Duplicate listing',
            'Suspicious seller',
            'Other'
        ];

        // Open listing report modal
        function openListingReportModal() {
            const listingId = document.getElementById('propertyModal')?.dataset?.listingId || 0;
            if (listingId) {
                window.openReportModal('listing', listingId, listingReportReasons);
            } else {
                alert('Unable to report this listing at this time.');
            }
        }
    </script>
</body>
</html>
