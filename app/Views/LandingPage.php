<?php
$availableListings = array_values(array_filter(
    is_array($availableListings ?? null) ? $availableListings : [],
    static fn(array $listing): bool => strtolower(trim((string) ($listing['listing_status'] ?? ''))) === 'available'
));
$listingMapPins = array_values(array_filter(
    array_map(static function (array $listing): ?array {
        $lat = isset($listing['latitude']) && is_numeric($listing['latitude']) ? (float) $listing['latitude'] : null;
        $lng = isset($listing['longitude']) && is_numeric($listing['longitude']) ? (float) $listing['longitude'] : null;
        if ($lat === null || $lng === null) {
            return null;
        }

        return [
            'id' => (int) ($listing['listing_id'] ?? 0),
            'title' => (string) ($listing['title'] ?? 'Untitled Listing'),
            'location' => (string) ($listing['location_label'] ?? 'Location unavailable'),
            'price' => (float) ($listing['price_value'] ?? 0),
            'lat' => $lat,
            'lng' => $lng,
        ];
    }, $availableListings),
    static fn(?array $pin): bool => is_array($pin)
));
$availableResultCount = count($availableListings);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Landly | Buy & Sell Lands</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        :root {
            --green-900: #0f1b1b;
            --green-800: #14312c;
            --cream-100: #f5f5dc;
            --cream-200: #efe7d8;
            --accent: #d2b48c;
            --text-dark: #0f1b1b;
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
            position: relative;
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

        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background:
                linear-gradient(180deg, transparent 0%, rgba(15, 27, 27, 0.3) 50%, rgba(15, 27, 27, 0.6) 100%);
            pointer-events: none;
            z-index: 0;
        }

        body > * {
            position: relative;
            z-index: 1;
        }

        img {
            max-width: 100%;
            display: block;
        }

        /* === ANIMATIONS === */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }

        @keyframes glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(210, 180, 140, 0.3);
            }
            50% {
                box-shadow: 0 0 40px rgba(210, 180, 140, 0.6);
            }
        }

        @keyframes rotate {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes slideInScale {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes textReveal {
            from {
                clip-path: inset(0 100% 0 0);
            }
            to {
                clip-path: inset(0 0 0 0);
            }
        }

        @keyframes borderGlow {
            0%, 100% {
                border-color: rgba(210, 180, 140, 0.3);
            }
            50% {
                border-color: rgba(210, 180, 140, 0.7);
            }
        }

        /* Scroll reveal classes */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal-left.active {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(50px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal-right.active {
            opacity: 1;
            transform: translateX(0);
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(0.85);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .reveal-scale.active {
            opacity: 1;
            transform: scale(1);
        }

        /* Stagger children animations */
        .stagger-children > * {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stagger-children.active > *:nth-child(1) { transition-delay: 0.1s; }
        .stagger-children.active > *:nth-child(2) { transition-delay: 0.2s; }
        .stagger-children.active > *:nth-child(3) { transition-delay: 0.3s; }
        .stagger-children.active > *:nth-child(4) { transition-delay: 0.4s; }
        .stagger-children.active > *:nth-child(5) { transition-delay: 0.5s; }
        .stagger-children.active > *:nth-child(6) { transition-delay: 0.6s; }

        .stagger-children.active > * {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- Header --- */
        header {
            position: fixed;
            top: 0; width: 100%;
            z-index: 100;
            background: rgba(20, 49, 44, 0.35);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(20, 49, 44, 0.55);
            box-shadow: 0 12px 30px rgba(8, 18, 17, 0.35);
        }

        .container {
            width: min(1180px, 92%);
            margin: 0 auto;
        }

        .nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
        }

        .brand {
            display: flex; align-items: center; gap: 10px;
            font-weight: 700; font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .brand:hover {
            transform: scale(1.05);
        }

        .brand-badge {
            width: 35px; height: 35px; border-radius: 8px;
            background: linear-gradient(135deg, var(--accent) 0%, #c9a86c 100%);
            color: var(--green-900);
            display: grid; place-items: center;
            transition: all 0.4s ease;
            animation: pulse 3s ease-in-out infinite;
        }

        .brand:hover .brand-badge {
            transform: rotate(10deg) scale(1.1);
            box-shadow: 0 5px 20px rgba(210, 180, 140, 0.4);
        }

        .nav-links a {
            margin-left: 25px; font-size: 0.9rem; text-decoration: none; color: inherit;
            position: relative;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--accent);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
        }

        .nav-links a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -6px;
            width: 0;
            height: 2px;
            background: var(--accent);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* --- Hero --- */
        .hero-section {
            padding: 160px 0 100px;
            background: none;
            text-align: left;
        }

        .hero-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background: url('https://images.unsplash.com/photo-1501555088652-021faa106b9b?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat;
            filter: blur(3px);
            transform: scale(1.03);
            z-index: 0;
        }

        .hero-section::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(12, 26, 27, 0.7), rgba(12, 26, 27, 0.4));
            z-index: 1;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 40px;
            align-items: center;
        }

        .hero-content {
            max-width: 560px;
            animation: fadeInLeft 1s ease-out;
        }

        .hero-section h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: clamp(2.8rem, 5.5vw, 4.5rem);
            font-weight: 600;
            margin: 20px 0;
            line-height: 1.1;
            letter-spacing: -0.5px;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        .hero-glass {
            padding: 18px;
            border-radius: 22px;
            background: rgba(239, 231, 216, 0.15);
            border: 1px solid rgba(202, 164, 110, 0.35);
            box-shadow: 0 18px 40px rgba(12, 26, 27, 0.35);
            backdrop-filter: blur(8px);
            animation: fadeInRight 1s ease-out 0.5s both;
            transition: all 0.4s ease;
        }

        .hero-glass:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(12, 26, 27, 0.45);
            border-color: rgba(210, 180, 140, 0.5);
        }

        .hero-slideshow {
            position: relative;
            height: 320px;
            border-radius: 18px;
            overflow: hidden;
        }

        .hero-slide {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            opacity: 0;
            animation: heroSlide 15s ease-in-out infinite;
        }

        .hero-slide:nth-child(1) { animation-delay: 0s; }
        .hero-slide:nth-child(2) { animation-delay: 5s; }
        .hero-slide:nth-child(3) { animation-delay: 10s; }

        .pill {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            border: 1px solid var(--accent);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            animation: fadeInDown 0.8s ease-out;
            background: linear-gradient(135deg, rgba(210, 180, 140, 0.1), rgba(210, 180, 140, 0.05));
            position: relative;
            overflow: hidden;
        }

        .pill::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(210, 180, 140, 0.3), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }

        /* --- WAVY DIVIDER STYLE --- */
        .wave-container {
            position: relative;
            width: 100%;
            line-height: 0;
            margin-top: -60px;
            overflow: hidden;
        }

        .wave-container svg {
            width: 100%;
            height: 160px;
        }

        .wave-container .wave-layer {
            transform-origin: center;
        }

        .wave-container .wave-layer--move {
            animation: waveShift 8s ease-in-out infinite;
        }

        .wave-container::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -1px;
            height: 60px;
            z-index: 0;
            pointer-events: none;
        }

        .wave-container.wave-cream::after {
            background: linear-gradient(to bottom, rgba(239, 231, 216, 0), rgba(239, 231, 216, 0.98));
        }

        .wave-container.wave-green::after {
            background: linear-gradient(to bottom, rgba(15, 27, 27, 0), rgba(15, 27, 27, 0.98));
        }

        @keyframes waveShift {
            0% { transform: translateY(0); }
            25% { transform: translateY(-4px); }
            50% { transform: translateY(-8px); }
            75% { transform: translateY(-4px); }
            100% { transform: translateY(0); }
        }

        @keyframes heroSlide {
            0%, 25% { opacity: 1; }
            33%, 100% { opacity: 0; }
        }

        @keyframes headerGlow {
            0%, 100% { opacity: 0.6; transform: translateX(0); }
            50% { opacity: 1; transform: translateX(10%); }
        }

        .wave-top {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 80px;
            margin-top: 0;
            z-index: 1;
        }

        .wave-top svg {
            height: 80px;
        }

        .hero-section {
            position: relative;
        }

        .hero-section .container {
            position: relative;
            z-index: 2;
        }


        /* --- Sections --- */
        .section { padding: 100px 0; }

        /* Features is Cream (as requested) */
        #features {
            background: linear-gradient(165deg, #0f1b1b 0%, #14312c 50%, #1a3d35 100%);
            color: var(--cream-100);
            position: relative;
            overflow: hidden;
        }

        #features::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 10% 20%, rgba(210, 180, 140, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(210, 180, 140, 0.05) 0%, transparent 35%);
            pointer-events: none;
        }

        #features .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 50px;
        }

        #features h2 {
            font-size: 2.2rem;
            background: linear-gradient(135deg, var(--cream-100), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .feature-pill {
            padding: 10px 22px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(210, 180, 140, 0.15), rgba(210, 180, 140, 0.05));
            border: 1px solid rgba(210, 180, 140, 0.4);
            color: var(--accent);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        /* Listings is Forest */
        #listings {
            background-color: var(--green-900);
            color: var(--cream-100);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        @media (max-width: 1024px) {
            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 680px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
        }

        .feature-card {
            padding: 36px 32px;
            border-radius: 24px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(210, 180, 140, 0.2);
            backdrop-filter: blur(20px);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 320px;
            display: flex;
            flex-direction: column;
        }

        .feature-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .feature-card::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 0%, rgba(210, 180, 140, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .feature-card:hover {
            transform: translateY(-12px) scale(1.02);
            border-color: rgba(210, 180, 140, 0.5);
            box-shadow:
                0 30px 70px rgba(0, 0, 0, 0.5),
                0 0 50px rgba(210, 180, 140, 0.15);
        }

        .feature-card:hover::before {
            opacity: 1;
            animation: shimmer 1.5s ease infinite;
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(210, 180, 140, 0.2), rgba(210, 180, 140, 0.05));
            border: 1px solid rgba(210, 180, 140, 0.3);
            display: grid;
            place-items: center;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
            transition: all 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 10px 30px rgba(210, 180, 140, 0.3);
        }

        .feature-icon svg {
            width: 28px;
            height: 28px;
            stroke: var(--accent);
            stroke-width: 1.5;
            fill: none;
            transition: all 0.4s ease;
        }

        .feature-card:hover .feature-icon svg {
            transform: scale(1.1);
        }

        .feature-card h3 {
            font-size: 1.25rem;
            margin-bottom: 12px;
            color: var(--cream-100);
            position: relative;
            z-index: 1;
        }

        .feature-card p {
            color: rgba(245, 245, 220, 0.7);
            line-height: 1.7;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .feature-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: auto;
            padding: 8px 16px;
            border-radius: 999px;
            background: linear-gradient(135deg, rgba(210, 180, 140, 0.15), rgba(210, 180, 140, 0.05));
            border: 1px solid rgba(210, 180, 140, 0.25);
            font-size: 0.7rem;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-tag {
            background: linear-gradient(135deg, rgba(210, 180, 140, 0.25), rgba(210, 180, 140, 0.1));
            border-color: rgba(210, 180, 140, 0.4);
            transform: translateX(5px);
        }

        .feature-tag::before {
            content: "";
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--accent);
            animation: pulse 2s ease-in-out infinite;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px; margin-top: 40px;
        }

        .card {
            padding: 30px; border-radius: 20px;
            background: rgba(255, 255, 255, 0.4);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 18px 40px rgba(8, 18, 17, 0.2);
        }

        #listings .card {
            background: rgba(30, 75, 67, 0.5);
            border: 1px solid rgba(202, 164, 110, 0.2);
        }

        /* --- Buttons --- */
        .btn {
            padding: 12px 25px; border-radius: 30px;
            text-decoration: none; font-weight: 600;
            display: inline-block; cursor: pointer; border: none;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent) 0%, #c9a86c 100%);
            color: var(--green-900);
            box-shadow: 0 4px 15px rgba(210, 180, 140, 0.3);
        }

        .btn-primary:hover { 
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(210, 180, 140, 0.5);
        }

        .btn-outline {
            border: 2px solid var(--accent);
            color: var(--accent);
            background: transparent;
        }

        .btn-outline:hover {
            background: var(--accent);
            color: var(--green-900);
            transform: translateY(-2px);
        }

        /* Detail Panel Styling */
        #detailsPanel {
            margin-top: 40px; padding: 30px;
            border-radius: 20px; background: rgba(202, 164, 110, 0.1);
            border: 1px dashed var(--accent);
        }

        .hidden { display: none; }

        /* --- Map Section --- */
        .map-section {
            padding: 80px 0 100px;
            background: linear-gradient(180deg, #efe7d8 0%, #f5f5dc 65%, #efe7d8 100%);
            color: var(--text-dark);
            position: relative;
            overflow: hidden;
        }

        .map-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 15% 20%, rgba(210, 180, 140, 0.12), transparent 55%),
                              radial-gradient(circle at 85% 80%, rgba(245, 245, 220, 0.08), transparent 60%);
            pointer-events: none;
        }

        .map-section h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 3rem;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--green-900);
            letter-spacing: -0.5px;
        }

        .map-section .section-subtitle {
            color: #4a5d5d;
            font-size: 1.05rem;
            margin-bottom: 35px;
            max-width: 500px;
        }

        /* Search Controls */
        .search-controls {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(210, 180, 140, 0.3);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(15, 27, 27, 0.08);
        }

        .search-row {
            display: grid;
            grid-template-columns: 1fr 1.2fr auto;
            gap: 20px;
            align-items: end;
        }

        .search-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .search-group label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--green-900);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-group label svg {
            width: 16px;
            height: 16px;
            stroke: var(--tan);
            fill: none;
            stroke-width: 2;
        }

        .location-input-wrap {
            position: relative;
        }

        .location-input-wrap input {
            width: 100%;
            padding: 14px 18px 14px 48px;
            border-radius: 12px;
            border: 2px solid rgba(15, 27, 27, 0.1);
            background: #fff;
            font-size: 1rem;
            color: var(--green-900);
            transition: all 0.3s ease;
        }

        .location-input-wrap input:focus {
            outline: none;
            border-color: var(--tan);
            box-shadow: 0 0 0 4px rgba(210, 180, 140, 0.15);
        }

        .location-input-wrap input::placeholder {
            color: #8a9a9a;
        }

        .location-input-wrap .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            stroke: #7a8a8a;
            fill: none;
            stroke-width: 2;
        }

        /* Price Range Slider */
        .price-range-wrap {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .price-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .price-badge {
            padding: 8px 16px;
            background: linear-gradient(135deg, var(--green-900) 0%, #1a3030 100%);
            color: var(--cream-100);
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            min-width: 80px;
            text-align: center;
        }

        .price-separator {
            color: #8a9a9a;
            font-size: 0.85rem;
        }

        .range-slider-container {
            position: relative;
            height: 6px;
            background: rgba(15, 27, 27, 0.1);
            border-radius: 3px;
        }

        .range-track {
            position: absolute;
            height: 100%;
            background: linear-gradient(90deg, var(--tan) 0%, #c9a86c 100%);
            border-radius: 3px;
            left: 10%;
            right: 30%;
        }

        .range-slider-container input[type="range"] {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            pointer-events: none;
            top: -8px;
        }

        .range-slider-container input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 22px;
            height: 22px;
            background: linear-gradient(135deg, var(--tan) 0%, #c9a86c 100%);
            border-radius: 50%;
            cursor: pointer;
            pointer-events: auto;
            border: 3px solid #fff;
            box-shadow: 0 2px 10px rgba(15, 27, 27, 0.2);
            transition: transform 0.2s ease;
        }

        .range-slider-container input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.15);
        }

        .search-btn {
            padding: 14px 32px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--green-900) 0%, #1a3030 100%);
            border: none;
            color: var(--cream-100);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(15, 27, 27, 0.25);
        }

        .search-btn svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        /* Map Container */
        .map-wrap {
            position: relative;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(210, 180, 140, 0.25);
            box-shadow: 0 20px 50px rgba(6, 12, 12, 0.15);
            margin-bottom: 40px;
        }

        .map-placeholder {
            height: 380px;
            background: #dce7d4;
            position: relative;
        }

        #landlyListingsMap {
            position: absolute;
            inset: 0;
            z-index: 1;
        }

        .map-empty-note {
            position: absolute;
            bottom: 14px;
            left: 14px;
            z-index: 400;
            padding: 8px 12px;
            border-radius: 10px;
            background: rgba(15, 27, 27, 0.86);
            color: var(--cream-100);
            font-size: 0.8rem;
            border: 1px solid rgba(210, 180, 140, 0.35);
            backdrop-filter: blur(4px);
        }

        .map-pins {
            position: absolute;
            inset: 0;
        }

        .map-pin {
            position: absolute;
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--tan) 0%, #c9a86c 100%);
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            animation: float 3s ease-in-out infinite;
        }

        .map-pin::after {
            content: '';
            width: 14px;
            height: 14px;
            background: var(--green-900);
            border-radius: 50%;
            transform: rotate(45deg);
        }

        .map-pin:hover {
            transform: rotate(-45deg) scale(1.2);
            animation-play-state: paused;
            box-shadow: 0 8px 25px rgba(210, 180, 140, 0.5);
        }

        .map-pin.pin-1 { top: 25%; left: 20%; animation-delay: 0s; }
        .map-pin.pin-2 { top: 45%; left: 55%; animation-delay: 0.5s; }
        .map-pin.pin-3 { top: 60%; left: 35%; animation-delay: 1s; }
        .map-pin.pin-4 { top: 30%; left: 70%; animation-delay: 1.5s; }

        .map-overlay {
            position: absolute;
            top: 16px;
            right: 16px;
            display: flex;
            gap: 10px;
            z-index: 2;
        }

        .map-pill {
            padding: 10px 18px;
            border-radius: 10px;
            background: rgba(15, 27, 27, 0.85);
            color: var(--cream-100);
            border: 1px solid rgba(210, 180, 140, 0.3);
            font-size: 0.85rem;
            font-weight: 500;
            backdrop-filter: blur(8px);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .map-pill svg {
            width: 14px;
            height: 14px;
            stroke: var(--tan);
            fill: none;
            stroke-width: 2;
        }

        .map-pill:hover {
            background: rgba(15, 27, 27, 0.95);
            transform: translateY(-2px);
        }

        /* Recommended Results */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .results-header h3 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--green-900);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .results-count {
            background: var(--tan);
            color: var(--green-900);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .results-sort {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #5a6d6d;
            font-size: 0.9rem;
        }

        .results-sort select {
            padding: 8px 14px;
            border-radius: 8px;
            border: 1px solid rgba(15, 27, 27, 0.15);
            background: #fff;
            color: var(--green-900);
            font-size: 0.9rem;
            cursor: pointer;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
        }

        .result-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(210, 180, 140, 0.2);
            box-shadow: 0 8px 30px rgba(15, 27, 27, 0.08);
            transition: all 0.4s ease;
            cursor: pointer;
        }

        .result-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(15, 27, 27, 0.15);
        }

        .result-card-image {
            position: relative;
            height: 180px;
            overflow: hidden;
        }

        .result-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .result-card:hover .result-card-image img {
            transform: scale(1.08);
        }

        .result-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(15, 27, 27, 0.85);
            color: var(--cream-100);
            font-size: 0.75rem;
            font-weight: 600;
            backdrop-filter: blur(4px);
        }

        .result-badge.verified {
            background: linear-gradient(135deg, var(--tan) 0%, #c9a86c 100%);
            color: var(--green-900);
        }

        .result-favorite {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .result-favorite svg {
            width: 18px;
            height: 18px;
            stroke: var(--green-900);
            fill: none;
            stroke-width: 2;
            transition: all 0.3s ease;
        }

        .result-favorite:hover svg {
            fill: #e74c3c;
            stroke: #e74c3c;
        }

        .result-card-body {
            padding: 20px;
        }

        .result-location {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: #5a6d6d;
            margin-bottom: 8px;
        }

        .result-location svg {
            width: 14px;
            height: 14px;
            stroke: var(--tan);
            fill: none;
            stroke-width: 2;
        }

        .result-card-body h4 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: 12px;
        }

        .result-specs {
            display: flex;
            gap: 16px;
            margin-bottom: 16px;
        }

        .result-spec {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: #4a5d5d;
        }

        .result-spec svg {
            width: 16px;
            height: 16px;
            stroke: #7a8a8a;
            fill: none;
            stroke-width: 2;
        }

        .result-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 16px;
            border-top: 1px solid rgba(15, 27, 27, 0.08);
        }

        .result-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--green-900);
        }

        .result-price span {
            font-size: 0.85rem;
            font-weight: 400;
            color: #7a8a8a;
        }

        .result-view-btn {
            padding: 10px 18px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--green-900) 0%, #1a3030 100%);
            border: none;
            color: var(--cream-100);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .result-view-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(15, 27, 27, 0.2);
        }

        @media (max-width: 900px) {
            .search-row {
                grid-template-columns: 1fr;
            }
            .search-btn {
                width: 100%;
                justify-content: center;
            }
        }

        .map-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: var(--cream-200);
        }

        /* --- Featured Listings --- */
        .featured-section {
            padding: 80px 0;
            background: linear-gradient(160deg, rgba(15, 27, 27, 0.98), rgba(10, 20, 20, 0.95));
            color: var(--cream-100);
            position: relative;
            overflow: hidden;
        }

        .featured-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(circle at 10% 10%, rgba(210, 180, 140, 0.2), transparent 50%),
                              radial-gradient(circle at 90% 80%, rgba(245, 245, 220, 0.08), transparent 60%);
            pointer-events: none;
        }

        .featured-section h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 3rem;
            font-weight: 600;
            color: var(--cream-100);
            letter-spacing: -0.5px;
            margin-bottom: 10px;
        }

        .featured-carousel {
            position: relative;
            margin-top: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .featured-track {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 24px;
            width: 100%;
            max-width: 1100px;
            position: relative;
            padding: 50px 0;
            overflow: visible;
        }

        .featured-card {
            flex: 0 0 280px;
            transition: all 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
            filter: blur(3px);
            opacity: 0.5;
            transform: scale(0.88) translateX(0);
            pointer-events: none;
            will-change: transform, opacity, filter;
        }

        .featured-card.active {
            flex: 0 0 380px;
            filter: blur(0);
            opacity: 1;
            transform: scale(1.12) translateX(0);
            z-index: 10;
            pointer-events: all;
            box-shadow: 0 35px 70px rgba(8, 18, 17, 0.6);
        }

        .featured-card.adjacent {
            filter: blur(2px);
            opacity: 0.65;
            transform: scale(0.92) translateX(0);
        }

        .featured-card.slide-left {
            transform: scale(0.88) translateX(-30px);
        }

        .featured-card.slide-right {
            transform: scale(0.88) translateX(30px);
        }

        .carousel-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(210, 180, 140, 0.18);
            border: 2px solid rgba(210, 180, 140, 0.5);
            color: var(--cream-100);
            font-size: 1.5rem;
            display: grid;
            place-items: center;
            cursor: pointer;
            z-index: 20;
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
        }

        .carousel-arrow:hover {
            background: rgba(210, 180, 140, 0.35);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-arrow:disabled,
        .carousel-arrow.disabled {
            opacity: 0.3;
            cursor: not-allowed;
            pointer-events: none;
        }

        .carousel-arrow:disabled:hover,
        .carousel-arrow.disabled:hover {
            background: rgba(210, 180, 140, 0.18);
            transform: translateY(-50%);
        }

        .carousel-arrow.prev { left: 20px; }
        .carousel-arrow.next { right: 20px; }

        .carousel-dots {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 24px;
        }

        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(210, 180, 140, 0.3);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .carousel-dot.active {
            background: var(--accent);
            transform: scale(1.3);
        }

        .featured-card {
            border-radius: 22px;
            overflow: hidden;
            border: 1px solid rgba(210, 180, 140, 0.35);
            background: rgba(15, 27, 27, 0.7);
            backdrop-filter: blur(10px);
        }

        .featured-card img {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }

        .featured-body {
            padding: 20px;
            display: grid;
            gap: 10px;
            color: var(--cream-100);
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.8rem;
            color: var(--cream-100);
            background: rgba(210, 180, 140, 0.2);
            padding: 4px 10px;
            border-radius: 999px;
        }

        .price {
            font-weight: 700;
            color: var(--cream-100);
        }

        .btn-tan {
            background: var(--accent);
            color: var(--green-900);
            border: none;
        }

        /* --- Testimonials --- */
        .testimonials-section {
            padding: 120px 0;
            background: linear-gradient(165deg, #0f1b1b 0%, #162825 50%, #1a3d35 100%);
            color: var(--cream-100);
            position: relative;
            overflow: hidden;
        }

        .testimonials-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 0%, rgba(210, 180, 140, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 100%, rgba(210, 180, 140, 0.08) 0%, transparent 45%);
            pointer-events: none;
        }

        .testimonials-section .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .testimonials-section h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 3rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--cream-100), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .testimonials-section .section-subtitle {
            color: rgba(245, 245, 220, 0.6);
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 32px;
        }

        .testimonial-card {
            padding: 36px;
            border-radius: 28px;
            background: linear-gradient(145deg, rgba(22, 40, 37, 0.95), rgba(15, 27, 27, 0.9));
            border: 1px solid rgba(210, 180, 140, 0.15);
            backdrop-filter: blur(24px);
            box-shadow:
                inset 0 1px 1px rgba(255, 255, 255, 0.05),
                inset 0 -1px 1px rgba(0, 0, 0, 0.1),
                0 20px 50px rgba(0, 0, 0, 0.3);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            transform: rotate(-1deg);
        }

        .testimonial-card:nth-child(2) {
            transform: rotate(1.5deg);
        }

        .testimonial-card:nth-child(3) {
            transform: rotate(-0.5deg);
        }

        .testimonial-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 0% 0%, rgba(26, 61, 53, 0.6) 0%, transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(22, 40, 37, 0.5) 0%, transparent 35%);
            pointer-events: none;
            border-radius: inherit;
        }

        .testimonial-card::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-12px) rotate(0deg) scale(1.02);
            border-color: rgba(210, 180, 140, 0.4);
            box-shadow:
                inset 0 1px 1px rgba(255, 255, 255, 0.1),
                inset 0 -1px 1px rgba(0, 0, 0, 0.1),
                0 35px 80px rgba(0, 0, 0, 0.45),
                0 0 60px rgba(210, 180, 140, 0.1);
        }

        .testimonial-card:hover::after {
            opacity: 1;
            animation: shimmer 2s ease infinite;
        }

        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        .avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(210, 180, 140, 0.6);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            transition: all 0.4s ease;
            filter: none;
        }

        .testimonial-card:hover .avatar {
            transform: scale(1.1);
            border-color: var(--accent);
            box-shadow: 0 10px 30px rgba(210, 180, 140, 0.35);
        }

        .testimonial-name {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .testimonial-name strong {
            font-size: 1.15rem;
            color: var(--cream-100);
        }

        .testimonial-name > div {
            color: rgba(245, 245, 220, 0.5);
            font-size: 0.9rem;
        }

        .testimonial-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, var(--accent), #c9a870);
            color: var(--green-900);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 5px 12px;
            border-radius: 999px;
            margin-top: 6px;
            box-shadow: 0 4px 12px rgba(210, 180, 140, 0.4);
            width: fit-content;
        }

        .testimonial-badge::before {
            content: "★";
            font-size: 0.75rem;
        }

        .quote {
            font-family: "Georgia", "Times New Roman", serif;
            color: rgba(245, 245, 220, 0.95);
            font-size: 1.1rem;
            line-height: 1.8;
            position: relative;
            z-index: 1;
            padding-left: 20px;
        }

        .quote::before {
            content: '"';
            font-size: 4rem;
            color: rgba(210, 180, 140, 0.2);
            position: absolute;
            top: -20px;
            left: -5px;
            font-family: Georgia, serif;
            line-height: 1;
        }

        .testimonial-rating {
            display: flex;
            gap: 4px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .testimonial-rating span {
            color: var(--accent);
            font-size: 1.1rem;
        }

        /* --- Cream Footer --- */
        .footer-wave {
            position: relative;
            width: 100%;
            line-height: 0;
            margin-top: -1px;
        }

        .footer-wave svg {
            width: 100%;
            height: 120px;
        }

        .cream-footer {
            background: linear-gradient(180deg, #efe7d8 0%, #f5f5dc 40%, #efe7d8 100%);
            color: var(--text-dark);
            padding: 80px 0 40px;
            position: relative;
            overflow: hidden;
        }

        .cream-footer::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                radial-gradient(circle at 10% 20%, rgba(210, 180, 140, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(210, 180, 140, 0.1) 0%, transparent 35%),
                url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%230f1b1b' fill-opacity='0.02'%3E%3Cpath d='M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z'/%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr repeat(2, 1fr) 1.5fr;
            gap: 40px;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 900px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .footer-grid {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .footer-brand .brand-badge {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--green-900), var(--green-800));
            color: var(--accent);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 1.3rem;
            box-shadow: 0 4px 15px rgba(15, 27, 27, 0.3);
        }

        .footer-brand span {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--green-900);
        }

        .footer-description {
            color: rgba(15, 27, 27, 0.7);
            font-size: 0.95rem;
            line-height: 1.7;
            max-width: 280px;
        }

        .footer-column h4 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--green-900);
            margin-bottom: 20px;
            font-weight: 700;
        }

        .footer-column a {
            display: block;
            color: rgba(15, 27, 27, 0.7);
            text-decoration: none;
            padding: 8px 0;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .footer-column a:hover {
            color: var(--green-900);
            padding-left: 8px;
        }

        .footer-column a::before {
            content: "";
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .footer-column a:hover::before {
            opacity: 1;
        }

        .footer-subscribe h4 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--green-900);
            margin-bottom: 12px;
            font-weight: 700;
        }

        .footer-subscribe p {
            color: rgba(15, 27, 27, 0.6);
            font-size: 0.9rem;
            margin-bottom: 16px;
        }

        .subscribe-form {
            display: flex;
            gap: 10px;
        }

        .subscribe-form input {
            flex: 1;
            padding: 14px 18px;
            border-radius: 999px;
            border: 1px solid rgba(15, 27, 27, 0.15);
            background: rgba(255, 255, 255, 0.7);
            color: var(--text-dark);
            font-size: 0.95rem;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .subscribe-form input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(210, 180, 140, 0.2);
        }

        .subscribe-form input::placeholder {
            color: rgba(15, 27, 27, 0.4);
        }

        .subscribe-btn {
            padding: 14px 28px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--green-900), var(--green-800));
            color: var(--cream-100);
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(15, 27, 27, 0.3);
        }

        .subscribe-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(15, 27, 27, 0.4);
        }

        .footer-bottom {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid rgba(15, 27, 27, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .footer-bottom p {
            color: rgba(15, 27, 27, 0.5);
            font-size: 0.9rem;
        }

        .footer-socials {
            display: flex;
            gap: 12px;
        }

        .footer-social {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(15, 27, 27, 0.08);
            border: 1px solid rgba(15, 27, 27, 0.1);
            display: grid;
            place-items: center;
            color: var(--green-900);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-social:hover {
            background: var(--green-900);
            color: var(--cream-100);
            transform: translateY(-3px);
        }

        .footer-social svg {
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        /* --- Footer --- */
        footer {
            background: var(--green-900);
            color: var(--cream-100);
            padding: 60px 0;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }

        .footer-grid a {
            color: var(--cream-200);
            text-decoration: none;
            display: block;
            margin: 6px 0;
        }

        .footer-input {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }

        .footer-input input {
            flex: 1;
            padding: 10px 12px;
            border-radius: 999px;
            border: 1px solid rgba(210, 180, 140, 0.35);
            background: rgba(255, 255, 255, 0.08);
            color: var(--cream-100);
        }

        /* --- Footer Wave & Cream Footer --- */
        .footer-wave {
            position: relative;
            height: 120px;
            background: linear-gradient(180deg, var(--green-900) 0%, var(--green-800) 100%);
            margin-top: -1px;
        }

        .footer-wave svg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .cream-footer {
            background: linear-gradient(180deg, #efe7d8 0%, #f5f0e6 100%);
            color: var(--green-900);
            padding: 80px 0 30px;
            position: relative;
        }

        .cream-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%230f1b1b' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .footer-grid {
            position: relative;
            z-index: 1;
        }

        .footer-about {
            max-width: 280px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: 18px;
        }

        .footer-brand .brand-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--tan) 0%, #b8996c 100%);
            display: grid;
            place-items: center;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--green-900);
        }

        .footer-description {
            color: #4a5d5d;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .footer-column h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: 18px;
            position: relative;
        }

        .footer-column h4::after {
            content: '';
            position: absolute;
            bottom: -6px;
            left: 0;
            width: 30px;
            height: 2px;
            background: var(--tan);
            border-radius: 2px;
        }

        .cream-footer .footer-column a {
            color: #3d5050;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 6px 0;
            display: inline-block;
        }

        .cream-footer .footer-column a:hover {
            color: var(--green-900);
            transform: translateX(5px);
        }

        .footer-subscribe {
            max-width: 300px;
        }

        .footer-subscribe h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: 12px;
        }

        .footer-subscribe p {
            color: #4a5d5d;
            font-size: 0.9rem;
            margin-bottom: 18px;
            line-height: 1.6;
        }

        .subscribe-form {
            display: flex;
            gap: 10px;
        }

        .subscribe-form input {
            flex: 1;
            padding: 14px 18px;
            border-radius: 999px;
            border: 2px solid rgba(15, 27, 27, 0.1);
            background: rgba(255, 255, 255, 0.7);
            color: var(--green-900);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .subscribe-form input:focus {
            outline: none;
            border-color: var(--tan);
            background: #fff;
        }

        .subscribe-form input::placeholder {
            color: #7a8a8a;
        }

        .subscribe-btn {
            padding: 14px 28px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--green-900) 0%, #1a3030 100%);
            border: none;
            color: var(--cream-100);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .subscribe-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(15, 27, 27, 0.3);
        }

        .footer-bottom {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 40px;
            margin-top: 50px;
            border-top: 1px solid rgba(15, 27, 27, 0.1);
        }

        .footer-bottom p {
            color: #5a6d6d;
            font-size: 0.9rem;
        }

        .footer-socials {
            display: flex;
            gap: 12px;
        }

        .cream-footer .footer-social {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(15, 27, 27, 0.05);
            border: 1px solid rgba(15, 27, 27, 0.1);
            display: grid;
            place-items: center;
            transition: all 0.3s ease;
        }

        .cream-footer .footer-social:hover {
            background: var(--green-900);
            border-color: var(--green-900);
            transform: translateY(-3px);
        }

        .cream-footer .footer-social svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: var(--green-900);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: stroke 0.3s ease;
        }

        .cream-footer .footer-social:hover svg {
            stroke: var(--cream-100);
        }

        /* Auth Modal Styles */
        .auth-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(12, 26, 27, 0.85);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 20px;
        }

        .auth-modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .auth-modal {
            width: 100%;
            max-width: 950px;
            height: 580px;
            display: flex;
            border-radius: 28px;
            overflow: hidden;
            position: relative;
            transform: translateY(30px) scale(0.95);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.5);
        }

        .auth-modal-overlay.active .auth-modal {
            transform: translateY(0) scale(1);
        }

        /* Panels Container */
        .auth-panels-container {
            position: absolute;
            inset: 0;
            display: flex;
        }

        /* Left Panel - Info/Branding */
        .auth-modal-info {
            width: 50%;
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 45px;
            background: 
                linear-gradient(135deg, rgba(12, 26, 27, 0.88) 0%, rgba(20, 49, 44, 0.8) 100%),
                url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80');
            background-size: cover;
            background-position: center;
            color: var(--cream-100);
            transition: all 0.7s cubic-bezier(0.68, -0.15, 0.32, 1.15);
            z-index: 10;
        }

        .auth-modal-info::before {
            content: '';
            position: absolute;
            inset: 0;
            backdrop-filter: blur(6px);
            z-index: 0;
        }

        .auth-modal-info::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 30% 70%, rgba(202, 164, 110, 0.15) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .auth-modal-info > * {
            position: relative;
            z-index: 1;
        }

        /* Signup mode - info panel slides to right */
        .auth-modal.signup-mode .auth-modal-info {
            left: 50%;
        }

        .auth-modal-info .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 0.5px;
            margin-bottom: 30px;
            opacity: 1;
            transform: translateX(0);
            transition: all 0.5s ease 0.2s;
        }

        .auth-modal-info .brand-badge {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, #caa46e, #e3c18a);
            color: var(--green-900);
            display: grid;
            place-items: center;
            font-weight: 900;
            font-size: 1.2rem;
            box-shadow: 0 6px 20px rgba(202, 164, 110, 0.3);
        }

        .auth-modal-info h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-style: italic;
            font-size: 2.1rem;
            font-weight: 600;
            line-height: 1.25;
            margin-bottom: 16px;
            opacity: 1;
            transform: translateX(0);
            transition: all 0.5s ease 0.25s;
        }

        .auth-modal-info .tagline {
            color: rgba(247, 242, 233, 0.8);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 30px;
            max-width: 320px;
            opacity: 1;
            transform: translateX(0);
            transition: all 0.5s ease 0.3s;
        }

        .auth-features-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            opacity: 1;
            transform: translateX(0);
            transition: all 0.5s ease 0.35s;
        }

        .auth-feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(247, 242, 233, 0.9);
            font-size: 0.9rem;
        }

        .auth-feature-item svg {
            width: 20px;
            height: 20px;
            stroke: #caa46e;
            fill: none;
            stroke-width: 2;
            flex-shrink: 0;
        }

        /* Info content variations */
        .auth-info-login,
        .auth-info-signup {
            position: absolute;
            inset: 0;
            padding: 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: all 0.5s ease;
            z-index: 1;
        }

        .auth-info-login {
            opacity: 1;
            transform: translateX(0);
        }

        .auth-info-signup {
            opacity: 0;
            transform: translateX(30px);
        }

        .auth-modal.signup-mode .auth-info-login {
            opacity: 0;
            transform: translateX(-30px);
        }

        .auth-modal.signup-mode .auth-info-signup {
            opacity: 1;
            transform: translateX(0);
        }

        /* Forms Container */
        .auth-forms-container {
            position: absolute;
            top: 0;
            width: 100%;
            height: 100%;
            display: flex;
        }

        /* Right Panel - Form */
        .auth-modal-form {
            width: 50%;
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            background: linear-gradient(180deg, #f7f2e9 0%, #efe7d8 100%);
            padding: 45px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: all 0.7s cubic-bezier(0.68, -0.15, 0.32, 1.15);
            overflow-y: auto;
        }

        /* Signup mode - form panel slides to left */
        .auth-modal.signup-mode .auth-modal-form {
            right: 50%;
        }

        .auth-modal-close {
            position: absolute;
            top: 18px;
            right: 18px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            background: rgba(15, 27, 27, 0.08);
            color: var(--green-900);
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: all 0.3s ease;
            z-index: 20;
        }

        .auth-modal-close:hover {
            background: var(--green-900);
            color: var(--cream-100);
            transform: rotate(90deg);
        }

        .auth-modal-close svg {
            width: 18px;
            height: 18px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
        }

        /* Form variations */
        .auth-form-login,
        .auth-form-signup {
            transition: all 0.5s ease;
        }

        .auth-form-login {
            opacity: 1;
            transform: translateX(0);
        }

        .auth-form-signup {
            position: absolute;
            inset: 0;
            padding: 70px 40px 45px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            opacity: 0;
            transform: translateX(-30px);
            pointer-events: none;
        }

        .auth-modal.signup-mode .auth-form-login {
            opacity: 0;
            transform: translateX(30px);
            pointer-events: none;
        }

        .auth-modal.signup-mode .auth-form-signup {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
        }

        .auth-modal-header {
            margin-bottom: 28px;
        }

        .auth-modal-header h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--green-900);
            margin-bottom: 6px;
        }

        .auth-modal-header p {
            color: #5a6d6d;
            font-size: 0.92rem;
        }

        .auth-form-group {
            margin-bottom: 18px;
        }

        .auth-form-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--green-900);
            margin-bottom: 7px;
        }

        .auth-form-group input {
            width: 100%;
            padding: 13px 16px;
            border-radius: 10px;
            border: 2px solid rgba(15, 27, 27, 0.1);
            background: #fff;
            color: var(--green-900);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .auth-form-group input:focus {
            outline: none;
            border-color: #caa46e;
            box-shadow: 0 0 0 4px rgba(202, 164, 110, 0.15);
        }

        .auth-form-group input::placeholder {
            color: #9aa5a5;
        }

        .auth-form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            font-size: 0.82rem;
        } 

        .auth-remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #5a6d6d;
            cursor: pointer;
        }

        .auth-remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #caa46e;
        }

        .auth-forgot {
            color: #caa46e;
            font-weight: 500;
            transition: color 0.3s ease;
            text-decoration: none;
        }

        .auth-forgot:hover {
            color: #b8956a;
        }

        .auth-btn {
            width: 100%;
            padding: 14px 22px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            border: none;
            cursor: pointer;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--green-900) 0%, #1a3030 100%);
            color: var(--cream-100);
        }

        .auth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(15, 27, 27, 0.3);
        }

        .auth-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .auth-btn:hover::before {
            left: 100%;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 20px 0;
            color: #9aa5a5;
            font-size: 0.82rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(15, 27, 27, 0.1);
        }

        .auth-social {
            display: flex;
            gap: 10px;
        }

        .auth-social-btn {
            flex: 1;
            padding: 11px;
            border-radius: 10px;
            border: 2px solid rgba(15, 27, 27, 0.1);
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--green-900);
            transition: all 0.3s ease;
        }

        .auth-social-btn:hover {
            border-color: #caa46e;
            background: rgba(202, 164, 110, 0.05);
            transform: translateY(-2px);
        }

        .auth-social-btn svg {
            width: 18px;
            height: 18px;
        }

        .auth-switch {
            text-align: center;
            margin-top: 22px;
            color: #5a6d6d;
            font-size: 0.88rem;
        }

        .auth-switch a {
            color: #caa46e;
            font-weight: 600;
            cursor: pointer;
            transition: color 0.3s ease;
            text-decoration: none;
        }

        .auth-switch a:hover {
            color: #b8956a;
        }

        @media (max-width: 800px) {
            .auth-modal {
                flex-direction: column;
                height: auto;
                max-height: 95vh;
            }

            .auth-modal-info,
            .auth-modal-form {
                position: relative;
                width: 100%;
                left: 0 !important;
                right: 0 !important;
            }

            .auth-modal-info {
                min-height: 200px;
                height: auto;
            }

            .auth-modal-form {
                height: auto;
            }

            .auth-info-login,
            .auth-info-signup,
            .auth-form-signup {
                position: relative;
                padding: 35px 30px;
            }

            .auth-modal-info h2 {
                font-size: 1.7rem;
            }

            .auth-features-list {
                display: none;
            }

            .auth-modal-form {
                padding: 35px 30px;
            }

            /* Mobile: show/hide instead of slide */
            .auth-info-signup,
            .auth-form-signup {
                display: none;
            }

            .auth-modal.signup-mode .auth-info-login,
            .auth-modal.signup-mode .auth-form-login {
                display: none;
            }

            .auth-modal.signup-mode .auth-info-signup,
            .auth-modal.signup-mode .auth-form-signup {
                display: flex;
                opacity: 1;
                transform: none;
                pointer-events: auto;
            }
        }

        @media (max-width: 500px) {
            .auth-modal-overlay {
                padding: 10px;
            }

            .auth-modal-info {
                padding: 28px 22px;
            }

            .auth-info-login,
            .auth-info-signup {
                padding: 28px 22px;
            }

            .auth-modal-form {
                padding: 28px 22px;
            }

            .auth-form-signup {
                padding: 28px 22px;
            }

            .auth-modal-header h2 {
                font-size: 1.5rem;
            }
        }
            

            .auth-modal-info {
                min-height: auto;
                padding: 35px 30px;
            }

            .auth-modal-info h2 {
                font-size: 1.7rem;
            }

            .auth-features-list {
                display: none;
            }

            .auth-modal-form {
                padding: 35px 30px;
            }
        

        @media (max-width: 500px) {
            .auth-modal-overlay {
                padding: 10px;
            }

            .auth-modal-info {
                padding: 28px 22px;
            }

            .auth-modal-form {
                padding: 28px 22px;
            }

            .auth-modal-header h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

    <header>
        <div class="container nav">
            <div class="brand"><div class="brand-badge">L</div> Landly</div>
            <nav class="nav-links">
                <a href="#features">Features</a>
                <a href="#listings">Listings</a>
                <a href="#contact">Contact</a>
            </nav>
            <div class="nav-actions">
                <a href="auth"> <button class="btn btn-outline" >Log in</button> </a>
            </div>
        </div>
    </header>

    <section class="hero-section">
        <div class="wave-container wave-top wave-green" aria-hidden="true">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path class="wave-layer" fill="#0c1a1b" fill-opacity="0.9" d="M0,48L80,53.3C160,59,320,69,480,69.3C640,69,800,59,960,53.3C1120,48,1280,48,1360,48L1440,48L1440,0L1360,0C1280,0,1120,0,960,0C800,0,640,0,480,0C320,0,160,0,80,0L0,0Z"></path>
                <path class="wave-layer wave-layer--move" fill="#14312c" fill-opacity="0.85" d="M0,32L60,37.3C120,43,240,53,360,53.3C480,53,600,43,720,37.3C840,32,960,32,1080,32C1200,32,1320,32,1380,32L1440,32L1440,0L1380,0C1320,0,1200,0,1080,0C960,0,840,0,720,0C600,0,480,0,360,0C240,0,120,0,60,0L0,0Z"></path>
            </svg>
        </div>
        <div class="container hero-grid">
            <div class="hero-content">
                <span class="pill">Exclusive land marketplace</span>
                <h1>Find and secure premium land in Nasugbu.</h1>
                <p style="margin-bottom: 30px">Explore verified lots in Nasugbu, Batangas and discover the right property for your next move.</p>
                <a href="#listings" class="btn btn-primary">Explore available lands</a>
            </div>
            <div class="hero-glass">
                <div class="hero-slideshow" aria-hidden="true">
                    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80');"></div>
                    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1472396961693-142e6e269027?auto=format&fit=crop&w=1200&q=80');"></div>
                    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1506318137071-a8e063b4bec0?auto=format&fit=crop&w=1200&q=80');"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Wavy Divider: Forest to Cream -->
    <div class="wave-container wave-cream">
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path class="wave-layer" fill="#efe7d8" fill-opacity="0.95" d="M0,80L80,72C160,64,320,48,480,48C640,48,800,64,960,72C1120,80,1280,80,1360,80L1440,80L1440,160L1360,160C1280,160,1120,160,960,160C800,160,640,160,480,160C320,160,160,160,80,160L0,160Z"></path>
            <path class="wave-layer wave-layer--move" fill="#efe7d8" fill-opacity="0.65" d="M0,96L100,88C200,80,400,64,600,64C800,64,1000,80,1200,88C1400,96,1600,96,1700,96L1800,96L1800,160L1700,160C1600,160,1400,160,1200,160C1000,160,800,160,600,160C400,160,200,160,100,160L0,160Z"></path>
        </svg>
    </div>

    <section class="map-section" id="listings">
        <div class="container">
            <h2>Find Your Perfect Land</h2>
            <p class="section-subtitle">Search by location and budget to discover available land opportunities</p>
            
            <!-- Search Controls -->
            <div class="search-controls reveal">
                <div class="search-row">
                    <div class="search-group">
                        <label>
                            <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Location
                        </label>
                        <div class="location-input-wrap">
                            <svg class="search-icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                            <input type="text" placeholder="Search city, province, or barangay..." id="locationSearch" />
                        </div>
                    </div>
                    <div class="search-group">
                        <label>
                            <svg viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            Price Range
                        </label>
                        <div class="price-range-wrap">
                            <div class="price-display">
                                <span class="price-badge" id="minPriceDisplay">₱500K</span>
                                <span class="price-separator">to</span>
                                <span class="price-badge" id="maxPriceDisplay">₱5M</span>
                            </div>
                            <div class="range-slider-container">
                                <div class="range-track" id="rangeTrack"></div>
                                <input type="range" min="0" max="100" value="10" id="minRange" />
                                <input type="range" min="0" max="100" value="70" id="maxRange" />
                            </div>
                        </div>
                    </div>
                    <button class="search-btn" type="button">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Search
                    </button>
                </div>
            </div>

            <!-- Map Container -->
            <div class="map-wrap reveal-scale">
                <div class="map-placeholder" aria-label="Interactive Map">
                    <div id="landlyListingsMap"></div>
                    <?php if ($listingMapPins === []): ?>
                        <div class="map-empty-note">No plotted coordinates available yet for Nasugbu listings.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recommended Results -->
            <div class="results-header">
                <h3>Available Lands <span id="resultsCountBadge" class="results-count"><?= esc((string) $availableResultCount) ?> Results</span></h3>
                <div class="results-sort">
                    <span>Sort by:</span>
                    <select>
                        <option>Relevance</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                        <option>Newest</option>
                        <option>Size</option>
                    </select>
                </div>
            </div>

            <div class="results-grid stagger-children">
                <?php if ($availableListings === []): ?>
                    <div class="result-card">
                        <div class="result-card-body">
                            <h4>No available lands right now.</h4>
                            <p>Please check back later for new listings.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ($availableListings as $listing): ?>
                    <?php
                    $title = trim((string) ($listing['title'] ?? 'Untitled Listing'));
                    $location = trim((string) ($listing['location_label'] ?? 'Location unavailable'));
                    $type = trim((string) ($listing['property_type_label'] ?? 'Land'));
                    $area = (float) ($listing['area_value'] ?? 0);
                    $price = (float) ($listing['price_value'] ?? 0);
                    $imageUrl = trim((string) ($listing['image_url'] ?? ''));
                    $searchText = strtolower($title . ' ' . $location . ' ' . $type);
                    ?>
                    <div class="result-card" data-listing-id="<?= (int) ($listing['listing_id'] ?? 0) ?>" data-price="<?= esc((string) $price) ?>" data-search="<?= esc($searchText) ?>">
                        <div class="result-card-image">
                            <img src="<?= esc($imageUrl) ?>" alt="<?= esc($title) ?>" />
                            <span class="result-badge verified">Available</span>
                            <button class="result-favorite" aria-label="Add to favorites">
                                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </button>
                        </div>
                        <div class="result-card-body">
                            <div class="result-location">
                                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <?= esc($location) ?>
                            </div>
                            <h4><?= esc($title) ?></h4>
                            <div class="result-specs">
                                <span class="result-spec">
                                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
                                    <?= $area > 0 ? esc(number_format($area, 0)) . ' sqm' : 'Area N/A' ?>
                                </span>
                                <span class="result-spec">
                                    <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                    <?= esc($type) ?>
                                </span>
                            </div>
                            <div class="result-footer">
                                <div class="result-price">₱<?= esc(number_format($price, 2)) ?> <span>/ lot</span></div>
                                <a class="result-view-btn" href="<?= base_url('auth') ?>">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="wave-container wave-green" style="margin-top: 0">
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path class="wave-layer" fill="#0f1b1b" fill-opacity="0.92" d="M0,72L80,64C160,56,320,40,480,40C640,40,800,56,960,64C1120,72,1280,72,1360,72L1440,72L1440,160L1360,160C1280,160,1120,160,960,160C800,160,640,160,480,160C320,160,160,160,80,160L0,160Z"></path>
            <path class="wave-layer wave-layer--move" fill="#0f1b1b" fill-opacity="0.55" d="M0,96L100,88C200,80,400,64,600,64C800,64,1000,80,1200,88C1400,96,1600,96,1700,96L1800,96L1800,160L1700,160C1600,160,1400,160,1200,160C1000,160,800,160,600,160C400,160,200,160,100,160L0,160Z"></path>
        </svg>
    </div>

    <section class="section" id="features">
        <div class="container">
            <div class="section-header">
                <h2>Designed for exclusive transactions</h2>
                <span class="feature-pill">Premium Experience</span>
            </div>
            <div class="features-grid stagger-children">
                <!-- Row 1 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3>Verified Ownership</h3>
                    <p>Every listing undergoes rigorous legal and document verification. We ensure the land is ready for a clean title transfer.</p>
                    <span class="feature-tag">Trusted & Secure</span>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    </div>
                    <h3>Advanced Mapping</h3>
                    <p>View high-resolution satellite imagery and topographic data. Walk the boundaries of your property from anywhere in the world.</p>
                    <span class="feature-tag">Precision View</span>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <h3>Zoning Intelligence</h3>
                    <p>Get instant clarity on land-use regulations and building codes. Know exactly what you can build before you make an offer.</p>
                    <span class="feature-tag">Data-Driven</span>
                </div>
                <!-- Row 2 -->
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <h3>Off-Market Access</h3>
                    <p>Gain exclusive entry to premium plots not listed on the open market. Secure unique opportunities before they go public.</p>
                    <span class="feature-tag">Member Only</span>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <h3>Concierge Support</h3>
                    <p>Dedicated land advisors guide you through negotiations and closing. Expert help for high-value land acquisitions.</p>
                    <span class="feature-tag">White-Glove Service</span>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/><path d="M12 8v4m0 0v.01"/></svg>
                    </div>
                    <h3>Secure Escrow</h3>
                    <p>Close deals with confidence using our integrated secure-pay system. Your funds are protected throughout the entire transaction.</p>
                    <span class="feature-tag">Financial Safety</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Layered Wave Divider to Footer -->
    <div class="footer-wave">
        <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path fill="#efe7d8" fill-opacity="0.3" d="M0,60L48,55C96,50,192,40,288,45C384,50,480,70,576,75C672,80,768,70,864,60C960,50,1056,40,1152,45C1248,50,1344,70,1392,80L1440,90L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            <path fill="#efe7d8" fill-opacity="0.6" d="M0,80L48,75C96,70,192,60,288,60C384,60,480,70,576,75C672,80,768,80,864,75C960,70,1056,60,1152,60C1248,60,1344,70,1392,75L1440,80L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
            <path fill="#efe7d8" d="M0,95L48,92C96,89,192,83,288,80C384,77,480,77,576,80C672,83,768,89,864,92C960,95,1056,95,1152,92C1248,89,1344,83,1392,80L1440,77L1440,120L1392,120C1344,120,1248,120,1152,120C1056,120,960,120,864,120C768,120,672,120,576,120C480,120,384,120,288,120C192,120,96,120,48,120L0,120Z"></path>
        </svg>
    </div>

    <footer class="cream-footer" id="contact">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="footer-brand">
                        <div class="brand-badge">L</div>
                        <span>Landly</span>
                    </div>
                    <p class="footer-description">The exclusive land marketplace connecting verified buyers with premium properties across the Philippines.</p>
                </div>
                <div class="footer-column">
                    <h4>Marketplace</h4>
                    <a href="#">Browse Listings</a>
                    <a href="#">Map Explorer</a>
                    <a href="#">Seller Verification</a>
                    <a href="#">Price Trends</a>
                </div>
                <div class="footer-column">
                    <h4>Company</h4>
                    <a href="#">About Us</a>
                    <a href="#">Careers</a>
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                </div>
                <div class="footer-subscribe">
                    <h4>Join the Private List</h4>
                    <p>Get exclusive access to premium listings before they go public.</p>
                    <form class="subscribe-form">
                        <input type="email" placeholder="Enter your email" />
                        <button type="submit" class="subscribe-btn">Join</button>
                    </form>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 Landly. All rights reserved.</p>
                <div class="footer-socials">
                    <a href="#" class="footer-social" aria-label="Facebook">
                        <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="Instagram">
                        <svg viewBox="0 0 24 24"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="Twitter">
                        <svg viewBox="0 0 24 24"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"/></svg>
                    </a>
                    <a href="#" class="footer-social" aria-label="LinkedIn">
                        <svg viewBox="0 0 24 24"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Auth Modal -->
    <div class="auth-modal-overlay" id="authModal">
        <div class="auth-modal" id="authModalContainer">
            <!-- Info Panel (slides between left and right) -->
            <div class="auth-modal-info">
                <!-- Login Info Content -->
                <div class="auth-info-login">
                    <div class="brand">
                        <div class="brand-badge">L</div>
                        <span>Landly</span>
                    </div>
                    <h2>Secure your place in the premium land marketplace.</h2>
                    <p class="tagline">Access exclusive land deals, verified ownership documents, and secure transactions all in one place.</p>
                    <div class="auth-features-list">
                        <div class="auth-feature-item">
                            <svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Verified ownership & secure escrow</span>
                        </div>
                        <div class="auth-feature-item">
                            <svg viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                            <span>Advanced satellite mapping tools</span>
                        </div>
                        <div class="auth-feature-item">
                            <svg viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Off-market premium listings</span>
                        </div>
                    </div>
                </div>
                <!-- Signup Info Content -->
                <div class="auth-info-signup">
                    <div class="brand">
                        <div class="brand-badge">L</div>
                        <span>Landly</span>
                    </div>
                    <h2>Join the exclusive land marketplace.</h2>
                    <p class="tagline">Create your account to access curated listings, secure document storage, and connect with verified buyers and sellers.</p>
                    <div class="auth-features-list">
                        <div class="auth-feature-item">
                            <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Join 10,000+ verified members</span>
                        </div>
                        <div class="auth-feature-item">
                            <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span>Bank-grade security & privacy</span>
                        </div>
                        <div class="auth-feature-item">
                            <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            <span>24/7 concierge support</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Panel (slides between right and left) -->
            <div class="auth-modal-form">
                <button class="auth-modal-close" id="closeAuthModal">
                    <svg viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>

                <!-- Login Form -->
                <div class="auth-form-login">
                    <div class="auth-modal-header">
                        <h2>Welcome back</h2>
                        <p>Enter your credentials to access your account</p>
                    </div>
                    <form>
                        <div class="auth-form-group">
                            <label for="login-email">Email address</label>
                            <input id="login-email" type="email" placeholder="you@example.com" />
                        </div>
                        <div class="auth-form-group">
                            <label for="login-password">Password</label>
                            <input id="login-password" type="password" placeholder="Enter your password" />
                        </div>
                        <div class="auth-form-options">
                            <label class="auth-remember">
                                <input type="checkbox" />
                                Remember me
                            </label>
                            <a href="#" class="auth-forgot">Forgot password?</a>
                        </div>
                        <button class="auth-btn" type="button">Sign In</button>
                    </form>
                    <div class="auth-divider">or continue with</div>
                    <div class="auth-social">
                        <button class="auth-social-btn" type="button">
                            <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                            Google
                        </button>
                        <button class="auth-social-btn" type="button">
                            <svg viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Facebook
                        </button>
                    </div>
                    <p class="auth-switch">Don't have an account? <a id="switchToSignup">Create one</a></p>
                </div>

                <!-- Signup Form -->
                <div class="auth-form-signup">
                    <div class="auth-modal-header">
                        <h2>Create account</h2>
                        <p>Join the premium land marketplace today</p>
                    </div>
                    <form>
                        <div class="auth-form-group">
                            <label for="signup-name">Full name</label>
                            <input id="signup-name" type="text" placeholder="Juan Dela Cruz" />
                        </div>
                        <div class="auth-form-group">
                            <label for="signup-email">Email address</label>
                            <input id="signup-email" type="email" placeholder="you@example.com" />
                        </div>
                        <div class="auth-form-group">
                            <label for="signup-password">Password</label>
                            <input id="signup-password" type="password" placeholder="Create a strong password" />
                        </div>
                        <button class="auth-btn" type="button">Create Account</button>
                    </form>
                    <div class="auth-divider">or sign up with</div>
                    <div class="auth-social">
                        <button class="auth-social-btn" type="button">
                            <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                            Google
                        </button>
                        <button class="auth-social-btn" type="button">
                            <svg viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Facebook
                        </button>
                    </div>
                    <p class="auth-switch">Already have an account? <a id="switchToLogin">Sign in</a></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const listingMapPins = <?= json_encode($listingMapPins, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        
        // Price Range Slider
        const minRange = document.getElementById('minRange');
        const maxRange = document.getElementById('maxRange');
        const rangeTrack = document.getElementById('rangeTrack');
        const minPriceDisplay = document.getElementById('minPriceDisplay');
        const maxPriceDisplay = document.getElementById('maxPriceDisplay');
        const locationSearchInput = document.getElementById('locationSearch');
        const searchButton = document.querySelector('.search-btn');
        const resultsCountBadge = document.getElementById('resultsCountBadge');
        const listingCards = Array.from(document.querySelectorAll('.results-grid .result-card[data-listing-id]'));

        let listingsMap = null;
        let listingsLayer = null;
        const mapDefaultCenter = [14.0664, 120.6325];
        let filterBaseMin = 100000;
        let filterBaseMax = 10000000;

        function formatPrice(value) {
            if (value >= 1000000) {
                return '₱' + (value / 1000000).toFixed(1) + 'M';
            } else {
                return '₱' + (value / 1000) + 'K';
            }
        }

        function getPriceFromPercent(percent) {
            return filterBaseMin + (percent / 100) * (filterBaseMax - filterBaseMin);
        }

        function getSelectedPriceRange() {
            const rawMin = getPriceFromPercent(parseInt(minRange.value, 10) || 0);
            const rawMax = getPriceFromPercent(parseInt(maxRange.value, 10) || 100);
            return [Math.min(rawMin, rawMax), Math.max(rawMin, rawMax)];
        }

        function updateRangeSlider() {
            const minVal = parseInt(minRange.value, 10) || 0;
            const maxVal = parseInt(maxRange.value, 10) || 100;

            // Prevent overlap
            if (minVal >= maxVal - 5) {
                if (this === minRange) {
                    minRange.value = maxVal - 5;
                } else {
                    maxRange.value = minVal + 5;
                }
            }

            const minPercent = parseInt(minRange.value, 10) || 0;
            const maxPercent = parseInt(maxRange.value, 10) || 100;

            // Update track position
            rangeTrack.style.left = minPercent + '%';
            rangeTrack.style.right = (100 - maxPercent) + '%';

            // Calculate actual price values
            const [actualMinPrice, actualMaxPrice] = getSelectedPriceRange();

            // Update price displays
            minPriceDisplay.textContent = formatPrice(Math.round(actualMinPrice));
            maxPriceDisplay.textContent = formatPrice(Math.round(actualMaxPrice));
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderMapPins(pins) {
            if (!listingsMap || !listingsLayer) {
                return;
            }

            listingsLayer.clearLayers();
            const bounds = [];

            (Array.isArray(pins) ? pins : []).forEach((pin) => {
                const lat = Number(pin.lat);
                const lng = Number(pin.lng);
                if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                    return;
                }

                bounds.push([lat, lng]);
                const marker = window.L.marker([lat, lng]);
                marker.bindPopup(
                    `<strong>${escapeHtml(pin.title || 'Land Listing')}</strong><br>` +
                    `${escapeHtml(pin.location || 'Nasugbu, Batangas')}<br>` +
                    `Price: ₱${Number(pin.price || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                );
                marker.addTo(listingsLayer);
            });

            if (bounds.length > 0) {
                listingsMap.fitBounds(bounds, { padding: [28, 28], maxZoom: 14 });
            } else {
                listingsMap.setView(mapDefaultCenter, 12);
            }
        }

        function applyListingFilters() {
            if (listingCards.length === 0) {
                return;
            }

            const keyword = String(locationSearchInput?.value || '').trim().toLowerCase();
            const [selectedMinPrice, selectedMaxPrice] = getSelectedPriceRange();
            const visibleListingIds = new Set();
            let visibleCount = 0;

            listingCards.forEach((card) => {
                const listingId = Number(card.dataset.listingId || 0);
                const searchableText = String(card.dataset.search || '').toLowerCase();
                const price = Number(card.dataset.price || 0);

                const matchesKeyword = keyword === '' || searchableText.includes(keyword);
                const matchesPrice = Number.isFinite(price) && price >= selectedMinPrice && price <= selectedMaxPrice;
                const isVisible = matchesKeyword && matchesPrice;

                card.style.display = isVisible ? '' : 'none';

                if (isVisible) {
                    visibleCount += 1;
                    if (listingId > 0) {
                        visibleListingIds.add(listingId);
                    }
                }
            });

            if (resultsCountBadge) {
                resultsCountBadge.textContent = `${visibleCount} Results`;
            }

            const filteredPins = listingMapPins.filter((pin) => visibleListingIds.has(Number(pin.id || 0)));
            renderMapPins(filteredPins);
        }

        minRange.addEventListener('input', updateRangeSlider);
        maxRange.addEventListener('input', updateRangeSlider);

        minRange.addEventListener('input', () => {
            applyListingFilters();
        });

        maxRange.addEventListener('input', () => {
            applyListingFilters();
        });

        // Nasugbu listings map with pins from available listings only
        (function initListingsMap() {
            const mapNode = document.getElementById('landlyListingsMap');
            if (!mapNode || !window.L) {
                return;
            }

            listingsMap = window.L.map(mapNode, {
                zoomControl: true,
                scrollWheelZoom: false,
            }).setView(mapDefaultCenter, 12);

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(listingsMap);

            listingsLayer = window.L.layerGroup().addTo(listingsMap);
            renderMapPins(listingMapPins);
        })();

        // Initialize dynamic price range and wire search actions
        (function initListingSearch() {
            const prices = listingCards
                .map((card) => Number(card.dataset.price || 0))
                .filter((price) => Number.isFinite(price) && price > 0);

            if (prices.length > 0) {
                const minDatasetPrice = Math.min(...prices);
                const maxDatasetPrice = Math.max(...prices);
                filterBaseMin = Math.max(0, Math.floor(minDatasetPrice * 0.8));
                filterBaseMax = Math.ceil(maxDatasetPrice * 1.2);
                if (filterBaseMax <= filterBaseMin) {
                    filterBaseMax = filterBaseMin + 100000;
                }
            }

            minRange.value = '0';
            maxRange.value = '100';
            updateRangeSlider();

            if (searchButton) {
                searchButton.addEventListener('click', applyListingFilters);
            }

            if (locationSearchInput) {
                locationSearchInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        applyListingFilters();
                    }
                });
            }

            applyListingFilters();
        })();

        // Scroll Reveal Animation
        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale, .stagger-children');
        
        const revealOnScroll = () => {
            const windowHeight = window.innerHeight;
            const revealPoint = 120;

            revealElements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                
                if (elementTop < windowHeight - revealPoint) {
                    element.classList.add('active');
                }
            });
        };

        window.addEventListener('scroll', revealOnScroll);
        revealOnScroll(); // Initial check

        // Smooth scroll for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Parallax effect on scroll
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const scrolled = window.pageYOffset;
                    const heroGlass = document.querySelector('.hero-glass');
                    if (heroGlass && scrolled < 600) {
                        heroGlass.style.transform = `translateY(${scrolled * 0.1}px)`;
                    }
                    ticking = false;
                });
                ticking = true;
            }
        });

        // Add hover ripple effect to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.style.cssText = `
                    position: absolute;
                    background: rgba(255,255,255,0.3);
                    border-radius: 50%;
                    width: 0;
                    height: 0;
                    left: ${x}px;
                    top: ${y}px;
                    transform: translate(-50%, -50%);
                    animation: ripple 0.6s ease-out forwards;
                `;
                this.appendChild(ripple);
                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add CSS for ripple animation dynamically
        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            @keyframes ripple {
                to {
                    width: 200px;
                    height: 200px;
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(rippleStyle);

        // Magnetic effect on cards
        document.querySelectorAll('.feature-card, .testimonial-card, .result-card').forEach(card => {
            card.addEventListener('mousemove', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;
                
                this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });

        // Counter animation for stats (if any)
        const animateValue = (element, start, end, duration) => {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                element.innerHTML = Math.floor(progress * (end - start) + start);
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        };

        // Typing effect for hero subtitle (optional enhancement)
        const heroSubtitle = document.querySelector('.hero-content p');
        if (heroSubtitle) {
            heroSubtitle.style.opacity = '0';
            heroSubtitle.style.animation = 'fadeInUp 1s ease-out 0.6s forwards';
        }

        // Add loading animation class removal
        document.body.classList.add('loaded');

        // Auth Modal Functionality
        const authModal = document.getElementById('authModal');
        const authModalContainer = document.getElementById('authModalContainer');
        const openLoginBtn = document.getElementById('openLoginModal');
        const openSignupBtn = document.getElementById('openSignupModal');
        const closeAuthBtn = document.getElementById('closeAuthModal');
        const switchToSignupBtn = document.getElementById('switchToSignup');
        const switchToLoginBtn = document.getElementById('switchToLogin');

        // Open modal in login mode
        if (openLoginBtn && authModal && authModalContainer) {
            openLoginBtn.addEventListener('click', () => {
                authModalContainer.classList.remove('signup-mode');
                authModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        // Open modal in signup mode
        if (openSignupBtn && authModal && authModalContainer) {
            openSignupBtn.addEventListener('click', () => {
                authModalContainer.classList.add('signup-mode');
                authModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        // Close modal
        const closeModal = () => {
            authModal.classList.remove('active');
            document.body.style.overflow = '';
        };

        if (closeAuthBtn) {
            closeAuthBtn.addEventListener('click', closeModal);
        }

        // Close on overlay click
        if (authModal) {
            authModal.addEventListener('click', (e) => {
                if (e.target === authModal) {
                    closeModal();
                }
            });
        }

        // Switch to signup mode (panels swap with animation)
        if (switchToSignupBtn && authModalContainer) {
            switchToSignupBtn.addEventListener('click', (e) => {
                e.preventDefault();
                authModalContainer.classList.add('signup-mode');
            });
        }

        // Switch to login mode (panels swap back)
        if (switchToLoginBtn && authModalContainer) {
            switchToLoginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                authModalContainer.classList.remove('signup-mode');
            });
        }

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>