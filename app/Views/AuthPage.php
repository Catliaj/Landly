<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Landly | Access</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600;1,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style>
		:root {
			--green-900: #0c1a1b;
			--green-800: #14312c;
			--green-700: #1e4b43;
			--cream-100: #f7f2e9;
			--cream-200: #efe7d8;
			--white: #ffffff;
			--accent: #caa46e;
			--accent-soft: #8bbfae;
			--shadow: rgba(5, 18, 18, 0.3);
			--glow: rgba(202, 164, 110, 0.28);
		}

		* {
			box-sizing: border-box;
			margin: 0;
			padding: 0;
			font-family: "Inter", "Segoe UI", system-ui, -apple-system, sans-serif;
		}

		body {
			background: var(--green-900);
			color: var(--cream-100);
			min-height: 100vh;
			display: grid;
			place-items: center;
			overflow: hidden;
		}

		a {
			color: inherit;
			text-decoration: none;
		}

		/* Main Auth Container */
		.auth-container {
			width: 100%;
			max-width: 1100px;
			height: 650px;
			display: flex;
			border-radius: 32px;
			overflow: hidden;
			box-shadow: 0 40px 80px rgba(0, 0, 0, 0.4);
			position: relative;
			margin: 20px;
		}

		/* Left Panel - Branding/Info */
		.info-panel {
			flex: 1;
			position: relative;
			display: flex;
			flex-direction: column;
			justify-content: center;
			padding: 50px;
			background: 
				linear-gradient(135deg, rgba(12, 26, 27, 0.85) 0%, rgba(20, 49, 44, 0.75) 100%),
				url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80');
			background-size: cover;
			background-position: center;
			overflow: hidden;
			z-index: 2;
		}

		.info-panel::before {
			content: '';
			position: absolute;
			inset: 0;
			backdrop-filter: blur(8px);
			z-index: -1;
		}

		.info-panel::after {
			content: '';
			position: absolute;
			inset: 0;
			background: radial-gradient(circle at 30% 70%, rgba(202, 164, 110, 0.15) 0%, transparent 50%);
			pointer-events: none;
		}

		.brand {
			display: flex;
			align-items: center;
			gap: 14px;
			font-weight: 700;
			font-size: 1.4rem;
			letter-spacing: 1px;
			margin-bottom: 30px;
			position: relative;
			z-index: 1;
		}

		.brand-badge {
			width: 50px;
			height: 50px;
			border-radius: 14px;
			background: linear-gradient(135deg, var(--accent), #e3c18a);
			color: var(--green-900);
			display: grid;
			place-items: center;
			font-weight: 900;
			font-size: 1.4rem;
			box-shadow: 0 8px 25px rgba(202, 164, 110, 0.3);
		}

		.info-panel h1 {
			font-family: 'Playfair Display', Georgia, serif;
			font-style: italic;
			font-size: 2.8rem;
			font-weight: 600;
			line-height: 1.2;
			margin-bottom: 20px;
			position: relative;
			z-index: 1;
		}

		.info-panel .tagline {
			color: rgba(247, 242, 233, 0.8);
			font-size: 1.05rem;
			line-height: 1.7;
			margin-bottom: 35px;
			max-width: 400px;
			position: relative;
			z-index: 1;
		}

		.features-list {
			display: flex;
			flex-direction: column;
			gap: 16px;
			position: relative;
			z-index: 1;
		}

		.feature-item {
			display: flex;
			align-items: center;
			gap: 14px;
			color: rgba(247, 242, 233, 0.9);
			font-size: 0.95rem;
		}

		.feature-item svg {
			width: 22px;
			height: 22px;
			stroke: var(--accent);
			fill: none;
			stroke-width: 2;
			flex-shrink: 0;
		}

		.back-link {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			margin-top: 40px;
			padding: 12px 24px;
			border-radius: 999px;
			border: 1px solid rgba(255, 255, 255, 0.2);
			color: var(--cream-100);
			font-size: 0.9rem;
			font-weight: 500;
			transition: all 0.3s ease;
			position: relative;
			z-index: 1;
		}

		.back-link:hover {
			background: rgba(255, 255, 255, 0.1);
			border-color: rgba(255, 255, 255, 0.3);
			transform: translateX(-5px);
		}

		.back-link svg {
			width: 18px;
			height: 18px;
			stroke: currentColor;
			fill: none;
			stroke-width: 2;
		}

		/* Right Panel - Forms */
		.form-panel {
			flex: 1;
			background: linear-gradient(180deg, #f7f2e9 0%, #efe7d8 100%);
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
			overflow: hidden;
		}

		/* Sliding Overlay */
		.sliding-overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: 
				linear-gradient(135deg, rgba(12, 26, 27, 0.9) 0%, rgba(20, 49, 44, 0.85) 100%),
				url('https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80');
			background-size: cover;
			background-position: center;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			padding: 50px;
			text-align: center;
			transform: translateX(100%);
			transition: transform 0.7s cubic-bezier(0.68, -0.15, 0.32, 1.15);
			z-index: 10;
		}

		.sliding-overlay::before {
			content: '';
			position: absolute;
			inset: 0;
			backdrop-filter: blur(10px);
			z-index: -1;
		}

		.auth-container.signup-mode .sliding-overlay {
			transform: translateX(0);
		}

		.sliding-overlay h2 {
			font-family: 'Playfair Display', Georgia, serif;
			font-style: italic;
			font-size: 2.2rem;
			font-weight: 600;
			margin-bottom: 16px;
			color: var(--cream-100);
		}

		.sliding-overlay p {
			color: rgba(247, 242, 233, 0.8);
			font-size: 1rem;
			margin-bottom: 30px;
			max-width: 320px;
		}

		.overlay-btn {
			padding: 14px 32px;
			border-radius: 999px;
			border: 2px solid var(--accent);
			background: transparent;
			color: var(--cream-100);
			font-weight: 600;
			font-size: 0.95rem;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.overlay-btn:hover {
			background: var(--accent);
			color: var(--green-900);
			transform: scale(1.05);
		}

		/* Form Container */
		.form-container {
			width: 100%;
			max-width: 380px;
			padding: 40px;
			position: relative;
		}

		.form-wrapper {
			position: relative;
			width: 100%;
		}

		/* Login Form */
		.login-form,
		.signup-form {
			width: 100%;
			transition: all 0.5s cubic-bezier(0.68, -0.15, 0.32, 1.15);
		}

		.login-form {
			opacity: 1;
			transform: translateX(0);
		}

		.signup-form {
			position: absolute;
			top: 0;
			left: 0;
			opacity: 0;
			transform: translateX(50px);
			pointer-events: none;
		}

		.auth-container.signup-mode .login-form {
			opacity: 0;
			transform: translateX(-50px);
			pointer-events: none;
		}

		.auth-container.signup-mode .signup-form {
			opacity: 1;
			transform: translateX(0);
			pointer-events: auto;
			position: relative;
		}

		.form-header {
			margin-bottom: 30px;
		}

		.form-header h2 {
			font-family: 'Playfair Display', Georgia, serif;
			font-size: 2rem;
			font-weight: 700;
			color: var(--green-900);
			margin-bottom: 8px;
		}

		.form-header p {
			color: #5a6d6d;
			font-size: 0.95rem;
		}

		.form-group {
			margin-bottom: 20px;
		}

		.form-group label {
			display: block;
			font-size: 0.85rem;
			font-weight: 600;
			color: var(--green-900);
			margin-bottom: 8px;
		}

		.form-group input {
			width: 100%;
			padding: 14px 18px;
			border-radius: 12px;
			border: 2px solid rgba(15, 27, 27, 0.1);
			background: #fff;
			color: var(--green-900);
			font-size: 1rem;
			transition: all 0.3s ease;
		}

		.form-group input:focus {
			outline: none;
			border-color: var(--accent);
			box-shadow: 0 0 0 4px rgba(202, 164, 110, 0.15);
		}

		.form-group input::placeholder {
			color: #9aa5a5;
		}

		.form-options {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 25px;
			font-size: 0.85rem;
		}

		.remember-me {
			display: flex;
			align-items: center;
			gap: 8px;
			color: #5a6d6d;
			cursor: pointer;
		}

		.remember-me input[type="checkbox"] {
			width: 18px;
			height: 18px;
			accent-color: var(--accent);
		}

		.forgot-link {
			color: var(--accent);
			font-weight: 500;
			transition: color 0.3s ease;
		}

		.forgot-link:hover {
			color: #b8956a;
		}

		.btn {
			width: 100%;
			padding: 16px 24px;
			border-radius: 12px;
			font-weight: 600;
			font-size: 1rem;
			border: none;
			cursor: pointer;
			transition: all 0.4s ease;
			position: relative;
			overflow: hidden;
		}

		.btn-primary {
			background: linear-gradient(135deg, var(--green-900) 0%, #1a3030 100%);
			color: var(--cream-100);
		}

		.btn-primary:hover {
			transform: translateY(-3px);
			box-shadow: 0 12px 30px rgba(15, 27, 27, 0.3);
		}

		.btn-primary::before {
			content: '';
			position: absolute;
			top: 0;
			left: -100%;
			width: 100%;
			height: 100%;
			background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
			transition: left 0.5s ease;
		}

		.btn-primary:hover::before {
			left: 100%;
		}

		.divider {
			display: flex;
			align-items: center;
			gap: 16px;
			margin: 25px 0;
			color: #9aa5a5;
			font-size: 0.85rem;
		}

		.divider::before,
		.divider::after {
			content: '';
			flex: 1;
			height: 1px;
			background: rgba(15, 27, 27, 0.1);
		}

		.social-login {
			display: flex;
			gap: 12px;
		}

		.social-btn {
			flex: 1;
			padding: 12px;
			border-radius: 12px;
			border: 2px solid rgba(15, 27, 27, 0.1);
			background: #fff;
			cursor: pointer;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			font-size: 0.9rem;
			font-weight: 500;
			color: var(--green-900);
			transition: all 0.3s ease;
		}

		.social-btn:hover {
			border-color: var(--accent);
			background: rgba(202, 164, 110, 0.05);
			transform: translateY(-2px);
		}

		.social-btn svg {
			width: 20px;
			height: 20px;
		}

		.switch-text {
			text-align: center;
			margin-top: 25px;
			color: #5a6d6d;
			font-size: 0.9rem;
		}

		.switch-text a {
			color: var(--accent);
			font-weight: 600;
			cursor: pointer;
			transition: color 0.3s ease;
		}

		.switch-text a:hover {
			color: #b8956a;
		}

		/* Role Toggle */
		.role-toggle {
			display: flex;
			gap: 10px;
			margin-bottom: 25px;
			background: rgba(15, 27, 27, 0.05);
			padding: 6px;
			border-radius: 14px;
		}

		.role-btn {
			flex: 1;
			padding: 12px 20px;
			border-radius: 10px;
			border: none;
			background: transparent;
			color: #5a6d6d;
			font-size: 0.9rem;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 8px;
		}

		.role-btn svg {
			width: 18px;
			height: 18px;
			stroke: currentColor;
			fill: none;
			stroke-width: 2;
		}

		.role-btn.active {
			background: linear-gradient(135deg, var(--green-900) 0%, #1a3030 100%);
			color: var(--cream-100);
			box-shadow: 0 4px 15px rgba(15, 27, 27, 0.2);
		}

		.role-btn:hover:not(.active) {
			background: rgba(15, 27, 27, 0.08);
		}

		/* Form Row for side-by-side fields */
		.form-row {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 12px;
		}

		/* File Upload */
		.file-upload {
			position: relative;
			border: 2px dashed rgba(15, 27, 27, 0.2);
			border-radius: 12px;
			padding: 20px;
			text-align: center;
			cursor: pointer;
			transition: all 0.3s ease;
			background: rgba(255, 255, 255, 0.5);
		}

		.file-upload:hover {
			border-color: var(--accent);
			background: rgba(202, 164, 110, 0.05);
		}

		.file-upload input {
			position: absolute;
			inset: 0;
			opacity: 0;
			cursor: pointer;
		}

		.file-upload-icon {
			width: 40px;
			height: 40px;
			margin: 0 auto 10px;
			background: rgba(15, 27, 27, 0.08);
			border-radius: 10px;
			display: grid;
			place-items: center;
		}

		.file-upload-icon svg {
			width: 20px;
			height: 20px;
			stroke: var(--accent);
			fill: none;
			stroke-width: 2;
		}

		.file-upload p {
			font-size: 0.85rem;
			color: #5a6d6d;
			margin: 0;
		}

		.file-upload p span {
			color: var(--accent);
			font-weight: 600;
		}

		.file-upload-hint {
			font-size: 0.75rem;
			color: #9aa5a5;
			margin-top: 6px;
		}

		/* Profile Image Upload */
		.profile-upload {
			display: flex;
			align-items: center;
			gap: 15px;
		}

		.profile-preview {
			width: 70px;
			height: 70px;
			border-radius: 50%;
			background: linear-gradient(135deg, rgba(15, 27, 27, 0.1), rgba(15, 27, 27, 0.05));
			display: grid;
			place-items: center;
			overflow: hidden;
			border: 3px solid rgba(15, 27, 27, 0.1);
			flex-shrink: 0;
		}

		.profile-preview svg {
			width: 28px;
			height: 28px;
			stroke: #9aa5a5;
			fill: none;
			stroke-width: 1.5;
		}

		.profile-preview img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.profile-upload-btn {
			flex: 1;
		}

		.profile-upload-btn label {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 10px 18px;
			background: rgba(15, 27, 27, 0.08);
			border-radius: 10px;
			font-size: 0.85rem;
			font-weight: 500;
			color: var(--green-900);
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.profile-upload-btn label:hover {
			background: rgba(202, 164, 110, 0.15);
		}

		.profile-upload-btn label svg {
			width: 16px;
			height: 16px;
			stroke: currentColor;
			fill: none;
			stroke-width: 2;
		}

		.profile-upload-btn input {
			display: none;
		}

		.profile-upload-btn span {
			display: block;
			font-size: 0.75rem;
			color: #9aa5a5;
			margin-top: 6px;
		}

		/* Verification Section */
		.verification-section {
			margin-top: 5px;
			padding-top: 20px;
			border-top: 1px solid rgba(15, 27, 27, 0.1);
		}

		.verification-header {
			display: flex;
			align-items: center;
			gap: 10px;
			margin-bottom: 15px;
		}

		.verification-header svg {
			width: 20px;
			height: 20px;
			stroke: var(--accent);
			fill: none;
			stroke-width: 2;
		}

		.verification-header h4 {
			font-size: 0.95rem;
			font-weight: 600;
			color: var(--green-900);
			margin: 0;
		}

		.verification-header span {
			font-size: 0.75rem;
			color: #9aa5a5;
			margin-left: auto;
		}

		.upload-item {
			margin-bottom: 15px;
		}

		.upload-item:last-child {
			margin-bottom: 0;
		}

		.upload-label {
			display: flex;
			align-items: center;
			gap: 6px;
			font-size: 0.85rem;
			font-weight: 600;
			color: var(--green-900);
			margin-bottom: 8px;
		}

		.upload-label .optional {
			font-size: 0.75rem;
			color: #9aa5a5;
			font-weight: 400;
		}

		.upload-label .required {
			color: #e74c3c;
		}

		/* Conditional Fields */
		.buyer-fields,
		.seller-fields {
			display: none;
		}

		.buyer-fields.active,
		.seller-fields.active {
			display: block;
		}

		/* Scrollable form for signup */
		.signup-form-inner {
			max-height: 480px;
			overflow-y: auto;
			padding-right: 5px;
			margin-right: -5px;
		}

		.signup-form-inner::-webkit-scrollbar {
			width: 4px;
		}

		.signup-form-inner::-webkit-scrollbar-track {
			background: rgba(15, 27, 27, 0.05);
			border-radius: 4px;
		}

		.signup-form-inner::-webkit-scrollbar-thumb {
			background: rgba(15, 27, 27, 0.2);
			border-radius: 4px;
		}

		.signup-form-inner::-webkit-scrollbar-thumb:hover {
			background: rgba(15, 27, 27, 0.3);
		}

		/* Mobile Left Overlay (for when in signup mode) */
		.mobile-overlay {
			position: absolute;
			top: 0;
			right: 0;
			width: 100%;
			height: 100%;
			background: 
				linear-gradient(135deg, rgba(12, 26, 27, 0.9) 0%, rgba(20, 49, 44, 0.85) 100%),
				url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80');
			background-size: cover;
			background-position: center;
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
			padding: 50px;
			text-align: center;
			transform: translateX(-100%);
			transition: transform 0.7s cubic-bezier(0.68, -0.15, 0.32, 1.15);
			z-index: 10;
		}

		.mobile-overlay::before {
			content: '';
			position: absolute;
			inset: 0;
			backdrop-filter: blur(10px);
			z-index: -1;
		}

		.info-panel .mobile-overlay {
			display: none;
		}

		.auth-container.signup-mode .info-panel .mobile-overlay {
			display: flex;
			transform: translateX(0);
		}

		/* Responsive */
		@media (max-width: 900px) {
			.auth-container {
				flex-direction: column;
				height: auto;
				max-width: 500px;
			}

			.info-panel {
				padding: 40px 30px;
				min-height: 300px;
			}

			.info-panel h1 {
				font-size: 2rem;
			}

			.features-list {
				display: none;
			}

			.form-panel {
				padding: 20px;
			}

			.sliding-overlay,
			.mobile-overlay {
				display: none !important;
			}

			.login-form,
			.signup-form {
				position: relative !important;
				opacity: 1 !important;
				transform: none !important;
				pointer-events: auto !important;
			}

			.signup-form {
				display: none;
			}

			.auth-container.signup-mode .login-form {
				display: none;
			}

			.auth-container.signup-mode .signup-form {
				display: block;
			}
		}
	</style>
</head>
<body>
	<div class="auth-container" id="authContainer">
		<!-- Left Panel - Info -->
		<div class="info-panel">
			<div class="brand">
				<div class="brand-badge">L</div>
				<span>Landly</span>
			</div>
			<h1>Secure your place in the premium land marketplace.</h1>
			<p class="tagline">Join verified buyers and sellers to access exclusive land deals, advanced mapping tools, and secure transactions.</p>
			
			<div class="features-list">
				<div class="feature-item">
					<svg viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
					<span>Verified ownership & secure escrow</span>
				</div>
				<div class="feature-item">
					<svg viewBox="0 0 24 24"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
					<span>Advanced satellite mapping & zoning data</span>
				</div>
				<div class="feature-item">
					<svg viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
					<span>Off-market premium listings access</span>
				</div>
			</div>

			<a class="back-link" href="<?= base_url('/') ?>">
				<svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
				Back to home
			</a>
		</div>

		<!-- Right Panel - Forms -->
		<div class="form-panel">
			<!-- Sliding Overlay (shows when switching to signup) -->
			<div class="sliding-overlay">
				<h2>Already have an account?</h2>
				<p>Sign in to access your portfolio, saved listings, and secure document vault.</p>
				<button class="overlay-btn" id="switchToLogin">Sign In</button>
			</div>

			<div class="form-container">
				<div class="form-wrapper">
					<!-- Login Form -->
					<div class="login-form">
						<div class="form-header">
							<h2>Welcome back</h2>
							<p>Enter your credentials to access your account</p>
						</div>
						<form>
							<div class="form-group">
								<label for="login-email">Email address</label>
								<input id="login-email" type="email" placeholder="you@example.com" />
							</div>
							<div class="form-group">
								<label for="login-password">Password</label>
								<input id="login-password" type="password" placeholder="Enter your password" />
							</div>
							<div class="form-options">
								<label class="remember-me">
									<input type="checkbox" />
									Remember me
								</label>
								<a href="#" class="forgot-link">Forgot password?</a>
							</div>
							<button class="btn btn-primary" type="button">Sign In</button>
						</form>
						<div class="divider">or continue with</div>
						<div class="social-login">
							<button class="social-btn" type="button">
								<svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
								Google
							</button>
							<button class="social-btn" type="button">
								<svg viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
								Facebook
							</button>
						</div>
						<p class="switch-text">Don't have an account? <a id="showSignup">Create one</a></p>
					</div>

					<!-- Signup Form -->
					<div class="signup-form">
						<div class="form-header">
							<h2>Create account</h2>
							<p>Join the premium land marketplace today</p>
						</div>

						<!-- Role Toggle -->
						<div class="role-toggle">
							<button type="button" class="role-btn active" data-role="buyer" id="buyerRoleBtn">
								<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
								Buyer
							</button>
							<button type="button" class="role-btn" data-role="seller" id="sellerRoleBtn">
								<svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
								Seller
							</button>
						</div>

						<div class="signup-form-inner">
						<form>
							<input type="hidden" name="role" id="selectedRole" value="buyer" />

							<!-- Buyer Fields -->
							<div class="buyer-fields active" id="buyerFields">
								<div class="form-row">
									<div class="form-group">
										<label for="buyer-firstname">First Name <span style="color:#e74c3c">*</span></label>
										<input id="buyer-firstname" type="text" placeholder="Juan" required />
									</div>
									<div class="form-group">
										<label for="buyer-lastname">Last Name <span style="color:#e74c3c">*</span></label>
										<input id="buyer-lastname" type="text" placeholder="Dela Cruz" required />
									</div>
								</div>
								<div class="form-group">
									<label for="buyer-email">Email Address <span style="color:#e74c3c">*</span></label>
									<input id="buyer-email" type="email" placeholder="you@example.com" required />
								</div>
								<div class="form-group">
									<label for="buyer-phone">Phone Number <span style="color:#e74c3c">*</span></label>
									<input id="buyer-phone" type="tel" placeholder="+63 9XX XXX XXXX" required />
								</div>
								<div class="form-group">
									<label for="buyer-password">Password <span style="color:#e74c3c">*</span></label>
									<input id="buyer-password" type="password" placeholder="Create a strong password" required />
								</div>
								<div class="form-group">
									<label>Profile Image</label>
									<div class="profile-upload">
										<div class="profile-preview" id="buyerProfilePreview">
											<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
										</div>
										<div class="profile-upload-btn">
											<label for="buyer-profile-img">
												<svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
												Upload Photo
											</label>
											<input type="file" id="buyer-profile-img" accept="image/*" />
											<span>JPG, PNG up to 5MB</span>
										</div>
									</div>
								</div>
								<button class="btn btn-primary" type="button">Create Buyer Account</button>
							</div>

							<!-- Seller Fields -->
							<div class="seller-fields" id="sellerFields">
								<div class="form-row">
									<div class="form-group">
										<label for="seller-firstname">First Name <span style="color:#e74c3c">*</span></label>
										<input id="seller-firstname" type="text" placeholder="Juan" required />
									</div>
									<div class="form-group">
										<label for="seller-lastname">Last Name <span style="color:#e74c3c">*</span></label>
										<input id="seller-lastname" type="text" placeholder="Dela Cruz" required />
									</div>
								</div>
								<div class="form-group">
									<label for="seller-email">Email Address <span style="color:#e74c3c">*</span></label>
									<input id="seller-email" type="email" placeholder="you@example.com" required />
								</div>
								<div class="form-group">
									<label for="seller-phone">Phone Number <span style="color:#e74c3c">*</span></label>
									<input id="seller-phone" type="tel" placeholder="+63 9XX XXX XXXX" required />
								</div>
								<div class="form-group">
									<label for="seller-password">Password <span style="color:#e74c3c">*</span></label>
									<input id="seller-password" type="password" placeholder="Create a strong password" required />
								</div>
								<div class="form-group">
									<label>Profile Picture</label>
									<div class="profile-upload">
										<div class="profile-preview" id="sellerProfilePreview">
											<svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
										</div>
										<div class="profile-upload-btn">
											<label for="seller-profile-img">
												<svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
												Upload Photo
											</label>
											<input type="file" id="seller-profile-img" accept="image/*" />
											<span>JPG, PNG up to 5MB</span>
										</div>
									</div>
								</div>

								<!-- Seller Verification Section -->
								<div class="verification-section">
									<div class="verification-header">
										<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
										<h4>Seller Verification</h4>
										<span>For trusted seller badge</span>
									</div>

									<div class="upload-item">
										<div class="upload-label">
											Valid ID <span class="required">*</span>
										</div>
										<div class="file-upload" id="validIdUpload">
											<input type="file" id="seller-valid-id" accept="image/*,.pdf" required />
											<div class="file-upload-icon">
												<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect><line x1="7" y1="8" x2="7" y2="8"></line><line x1="7" y1="12" x2="17" y2="12"></line><line x1="7" y1="16" x2="13" y2="16"></line></svg>
											</div>
											<p><span>Click to upload</span> or drag and drop</p>
											<p class="file-upload-hint">Government-issued ID (Passport, Driver's License, etc.)</p>
										</div>
									</div>

									<div class="upload-item">
										<div class="upload-label">
											BIR / Business Permit <span class="optional">(Optional)</span>
										</div>
										<div class="file-upload" id="birUpload">
											<input type="file" id="seller-bir" accept="image/*,.pdf" />
											<div class="file-upload-icon">
												<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
											</div>
											<p><span>Click to upload</span> or drag and drop</p>
											<p class="file-upload-hint">BIR Certificate or Business Permit</p>
										</div>
									</div>
								</div>

								<button class="btn btn-primary" type="button" style="margin-top: 20px;">Create Seller Account</button>
							</div>
						</form>
						</div>

						<div class="divider">or sign up with</div>
						<div class="social-login">
							<button class="social-btn" type="button">
								<svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
								Google
							</button>
							<button class="social-btn" type="button">
								<svg viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
								Facebook
							</button>
						</div>
						<p class="switch-text">Already have an account? <a id="showLogin">Sign in</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script>
		const authContainer = document.getElementById('authContainer');
		const showSignup = document.getElementById('showSignup');
		const showLogin = document.getElementById('showLogin');
		const switchToLogin = document.getElementById('switchToLogin');

		// Role toggle elements
		const buyerRoleBtn = document.getElementById('buyerRoleBtn');
		const sellerRoleBtn = document.getElementById('sellerRoleBtn');
		const buyerFields = document.getElementById('buyerFields');
		const sellerFields = document.getElementById('sellerFields');
		const selectedRole = document.getElementById('selectedRole');

		// Check URL for signup mode parameter
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.get('mode') === 'signup') {
			authContainer.classList.add('signup-mode');
		}

		showSignup.addEventListener('click', () => {
			authContainer.classList.add('signup-mode');
		});

		showLogin.addEventListener('click', () => {
			authContainer.classList.remove('signup-mode');
		});

		switchToLogin.addEventListener('click', () => {
			authContainer.classList.remove('signup-mode');
		});

		// Role toggle functionality
		buyerRoleBtn.addEventListener('click', () => {
			buyerRoleBtn.classList.add('active');
			sellerRoleBtn.classList.remove('active');
			buyerFields.classList.add('active');
			sellerFields.classList.remove('active');
			selectedRole.value = 'buyer';
		});

		sellerRoleBtn.addEventListener('click', () => {
			sellerRoleBtn.classList.add('active');
			buyerRoleBtn.classList.remove('active');
			sellerFields.classList.add('active');
			buyerFields.classList.remove('active');
			selectedRole.value = 'seller';
		});

		// Profile image preview
		function setupImagePreview(inputId, previewId) {
			const input = document.getElementById(inputId);
			const preview = document.getElementById(previewId);

			if (input && preview) {
				input.addEventListener('change', function(e) {
					const file = e.target.files[0];
					if (file) {
						const reader = new FileReader();
						reader.onload = function(e) {
							preview.innerHTML = `<img src="${e.target.result}" alt="Profile">`;
						}
						reader.readAsDataURL(file);
					}
				});
			}
		}

		setupImagePreview('buyer-profile-img', 'buyerProfilePreview');
		setupImagePreview('seller-profile-img', 'sellerProfilePreview');

		// File upload visual feedback
		function setupFileUpload(uploadId, inputId) {
			const uploadArea = document.getElementById(uploadId);
			const input = document.getElementById(inputId);

			if (uploadArea && input) {
				input.addEventListener('change', function(e) {
					const file = e.target.files[0];
					if (file) {
						const fileName = file.name.length > 25 ? file.name.substring(0, 22) + '...' : file.name;
						const pElement = uploadArea.querySelector('p:not(.file-upload-hint)');
						if (pElement) {
							pElement.innerHTML = `<span style="color: #2ecc71;">✓</span> ${fileName}`;
						}
						uploadArea.style.borderColor = '#2ecc71';
						uploadArea.style.background = 'rgba(46, 204, 113, 0.05)';
					}
				});

				// Drag and drop
				uploadArea.addEventListener('dragover', (e) => {
					e.preventDefault();
					uploadArea.style.borderColor = 'var(--accent)';
					uploadArea.style.background = 'rgba(202, 164, 110, 0.1)';
				});

				uploadArea.addEventListener('dragleave', (e) => {
					e.preventDefault();
					uploadArea.style.borderColor = '';
					uploadArea.style.background = '';
				});

				uploadArea.addEventListener('drop', (e) => {
					e.preventDefault();
					const files = e.dataTransfer.files;
					if (files.length) {
						input.files = files;
						input.dispatchEvent(new Event('change'));
					}
				});
			}
		}

		setupFileUpload('validIdUpload', 'seller-valid-id');
		setupFileUpload('birUpload', 'seller-bir');
	</script>
</body>
</html>
