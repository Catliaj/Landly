<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Buyer Dashboard | Landly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
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
            width: var(--sidebar-width);
            background: linear-gradient(180deg, rgba(45, 106, 79, 0.95) 0%, rgba(13, 40, 24, 0.98) 100%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(149, 213, 178, 0.15);
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
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
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

        .inquiry-property {
            display: flex;
            align-items: center;
            gap: 12px;
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
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
            opacity: 1;
        }

        .property-modal {
            background: linear-gradient(145deg, var(--green-800), var(--green-900));
            border: 1px solid rgba(149, 213, 178, 0.2);
            border-radius: 24px;
            width: 100%;
            max-width: 1000px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: modalSlideIn 0.4s ease;
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
            padding: 20px 24px;
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .modal-header h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--cream-100);
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

        .modal-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            flex: 1;
            overflow-y: auto;
        }

        .modal-gallery {
            padding: 24px;
            border-right: 1px solid rgba(149, 213, 178, 0.1);
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
            height: 200px;
            border-radius: 16px;
            overflow: hidden;
            margin-top: 15px;
            background: var(--green-700);
            position: relative;
        }

        .modal-map iframe {
            width: 100%;
            height: 100%;
            border: none;
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
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 12px;
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

        .seller-card-info span {
            font-size: 0.8rem;
            color: rgba(254, 250, 224, 0.5);
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
            z-index: 1100;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .map-modal-overlay.active {
            display: flex;
        }

        .map-modal {
            width: 100%;
            max-width: 1200px;
            height: 80vh;
            background: var(--green-900);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(149, 213, 178, 0.2);
        }

        .map-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: var(--green-800);
            border-bottom: 1px solid rgba(149, 213, 178, 0.1);
        }

        .map-modal-header h3 {
            font-size: 1rem;
            color: var(--cream-100);
        }

        .map-modal-body {
            height: calc(100% - 60px);
        }

        .map-modal-body iframe {
            width: 100%;
            height: 100%;
            border: none;
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
                        <div class="brand-subtitle">Buyer Portal</div>
                    </div>
                </a>
            </div>

            <div class="user-profile">
                <div class="user-info">
                    <div class="user-avatar">JB</div>
                    <div class="user-details">
                        <h4>John Buyer</h4>
                        <span>buyer@example.com</span>
                        <div class="member-badge">
                            <svg viewBox="0 0 24 24" width="12" height="12" stroke="currentColor" fill="none" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                            Active Buyer
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
                    <a href="#" class="nav-item" data-section="browse">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <span>Browse Listings</span>
                    </a>
                    <a href="#" class="nav-item" data-section="saved">
                        <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                        <span>Saved Properties</span>
                        <span class="nav-badge">5</span>
                    </a>
                </div>

                <div class="nav-section">
                    <div class="nav-section-title">Communication</div>
                    <a href="#" class="nav-item" data-section="inquiries">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                        <span>My Inquiries</span>
                        <span class="nav-badge">3</span>
                    </a>
                    <a href="#" class="nav-item" data-section="messages">
                        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <span>Messages</span>
                        <span class="nav-badge">2</span>
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
                    <p id="page-subheading">Welcome back! Discover your perfect land investment.</p>
                </div>
                <div class="top-actions">
                    <button class="mobile-menu-btn" onclick="toggleSidebar()">
                        <svg viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="search-box">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        <input type="text" placeholder="Search properties...">
                    </div>
                    <button class="notification-btn">
                        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                        <span class="notification-dot"></span>
                    </button>
                </div>
            </div>

            <!-- Dashboard Section -->
            <?= view('Pages/Buyer/Components/DashboardSection') ?>

            <!-- Browse Listings Section -->
            <?= view('Pages/Buyer/Components/BrowseListingSection') ?>

            <!-- Saved Properties Section -->
            <?= view('Pages/Buyer/Components/SavedPropertiesSection') ?>

            <!-- My Inquiries Section -->
            <?= view('Pages/Buyer/Components/InquiriesSection') ?>

            <!-- Messages Section -->
            <?= view('Pages/Buyer/Components/MessageSection') ?>

            <!-- Profile Section -->
            <?= view('Pages/Buyer/Components/ProfileSection') ?>
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
                subtitle: 'Welcome back! Discover your perfect land investment.'
            },
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

        // Filter buttons functionality
        const filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const parent = btn.closest('.toolbar-filters');
                parent.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });

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
        const propertyData = {
            1: {
                title: 'Prime Agricultural Land in Cavite',
                price: '₱4,500,000',
                pricePerSqm: '₱900/sqm',
                area: '5,000 sqm',
                type: 'Agricultural',
                titleStatus: 'Clean Title',
                location: 'Silang, Cavite',
                coordinates: { lat: 14.2315, lng: 120.9767 },
                images: [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
                    'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=800',
                    'https://images.unsplash.com/photo-1500076656116-558758c991c1?w=800',
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800'
                ],
                description: 'This prime agricultural land is perfect for farming, orchard development, or future residential conversion. Features fertile soil, natural water source, and easy access to main roads. Surrounded by established farms with excellent growth potential.',
                features: ['Fertile Soil', 'Water Source', 'Road Access', 'Electricity Available', 'Near Town Center', 'Flat Terrain'],
                seller: { name: 'Juan Seller', initials: 'JS', phone: '+63 912 345 6789', email: 'juan.seller@email.com', verified: true, listings: 12, memberSince: 'Jan 2022' }
            },
            2: {
                title: 'Commercial Lot in Tagaytay',
                price: '₱8,200,000',
                pricePerSqm: '₱6,833/sqm',
                area: '1,200 sqm',
                type: 'Commercial',
                titleStatus: 'Clean Title',
                location: 'Tagaytay City, Cavite',
                coordinates: { lat: 14.1153, lng: 120.9621 },
                images: [
                    'https://images.unsplash.com/photo-1628624747186-a941c476b7ef?w=800',
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800'
                ],
                description: 'Premium commercial lot in the heart of Tagaytay with stunning Taal Lake views. Ideal for hotel, restaurant, or resort development. High foot traffic area with excellent tourism potential year-round.',
                features: ['Taal Lake View', 'High Traffic Area', 'Near Tourist Spots', 'Main Road Frontage', 'All Utilities', 'Flat Corner Lot'],
                seller: { name: 'Maria Seller', initials: 'MS', phone: '+63 923 456 7890', email: 'maria.seller@email.com', verified: true, listings: 8, memberSince: 'Mar 2021' }
            },
            3: {
                title: 'Residential Lot in Laguna',
                price: '₱2,800,000',
                pricePerSqm: '₱3,500/sqm',
                area: '800 sqm',
                type: 'Residential',
                titleStatus: 'Clean Title',
                location: 'San Pablo City, Laguna',
                coordinates: { lat: 14.0685, lng: 121.3254 },
                images: [
                    'https://images.unsplash.com/photo-1500076656116-558758c991c1?w=800',
                    'https://images.unsplash.com/photo-1628624747186-a941c476b7ef?w=800',
                    'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=800'
                ],
                description: 'Beautiful residential lot in a peaceful subdivision in San Pablo City. Near schools, churches, and commercial centers. Perfect for building your dream home in a family-friendly community.',
                features: ['Subdivision Lot', 'Near Schools', 'Gated Community', 'Paved Roads', 'Underground Utilities', 'Park Access'],
                seller: { name: 'Pedro Seller', initials: 'PS', phone: '+63 934 567 8901', email: 'pedro.seller@email.com', verified: false, listings: 3, memberSince: 'Aug 2023' }
            },
            4: {
                title: 'Farm Land in Quezon',
                price: '₱6,500,000',
                pricePerSqm: '₱650/sqm',
                area: '10,000 sqm',
                type: 'Agricultural',
                titleStatus: 'Tax Declaration',
                location: 'Lucena City, Quezon',
                coordinates: { lat: 13.9373, lng: 121.6170 },
                images: [
                    'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800',
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800',
                    'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=800'
                ],
                description: 'Expansive farm land ideal for coconut plantation, fruit orchards, or livestock farming. Features natural irrigation from nearby river and established mango trees. Great investment opportunity in growing agricultural area.',
                features: ['River Access', 'Existing Mango Trees', 'Irrigation System', 'Farm House Included', 'Caretaker Available', 'Productive Land'],
                seller: { name: 'Ana Seller', initials: 'AS', phone: '+63 945 678 9012', email: 'ana.seller@email.com', verified: true, listings: 15, memberSince: 'Jun 2020' }
            },
            5: {
                title: 'Beach Lot in Batangas',
                price: '₱15,000,000',
                pricePerSqm: '₱6,000/sqm',
                area: '2,500 sqm',
                type: 'Commercial',
                titleStatus: 'Clean Title',
                location: 'Nasugbu, Batangas',
                coordinates: { lat: 14.0714, lng: 120.6364 },
                images: [
                    'https://images.unsplash.com/photo-1518173946687-a4c036bc1bf3?w=800',
                    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800',
                    'https://images.unsplash.com/photo-1519046904884-53103b34b206?w=800',
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'
                ],
                description: 'Stunning beachfront property with white sand beach access. Perfect for resort development, beach house, or eco-tourism venture. Crystal clear waters and breathtaking sunset views. A rare find in the area.',
                features: ['Beachfront', 'White Sand', 'Clear Waters', 'Sunset View', 'Electricity Available', 'Road Access'],
                seller: { name: 'Rico Seller', initials: 'RS', phone: '+63 956 789 0123', email: 'rico.seller@email.com', verified: true, listings: 6, memberSince: 'Nov 2021' }
            },
            6: {
                title: 'Mountain View Lot in Rizal',
                price: '₱5,400,000',
                pricePerSqm: '₱1,800/sqm',
                area: '3,000 sqm',
                type: 'Residential',
                titleStatus: 'Clean Title',
                location: 'Tanay, Rizal',
                coordinates: { lat: 14.4969, lng: 121.2847 },
                images: [
                    'https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=800',
                    'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800',
                    'https://images.unsplash.com/photo-1501785888041-af3ef285b470?w=800'
                ],
                description: 'Breathtaking mountain view lot perfect for vacation home or eco-retreat. Cool climate year-round with stunning panoramic views of Sierra Madre mountains. Surrounded by nature with nearby hiking trails and waterfalls.',
                features: ['Mountain View', 'Cool Climate', 'Near Waterfalls', 'Hiking Trails', 'Private Location', 'Spring Water Source'],
                seller: { name: 'Carlo Seller', initials: 'CS', phone: '+63 967 890 1234', email: 'carlo.seller@email.com', verified: false, listings: 4, memberSince: 'Feb 2024' }
            }
        };

        // Open Property Modal
        function openPropertyModal(propertyId) {
            const property = propertyData[propertyId];
            if (!property) return;

            const modal = document.getElementById('propertyModal');
            
            // Set main image
            document.getElementById('modalMainImage').src = property.images[0];
            
            // Set thumbnails
            const thumbsContainer = document.getElementById('modalThumbnails');
            thumbsContainer.innerHTML = property.images.map((img, idx) => `
                <img src="${img}" alt="Thumbnail ${idx + 1}" class="modal-thumb ${idx === 0 ? 'active' : ''}" onclick="changeMainImage('${img}', this)">
            `).join('');
            
            // Set property details
            document.getElementById('modalTitle').textContent = property.title;
            document.getElementById('modalPrice').textContent = property.price;
            document.getElementById('modalPricePerSqm').textContent = property.pricePerSqm;
            document.getElementById('modalLocation').textContent = property.location;
            document.getElementById('modalArea').textContent = property.area;
            document.getElementById('modalType').textContent = property.type;
            document.getElementById('modalTitleStatus').textContent = property.titleStatus;
            document.getElementById('modalDescription').textContent = property.description;
            
            // Set features
            const featuresContainer = document.getElementById('modalFeatures');
            featuresContainer.innerHTML = property.features.map(feature => `
                <span class="feature-tag">${feature}</span>
            `).join('');
            
            // Set map
            const mapIframe = document.getElementById('modalMap');
            mapIframe.src = `https://www.openstreetmap.org/export/embed.html?bbox=${property.coordinates.lng - 0.01}%2C${property.coordinates.lat - 0.01}%2C${property.coordinates.lng + 0.01}%2C${property.coordinates.lat + 0.01}&layer=mapnik&marker=${property.coordinates.lat}%2C${property.coordinates.lng}`;
            
            // Store coordinates for full map
            modal.dataset.lat = property.coordinates.lat;
            modal.dataset.lng = property.coordinates.lng;
            modal.dataset.title = property.title;
            
            // Set seller info
            document.getElementById('sellerAvatar').textContent = property.seller.initials;
            document.getElementById('sellerName').textContent = property.seller.name;
            document.getElementById('sellerVerified').style.display = property.seller.verified ? 'inline-flex' : 'none';
            document.getElementById('sellerListings').textContent = `${property.seller.listings} listings`;
            document.getElementById('sellerMember').textContent = `Member since ${property.seller.memberSince}`;
            
            // Show modal
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
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
        }

        // Open Full Map Modal
        function openFullMap() {
            const propertyModal = document.getElementById('propertyModal');
            const lat = propertyModal.dataset.lat;
            const lng = propertyModal.dataset.lng;
            const title = propertyModal.dataset.title;
            
            const mapModal = document.getElementById('mapModal');
            document.getElementById('fullMapTitle').textContent = title;
            document.getElementById('fullMapIframe').src = `https://www.openstreetmap.org/export/embed.html?bbox=${parseFloat(lng) - 0.02}%2C${parseFloat(lat) - 0.02}%2C${parseFloat(lng) + 0.02}%2C${parseFloat(lat) + 0.02}&layer=mapnik&marker=${lat}%2C${lng}`;
            
            mapModal.classList.add('active');
        }

        // Close Map Modal
        function closeMapModal() {
            document.getElementById('mapModal').classList.remove('active');
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
                        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        <span id="modalLocation"></span>
                    </div>
                </div>
                
                <div class="modal-details-grid">
                    <div class="detail-item">
                        <span class="detail-label">Land Area</span>
                        <span id="modalArea" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Property Type</span>
                        <span id="modalType" class="detail-value"></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Title Status</span>
                        <span id="modalTitleStatus" class="detail-value"></span>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Description</h3>
                    <p id="modalDescription"></p>
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
                            <button class="btn-contact-seller">
                                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                Message Seller
                            </button>
                            <button class="btn-save-property">
                                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                Save Property
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
            <iframe id="fullMapIframe" class="full-map-iframe" frameborder="0" scrolling="no"></iframe>
        </div>
    </div>
</body>
</html>
