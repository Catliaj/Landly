<?php helper('html'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | Landly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            --sidebar-width: 280px;
            --chart-teal: #1D9E75;
            --chart-amber: #EF9F27;
            --chart-red: #E24B4A;
            --chart-gray: #888780;
            --chart-green: #639922;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: "Inter", system-ui, sans-serif; }

        body { background-color: var(--green-900); color: var(--cream-100); min-height: 100vh; overflow-x: hidden; line-height: 1.6; }

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

        .dashboard-container { display: flex; min-height: 100vh; position: relative; z-index: 1; }
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, rgba(45, 106, 79, 0.95) 0%, rgba(13, 40, 24, 0.98) 100%);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
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
        .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit; }
        .brand-badge { width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%); color: var(--green-900); display: grid; place-items: center; font-weight: 700; font-size: 1.2rem; animation: pulse 3s ease-in-out infinite; }
        .brand-text { font-weight: 700; font-size: 1.3rem; letter-spacing: -0.5px; }
        .brand-subtitle { font-size: 0.7rem; color: var(--accent); text-transform: uppercase; letter-spacing: 1.5px; margin-top: 2px; }
        .sidebar-nav { flex: 1; padding: 20px 12px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
        .nav-item { display: flex; align-items: center; gap: 12px; padding: 10px 16px; border-radius: 8px; color: rgba(254, 250, 224, 0.75); text-decoration: none; transition: background 0.2s ease, color 0.2s ease; font-size: 14px; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.07); color: var(--accent); }
        .nav-item.active { background: rgba(255, 255, 255, 0.12); color: #ffffff; }
        .main-content { margin-left: var(--sidebar-width); padding: 32px; width: calc(100% - var(--sidebar-width)); background: #0f1f0f; }
        .section-content { display: none; }
        .section-content.active { display: block; }
        .top-bar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 24px; }
        .page-title h1 { font-family: 'Playfair Display', serif; font-size: 26px; font-style: italic; color: #ffffff; font-weight: 700; }
        .page-title p { color: rgba(254, 250, 224, 0.85); margin-top: 6px; }
        .section-links { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .section-links a { text-decoration: none; padding: 8px 14px; border-radius: 999px; background: rgba(210, 180, 140, 0.25); color: var(--green-900); font-weight: 600; border: 1px solid rgba(210,180,140,.4); }
        .section-links a:hover { background: rgba(210, 180, 140, 0.4); }
        .stats-grid { display: grid; gap: 12px; }
        .stats-row-1 { grid-template-columns: repeat(5, 1fr); }
        .stats-row-2 { grid-template-columns: repeat(4, 1fr); margin-bottom: 24px; }
        .stats-row-label { font-size: 10px; color: rgba(255, 255, 255, 0.4); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; margin-bottom: 8px; display: block; }
        .stat-card { background: #1e3a1e; border-radius: 10px; padding: 20px; border: none; border-left: 4px solid #4a7a4a; transition: all 0.2s ease; }
        .stat-card:hover { box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); }
        .stat-card h3 { font-size: 12px; color: rgba(255, 255, 255, 0.55); margin-bottom: 12px; font-weight: 400; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .stat-value { font-size: 28px; font-weight: 600; color: #ffffff; display: block; margin-top: 8px; }
        .content-card { background: #1e3a1e; border-radius: 12px; padding: 28px; border: 1px solid rgba(255,255,255,0.06); margin-bottom: 20px; }
        .content-card h3 { font-size: 20px; font-weight: 600; color: #ffffff; margin-bottom: 20px; border-left: 3px solid #4a7a4a; padding-left: 12px; }
        table { width: 100%; border-collapse: collapse; }
        table thead { background: rgba(0,0,0,0.2); }
        table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: 0.8px; color: rgba(255,255,255,0.45); padding: 12px 16px; font-weight: 600; text-align: left; }
        table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); }
        table tbody tr:hover { background: rgba(255,255,255,0.03); }
        table tbody td { font-size: 14px; color: rgba(255,255,255,0.85); padding: 14px 16px; }
        .badge { display: inline-block; font-size: 11px; font-weight: 500; padding: 3px 10px; border-radius: 20px; letter-spacing: 0.3px; border: 1px solid; }
        .badge.active { background: #0d3d1f; color: #4ade80; border-color: #1a6b38; }
        .badge.pending { background: #3d2e00; color: #fbbf24; border-color: #6b4f00; }
        .badge.verified { background: #0d3d1f; color: #4ade80; border-color: #1a6b38; }
        .badge.rejected { background: #3d0d0d; color: #f87171; border-color: #6b1a1a; }
        .badge.suspended { background: #3d0d0d; color: #f87171; border-color: #6b1a1a; }
        .badge.available { background: #0d3d1f; color: #4ade80; border-color: #1a6b38; }
        .badge.closed { background: #3d0d0d; color: #f87171; border-color: #6b1a1a; }
        .badge.reserved { background: #3d2e00; color: #fbbf24; border-color: #6b4f00; }
        .btn { border: none; padding: 7px 16px; border-radius: 7px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all 0.15s ease; color: #fff; }
        .btn:hover { transform: none; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .btn-primary, .btn-approve { background: #1a6b38; color: #fff; }
        .btn-primary:hover, .btn-approve:hover { background: #22883f; }
        .btn-danger, .btn-reject { background: #6b1a1a; color: #fff; }
        .btn-danger:hover, .btn-reject:hover { background: #8b2020; }
        .btn-neutral, .btn-view, .btn-deactivate { background: transparent; color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.2); }
        .btn-neutral:hover, .btn-view:hover, .btn-deactivate:hover { background: rgba(255,255,255,0.07); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-success { background: #1a6b38; color: #fff; }
        .btn-success:hover { background: #22883f; }
        .btn-warning { background: #6b6b1a; color: #fff; }
        .btn-warning:hover { background: #888820; }
        .listings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; margin-top: 20px; }
        .listing-card { background: #162e16; border-radius: 12px; padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: all 0.2s ease; border: 1px solid rgba(255,255,255,0.07); }
        .listing-card:hover { border-color: rgba(255,255,255,0.14); box-shadow: 0 8px 16px rgba(0,0,0,0.3); }
        .listing-card-image { width: 100%; height: 180px; background: #2a7a6a; object-fit: cover; display: flex; align-items: center; justify-content: center; color: rgba(254,250,224,0.5); font-size: 40px; position: relative; border-radius: 12px 12px 0 0; }
        .listing-card-content { padding: 16px 20px 20px; flex: 1; display: flex; flex-direction: column; }
        .listing-card-title { font-size: 17px; font-weight: 600; color: #ffffff; margin-bottom: 4px; }
        .listing-card-seller { font-size: 12px; color: rgba(255,255,255,0.4); margin-bottom: 8px; }
        .listing-card-description { font-size: 13px; color: rgba(255,255,255,0.55); margin-bottom: 10px; line-height: 1.4; flex: 1; max-height: 2.8em; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .listing-card-actions { display: flex; gap: 8px; margin-top: 14px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.05); }
        .btn-sm { padding: 8px 16px; font-size: 13px; margin-right: 0; flex: 1; text-align: center; border-radius: 7px; }
        .btn-neutral.btn-sm { background: transparent; border: 1px solid rgba(255,255,255,0.2); color: rgba(255,255,255,0.7); }
        .btn-neutral.btn-sm:hover { background: rgba(255,255,255,0.07); }
        .btn-primary.btn-sm { background: #1a6b38; border: none; color: #fff; }
        .btn-primary.btn-sm:hover { background: #22883f; }
        .btn-danger.btn-sm { background: #6b1a1a; border: none; color: #fff; }
        .btn-danger.btn-sm:hover { background: #8b2020; }
        .btn-approved { background: #0d3d1f; color: #4ade80; border: 1px solid #1a6b38; }
        .btn-approved:hover { background: #124a27; }
        .btn-rejected { background: #3d0d0d; color: #f87171; border: 1px solid #6b1a1a; }
        .btn-rejected:hover { background: #521010; }
        .btn-incomplete { background: #3d2e00; color: #fbbf24; border: 1px solid #6b4f00; }
        .btn-incomplete:hover { background: #524000; }
        .sellers-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 20px; margin-top: 20px; }
        .seller-card { background: #162e16; border-radius: 12px; padding: 20px; display: flex; flex-direction: column; transition: all 0.2s ease; border: 1px solid rgba(255,255,255,0.07); }
        .seller-card:hover { border-color: rgba(255,255,255,0.14); box-shadow: 0 8px 16px rgba(0,0,0,0.3); }
        .seller-profile-pic { width: 80px; height: 100px; border-radius: 8px; background: #2a7a6a; color: #fff; font-size: 32px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .seller-card-info { flex: 1; }
        .seller-card-header { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 16px; }
        .seller-card-header > div:first-child { flex: 1; }
        .seller-card-name { font-size: 18px; font-weight: 600; color: #ffffff; margin-bottom: 4px; }
        .seller-card-id { font-size: 12px; color: rgba(255,255,255,0.45); margin-bottom: 8px; }
        .seller-card-details { font-size: 13px; color: rgba(255,255,255,0.8); margin-bottom: 12px; }
        .seller-card-details strong { color: rgba(255,255,255,0.45); font-weight: 600; }
        .seller-documents { border-top: 1px solid rgba(255,255,255,0.07); padding-top: 12px; margin-top: 12px; }
        .seller-documents-title { font-size: 10px; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; display: block; }
        .btn-files { background: transparent; color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.2); padding: 7px 16px; font-weight: 500; cursor: pointer; border-radius: 7px; transition: all 0.15s ease; display: inline-block; font-size: 13px; }
        .btn-files:hover { background: rgba(255,255,255,0.07); }
        .seller-card-actions { display: flex; gap: 8px; margin-left: auto; }
        .btn-approve { background: #1a6b38; color: #fff; padding: 7px 16px; border: none; font-weight: 500; cursor: pointer; border-radius: 7px; transition: all 0.15s ease; display: inline-flex; font-size: 13px; }
        .btn-approve:hover { background: #22883f; }
        .btn-reject { background: #6b1a1a; color: #fff; padding: 7px 16px; border: none; font-weight: 500; cursor: pointer; border-radius: 7px; transition: all 0.15s ease; display: inline-flex; font-size: 13px; }
        .btn-reject:hover { background: #8b2020; }
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-top: 16px; }
        .gallery-thumbnail { width: 100%; aspect-ratio: 1; border-radius: 12px; object-fit: cover; cursor: pointer; transition: all .2s ease; border: 1px solid rgba(149,213,178,.15); }
        .gallery-thumbnail:hover { transform: scale(1.05); border-color: rgba(149,213,178,.35); }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); backdrop-filter: blur(2px); align-items: center; justify-content: center; animation: fadeIn 0.3s ease; }
        .modal.active { display: flex; }
        .modal-content { background: #1a3a1a; border-radius: 14px; padding: 28px 32px; border: 1px solid rgba(255,255,255,0.1); max-width: 520px; width: 90%; box-shadow: 0 24px 48px rgba(0,0,0,0.4); max-height: 90vh; overflow-y: auto; position: relative; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { font-size: 18px; font-weight: 600; color: #ffffff; margin: 0; }
        .modal-close { position: absolute; top: 12px; right: 12px; color: rgba(255,255,255,0.5); font-size: 28px; font-weight: bold; cursor: pointer; background: rgba(255,255,255,0.07); border: none; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.15s ease; padding: 0; }
        .modal-close:hover { background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.7); }
        .modal-body { margin-bottom: 20px; }
        .modal-body label { font-size: 12px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; display: block; }
        .modal-body p { font-size: 14px; color: rgba(255,255,255,0.85); }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .confirmation-icon { width: 48px; height: 48px; background: rgba(239,68,68,0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 16px; }
        .modal-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px; }
        .modal-gallery-item { cursor: pointer; border-radius: 12px; overflow: hidden; border: 1px solid rgba(149,213,178,.2); transition: all .2s ease; }
        .modal-gallery-item:hover { border-color: rgba(149,213,178,.5); transform: scale(1.03); }
        .modal-gallery-image { width: 100%; aspect-ratio: 1; object-fit: cover; }
        .modal-title { color: var(--cream-100); margin-bottom: 16px; font-size: 1.2rem; font-weight: 700; }
        
        /* Listing Details Modal Styles */
        #listingDetailsModal .modal-content {
            max-width: 780px;
            width: 100%;
            padding: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            max-height: 90vh;
        }
        
        .listing-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            background: #162e16;
            flex-shrink: 0;
        }
        
        .listing-modal-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .listing-modal-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            flex: 1;
            overflow: hidden;
        }
        
        .listing-modal-left {
            padding: 20px 20px 20px 24px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            border-right: 1px solid rgba(255,255,255,0.07);
            overflow-y: auto;
        }
        
        .listing-modal-image {
            width: 100%;
            height: 200px;
            background: #2a7a6a;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .listing-modal-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .listing-modal-image--empty {
            color: rgba(255,255,255,0.3);
            font-size: 36px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-direction: row;
        }
        
        .listing-modal-image--empty span {
            font-size: 14px;
        }
        
        .listing-modal-description {
            background: rgba(0,0,0,0.15);
            border-radius: 8px;
            padding: 12px 14px;
        }
        
        .listing-modal-description-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.35);
            margin-bottom: 6px;
            display: block;
        }
        
        .listing-modal-description-text {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
            max-height: 100px;
            overflow-y: auto;
        }
        
        .listing-modal-location-price {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .listing-modal-field-label {
            font-size: 10px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
            display: block;
            margin-bottom: 2px;
        }
        
        .listing-modal-field-value {
            font-size: 14px;
            color: #ffffff;
            font-weight: 500;
        }
        
        .listing-modal-right {
            padding: 20px 24px 20px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            overflow-y: auto;
        }
        
        .listing-modal-details-title {
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 4px;
        }
        
        .listing-modal-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .listing-modal-detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .listing-modal-detail-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: rgba(255,255,255,0.35);
            margin-bottom: 3px;
        }
        
        .listing-modal-detail-value {
            font-size: 14px;
            color: rgba(255,255,255,0.85);
            font-weight: 500;
        }
        
        .listing-modal-footer {
            padding: 14px 24px;
            border-top: 1px solid rgba(255,255,255,0.07);
            background: #162e16;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .listing-modal-footer button {
            padding: 8px 20px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        
        .listing-modal-footer .btn-close {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.7);
        }
        
        .listing-modal-footer .btn-close:hover {
            background: rgba(255,255,255,0.05);
            color: rgba(255,255,255,0.9);
        }
        
        .listing-modal-footer .btn-approve {
            background: #1a6b38;
            color: #fff;
        }
        
        .listing-modal-footer .btn-approve:hover {
            background: #22883f;
        }
        
        .listing-modal-footer .btn-reject {
            background: #6b1a1a;
            color: #fff;
        }
        
        .listing-modal-footer .btn-reject:hover {
            background: #8b2020;
        }
        
        /* Modal close button positioning */
        #listingDetailsModal .modal-close {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 30px;
            height: 30px;
            font-size: 16px;
        }
        
        .report-reason { background: rgba(241,196,15,.15); color: #f1c40f; padding: 4px 10px; border-radius: 6px; font-size: .8rem; font-weight: 500; display: inline-block; }
        .report-badge { padding: 4px 8px; border-radius: 6px; font-weight: 600; display: inline-block; cursor: pointer; transition: all .2s ease; }
        .report-badge.filed { background: rgba(46,204,113,.15); color: #2ecc71; }
        .report-badge.against { background: rgba(231, 76, 60,.15); color: #e74c3c; }
        .report-badge:hover { transform: scale(1.05); }
        .user-row { cursor: pointer; transition: all .2s ease; }
        .user-row:hover { background: rgba(149, 213, 178, 0.08); }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        /* User Filter Styles */
        #filterStatus, #filterRole, #filterVerification { 
            min-width: 120px;
        }
        
        #filterStatus:hover, #filterRole:hover, #filterVerification:hover {
            background: rgba(0,0,0,.4);
            border-color: rgba(149,213,178,.5);
        }
        
        /* Filter Bar Global Styles */
        .filter-bar { display: flex; gap: 12px; margin-bottom: 20px; padding: 16px; background: rgba(0,0,0,0.15); border-radius: 8px; flex-wrap: wrap; align-items: center; }
        .filter-bar label { font-size: 12px; color: rgba(255,255,255,0.45); margin-right: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        .filter-bar select, .filter-bar input { background: #1a3a1a; color: #fff; border: 1px solid rgba(255,255,255,0.15); border-radius: 7px; padding: 7px 12px; font-size: 13px; outline: none; font-family: inherit; }
        .filter-bar select:focus, .filter-bar input:focus { border-color: rgba(255,255,255,0.35); }
        .filter-bar option { background: #0f1f0f; color: #fff; }
        
        /* Report badge styles */
        .report-badge { padding: 4px 8px; border-radius: 6px; font-weight: 600; display: inline-block; cursor: pointer; transition: all 0.2s ease; font-size: 12px; }
        .report-badge.filed { background: rgba(46,204,113,0.15); color: #2ecc71; }
        .report-badge.against { background: rgba(231, 76, 60,0.15); color: #e74c3c; }
        .report-badge:hover { transform: scale(1.05); }
        
        /* Table utility classes */
        .table-empty-state { text-align: center; padding: 48px; color: rgba(255,255,255,0.35); font-size: 14px; font-style: italic; }
        .user-row { cursor: pointer; transition: all 0.2s ease; }
        .user-row:hover { background: rgba(255, 255, 255, 0.03); }
        
        /* Gallery styles */
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-top: 16px; }
        .gallery-thumbnail { width: 100%; aspect-ratio: 1; border-radius: 12px; object-fit: cover; cursor: pointer; transition: all 0.2s ease; border: 1px solid rgba(255,255,255,0.1); }
        .gallery-thumbnail:hover { transform: scale(1.05); border-color: rgba(255,255,255,0.3); }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        @media (max-width: 980px) {
            .dashboard-container { display: block; }
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content { margin-left: 0; width: 100%; padding: 16px; }
            .top-bar { flex-direction: column; align-items: flex-start; gap: 8px; }
            .page-title h1 { font-size: 1.6rem; }
            .section-links { width: 100%; justify-content: center; flex-wrap: wrap; }
            .section-links a { padding: 8px 10px; font-size: 0.82rem; }
            .stats-grid { grid-template-columns: 1fr; }
            .stats-row-1 { grid-template-columns: 1fr; }
            .stats-row-2 { grid-template-columns: 1fr; margin-bottom: 32px; }
            .listings-grid { grid-template-columns: 1fr; }
            table { font-size: 0.78rem; }
            .table-wrapper, .content-card { overflow-x: auto; }
            table th, table td { white-space: nowrap; }
            .badge { font-size: 0.72rem; }
            .listing-card-description { max-height: 80px; overflow: hidden; }
            .listing-card-actions { flex-wrap: wrap; }
            .btn-sm { flex: 1; min-width: calc(50% - 4px); }
            .sellers-grid { grid-template-columns: 1fr; }
            .seller-card { flex-direction: column; }
            .seller-card-header { flex-direction: column; }
            .seller-profile-pic { width: 100%; }
            .seller-card-actions { margin-left: 0; margin-top: 12px; width: 100%; }
            .chatbot { right: 12px; bottom: 12px; width: 260px; }
            .chatbot-content { height: 340px; }
            
            /* Responsive modal styles */
            #listingDetailsModal .modal-content {
                max-width: 95vw;
                width: 95vw;
                max-height: 95vh;
            }
            
            .listing-modal-body {
                grid-template-columns: 1fr;
            }
            
            .listing-modal-left {
                border-right: none;
                border-bottom: 1px solid rgba(255,255,255,0.07);
                padding: 20px;
            }
            
            .listing-modal-right {
                padding: 20px;
            }
            
            .listing-modal-image {
                height: 150px;
            }
            
            .listing-modal-details-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 640px) {
            #listingDetailsModal .modal-content {
                max-width: 98vw;
                width: 98vw;
                max-height: 98vh;
                border-radius: 10px;
            }
            
            .listing-modal-header {
                padding: 14px 16px;
            }
            
            .listing-modal-header h2 {
                font-size: 16px;
            }
            
            .listing-modal-footer {
                padding: 12px 16px;
                flex-wrap: wrap;
            }
            
            .listing-modal-footer button {
                flex: 1;
                min-width: 80px;
                padding: 6px 12px;
                font-size: 12px;
            }
            
            .listing-modal-image {
                height: 120px;
            }
            
            .listing-modal-left,
            .listing-modal-right {
                padding: 16px;
            }
            
            .listing-modal-details-title {
                font-size: 14px;
            }
            
            .listing-modal-description-label,
            .listing-modal-detail-label {
                font-size: 9px;
            }
            
            .listing-modal-field-value,
            .listing-modal-detail-value {
                font-size: 13px;
            }
            
            .listing-modal-description-text {
                font-size: 12px;
            }
        }

        /* Analytics Dashboard Styles */
        .analytics-section { margin-bottom: 32px; }
        .section-header { font-size: 16px; font-weight: 500; color: rgba(255, 255, 255, 0.9); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .analytics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 32px; }
        .chart-card { background: #1e3a1e; border-radius: 12px; padding: 24px; position: relative; border: none; }
        .chart-card h3 { color: rgba(255, 255, 255, 0.9); margin-bottom: 16px; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .chart-container { position: relative; height: 320px; width: 100%; }
        .gauge-container { display: flex; justify-content: center; align-items: center; }
        .gauge-chart { max-width: 300px; margin: 0 auto; }
        .chart-legend { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.08); }
        .legend-item { display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: 14px; color: rgba(255, 255, 255, 0.8); }
        .legend-color { width: 12px; height: 12px; border-radius: 3px; }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: auto;
        }

        .logout-btn {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(226, 75, 74, 0.15);
            border: 1px solid rgba(226, 75, 74, 0.3);
            border-radius: 8px;
            color: #e24b4a;
            font-size: 14px;
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

        @media (max-width: 1200px) {
            .analytics-grid { grid-template-columns: 1fr; }
            .chart-container { height: 280px; }
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

        .swal2-popup.landly-swal .swal2-deny {
            background: rgba(217, 111, 91, 0.3);
            color: #e74c3c;
            border: 1px solid rgba(217, 111, 91, 0.4);
            border-radius: 10px;
        }

        .swal2-popup.landly-swal .swal2-confirm:hover,
        .swal2-popup.landly-swal .swal2-cancel:hover,
        .swal2-popup.landly-swal .swal2-deny:hover {
            transform: scale(1.02);
        }

        .swal2-popup.landly-swal .swal2-confirm:focus,
        .swal2-popup.landly-swal .swal2-cancel:focus,
        .swal2-popup.landly-swal .swal2-deny:focus {
            box-shadow: 0 0 0 3px rgba(149, 213, 178, 0.35);
        }

        /* Styled backdrop with gradient design */
        .swal2-backdrop.swal2-shown {
            background: radial-gradient(circle at center, rgba(15, 27, 27, 0.85) 0%, rgba(5, 12, 12, 0.95) 70%, rgba(2, 6, 6, 0.98) 100%);
            backdrop-filter: blur(2px);
        }

        /* Add subtle pattern overlay to backdrop */
        .swal2-backdrop.swal2-shown::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                linear-gradient(45deg, transparent 48%, rgba(149, 213, 178, 0.02) 49%, rgba(149, 213, 178, 0.02) 51%, transparent 52%),
                linear-gradient(-45deg, transparent 48%, rgba(149, 213, 178, 0.02) 49%, rgba(149, 213, 178, 0.02) 51%, transparent 52%);
            background-size: 60px 60px;
            background-position: 0 0;
            pointer-events: none;
            z-index: -1;
        }

        @keyframes landlyFadeInUp {
            from { opacity: 0; transform: translateY(16px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes landlyFadeOutDown {
            from { opacity: 1; transform: translateY(0) scale(1); }
            to { opacity: 0; transform: translateY(12px) scale(0.98); }
        }

        .landly-swal-show { animation: landlyFadeInUp 0.28s ease-out; }
        .landly-swal-hide { animation: landlyFadeOutDown 0.2s ease-in; }

        .swal2-popup.landly-swal .swal2-confirm:hover,
        .swal2-popup.landly-swal .swal2-cancel:hover,
        .swal2-popup.landly-swal .swal2-deny:hover {
            transform: scale(1.02);
        }

        .swal2-popup.landly-swal .swal2-confirm:focus,
        .swal2-popup.landly-swal .swal2-cancel:focus,
        .swal2-popup.landly-swal .swal2-deny:focus {
            box-shadow: 0 0 0 3px rgba(149, 213, 178, 0.35);
        }

        /* Styled backdrop with gradient design */
        .swal2-backdrop.swal2-shown {
            background: radial-gradient(circle at center, rgba(15, 27, 27, 0.85) 0%, rgba(5, 12, 12, 0.95) 70%, rgba(2, 6, 6, 0.98) 100%);
            backdrop-filter: blur(2px);
        }
        
        /* Add subtle pattern overlay to backdrop */
        .swal2-backdrop.swal2-shown::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(45deg, transparent 48%, rgba(149, 213, 178, 0.02) 49%, rgba(149, 213, 178, 0.02) 51%, transparent 52%),
                linear-gradient(-45deg, transparent 48%, rgba(149, 213, 178, 0.02) 49%, rgba(149, 213, 178, 0.02) 51%, transparent 52%);
            background-size: 60px 60px;
            background-position: 0 0;
            pointer-events: none;
            z-index: -1;
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!-- Dashboard Data Configuration -->
    <script type="application/json" id="dashboardData">
    {
        "totalBuyers": <?php echo isset($totalBuyers) ? intval($totalBuyers) : 0; ?>,
        "totalSellers": <?php echo isset($totalSellers) ? intval($totalSellers) : 0; ?>,
        "totalAdmins": <?php echo isset($totalAdmins) ? intval($totalAdmins) : 0; ?>,
        "listingPending": <?php echo isset($listingStats['pending']) ? intval($listingStats['pending']) : 0; ?>,
        "listingVerified": <?php echo isset($listingStats['verified']) ? intval($listingStats['verified']) : 0; ?>,
        "listingRejected": <?php echo isset($listingStats['rejected']) ? intval($listingStats['rejected']) : 0; ?>,
        "reportPending": <?php echo isset($reportStats['pending']) ? intval($reportStats['pending']) : 0; ?>,
        "reportResolved": <?php echo isset($reportStats['resolved']) ? intval($reportStats['resolved']) : 0; ?>,
        "reportSuspended": <?php echo isset($reportStats['suspended']) ? intval($reportStats['suspended']) : 0; ?>,
        "verificationVerified": <?php echo isset($verificationStats['verified']) ? intval($verificationStats['verified']) : 0; ?>,
        "verificationPending": <?php echo isset($verificationStats['pending']) ? intval($verificationStats['pending']) : 0; ?>,
        "verificationUnverified": <?php echo isset($verificationStats['unverified']) ? intval($verificationStats['unverified']) : 0; ?>
    }
    </script>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <a class="brand" href="/admin/dashboard">
                    <div class="brand-badge">A</div>
                    <div>
                        <div class="brand-text">Landly</div>
                        <div class="brand-subtitle">Admin Panel</div>
                    </div>
                </a>
            </div>
            <nav class="sidebar-nav">
                <a class="nav-item active" href="#" data-section="dashboard">Dashboard</a>
                <a class="nav-item" href="#" data-section="users">Users</a>
                <a class="nav-item" href="#" data-section="sellers">Seller Approval</a>
                <a class="nav-item" href="#" data-section="listings">Land Listings</a>
                <a class="nav-item" href="#" data-section="reports">Reports & Disputes</a>
            </nav>

            <div class="sidebar-footer">
                <button class="logout-btn" id="adminLogoutBtn" type="button">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Logout
                </button>
            </div>
        </aside>

        <main class="main-content">
            <div class="top-bar" id="dashboard">
                <div class="page-title">
                    <h1>Admin Dashboard</h1>
                    <p>Welcome back, <?= esc($fullname ?? 'Administrator') ?>. Overview of key platform metrics.</p>
                </div>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="content-card" style="border-color:#2ecc71;background:rgba(46,204,113,.08);color:#2ecc71;"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('warning')): ?>
                <div class="content-card" style="border-color:#f1c40f;background:rgba(241,196,15,.08);color:#f1c40f;"><?= esc(session()->getFlashdata('warning')) ?></div>
            <?php endif; ?>

            <section id="dashboard-section" class="section-content active">
                <!-- Row 1: User & Content Metrics -->
                <label class="stats-row-label">User & Content Metrics</label>
                <div class="stats-grid stats-row-1">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <div class="stat-value"><?= isset($totalUsers) ? esc($totalUsers) : 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Buyers</h3>
                        <div class="stat-value"><?= isset($totalBuyers) ? esc($totalBuyers) : 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Sellers</h3>
                        <div class="stat-value"><?= isset($totalSellers) ? esc($totalSellers) : 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Admins</h3>
                        <div class="stat-value"><?= isset($totalAdmins) ? esc($totalAdmins) : 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Listings</h3>
                        <div class="stat-value"><?= isset($totalListings) ? esc($totalListings) : 0; ?></div>
                    </div>
                </div>

                <!-- Row 2: Listing & Report Status -->
                <label class="stats-row-label">Listing & Report Status</label>
                <div class="stats-grid stats-row-2">
                    <div class="stat-card">
                        <h3>Pending Verifications</h3>
                        <div class="stat-value"><?= isset($listingStats['pending']) ? esc($listingStats['pending']) : 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Verified Listings</h3>
                        <div class="stat-value"><?= isset($listingStats['verified']) ? esc($listingStats['verified']) : 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Rejected Listings</h3>
                        <div class="stat-value"><?= isset($listingStats['rejected']) ? esc($listingStats['rejected']) : 0; ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Reports</h3>
                        <div class="stat-value"><?= isset($totalReports) ? esc($totalReports) : 0; ?></div>
                    </div>
                </div>

                <!-- Analytics Section -->
                <div class="analytics-section">
                    <h3 style="color: rgba(254,250,224,.9); margin-bottom: 20px; font-size: 1.3rem; font-weight: 700;">📊 Platform Analytics</h3>
                    
                    <div class="analytics-grid">
                        <!-- Users Category Pie Chart -->
                        <div class="chart-card">
                            <h3>👥 User Distribution</h3>
                            <div class="chart-container" style="position: relative; height: 280px;">
                                <canvas id="userCategoryChart"></canvas>
                            </div>
                            <div class="chart-legend">
                                <div class="legend-item"><div class="legend-color" style="background: #95d5b2;"></div><span>Buyers</span></div>
                                <div class="legend-item"><div class="legend-color" style="background: #d2b48c;"></div><span>Sellers</span></div>
                                <div class="legend-item"><div class="legend-color" style="background: #f1c40f;"></div><span>Admins</span></div>
                            </div>
                        </div>

                        <!-- Listing Status Bar Chart -->
                        <div class="chart-card">
                            <h3>📋 Listing Status Overview</h3>
                            <div class="chart-container" style="position: relative; height: 280px;">
                                <canvas id="listingStatusChart"></canvas>
                            </div>
                            <div class="chart-legend">
                                <div class="legend-item"><div class="legend-color" style="background: #f1c40f;"></div><span>Pending</span></div>
                                <div class="legend-item"><div class="legend-color" style="background: #2ecc71;"></div><span>Verified</span></div>
                                <div class="legend-item"><div class="legend-color" style="background: #e74c3c;"></div><span>Rejected</span></div>
                            </div>
                        </div>

                        <!-- Report Status Chart -->
                        <div class="chart-card">
                            <h3>⚠️ Report Analytics</h3>
                            <div class="chart-container" style="position: relative; height: 280px;">
                                <canvas id="reportStatusChart"></canvas>
                            </div>
                            <div class="chart-legend">
                                <div class="legend-item"><div class="legend-color" style="background: #f1c40f;"></div><span>Pending</span></div>
                                <div class="legend-item"><div class="legend-color" style="background: #2ecc71;"></div><span>Resolved</span></div>
                                <div class="legend-item"><div class="legend-color" style="background: #e74c3c;"></div><span>Suspended</span></div>
                            </div>
                        </div>

                        <!-- Verification Status Chart -->
                        <div class="chart-card">
                            <h3>✅ User Verification Status</h3>
                            <div class="chart-container" style="position: relative; height: 280px;">
                                <canvas id="verificationChart"></canvas>
                            </div>
                            <div class="chart-legend">
                                <div class="legend-item"><div class="legend-color" style="background: #2ecc71;"></div><span>Verified</span></div>
                                <div class="legend-item"><div class="legend-color" style="background: #f1c40f;"></div><span>Pending</span></div>
                                <div class="legend-item"><div class="legend-color" style="background: #95a5a6;"></div><span>Unverified</span></div>
                            </div>
                        </div>

                        <!-- Activity Trend Chart -->
                        <div class="chart-card" style="grid-column: 1 / -1;">
                            <h3>📈 Monthly Activity Trend</h3>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="activityTrendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <?= view('Pages/Admin/Components/UserSection') ?>

            <?= view('Pages/Admin/Components/ListingSection') ?>

            <?= view('Pages/Admin/Components/SellerSection') ?>

            <!-- Reports & Disputes Section -->
            <?= view('Pages/Admin/Components/ReportSection') ?>
            
            <!-- User Report History Modal -->
            <div id="userReportHistoryModal" class="modal">
                <div class="modal-content" style="max-width: 900px;">
                    <button class="modal-close" onclick="closeUserReportHistoryModal()">&times;</button>
                    <div class="modal-title">Report History for <span id="reportHistoryUserName"></span></div>
                    
                    <div id="reportHistoryTabs" style="display: flex; gap: 12px; margin-bottom: 16px; border-bottom: 1px solid rgba(149,213,178,.2); padding-bottom: 12px;">
                        <button id="filedTab" onclick="switchReportTab('filed')" style="padding: 6px 14px; border: none; background: rgba(46,204,113,.2); color: #2ecc71; font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">📤 Reports Filed</button>
                        <button id="againstTab" onclick="switchReportTab('against')" style="padding: 6px 14px; border: none; background: transparent; color: rgba(254,250,224,.6); font-weight: 600; cursor: pointer; border-radius: 6px; transition: all .2s ease;">📥 Reports Against</button>
                    </div>
                    
                    <div id="reportHistoryContent" style="color: var(--cream-100); max-height: 60vh; overflow-y: auto;">
                        <!-- Report items will be populated here -->
                    </div>
                </div>
            </div>

            <!-- Files Gallery Modal -->
            <div id="filesModal" class="modal">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeFilesGallery()">&times;</button>
                    <div class="modal-title" id="modalGalleryTitle">Documents from Seller</div>
                    <div class="modal-gallery" id="modalGalleryGrid"></div>
                </div>
            </div>

            <!-- Listing Details Modal -->
            <div id="listingDetailsModal" class="modal">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeListingDetailsModal()">&times;</button>
                    
                    <!-- Header -->
                    <div class="listing-modal-header">
                        <h2>
                            <span id="detailListingTitle"></span>
                            <span id="detailListingStatus"></span>
                        </h2>
                    </div>
                    
                    <!-- Body: Two-column layout -->
                    <div class="listing-modal-body">
                        <!-- Left Column: Image + Description -->
                        <div class="listing-modal-left">
                            <!-- Image -->
                            <div class="listing-modal-image" id="detailListingImage">
                                <div class="listing-modal-image--empty">
                                    <span>🏞️</span>
                                    <span>Property Image</span>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="listing-modal-description">
                                <label class="listing-modal-description-label">Full Description</label>
                                <p class="listing-modal-description-text" id="detailListingDescription"></p>
                            </div>
                            
                            <!-- Location & Price -->
                            <div class="listing-modal-location-price">
                                <div>
                                    <span class="listing-modal-field-label">📍 Location</span>
                                    <span class="listing-modal-field-value" id="detailListingLocation"></span>
                                </div>
                                <div>
                                    <span class="listing-modal-field-label">💰 Price</span>
                                    <span class="listing-modal-field-value" id="detailListingPrice"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column: Details Panel -->
                        <div class="listing-modal-right">
                            <h3 class="listing-modal-details-title">Details</h3>
                            
                            <div class="listing-modal-details-grid">
                                <!-- Listing ID -->
                                <div class="listing-modal-detail-item">
                                    <span class="listing-modal-detail-label">Listing ID</span>
                                    <span class="listing-modal-detail-value" id="detailListingId"></span>
                                </div>
                                
                                <!-- Status -->
                                <div class="listing-modal-detail-item">
                                    <span class="listing-modal-detail-label">Status</span>
                                    <span class="listing-modal-detail-value" id="detailListingStatusDetail"></span>
                                </div>
                                
                                <!-- Seller -->
                                <div class="listing-modal-detail-item">
                                    <span class="listing-modal-detail-label">Seller</span>
                                    <span class="listing-modal-detail-value" id="detailListingSeller"></span>
                                </div>
                                
                                <!-- Created Date -->
                                <div class="listing-modal-detail-item">
                                    <span class="listing-modal-detail-label">Created Date</span>
                                    <span class="listing-modal-detail-value" id="detailListingDate"></span>
                                </div>
                                
                                <!-- Land Area (if available) -->
                                <div class="listing-modal-detail-item">
                                    <span class="listing-modal-detail-label">Land Area</span>
                                    <span class="listing-modal-detail-value" id="detailListingArea">N/A</span>
                                </div>
                                
                                <!-- Land Type (if available) -->
                                <div class="listing-modal-detail-item">
                                    <span class="listing-modal-detail-label">Land Type</span>
                                    <span class="listing-modal-detail-value" id="detailListingType">N/A</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="listing-modal-footer">
                        <button class="listing-modal-footer-btn btn-close" onclick="closeListingDetailsModal()">Close</button>
                        <button class="listing-modal-footer-btn btn-approve" onclick="approveListing(currentListingId, currentListingTitle)">Approve</button>
                        <button class="listing-modal-footer-btn btn-reject" onclick="rejectListing(currentListingId, currentListingTitle)">Reject</button>
                    </div>
                </div>
            </div>

            <!-- Report Details Modal -->
            <div id="reportDetailsModal" class="modal">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeReportModal()">&times;</button>
                    <div class="modal-title">Report Details</div>
                    <div id="reportDetailsContent" style="color: var(--cream-100); line-height: 1.6;">
                        <p><strong>Report ID:</strong> <span id="detailReportId"></span></p>
                        <p><strong>Subject:</strong> <span id="detailSubject"></span></p>
                        <p><strong>Reported By:</strong> <span id="detailReporter"></span></p>
                        <p><strong>Against:</strong> <span id="detailTarget"></span></p>
                        <p><strong>Reason:</strong> <span id="detailReason"></span></p>
                        <p><strong>Description:</strong> <span id="detailDescription"></span></p>
                        <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                        <p><strong>Date Filed:</strong> <span id="detailDate"></span></p>
                    </div>
                </div>
            </div>


            
            <!-- Reply Modal -->
            <div id="replyModal" class="modal">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeReplyModal()">&times;</button>
                    <div class="modal-title">Send Reply to Report</div>
                    <form onsubmit="submitReply(event)" style="color: var(--cream-100);">
                        <div style="margin-bottom: 12px;">
                            <label for="replyText" style="display: block; margin-bottom: 6px; font-weight: 600;">Your Reply:</label>
                            <textarea id="replyText" name="reply" required style="width: 100%; min-height: 120px; padding: 10px; border-radius: 8px; border: 1px solid rgba(149,213,178,.3); background: rgba(0,0,0,.3); color: var(--cream-100); font-family: inherit; font-size: .9rem;" placeholder="Enter your response to this report..."></textarea>
                        </div>
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" class="btn btn-warning" onclick="closeReplyModal()">Cancel</button>
                            <button type="submit" class="btn btn-success">Send Reply</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- User Details Modal -->
            <div id="userDetailsModal" class="modal">
                <div class="modal-content" style="max-width: 1000px; max-height: 90vh; overflow-y: auto;">
                    <button class="modal-close" onclick="closeUserDetailsModal()">&times;</button>
                    <div style="color: var(--cream-100);">
                        <!-- User Header Section -->
                        <div style="display: grid; grid-template-columns: 200px 1fr; gap: 32px; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid rgba(149,213,178,.2);">
                            <!-- Profile Picture -->
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                                <div id="userProfilePic" style="width: 180px; height: 180px; border-radius: 50%; background: linear-gradient(135deg, #2a6c62, #1f4f48); display: flex; align-items: center; justify-content: center; font-size: 4rem; border: 3px solid rgba(149,213,178,.3);"></div>
                                <p id="userRole" style="padding: 4px 12px; border-radius: 6px; background: rgba(46,204,113,.2); color: #2ecc71; font-weight: 600; font-size: 0.9rem;"></p>
                            </div>
                            <!-- User Info -->
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <h2 id="userFullName" style="font-size: 2rem; font-weight: 700; margin-bottom: 4px; color: var(--cream-100);"></h2>
                                    <p id="userEmail" style="color: rgba(254,250,224,.7); font-size: 1rem;"></p>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div>
                                        <p style="color: rgba(254,250,224,.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">User ID</p>
                                        <p id="userID" style="color: var(--cream-100); font-weight: 600;"></p>
                                    </div>
                                    <div>
                                        <p style="color: rgba(254,250,224,.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Account Status</p>
                                        <p id="userStatus" style="color: #2ecc71; font-weight: 600;"></p>
                                    </div>
                                    <div>
                                        <p style="color: rgba(254,250,224,.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Member Since</p>
                                        <p id="userJoinDate" style="color: var(--cream-100); font-weight: 600;"></p>
                                    </div>
                                    <div>
                                        <p style="color: rgba(254,250,224,.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Verification</p>
                                        <p id="userVerification" style="color: #f1c40f; font-weight: 600;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Section -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 32px;">
                            <div style="background: rgba(149,213,178,.08); border: 1px solid rgba(149,213,178,.2); border-radius: 12px; padding: 20px;">
                                <h3 style="color: rgba(254,250,224,.9); margin-bottom: 16px; font-weight: 600; font-size: 1.1rem;">Contact Information</h3>
                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    <div>
                                        <p style="color: rgba(254,250,224,.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Phone</p>
                                        <p id="userPhone" style="color: var(--cream-100); font-weight: 500;">+63 917 123 4567</p>
                                    </div>
                                    <div>
                                        <p style="color: rgba(254,250,224,.6); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Location</p>
                                        <p id="userLocation" style="color: var(--cream-100); font-weight: 500;">Metro Manila, Philippines</p>
                                    </div>
                                </div>
                            </div>

                            <div style="background: rgba(149,213,178,.08); border: 1px solid rgba(149,213,178,.2); border-radius: 12px; padding: 20px;">
                                <h3 style="color: rgba(254,250,224,.9); margin-bottom: 16px; font-weight: 600; font-size: 1.1rem;">Account Statistics</h3>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div>
                                        <p style="color: rgba(254,250,224,.6); font-size: 0.85rem; margin-bottom: 4px;">📤 Reports Filed</p>
                                        <p id="userReportsFiled" style="color: var(--cream-100); font-size: 1.4rem; font-weight: 700;">0</p>
                                    </div>
                                    <div>
                                        <p style="color: rgba(254,250,224,.6); font-size: 0.85rem; margin-bottom: 4px;">📥 Reports Against</p>
                                        <p id="userReportsAgainst" style="color: #e74c3c; font-size: 1.4rem; font-weight: 700;">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Listing History Section -->
                        <div style="background: rgba(149,213,178,.08); border: 1px solid rgba(149,213,178,.2); border-radius: 12px; padding: 20px;">
                            <h3 style="color: rgba(254,250,224,.9); margin-bottom: 16px; font-weight: 600; font-size: 1.1rem;">📍 Land Listings History</h3>
                            <div id="userListingsContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                                <!-- Listings will be populated here -->
                            </div>
                            <div id="noListingsMessage" style="text-align: center; padding: 24px; color: rgba(254,250,224,.6);">
                                <p>No listings found for this user.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script>
        // === SweetAlert Configuration ===
        const defaultAlertConfig = {
            confirmButtonText: 'OK',
            customClass: {
                popup: 'landly-swal',
                confirmButton: 'landly-swal-confirm',
                cancelButton: 'landly-swal-cancel',
                denyButton: 'landly-swal-deny'
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

        const logoutRedirectUrl = <?= json_encode(base_url('auth'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        async function confirmAdminLogout() {
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

        const adminSectionStorageKey = 'adminDashboardActiveSection';

        function normalizeAdminSectionName(value) {
            return String(value || '').replace('#', '').trim().toLowerCase();
        }

        function getAdminSectionKey(link) {
            return normalizeAdminSectionName(link?.dataset?.section || link?.getAttribute('href') || '');
        }

        function setSection(sectionId) {
            sectionId = normalizeAdminSectionName(sectionId) || 'dashboard';

            document.querySelectorAll('.section-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.section-links a, .sidebar-nav a').forEach(el => el.classList.remove('active'));

            const section = document.getElementById(sectionId + '-section');
            if (section) {
                section.classList.add('active');
            }

            document.querySelectorAll('.section-links a, .sidebar-nav a').forEach(el => {
                if (getAdminSectionKey(el) === sectionId) {
                    el.classList.add('active');
                }
            });

            try {
                localStorage.setItem(adminSectionStorageKey, sectionId);
                sessionStorage.setItem(adminSectionStorageKey, sectionId);
            } catch (error) {
            }

            if (window.location.hash !== `#${sectionId}`) {
                history.replaceState(null, '', `#${sectionId}`);
            }
        }

        document.querySelectorAll('.section-links a, .sidebar-nav a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                setSection(getAdminSectionKey(this));
            });
        });

        const adminLogoutBtn = document.getElementById('adminLogoutBtn');
        adminLogoutBtn?.addEventListener('click', () => {
            confirmAdminLogout();
        });

        const initialFromHash = normalizeAdminSectionName(window.location.hash || '');
        let initial = ['dashboard', 'users', 'sellers', 'listings', 'reports'].includes(initialFromHash) ? initialFromHash : '';

        if (!initial) {
            try {
                const savedSection = normalizeAdminSectionName(
                    sessionStorage.getItem(adminSectionStorageKey)
                    || localStorage.getItem(adminSectionStorageKey)
                    || ''
                );
                if (['dashboard', 'users', 'sellers', 'listings', 'reports'].includes(savedSection)) {
                    initial = savedSection;
                }
            } catch (error) {
                initial = '';
            }
        }

        setSection(initial || 'dashboard');

        // Modal Functions for Files Gallery
        function openDocumentsGallery(sellerName, documents) {
            console.log('=== OPENING GALLERY ===');
            console.log('Seller Name:', sellerName);
            console.log('Documents:', documents);
            console.log('Documents Type:', typeof documents);
            console.log('Documents is Array:', Array.isArray(documents));
            
            const modal = document.getElementById('filesModal');
            const modalTitle = document.getElementById('modalGalleryTitle');
            const galleryGrid = document.getElementById('modalGalleryGrid');
            
            if (!modal) {
                console.error('Modal element not found!');
                return;
            }
            
            modalTitle.textContent = 'Documents from ' + sellerName;
            galleryGrid.innerHTML = '';
            
            // Ensure documents is an array
            let docsArray = documents;
            if (typeof documents === 'string') {
                try {
                    docsArray = JSON.parse(documents);
                } catch (e) {
                    console.error('Failed to parse documents JSON:', documents);
                    docsArray = [];
                }
            }
            
            console.log('Final Documents Array:', docsArray);
            
            if (!docsArray || docsArray.length === 0) {
                console.log('No documents to display');
                galleryGrid.innerHTML = '<p style="color: rgba(254,250,224,.65); text-align: center; padding: 40px; grid-column: 1/-1;">No documents available</p>';
                modal.classList.add('active');
                return;
            }
            
            // Extract URLs for fullscreen viewer
            window.currentDocuments = docsArray;
            
            docsArray.forEach((doc, index) => {
                console.log('Adding document', index, doc);
                
                const galleryItem = document.createElement('div');
                galleryItem.className = 'modal-gallery-item';
                galleryItem.style.cursor = 'pointer';
                galleryItem.style.position = 'relative';
                galleryItem.title = doc.type || 'Document ' + (index + 1);
                
                galleryItem.onclick = function() {
                    console.log('Clicked document', index);
                    showFullImage(docsArray, index);
                };
                
                const img = document.createElement('img');
                img.className = 'modal-gallery-image';
                img.src = doc.url;
                img.alt = doc.type || 'Document ' + (index + 1);
                img.style.cursor = 'pointer';
                img.style.display = 'block';
                
                // Handle image load errors
                img.onerror = function() {
                    console.error('Failed to load document image:', doc.url);
                    galleryItem.style.background = 'rgba(231, 76, 60, 0.2)';
                    galleryItem.innerHTML = '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;"><p style="font-size: 2rem; margin: 0;">📄</p><p style="color: #e74c3c; margin-top: 8px; font-size: 0.8rem;">' + (doc.type || 'Document') + '</p></div>';
                };
                
                img.onload = function() {
                    console.log('Loaded document image:', doc.url);
                };
                
                galleryItem.appendChild(img);
                
                // Add document type label
                const typeLabel = document.createElement('div');
                typeLabel.style.cssText = 'position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: rgba(254,250,224,.9); padding: 6px; font-size: 0.75rem; text-align: center; border-radius: 0 0 12px 12px; word-wrap: break-word;';
                typeLabel.textContent = doc.type || 'Document ' + (index + 1);
                galleryItem.appendChild(typeLabel);
                
                galleryGrid.appendChild(galleryItem);
            });
            
            console.log('Opening modal, adding active class');
            modal.classList.add('active');
            console.log('=== GALLERY OPENED ===');
        }

        function showFullImage(documents, index) {
            // Create fullscreen image viewer
            const currentDoc = documents[index];
            const viewer = document.createElement('div');
            viewer.className = 'modal';
            viewer.style.zIndex = '1001';
            viewer.innerHTML = `
                <div class="modal-content" style="max-width: 95%; max-height: 95%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
                    <div style="text-align: center; margin-bottom: 12px; color: rgba(254,250,224,.75); width: 100%;">
                        <p style="margin: 0; font-weight: 600;">${currentDoc.type || 'Document'}</p>
                    </div>
                    <img src="${currentDoc.url}" style="max-width: 100%; max-height: 75vh; object-fit: contain; border-radius: 12px;" onerror="this.style.display='none'; this.parentElement.innerHTML+='<p style=\"color: #e74c3c; text-align: center;\">Failed to load document. The file may have been deleted.</p>'">
                    <div style="text-align: center; margin-top: 12px; color: rgba(254,250,224,.75); width: 100%;">
                        <p style="margin-bottom: 12px;">${index + 1} / ${documents.length}</p>
                        <div>
                            ${index > 0 ? `<button class="btn btn-files" onclick="showFullImage(window.currentDocuments, ${index - 1}); event.stopPropagation();" style="margin-left: 8px;">← Previous</button>` : ''}
                            ${index < documents.length - 1 ? `<button class="btn btn-files" onclick="showFullImage(window.currentDocuments, ${index + 1}); event.stopPropagation();" style="margin-left: 8px;">Next →</button>` : ''}
                        </div>
                    </div>
                </div>
            `;
            viewer.classList.add('active');
            viewer.onclick = function(e) {
                if (e.target === this) {
                    this.remove();
                }
            };
            document.body.appendChild(viewer);
        }

        function closeFilesGallery() {
            const modal = document.getElementById('filesModal');
            modal.classList.remove('active');
        }

        // Close modal when clicking outside the modal content
        document.getElementById('filesModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeFilesGallery();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFilesGallery();
                // Also close fullscreen images
                document.querySelectorAll('.modal').forEach(m => {
                    if (m.style.zIndex === '1001') {
                        m.remove();
                    }
                });
            }
        });

        // Report Management Functions
        function viewReportDetails(reportId) {
            // Fetch report data from the server
            fetch(`/admin/reports/${reportId}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.success) {
                        const report = data.report;
                        document.getElementById('detailReportId').textContent = report.report_id || reportId;
                        document.getElementById('detailSubject').textContent = report.subject || '';
                        document.getElementById('detailReporter').textContent = report.reported_by_name || '';
                        document.getElementById('detailTarget').textContent = report.reported_against_name || '';
                        document.getElementById('detailReason').textContent = report.reason || '';
                        document.getElementById('detailDescription').textContent = report.description || '';
                        document.getElementById('detailStatus').textContent = report.status ? report.status.charAt(0).toUpperCase() + report.status.slice(1) : '';
                        document.getElementById('detailDate').textContent = report.created_at ? new Date(report.created_at).toLocaleDateString() : '';
                    } else {
                        document.getElementById('detailReportId').textContent = reportId;
                        document.getElementById('detailSubject').textContent = 'Report not found';
                        document.getElementById('detailReporter').textContent = '';
                        document.getElementById('detailTarget').textContent = '';
                        document.getElementById('detailReason').textContent = '';
                        document.getElementById('detailDescription').textContent = 'The report data could not be loaded from the database.';
                        document.getElementById('detailStatus').textContent = '';
                        document.getElementById('detailDate').textContent = '';
                    }
                    const modal = document.getElementById('reportDetailsModal');
                    modal.classList.add('active');
                })
                .catch(error => {
                    console.error('Error fetching report:', error);
                    document.getElementById('detailReportId').textContent = reportId;
                    document.getElementById('detailSubject').textContent = 'Error Loading Report';
                    document.getElementById('detailDescription').textContent = 'An error occurred while loading the report details. Please try again.';
                    const modal = document.getElementById('reportDetailsModal');
                    modal.classList.add('active');
                });
        }

        function closeReportModal() {
            const modal = document.getElementById('reportDetailsModal');
            modal.classList.remove('active');
        }

        function showReplyModal(reportId) {
            document.getElementById('replyModal').currentReportId = reportId;
            document.getElementById('replyText').value = '';
            const modal = document.getElementById('replyModal');
            modal.classList.add('active');
        }

        function closeReplyModal() {
            const modal = document.getElementById('replyModal');
            modal.classList.remove('active');
        }

        // === Listing Details Modal Functions ===
        let currentListingId = null;
        let currentListingTitle = null;
        
        function viewListingDetails(listingId, title, sellerName) {
            // Store for footer buttons
            currentListingId = listingId;
            currentListingTitle = title;
            
            // Clear all fields
            document.getElementById('detailListingId').textContent = listingId;
            document.getElementById('detailListingTitle').textContent = title;
            document.getElementById('detailListingSeller').textContent = sellerName;
            document.getElementById('detailListingDescription').textContent = 'Loading...';
            document.getElementById('detailListingStatusDetail').textContent = 'Loading...';
            document.getElementById('detailListingDate').textContent = 'Loading...';
            document.getElementById('detailListingLocation').textContent = 'Loading...';
            document.getElementById('detailListingPrice').textContent = 'Loading...';
            document.getElementById('detailListingStatus').innerHTML = '<span class="badge pending">Loading...</span>';
            
            // Set image placeholder
            const imageDiv = document.getElementById('detailListingImage');
            imageDiv.innerHTML = '<div class="listing-modal-image--empty"><span>🏞️</span><span>Property Image</span></div>';
            
            // Fetch listing details from the server
            fetch(`/admin/listings/${listingId}/view`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.success && data.listing) {
                        const listing = data.listing;
                        
                        // Populate description
                        document.getElementById('detailListingDescription').textContent = listing.description || 'No description provided';
                        
                        // Populate location
                        const location = [listing.barangay, listing.city, listing.province].filter(Boolean).join(', ');
                        document.getElementById('detailListingLocation').textContent = location || 'No location provided';
                        
                        // Populate price
                        if (listing.price) {
                            document.getElementById('detailListingPrice').textContent = '₱' + Number(listing.price).toLocaleString();
                        } else {
                            document.getElementById('detailListingPrice').textContent = 'N/A';
                        }
                        
                        // Populate date
                        if (listing.created_at) {
                            try {
                                const date = new Date(listing.created_at);
                                document.getElementById('detailListingDate').textContent = date.toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                });
                            } catch (e) {
                                document.getElementById('detailListingDate').textContent = listing.created_at;
                            }
                        } else {
                            document.getElementById('detailListingDate').textContent = 'N/A';
                        }
                        
                        // Determine status
                        let statusClass = 'pending';
                        let statusText = 'Pending';
                        if (listing.is_verified_listing === 'verified' || listing.listing_status === 'approved') {
                            statusClass = 'available';
                            statusText = 'Approved';
                        } else if (listing.listing_status === 'rejected' || listing.is_verified_listing === 'rejected') {
                            statusClass = 'closed';
                            statusText = 'Rejected';
                        }
                        
                        document.getElementById('detailListingStatus').innerHTML = `<span class="badge ${statusClass}">${statusText}</span>`;
                        document.getElementById('detailListingStatusDetail').textContent = statusText;
                        
                        // Populate land area if available
                        const areaElement = document.getElementById('detailListingArea');
                        if (listing.land_area) {
                            areaElement.textContent = listing.land_area + ' sq.m';
                        } else {
                            areaElement.textContent = 'N/A';
                        }
                        
                        // Populate land type if available
                        const typeElement = document.getElementById('detailListingType');
                        if (listing.land_type) {
                            typeElement.textContent = listing.land_type;
                        } else {
                            typeElement.textContent = 'N/A';
                        }
                    } else {
                        document.getElementById('detailListingDescription').textContent = 'Unable to load listing details.';
                    }
                })
                .catch(error => {
                    console.error('Error loading listing details:', error);
                    document.getElementById('detailListingDescription').textContent = 'Unable to load listing details.';
                });
            
            const modal = document.getElementById('listingDetailsModal');
            modal.classList.add('active');
        }

        function closeListingDetailsModal() {
            const modal = document.getElementById('listingDetailsModal');
            modal.classList.remove('active');
        }

        // Approve Listing Function
        function approveListing(listingId, listingTitle) {
            Swal.fire({
                title: 'Approve Listing?',
                html: `<p>Are you sure you want to approve <strong>${listingTitle}</strong>?</p>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'landly-swal',
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send approval request
                    fetch(`/admin/listings/${listingId}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Approved!',
                                text: 'Listing has been approved successfully.',
                                icon: 'success',
                                customClass: { popup: 'landly-swal' }
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Failed to approve listing.',
                                icon: 'error',
                                customClass: { popup: 'landly-swal' }
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while approving the listing.',
                            icon: 'error',
                            customClass: { popup: 'landly-swal' }
                        });
                    });
                }
            });
        }

        // Reject Listing Function
        function rejectListing(listingId, listingTitle) {
            Swal.fire({
                title: 'Reject Listing?',
                html: `<p>Are you sure you want to reject <strong>${listingTitle}</strong>?</p>
                       <textarea id="rejectReason" placeholder="Reason for rejection (optional)" style="width: 100%; padding: 10px; margin-top: 10px; border-radius: 8px; border: 1px solid rgba(149,213,178,.3); background: rgba(0,0,0,.3); color: var(--cream-100); min-height: 80px;"></textarea>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'landly-swal',
                    confirmButton: 'swal2-deny',
                    cancelButton: 'swal2-cancel'
                },
                didOpen: () => {
                    // Focus on textarea
                    setTimeout(() => {
                        document.getElementById('rejectReason').focus();
                    }, 100);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const reason = document.getElementById('rejectReason').value;
                    
                    // Send rejection request
                    fetch(`/admin/listings/${listingId}/reject`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ reason: reason })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Rejected!',
                                text: 'Listing has been rejected successfully.',
                                icon: 'success',
                                customClass: { popup: 'landly-swal' }
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: data.message || 'Failed to reject listing.',
                                icon: 'error',
                                customClass: { popup: 'landly-swal' }
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while rejecting the listing.',
                            icon: 'error',
                            customClass: { popup: 'landly-swal' }
                        });
                    });
                }
            });
        }

        // Close listing modal when clicking outside
        document.getElementById('listingDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeListingDetailsModal();
            }
        });

        function submitReply(event) {
            event.preventDefault();
            const reportId = document.getElementById('replyModal').currentReportId;
            const replyText = document.getElementById('replyText').value;

            if (!replyText.trim()) {
                fireAppAlert({
                    icon: 'warning',
                    title: 'Empty Reply',
                    text: 'Please enter a reply message.'
                });
                return;
            }

            // Show success message
            fireAppAlert({
                icon: 'success',
                title: 'Reply Sent',
                html: `Reply sent to report <strong>${reportId}</strong><br><br><em>${replyText}</em>`,
                confirmButtonText: 'OK'
            });
            closeReplyModal();
        }

        // === User Details Modal Functions ===
        function viewUserDetailsModal(userId, fullName, email, role, accountStatus, joinDate, verification, reportsFiled, reportsAgainst) {
            // Populate user info
            document.getElementById('userID').textContent = userId;
            document.getElementById('userFullName').textContent = fullName;
            document.getElementById('userEmail').textContent = email;
            document.getElementById('userRole').textContent = role.toUpperCase();
            document.getElementById('userStatus').textContent = accountStatus;
            document.getElementById('userJoinDate').textContent = joinDate;
            document.getElementById('userVerification').textContent = verification;
            document.getElementById('userReportsFiled').textContent = reportsFiled;
            document.getElementById('userReportsAgainst').textContent = reportsAgainst;
            
            // Set profile picture emoji based on role
            const profileEmoji = role === 'Seller' ? '🏢' : (role === 'Admin' ? '👨‍💼' : '👤');
            document.getElementById('userProfilePic').textContent = profileEmoji;
            
            // Fetch user listings from the server if seller
            const listingsContainer = document.getElementById('userListingsContainer');
            const noListingsMessage = document.getElementById('noListingsMessage');
            
            // Only show listings if user is a seller
            if (role === 'Seller' || role === 'seller') {
                // Fetch listings for this seller
                fetch(`/admin/user/${userId}/listings`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.listings && data.listings.length > 0) {
                            listingsContainer.innerHTML = data.listings.map(listing => `
                                <div style="background: rgba(149,213,178,.08); border: 1px solid rgba(149,213,178,.2); border-radius: 12px; padding: 16px; cursor: pointer; transition: all .2s ease;" onmouseover="this.style.borderColor='rgba(149,213,178,.5)'" onmouseout="this.style.borderColor='rgba(149,213,178,.2)'">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                        <h4 style="color: var(--cream-100); font-weight: 600; margin: 0; font-size: 1rem;">${listing.title || 'Untitled'}</h4>
                                        <span style="padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: ${listing.is_verified_listing === 'verified' || listing.listing_status === 'approved' ? 'rgba(46,204,113,.2); color: #2ecc71;' : (listing.listing_status === 'rejected' ? 'rgba(231, 76, 60,.2); color: #e74c3c;' : 'rgba(241,196,15,.2); color: #f1c40f;')}">${listing.is_verified_listing === 'verified' || listing.listing_status === 'approved' ? 'Verified' : (listing.listing_status === 'rejected' ? 'Rejected' : 'Pending')}</span>
                                    </div>
                                    <p style="color: rgba(254,250,224,.7); font-size: 0.9rem; margin: 8px 0;">📍 ${listing.barangay}, ${listing.city}, ${listing.province}</p>
                                    <p style="color: var(--accent); font-weight: 700; font-size: 1.1rem; margin: 8px 0;">₱${Number(listing.price).toLocaleString()}</p>
                                    <p style="color: rgba(254,250,224,.5); font-size: 0.85rem; margin: 0;">Posted: ${new Date(listing.created_at).toLocaleDateString()}</p>
                                </div>
                            `).join('');
                            noListingsMessage.style.display = 'none';
                        } else {
                            listingsContainer.innerHTML = '';
                            noListingsMessage.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching listings:', error);
                        listingsContainer.innerHTML = '';
                        noListingsMessage.textContent = 'Unable to load listings.';
                        noListingsMessage.style.display = 'block';
                    });
            } else {
                listingsContainer.innerHTML = '';
                noListingsMessage.style.display = 'block';
            }
            
            // Open modal
            const modal = document.getElementById('userDetailsModal');
            modal.classList.add('active');
        }

        function closeUserDetailsModal() {
            const modal = document.getElementById('userDetailsModal');
            modal.classList.remove('active');
        }

        // Close user details modal when clicking outside
        document.getElementById('userDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserDetailsModal();
            }
        });

        function suspendAccount(userName, reportId) {
            fireAppAlert({
                icon: 'warning',
                title: 'Suspend Account?',
                html: `Are you sure you want to suspend the account of <strong>${userName}</strong>?<br><br><small style="color: rgba(254,250,224,.7);">Report ID: ${reportId}</small>`,
                showCancelButton: true,
                confirmButtonText: 'Yes, Suspend',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    fireAppAlert({
                        icon: 'success',
                        title: 'Account Suspended',
                        html: `Account for <strong>${userName}</strong> has been suspended.`,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        // Close modals when clicking outside
        ['reportDetailsModal', 'replyModal'].forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.remove('active');
                    }
                });
            }
        });

        // User Filtering Functions
        function filterUsersTable() {
            applyUserFiltersAndSearch();
        }

        function searchUsersTable() {
            applyUserFiltersAndSearch();
        }

        function applyUserFiltersAndSearch() {
            const statusFilter = document.getElementById('filterStatus').value;
            const roleFilter = document.getElementById('filterRole').value;
            const verificationFilter = document.getElementById('filterVerification').value;
            const searchTerm = document.getElementById('searchUsers').value.toLowerCase();
            
            const rows = document.querySelectorAll('#usersTable .user-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowRole = row.getAttribute('data-role');
                const rowVerification = row.getAttribute('data-verification');
                
                // Get text content from cells for search
                const cells = row.querySelectorAll('td');
                const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');

                let showRow = true;

                // Apply filter checks
                if (statusFilter && rowStatus !== statusFilter) {
                    showRow = false;
                }
                if (roleFilter && rowRole !== roleFilter) {
                    showRow = false;
                }
                if (verificationFilter && rowVerification !== verificationFilter) {
                    showRow = false;
                }
                
                // Apply search check
                if (searchTerm && !rowText.includes(searchTerm)) {
                    showRow = false;
                }

                if (showRow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show "no results" message if needed
            if (visibleCount === 0) {
                const table = document.getElementById('usersTable');
                let noResultsRow = table.querySelector('tr.no-results');
                if (!noResultsRow) {
                    noResultsRow = document.createElement('tr');
                    noResultsRow.className = 'no-results';
                    noResultsRow.innerHTML = '<td colspan="9" style="text-align: center; padding: 20px; color: rgba(254,250,224,.6);">No users match your search or filters.</td>';
                    table.querySelector('tbody').appendChild(noResultsRow);
                }
            } else {
                const noResultsRow = document.querySelector('#usersTable .no-results');
                if (noResultsRow) {
                    noResultsRow.remove();
                }
            }
        }

        function resetUsersFilter() {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterRole').value = '';
            document.getElementById('filterVerification').value = '';
            document.getElementById('searchUsers').value = '';
            
            document.querySelectorAll('#usersTable .user-row').forEach(row => {
                row.style.display = '';
            });

            const noResultsRow = document.querySelector('#usersTable .no-results');
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }

        // User Report History Data
        const userReportData = {
            'U001': {
                name: 'John Buyer',
                filed: [],
                against: []
            },
            'U002': {
                name: 'Maria Santos',
                filed: [],
                against: [
                    {
                        report_id: 'R001',
                        filed_by: 'John Buyer',
                        subject: 'Fake Documents',
                        reason: 'Fraudulent Documents Submitted',
                        description: 'The seller submitted counterfeit identification and property documents.',
                        status: 'Pending',
                        date: 'Apr 10, 2026'
                    }
                ]
            },
            'U003': {
                name: 'Juan Dela Cruz',
                filed: [],
                against: [
                    {
                        report_id: 'R002',
                        filed_by: 'Jane Investor',
                        subject: 'Misrepresented Property',
                        reason: 'Property Photos Don\'t Match Actual',
                        description: 'The photos used in the listing do not match the actual property condition.',
                        status: 'Pending',
                        date: 'Apr 09, 2026'
                    }
                ]
            },
            'U004': {
                name: 'Carlos Lopez',
                filed: [
                    {
                        report_id: 'R003',
                        filed_against: 'Ana Rodriguez',
                        subject: 'Unprofessional Conduct',
                        reason: 'Harassment and Rude Behavior',
                        description: 'During property negotiations, the user engaged in unprofessional and disrespectful communication.',
                        status: 'Resolved',
                        date: 'Apr 08, 2026'
                    }
                ],
                against: []
            },
            'U005': {
                name: 'Admin User',
                filed: [],
                against: []
            },
            'U006': {
                name: 'Jane Investor',
                filed: [
                    {
                        report_id: 'R002',
                        filed_against: 'Juan Dela Cruz',
                        subject: 'Misrepresented Property',
                        reason: 'Property Photos Don\'t Match Actual',
                        description: 'The photos used in the listing do not match the actual property condition.',
                        status: 'Pending',
                        date: 'Apr 09, 2026'
                    }
                ],
                against: []
            },
            'U007': {
                name: 'Ana Rodriguez',
                filed: [],
                against: []
            }
        };

        // Event Delegation for Data Action Attributes
        document.addEventListener('click', function(e) {
            const action = e.target.dataset.action || e.target.closest('[data-action]')?.dataset.action;
            if (!action) return;
            
            const element = e.target.dataset.action ? e.target : e.target.closest('[data-action]');
            
            switch(action) {
                case 'viewUserReportHistory':
                    viewUserReportHistory(
                        element.dataset.userId,
                        element.dataset.userName,
                        element.dataset.reportType
                    );
                    break;
                case 'viewReportDetails':
                    viewReportDetails(element.dataset.reportId);
                    break;
                case 'showReplyModal':
                    showReplyModal(element.dataset.reportId);
                    break;
                case 'suspendAccount':
                    suspendAccount(element.dataset.userName, element.dataset.reportId);
                    break;
                case 'viewListingDetails':
                    viewListingDetails(element.dataset.listingId, element.dataset.listingTitle, element.dataset.listingSeller);
                    break;
            }
        });

        // Report History Modal Functions
        function viewUserReportHistory(userId, userName, type) {
            const userData = userReportData[userId] || { name: userName, filed: [], against: [] };
            document.getElementById('reportHistoryUserName').textContent = userData.name;
            
            // Reset tabs
            document.getElementById('filedTab').style.background = (type === 'filed') ? 'rgba(46,204,113,.2)' : 'transparent';
            document.getElementById('filedTab').style.color = (type === 'filed') ? '#2ecc71' : 'rgba(254,250,224,.6)';
            document.getElementById('againstTab').style.background = (type === 'against') ? 'rgba(46,204,113,.2)' : 'transparent';
            document.getElementById('againstTab').style.color = (type === 'against') ? '#2ecc71' : 'rgba(254,250,224,.6)';
            
            window.currentReportUserId = userId;
            window.currentReportType = type;
            
            displayReportHistory(type, userData);
            document.getElementById('userReportHistoryModal').classList.add('active');
        }

        function switchReportTab(type) {
            const userData = userReportData[window.currentReportUserId] || { filed: [], against: [] };
            
            // Update tab appearance
            document.getElementById('filedTab').style.background = (type === 'filed') ? 'rgba(46,204,113,.2)' : 'transparent';
            document.getElementById('filedTab').style.color = (type === 'filed') ? '#2ecc71' : 'rgba(254,250,224,.6)';
            document.getElementById('againstTab').style.background = (type === 'against') ? 'rgba(46,204,113,.2)' : 'transparent';
            document.getElementById('againstTab').style.color = (type === 'against') ? '#2ecc71' : 'rgba(254,250,224,.6)';
            
            window.currentReportType = type;
            displayReportHistory(type, userData);
        }

        function displayReportHistory(type, userData) {
            const contentDiv = document.getElementById('reportHistoryContent');
            const reports = type === 'filed' ? userData.filed : userData.against;
            
            if (reports.length === 0) {
                contentDiv.innerHTML = `<div style="text-align: center; padding: 32px; color: rgba(254,250,224,.6);">No ${type === 'filed' ? 'reports filed' : 'reports against'} for this user.</div>`;
                return;
            }
            
            contentDiv.innerHTML = reports.map((report, index) => `
                <div style="background: rgba(149,213,178,.05); border: 1px solid rgba(149,213,178,.2); border-radius: 12px; padding: 16px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <div>
                            <div style="font-weight: 700; font-size: 1.1rem;">${report.subject}</div>
                            <div style="color: rgba(254,250,224,.7); font-size: .85rem; margin-top: 4px;">
                                ${type === 'filed' ? 'Against: ' + report.filed_against : 'Filed by: ' + report.filed_by}
                            </div>
                        </div>
                        <span class="badge ${report.status.toLowerCase() === 'pending' ? 'pending' : 'available'}">
                            ${report.status}
                        </span>
                    </div>
                    <div style="margin-bottom: 12px; font-size: .9rem; line-height: 1.6;">
                        <p><strong>Reason:</strong> ${report.reason}</p>
                        <p><strong>Details:</strong> ${report.description}</p>
                        <p style="color: rgba(254,250,224,.6); font-size: .85rem;"><strong>Date:</strong> ${report.date} | <strong>Report ID:</strong> ${report.report_id}</p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button class="btn btn-sm btn-view" type="button" onclick="showReportFullView('${report.report_id}', '${report.subject}')">View Full Report</button>
                        <button class="btn btn-sm btn-success" type="button" onclick="showReplyForm('${report.report_id}')">Reply</button>
                    </div>
                </div>
            `).join('');
        }

        function showReportFullView(reportId, subject) {
            fireAppAlert({
                icon: 'info',
                title: 'Report Details',
                html: `<strong>${subject}</strong><br><br><small style="color: rgba(254,250,224,.7);">Report ID: ${reportId}</small><br><br><small style="color: rgba(254,250,224,.7);">Full report details and attachments would be displayed here.</small>`,
                confirmButtonText: 'Close'
            });
        }

        function showReplyForm(reportId) {
            fireAppAlert({
                icon: 'info',
                title: 'Reply to Report',
                html: `<textarea id="replyTextAlert" placeholder="Enter your reply..." style="width: 100%; min-height: 100px; padding: 10px; border-radius: 8px; border: 1px solid rgba(149,213,178,.3); background: rgba(0,0,0,.3); color: var(--cream-100); font-family: inherit; font-size: .9rem; resize: vertical;"></textarea>`,
                confirmButtonText: 'Send Reply',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                preConfirm: () => {
                    const replyText = document.getElementById('replyTextAlert')?.value || '';
                    if (!replyText.trim()) {
                        Swal.showValidationMessage('Please enter a reply message');
                        return false;
                    }
                    return replyText;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    fireAppAlert({
                        icon: 'success',
                        title: 'Reply Sent',
                        html: `Reply sent to report <strong>${reportId}</strong>`,
                        confirmButtonText: 'OK'
                    });
                }
            });
        }

        function closeUserReportHistoryModal() {
            document.getElementById('userReportHistoryModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('userReportHistoryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeUserReportHistoryModal();
            }
        });

        // ============================================
        // CHART.JS INITIALIZATION FOR ANALYTICS
        // ============================================
        
        const chartColors = {
            primary: '#95d5b2',
            accent: '#d2b48c',
            warning: '#f1c40f',
            success: '#2ecc71',
            danger: '#e74c3c',
            info: '#3498db',
            secondary: '#95a5a6'
        };

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: 'rgba(254,250,224,.8)',
                        font: { family: 'Inter, system-ui, sans-serif', size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,.7)',
                    titleColor: 'rgba(254,250,224,.9)',
                    bodyColor: 'rgba(254,250,224,.8)',
                    borderColor: 'rgba(149,213,178,.3)',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    ticks: { color: 'rgba(254,250,224,.6)' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                },
                y: {
                    ticks: { color: 'rgba(254,250,224,.6)' },
                    grid: { color: 'rgba(255,255,255,0.05)' }
                }
            },
            scale: {
                ticks: { color: 'rgba(254,250,224,.6)' },
                grid: { color: 'rgba(255,255,255,0.05)' }
            }
        };

        // Initialize chart data from database variables passed by the controller
        let dashboardData = {};
        try {
            const dataElement = document.getElementById('dashboardData');
            if (dataElement && dataElement.textContent) {
                dashboardData = JSON.parse(dataElement.textContent);
            }
        } catch (e) {
            console.error('Error parsing dashboard data:', e);
            // Use zeros if parsing fails
            dashboardData = {
                totalBuyers: 0,
                totalSellers: 0,
                totalAdmins: 0,
                listingPending: 0,
                listingVerified: 0,
                listingRejected: 0,
                reportPending: 0,
                reportResolved: 0,
                reportSuspended: 0,
                verificationVerified: 0,
                verificationPending: 0,
                verificationUnverified: 0
            };
        }

        const totalBuyers = dashboardData.totalBuyers || 0;
        const totalSellers = dashboardData.totalSellers || 0;
        const totalAdmins = dashboardData.totalAdmins || 0;
        const listingPending = dashboardData.listingPending || 0;
        const listingVerified = dashboardData.listingVerified || 0;
        const listingRejected = dashboardData.listingRejected || 0;
        const reportPending = dashboardData.reportPending || 0;
        const reportResolved = dashboardData.reportResolved || 0;
        const reportSuspended = dashboardData.reportSuspended || 0;
        const verificationVerified = dashboardData.verificationVerified || 0;
        const verificationPending = dashboardData.verificationPending || 0;
        const verificationUnverified = dashboardData.verificationUnverified || 0;

        // 1. USER CATEGORY PIE CHART
        const userCategoryCtx = document.getElementById('userCategoryChart');
        if (userCategoryCtx) {
            new Chart(userCategoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Buyers', 'Sellers', 'Admins'],
                    datasets: [{
                        data: [totalBuyers, totalSellers, totalAdmins],
                        backgroundColor: [
                            'rgba(149,213,178,.8)',
                            'rgba(210,180,140,.8)',
                            'rgba(241,196,15,.8)'
                        ],
                        borderColor: [
                            'rgba(149,213,178,.3)',
                            'rgba(210,180,140,.3)',
                            'rgba(241,196,15,.3)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        legend: {
                            ...chartOptions.plugins.legend,
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // 2. LISTING STATUS BAR CHART
        const listingStatusCtx = document.getElementById('listingStatusChart');
        if (listingStatusCtx) {
            new Chart(listingStatusCtx, {
                type: 'bar',
                data: {
                    labels: ['Pending', 'Verified', 'Rejected'],
                    datasets: [{
                        label: 'Listings',
                        data: [listingPending, listingVerified, listingRejected],
                        backgroundColor: [
                            'rgba(241,196,15,.8)',
                            'rgba(46,204,113,.8)',
                            'rgba(231,76,60,.8)'
                        ],
                        borderColor: [
                            'rgba(241,196,15,.3)',
                            'rgba(46,204,113,.3)',
                            'rgba(231,76,60,.3)'
                        ],
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    ...chartOptions,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            ticks: { color: 'rgba(254,250,224,.6)' },
                            grid: { color: 'rgba(149,213,178,.1)' }
                        },
                        y: {
                            ticks: { color: 'rgba(254,250,224,.6)' },
                            grid: { color: 'rgba(149,213,178,.1)' }
                        }
                    },
                    plugins: {
                        ...chartOptions.plugins,
                        legend: { display: false }
                    }
                }
            });
        }

        // 3. REPORT STATUS CHART
        const reportStatusCtx = document.getElementById('reportStatusChart');
        if (reportStatusCtx) {
            new Chart(reportStatusCtx, {
                type: 'pie',
                data: {
                    labels: ['Pending', 'Resolved', 'Suspended'],
                    datasets: [{
                        data: [reportPending, reportResolved, reportSuspended],
                        backgroundColor: [
                            'rgba(241,196,15,.8)',
                            'rgba(46,204,113,.8)',
                            'rgba(231,76,60,.8)'
                        ],
                        borderColor: [
                            'rgba(241,196,15,.3)',
                            'rgba(46,204,113,.3)',
                            'rgba(231,76,60,.3)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        legend: {
                            ...chartOptions.plugins.legend,
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // 4. VERIFICATION STATUS CHART
        const verificationCtx = document.getElementById('verificationChart');
        if (verificationCtx) {
            new Chart(verificationCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Verified', 'Pending', 'Unverified'],
                    datasets: [{
                        data: [verificationVerified, verificationPending, verificationUnverified],
                        backgroundColor: [
                            'rgba(46,204,113,.8)',
                            'rgba(241,196,15,.8)',
                            'rgba(149,165,166,.8)'
                        ],
                        borderColor: [
                            'rgba(46,204,113,.3)',
                            'rgba(241,196,15,.3)',
                            'rgba(149,165,166,.3)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        legend: {
                            ...chartOptions.plugins.legend,
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // 5. ACTIVITY TREND LINE CHART
        const activityTrendCtx = document.getElementById('activityTrendChart');
        if (activityTrendCtx) {
            new Chart(activityTrendCtx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7'],
                    datasets: [
                        {
                            label: 'New Users',
                            data: [],
                            borderColor: 'rgba(149,213,178,.8)',
                            backgroundColor: 'rgba(149,213,178,.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: 'rgba(149,213,178,.8)',
                            pointBorderColor: '#0f1b1b',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'New Listings',
                            data: [],
                            borderColor: 'rgba(210,180,140,.8)',
                            backgroundColor: 'rgba(210,180,140,.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: 'rgba(210,180,140,.8)',
                            pointBorderColor: '#0f1b1b',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Reports Filed',
                            data: [],
                            borderColor: 'rgba(231,76,60,.8)',
                            backgroundColor: 'rgba(231,76,60,.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3,
                            pointRadius: 4,
                            pointBackgroundColor: 'rgba(231,76,60,.8)',
                            pointBorderColor: '#0f1b1b',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: 'rgba(254,250,224,.6)' },
                            grid: { color: 'rgba(149,213,178,.1)' }
                        },
                        x: {
                            ticks: { color: 'rgba(254,250,224,.6)' },
                            grid: { color: 'rgba(149,213,178,.1)' }
                        }
                    },
                    plugins: {
                        ...chartOptions.plugins,
                        legend: {
                            ...chartOptions.plugins.legend,
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // === Form Confirmation Handler with SweetAlert ===
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept forms that would show confirm dialogs
            const forms = document.querySelectorAll('form[onsubmit*="confirm"]');
            forms.forEach(form => {
                const onsubmitAttr = form.getAttribute('onsubmit');
                form.removeAttribute('onsubmit');
                
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Extract action and confirm message
                    const action = form.action;
                    let confirmMessage = "Are you sure?";
                    
                    if (onsubmitAttr.includes('Delete user')) {
                        confirmMessage = "Are you sure you want to delete this user? This action cannot be undone.";
                    }
                    
                    // Show SweetAlert confirmation
                    const result = await fireAppAlert({
                        icon: 'warning',
                        title: 'Confirm Action',
                        text: confirmMessage,
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Delete',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        customClass: {
                            confirmButton: 'landly-swal-confirm',
                            cancelButton: 'landly-swal-cancel'
                        }
                    });
                    
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Setup document gallery button listeners
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('documents-gallery-btn')) {
                const sellerName = e.target.dataset.sellerName;
                const documentsJson = e.target.dataset.documents;
                
                console.log('=== DOCUMENTS BUTTON CLICKED ===');
                console.log('Seller Name:', sellerName);
                console.log('Documents JSON:', documentsJson);
                
                try {
                    const documents = JSON.parse(documentsJson);
                    console.log('Parsed documents:', documents);
                    openDocumentsGallery(sellerName, documents);
                } catch (error) {
                    console.error('Failed to parse documents:', error);
                    console.error('Documents JSON was:', documentsJson);
                    alert('Error loading documents. Check browser console for details.');
                }
            }
        });

        // Seller Approval Functions
        async function approveSeller(sellerId) {
            const result = await fireAppAlert({
                title: 'Approve Seller',
                text: 'Are you sure you want to approve this seller?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                fetch(`<?php echo base_url('admin/approve-seller'); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ seller_id: sellerId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fireAppAlert({
                            title: 'Success',
                            text: 'Seller approved successfully!',
                            icon: 'success',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        fireAppAlert({
                            title: 'Error',
                            text: data.message || 'Failed to approve seller',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    fireAppAlert({
                        title: 'Error',
                        text: 'An error occurred while approving the seller',
                        icon: 'error'
                    });
                });
            }
        }

        async function rejectSeller(sellerId) {
            const result = await fireAppAlert({
                title: 'Reject Seller',
                text: 'Are you sure you want to reject this seller?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                fetch(`<?php echo base_url('admin/reject-seller'); ?>`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ seller_id: sellerId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fireAppAlert({
                            title: 'Success',
                            text: 'Seller rejected successfully!',
                            icon: 'success',
                            timer: 2000
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        fireAppAlert({
                            title: 'Error',
                            text: data.message || 'Failed to reject seller',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    fireAppAlert({
                        title: 'Error',
                        text: 'An error occurred while rejecting the seller',
                        icon: 'error'
                    });
                });
            }
        }

        // User Management Confirmation Functions
        async function confirmActivateUser(userId, userName) {
            const result = await fireAppAlert({
                title: 'Activate User',
                text: `Are you sure you want to activate ${userName}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Activate',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `<?php echo base_url('admin/users'); ?>/${userId}/activate`;
                
                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '<?php echo csrf_token(); ?>';
                csrfField.value = '<?php echo csrf_hash(); ?>';
                form.appendChild(csrfField);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        async function confirmDeactivateUser(userId, userName) {
            const result = await fireAppAlert({
                title: 'Deactivate User',
                text: `Are you sure you want to deactivate ${userName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Deactivate',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `<?php echo base_url('admin/users'); ?>/${userId}/deactivate`;
                
                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '<?php echo csrf_token(); ?>';
                csrfField.value = '<?php echo csrf_hash(); ?>';
                form.appendChild(csrfField);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        async function confirmDeleteUser(userId, userName) {
            const result = await fireAppAlert({
                title: 'Delete User',
                text: `Are you sure you want to permanently delete ${userName}? This action cannot be undone.`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                confirmButtonColor: '#dc3545'
            });

            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `<?php echo base_url('admin/users'); ?>/${userId}/delete`;
                
                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '<?php echo csrf_token(); ?>';
                csrfField.value = '<?php echo csrf_hash(); ?>';
                form.appendChild(csrfField);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        async function confirmSuspendAccount(userName, reportId) {
            const result = await fireAppAlert({
                title: 'Suspend Account',
                text: `Are you sure you want to suspend the account of ${userName}? They will be unable to access the platform.`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonText: 'Yes, Suspend',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                confirmButtonColor: '#dc3545'
            });

            if (result.isConfirmed) {
                fireAppAlert({
                    title: 'Account Suspended',
                    text: `${userName}'s account has been suspended.`,
                    icon: 'success',
                    timer: 2000
                }).then(() => {
                    location.reload();
                });
            }
        }
    </script>
</body>
</html>