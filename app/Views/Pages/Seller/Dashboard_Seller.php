<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Seller Dashboard | Landly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green-900: #0f1b1b;
            --green-800: #14312c;
            --green-700: #1a3d35;
            --cream-100: #f5f5dc;
            --cream-200: #efe7d8;
            --accent: #d2b48c;
            --text-dark: #0f1b1b;
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
                radial-gradient(circle at 20% 30%, rgba(210, 180, 140, 0.03) 0%, transparent 25%),
                radial-gradient(circle at 80% 70%, rgba(210, 180, 140, 0.025) 0%, transparent 30%),
                radial-gradient(circle at 50% 50%, rgba(20, 49, 44, 0.15) 0%, transparent 50%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d2b48c' fill-opacity='0.015'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
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
            width: var(--sidebar-width);
            background: linear-gradient(180deg, rgba(20, 49, 44, 0.95) 0%, rgba(15, 27, 27, 0.98) 100%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(210, 180, 140, 0.15);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(210, 180, 140, 0.1);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: inherit;
        }

        .brand-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent) 0%, #c9a86c 100%);
            color: var(--green-900);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 1.2rem;
            animation: pulse 3s ease-in-out infinite;
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
            border-bottom: 1px solid rgba(210, 180, 140, 0.1);
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
            background: linear-gradient(135deg, var(--accent) 0%, #c9a86c 100%);
            display: grid;
            place-items: center;
            font-weight: 600;
            color: var(--green-900);
            font-size: 1.1rem;
        }

        .user-details h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .user-details span {
            font-size: 0.75rem;
            color: rgba(245, 245, 220, 0.6);
        }

        .verification-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid rgba(46, 204, 113, 0.4);
            border-radius: 12px;
            font-size: 0.65rem;
            color: #2ecc71;
            margin-top: 6px;
        }

        .verification-badge.pending {
            background: rgba(241, 196, 15, 0.2);
            border-color: rgba(241, 196, 15, 0.4);
            color: #f1c40f;
        }

        /* Navigation */
        .sidebar-nav {
            flex: 1;
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
            color: rgba(245, 245, 220, 0.4);
            margin-bottom: 10px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            color: rgba(245, 245, 220, 0.7);
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
            background: rgba(210, 180, 140, 0.08);
            color: var(--cream-100);
        }

        .nav-item:hover::before {
            transform: scaleY(1);
        }

        .nav-item.active {
            background: rgba(210, 180, 140, 0.12);
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
            padding: 20px;
            border-top: 1px solid rgba(210, 180, 140, 0.1);
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
            color: rgba(245, 245, 220, 0.6);
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
            border: 1px solid rgba(210, 180, 140, 0.2);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(210, 180, 140, 0.1);
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
            color: rgba(245, 245, 220, 0.4);
        }

        .search-box svg {
            width: 18px;
            height: 18px;
            stroke: rgba(245, 245, 220, 0.5);
            stroke-width: 2;
            fill: none;
        }

        .notification-btn {
            position: relative;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(210, 180, 140, 0.2);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .notification-btn:hover {
            background: rgba(210, 180, 140, 0.1);
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
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            transition: all 0.4s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(210, 180, 140, 0.3);
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
            background: linear-gradient(135deg, rgba(210, 180, 140, 0.2), rgba(210, 180, 140, 0.05));
            border: 1px solid rgba(210, 180, 140, 0.3);
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
            color: rgba(245, 245, 220, 0.6);
        }

        /* === CONTENT SECTIONS === */
        .content-section {
            display: none;
            animation: fadeInUp 0.5s ease;
        }

        .content-section.active {
            display: block;
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
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(210, 180, 140, 0.1);
        }

        .card-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .card-header-action {
            padding: 6px 14px;
            background: rgba(210, 180, 140, 0.1);
            border: 1px solid rgba(210, 180, 140, 0.3);
            border-radius: 8px;
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .card-header-action:hover {
            background: rgba(210, 180, 140, 0.2);
        }

        .card-body {
            padding: 20px 24px;
        }

        /* Recent Listings */
        .listing-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(210, 180, 140, 0.08);
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
            color: rgba(245, 245, 220, 0.5);
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

        .listing-status.active {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
        }

        .listing-status.pending {
            background: rgba(241, 196, 15, 0.15);
            color: #f1c40f;
        }

        .listing-status.sold {
            background: rgba(155, 89, 182, 0.15);
            color: #9b59b6;
        }

        .listing-status.available {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
        }

        .listing-status.inquiry {
            background: rgba(52, 152, 219, 0.15);
            color: #5dade2;
        }

        .listing-status.reserved {
            background: rgba(241, 196, 15, 0.15);
            color: #f4d03f;
        }

        .listing-status.closed {
            background: rgba(231, 76, 60, 0.15);
            color: #ec7063;
        }

        .listing-status.rejected {
            background: rgba(231, 76, 60, 0.22);
            color: #ff9b8f;
        }

        .verification-status {
            position: static;
            display: inline-flex;
            align-items: center;
            width: fit-content;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.2);
        }

        .verification-status.verified {
            background: rgba(46, 204, 113, 0.2);
            color: #7ef0a3;
        }

        .verification-status.pending {
            background: rgba(241, 196, 15, 0.22);
            color: #ffe082;
        }

        .verification-status.rejected {
            background: rgba(231, 76, 60, 0.24);
            color: #ffb3a8;
        }

        /* Activity Feed */
        .activity-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid rgba(210, 180, 140, 0.08);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(210, 180, 140, 0.1);
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
            color: rgba(245, 245, 220, 0.4);
        }

        /* === MY LISTINGS SECTION === */
        .listings-toolbar {
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
            border: 1px solid rgba(210, 180, 140, 0.2);
            border-radius: 10px;
            color: rgba(245, 245, 220, 0.7);
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: rgba(210, 180, 140, 0.15);
            border-color: var(--accent);
            color: var(--accent);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--accent) 0%, #c9a86c 100%);
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
            box-shadow: 0 8px 25px rgba(210, 180, 140, 0.4);
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
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .listing-card[data-verification-status="verified"] {
            border-color: rgba(46, 204, 113, 0.24);
        }

        .listing-card[data-verification-status="pending"] {
            border-color: rgba(241, 196, 15, 0.28);
        }

        .listing-card[data-verification-status="rejected"] {
            border-color: rgba(231, 76, 60, 0.32);
        }

        .listing-card:hover {
            transform: translateY(-8px);
            border-color: rgba(210, 180, 140, 0.3);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        }

        .listing-card.is-hidden {
            display: none;
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
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .listing-card-badges {
            position: absolute;
            top: 15px;
            left: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: calc(100% - 90px);
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
            background: rgba(15, 27, 27, 0.8);
            backdrop-filter: blur(10px);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
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
            color: rgba(245, 245, 220, 0.6);
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
            border-top: 1px solid rgba(210, 180, 140, 0.1);
            border-bottom: 1px solid rgba(210, 180, 140, 0.1);
            margin-bottom: 12px;
        }

        .listing-card-detail {
            font-size: 0.8rem;
            color: rgba(245, 245, 220, 0.5);
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

        .listing-card-verification-note {
            margin-bottom: 14px;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: 0.8rem;
            line-height: 1.5;
            border: 1px solid transparent;
        }

        .listing-card-verification-note strong {
            color: inherit;
        }

        .listing-card-verification-note.verified {
            background: rgba(46, 204, 113, 0.09);
            border-color: rgba(46, 204, 113, 0.3);
            color: #b8f1c6;
        }

        .listing-card-verification-note.pending {
            background: rgba(241, 196, 15, 0.1);
            border-color: rgba(241, 196, 15, 0.28);
            color: #ffe39b;
        }

        .listing-card-verification-note.rejected {
            background: rgba(231, 76, 60, 0.11);
            border-color: rgba(231, 76, 60, 0.28);
            color: #ffc4ba;
        }

        .listing-card-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent);
        }

        .listing-card-views {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: rgba(245, 245, 220, 0.5);
        }

        .listing-card-views svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            stroke-width: 2;
            fill: none;
        }

        .listings-empty-state {
            display: none;
            padding: 32px;
            border: 1px dashed rgba(210, 180, 140, 0.22);
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.03);
            text-align: center;
            color: rgba(245, 245, 220, 0.72);
        }

        .listings-empty-state.is-visible {
            display: block;
        }

        .listings-empty-state h3 {
            margin-bottom: 8px;
            color: var(--cream-100);
            font-size: 1.1rem;
        }

        .listings-empty-state p {
            font-size: 0.92rem;
            line-height: 1.6;
        }

        /* === ADD LISTING FORM === */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--cream-100);
        }

        .form-group label span {
            color: #e74c3c;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(210, 180, 140, 0.2);
            border-radius: 12px;
            color: var(--cream-100);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(210, 180, 140, 0.15);
        }

        .form-control::placeholder {
            color: rgba(245, 245, 220, 0.4);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='12' viewBox='0 0 12 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2 4L6 8L10 4' stroke='%23d2b48c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            cursor: pointer;
        }

        select.form-control option {
            background: var(--green-900);
            color: var(--cream-100);
        }

        .image-upload {
            border: 2px dashed rgba(210, 180, 140, 0.3);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .image-upload:hover {
            border-color: var(--accent);
            background: rgba(210, 180, 140, 0.05);
        }

        .image-upload svg {
            width: 48px;
            height: 48px;
            stroke: var(--accent);
            stroke-width: 1.5;
            fill: none;
            margin-bottom: 15px;
        }

        .image-upload p {
            color: rgba(245, 245, 220, 0.7);
            font-size: 0.9rem;
        }

        .image-upload span {
            color: var(--accent);
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn-secondary {
            padding: 12px 24px;
            background: transparent;
            border: 1px solid rgba(210, 180, 140, 0.3);
            border-radius: 12px;
            color: var(--cream-100);
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(210, 180, 140, 0.1);
            border-color: var(--accent);
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
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 20px;
            overflow: hidden;
        }

        .messages-list-header {
            padding: 20px;
            border-bottom: 1px solid rgba(210, 180, 140, 0.1);
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
            border-bottom: 1px solid rgba(210, 180, 140, 0.08);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .message-item:hover,
        .message-item.active {
            background: rgba(210, 180, 140, 0.08);
        }

        .message-item.unread {
            background: rgba(210, 180, 140, 0.05);
        }

        .message-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #c9a86c);
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
            color: rgba(245, 245, 220, 0.4);
        }

        .message-preview {
            font-size: 0.8rem;
            color: rgba(245, 245, 220, 0.6);
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
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 20px;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border-bottom: 1px solid rgba(210, 180, 140, 0.1);
        }

        .chat-user-info h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .chat-user-info span {
            font-size: 0.8rem;
            color: rgba(245, 245, 220, 0.5);
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
            background: linear-gradient(135deg, var(--accent), #c9a86c);
            color: var(--green-900);
            border-bottom-right-radius: 4px;
        }

        .chat-time {
            font-size: 0.7rem;
            color: rgba(245, 245, 220, 0.4);
            margin-top: 5px;
        }

        .chat-message.sent .chat-time {
            text-align: right;
        }

        .chat-input {
            display: flex;
            gap: 12px;
            padding: 20px;
            border-top: 1px solid rgba(210, 180, 140, 0.1);
        }

        .chat-input input {
            flex: 1;
            padding: 14px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(210, 180, 140, 0.2);
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
            background: linear-gradient(135deg, var(--accent), #c9a86c);
            border: none;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .chat-send-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(210, 180, 140, 0.4);
        }

        .chat-send-btn svg {
            width: 20px;
            height: 20px;
            stroke: var(--green-900);
            stroke-width: 2;
            fill: none;
        }

        /* === INQUIRIES SECTION === */
        .inquiries-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .inquiry-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 16px;
            padding: 20px;
            transition: all 0.3s ease;
        }

        .inquiry-card:hover {
            border-color: rgba(210, 180, 140, 0.3);
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #c9a86c);
            display: grid;
            place-items: center;
            font-weight: 600;
            color: var(--green-900);
        }

        .inquiry-user-info h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .inquiry-user-info span {
            font-size: 0.8rem;
            color: rgba(245, 245, 220, 0.5);
        }

        .inquiry-status {
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .inquiry-status.new {
            background: rgba(52, 152, 219, 0.15);
            color: #3498db;
        }

        .inquiry-status.responded {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
        }

        .inquiry-property {
            padding: 12px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .inquiry-property-title {
            font-size: 0.85rem;
            color: var(--accent);
            margin-bottom: 4px;
        }

        .inquiry-property-detail {
            font-size: 0.8rem;
            color: rgba(245, 245, 220, 0.5);
        }

        .inquiry-message {
            font-size: 0.9rem;
            color: rgba(245, 245, 220, 0.8);
            line-height: 1.6;
            margin-bottom: 15px;
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

        .inquiry-btn.reply {
            background: linear-gradient(135deg, var(--accent), #c9a86c);
            border: none;
            color: var(--green-900);
        }

        .inquiry-btn.reply:hover {
            box-shadow: 0 5px 15px rgba(210, 180, 140, 0.3);
        }

        .inquiry-btn.dismiss {
            background: transparent;
            border: 1px solid rgba(210, 180, 140, 0.3);
            color: var(--cream-100);
        }

        .inquiry-btn.dismiss:hover {
            background: rgba(210, 180, 140, 0.1);
        }

        /* === ANALYTICS SECTION === */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .chart-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 20px;
            padding: 24px;
        }

        .chart-card.full-width {
            grid-column: span 2;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--cream-100);
        }

        .chart-period {
            display: flex;
            gap: 8px;
        }

        .period-btn {
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 8px;
            color: rgba(245, 245, 220, 0.6);
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .period-btn:hover,
        .period-btn.active {
            background: rgba(210, 180, 140, 0.15);
            border-color: var(--accent);
            color: var(--accent);
        }

        .chart-placeholder {
            height: 200px;
            background: linear-gradient(135deg, rgba(210, 180, 140, 0.05), rgba(210, 180, 140, 0.02));
            border-radius: 12px;
            display: flex;
            align-items: flex-end;
            justify-content: space-around;
            padding: 20px;
        }

        .chart-bar {
            width: 30px;
            background: linear-gradient(180deg, var(--accent), rgba(210, 180, 140, 0.3));
            border-radius: 6px 6px 0 0;
            transition: all 0.3s ease;
        }

        .chart-bar:hover {
            background: linear-gradient(180deg, #c9a86c, var(--accent));
        }

        .analytics-metrics {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .metric-item {
            text-align: center;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
        }

        .metric-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .metric-label {
            font-size: 0.8rem;
            color: rgba(245, 245, 220, 0.6);
        }

        /* === VERIFICATION SECTION === */
        .verification-container {
            max-width: 700px;
        }

        .verification-status-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            text-align: center;
        }

        .verification-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(46, 204, 113, 0.15);
            display: grid;
            place-items: center;
            margin: 0 auto 20px;
        }

        .verification-icon.pending {
            background: rgba(241, 196, 15, 0.15);
        }

        .verification-icon.unverified {
            background: rgba(231, 76, 60, 0.15);
        }

        .verification-icon svg {
            width: 40px;
            height: 40px;
            stroke-width: 2;
            fill: none;
        }

        .verification-icon svg.verified {
            stroke: #2ecc71;
        }

        .verification-icon svg.pending {
            stroke: #f1c40f;
        }

        .verification-icon svg.unverified {
            stroke: #e74c3c;
        }

        .verification-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--cream-100);
            margin-bottom: 8px;
        }

        .verification-desc {
            color: rgba(245, 245, 220, 0.6);
            font-size: 0.9rem;
            max-width: 400px;
            margin: 0 auto;
        }

        .verification-steps {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(210, 180, 140, 0.15);
            border-radius: 20px;
            padding: 30px;
        }

        .verification-steps h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--cream-100);
            margin-bottom: 25px;
        }

        .step-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(210, 180, 140, 0.1);
        }

        .step-item:last-child {
            border-bottom: none;
        }

        .step-number {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(210, 180, 140, 0.15);
            display: grid;
            place-items: center;
            font-weight: 600;
            color: var(--accent);
            flex-shrink: 0;
        }

        .step-number.completed {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }

        .step-content h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--cream-100);
            margin-bottom: 4px;
        }

        .step-content p {
            font-size: 0.85rem;
            color: rgba(245, 245, 220, 0.6);
        }

        .step-action {
            margin-left: auto;
            align-self: center;
        }

        .step-btn {
            padding: 8px 16px;
            background: rgba(210, 180, 140, 0.15);
            border: 1px solid rgba(210, 180, 140, 0.3);
            border-radius: 8px;
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .step-btn:hover {
            background: rgba(210, 180, 140, 0.25);
        }

        .step-btn.completed {
            background: rgba(46, 204, 113, 0.15);
            border-color: rgba(46, 204, 113, 0.3);
            color: #2ecc71;
            cursor: default;
        }

        /* === RESPONSIVE === */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .chart-card.full-width {
                grid-column: span 1;
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

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
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

            .listings-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .toolbar-filters {
                overflow-x: auto;
                padding-bottom: 10px;
            }

            .mobile-menu-btn {
                display: flex;
            }
        }

        .mobile-menu-btn {
            display: none;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(210, 180, 140, 0.2);
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
            stroke: rgba(210, 180, 140, 0.3);
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
            color: rgba(245, 245, 220, 0.5);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="<?= base_url('/') ?>" class="brand">
                    <div class="brand-badge">L</div>
                    <div>
                        <div class="brand-text">Landly</div>
                        <div class="brand-subtitle">Seller Portal</div>
                    </div>
                </a>
            </div>

            <div class="user-profile">
                <div class="user-info">
                    <div class="user-avatar">JS</div>
                    <div class="user-details">
                        <h4>John Seller</h4>
                        <span>john@example.com</span>
                        <div class="verification-badge">
                            <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" fill="none" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            Verified Seller
                        </div>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Main</div>
                    <a href="#" class="nav-item active" data-section="dashboard">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="#" class="nav-item" data-section="listings">
                        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <span>My Listings</span>
                        <span class="nav-badge">12</span>
                    </a>
                    <a href="#" class="nav-item" data-section="add-listing">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        <span>Add Listing</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Communication</div>
                    <a href="#" class="nav-item" data-section="messages">
                        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <span>Messages</span>
                        <span class="nav-badge">5</span>
                    </a>
                    <a href="#" class="nav-item" data-section="inquiries">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <span>Inquiries</span>
                        <span class="nav-badge">3</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Account</div>
                    <a href="#" class="nav-item" data-section="verification">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>Verification</span>
                    </a>
                </div>
            </nav>

            <div class="sidebar-footer">
                <button class="logout-btn" onclick="window.location.href='<?= base_url('/') ?>'">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <div class="page-title">
                    <h1 id="page-heading">Dashboard</h1>
                    <p id="page-subheading">Welcome back! Here's what's happening with your listings.</p>
                </div>
                <div class="top-actions">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="search-box">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" id="seller-listings-search" placeholder="Search listings..." autocomplete="off">
                    </div>
                    <button class="notification-btn">
                        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="notification-dot"></span>
                    </button>
                </div>
            </div>

            <!-- Dashboard Section -->
            <?= view('Pages/Seller/Components/DashboardSection') ?>

            <!-- My Listings Section -->
            <?= view('Pages/Seller/Components/ListingSection', ['sellerListings' => $sellerListings ?? [], 'listingCounts' => $listingCounts ?? []]) ?>

            <!-- Add Listing Section -->
            <?= view('Pages/Seller/Components/AddListingSection') ?>

            <!-- Messages Section -->
            <?= view('Pages/Seller/Components/MessagesSection') ?>

            <!-- Inquiries Section -->
            <?= view('Pages/Seller/Components/InquiriesSection') ?>

            <!-- Analytics Section -->
            <!-- <section id="section-analytics" class="content-section">
                <div class="analytics-grid">
                    <div class="chart-card full-width">
                        <div class="chart-header">
                            <h4 class="chart-title">Listing Views Overview</h4>
                            <div class="chart-period">
                                <button class="period-btn">7D</button>
                                <button class="period-btn active">30D</button>
                                <button class="period-btn">90D</button>
                            </div>
                        </div>
                        <div class="chart-placeholder">
                            <div class="chart-bar" style="height: 40%"></div>
                            <div class="chart-bar" style="height: 65%"></div>
                            <div class="chart-bar" style="height: 45%"></div>
                            <div class="chart-bar" style="height: 80%"></div>
                            <div class="chart-bar" style="height: 55%"></div>
                            <div class="chart-bar" style="height: 90%"></div>
                            <div class="chart-bar" style="height: 70%"></div>
                            <div class="chart-bar" style="height: 85%"></div>
                            <div class="chart-bar" style="height: 60%"></div>
                            <div class="chart-bar" style="height: 75%"></div>
                            <div class="chart-bar" style="height: 95%"></div>
                            <div class="chart-bar" style="height: 50%"></div>
                        </div>
                        <div class="analytics-metrics">
                            <div class="metric-item">
                                <div class="metric-value">2,847</div>
                                <div class="metric-label">Total Views</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value">237</div>
                                <div class="metric-label">Avg. Daily Views</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value">+18%</div>
                                <div class="metric-label">vs Last Month</div>
                            </div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h4 class="chart-title">Top Performing Listings</h4>
                        </div>
                        <div class="card-body" style="padding: 0;">
                            <div class="listing-item">
                                <div class="listing-thumb">
                                    <img src="https://images.unsplash.com/photo-1500076656116-558758c991c1?w=200" alt="Land">
                                </div>
                                <div class="listing-info">
                                    <h4>Residential Lot in Laguna</h4>
                                    <div class="listing-meta">
                                        <span>412 views</span>
                                    </div>
                                </div>
                            </div>
                            <div class="listing-item">
                                <div class="listing-thumb">
                                    <img src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=200" alt="Land">
                                </div>
                                <div class="listing-info">
                                    <h4>Agricultural Land in Batangas</h4>
                                    <div class="listing-meta">
                                        <span>245 views</span>
                                    </div>
                                </div>
                            </div>
                            <div class="listing-item">
                                <div class="listing-thumb">
                                    <img src="https://images.unsplash.com/photo-1628624747186-a941c476b7ef?w=200" alt="Land">
                                </div>
                                <div class="listing-info">
                                    <h4>Commercial Lot in Tagaytay</h4>
                                    <div class="listing-meta">
                                        <span>189 views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h4 class="chart-title">Inquiry Sources</h4>
                        </div>
                        <div class="analytics-metrics" style="margin-top: 0;">
                            <div class="metric-item">
                                <div class="metric-value">65%</div>
                                <div class="metric-label">Direct Search</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value">25%</div>
                                <div class="metric-label">Browse</div>
                            </div>
                            <div class="metric-item">
                                <div class="metric-value">10%</div>
                                <div class="metric-label">Referral</div>
                            </div>
                        </div>
                        <div style="margin-top: 20px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                <div style="flex: 1; height: 8px; background: rgba(0,0,0,0.2); border-radius: 4px; overflow: hidden;">
                                    <div style="width: 65%; height: 100%; background: linear-gradient(90deg, var(--accent), #c9a86c);"></div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                <div style="flex: 1; height: 8px; background: rgba(0,0,0,0.2); border-radius: 4px; overflow: hidden;">
                                    <div style="width: 25%; height: 100%; background: linear-gradient(90deg, #2ecc71, #27ae60);"></div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; height: 8px; background: rgba(0,0,0,0.2); border-radius: 4px; overflow: hidden;">
                                    <div style="width: 10%; height: 100%; background: linear-gradient(90deg, #3498db, #2980b9);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section> -->

            <!-- Verification Section -->
            <?= view('Pages/Seller/Components/VerificationSection') ?>
        </main>
    </div>

    <script>
        // Navigation functionality
        const navItems = document.querySelectorAll('.nav-item[data-section]');
        const sections = document.querySelectorAll('.content-section');
        const pageHeading = document.getElementById('page-heading');
        const pageSubheading = document.getElementById('page-subheading');

        const sectionInfo = {
            'dashboard': {
                title: 'Dashboard',
                subtitle: "Welcome back! Here's what's happening with your listings."
            },
            'listings': {
                title: 'My Listings',
                subtitle: 'Manage all your property listings in one place.'
            },
            'add-listing': {
                title: 'Add New Listing',
                subtitle: 'Create a new property listing to attract potential buyers.'
            },
            'messages': {
                title: 'Messages',
                subtitle: 'Communicate with potential buyers and answer their questions.'
            },
            'inquiries': {
                title: 'Inquiries',
                subtitle: 'View and respond to property inquiries from interested buyers.'
            },
            'analytics': {
                title: 'Analytics',
                subtitle: 'Track your listing performance and buyer engagement.'
            },
            'verification': {
                title: 'Verification',
                subtitle: 'Complete your seller verification to build trust with buyers.'
            }
        };

        function showSection(sectionName) {
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

            // Close mobile sidebar
            document.querySelector('.sidebar').classList.remove('open');
        }

        navItems.forEach(item => {
            item.addEventListener('click', (e) => {
                e.preventDefault();
                const sectionName = item.dataset.section;
                showSection(sectionName);
            });
        });

        // Mobile menu toggle
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
        }

        const listingSearchInput = document.getElementById('seller-listings-search');
        const listingFilterBtns = document.querySelectorAll('#section-listings .filter-btn[data-filter]');
        const listingCards = Array.from(document.querySelectorAll('#section-listings .listing-card'));
        const listingEmptyState = document.getElementById('seller-listings-empty');
        const listingEmptyTitle = document.getElementById('seller-listings-empty-title');
        const listingEmptyDescription = document.getElementById('seller-listings-empty-description');
        let activeListingFilter = 'all';

        function updateListingEmptyState(visibleCount, hasQuery) {
            if (!listingEmptyState || !listingEmptyTitle || !listingEmptyDescription) {
                return;
            }

            const hasListings = listingCards.length > 0;
            const shouldShowEmpty = !hasListings || visibleCount === 0;

            listingEmptyState.hidden = !shouldShowEmpty;
            listingEmptyState.classList.toggle('is-visible', shouldShowEmpty);

            if (!shouldShowEmpty) {
                return;
            }

            if (!hasListings) {
                listingEmptyTitle.textContent = 'No listings yet';
                listingEmptyDescription.textContent = 'Your property listings for this account will appear here once they are created.';
                return;
            }

            listingEmptyTitle.textContent = 'No matching listings';
            listingEmptyDescription.textContent = hasQuery || activeListingFilter !== 'all'
                ? 'Try a different status filter or search keyword.'
                : 'No listings are available for this view.';
        }

        function applyListingFilters() {
            const query = (listingSearchInput?.value || '').trim().toLowerCase();
            let visibleCount = 0;

            listingCards.forEach((card) => {
                const matchesFilter = activeListingFilter === 'all' || card.dataset.listingStatus === activeListingFilter;
                const searchIndex = [
                    card.dataset.searchTitle || '',
                    card.dataset.searchLocation || '',
                    card.dataset.searchType || '',
                    card.dataset.searchDocument || '',
                    card.dataset.searchVerification || ''
                ].join(' ').toLowerCase();
                const matchesSearch = query === '' || searchIndex.includes(query);
                const isVisible = matchesFilter && matchesSearch;

                card.hidden = !isVisible;
                card.classList.toggle('is-hidden', !isVisible);

                if (isVisible) {
                    visibleCount += 1;
                }
            });

            updateListingEmptyState(visibleCount, query !== '');
        }

        listingFilterBtns.forEach((btn) => {
            btn.addEventListener('click', () => {
                activeListingFilter = btn.dataset.filter || 'all';
                listingFilterBtns.forEach((filterBtn) => filterBtn.classList.remove('active'));
                btn.classList.add('active');
                applyListingFilters();
            });
        });

        listingSearchInput?.addEventListener('input', applyListingFilters);
        applyListingFilters();

        // Period buttons functionality
        const periodBtns = document.querySelectorAll('.period-btn');
        periodBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                periodBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

        // Message item click
        const messageItems = document.querySelectorAll('.message-item');
        messageItems.forEach(item => {
            item.addEventListener('click', () => {
                messageItems.forEach(m => m.classList.remove('active'));
                item.classList.add('active');
                item.classList.remove('unread');
                item.querySelector('.message-unread-dot')?.remove();
            });
        });
    </script>
</body>
</html>