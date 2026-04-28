<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Landly | Access</title>
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 15px;
			overflow-x: hidden;
		}

		a {
			color: inherit;
			text-decoration: none;
		}

		/* Main Auth Container */
		.auth-container {
			--panel-width: 50%;
			width: 100%;
			max-width: 1100px;
			display: flex;
			border-radius: 32px;
			overflow: hidden;
			box-shadow: 0 40px 80px rgba(0, 0, 0, 0.4);
			position: relative;
			background: linear-gradient(180deg, #f7f2e9 0%, #efe7d8 100%);
		}

		/* Default: Stack on mobile */
		.auth-container {
			flex-direction: column;
			height: auto;
			min-height: 100%;
		}

		/* Desktop: Side-by-side layout */
		@media (min-width: 992px) {
			.auth-container {
				flex-direction: row;
				height: 650px;
				--panel-width: 50%;
			}
		}

		/* Left Panel - Branding/Info */
		.info-panel {
			flex: 0 0 100%;
			position: relative;
			display: flex;
			flex-direction: column;
			justify-content: center;
			padding: 30px 20px;
			background: 
				linear-gradient(135deg, rgba(12, 26, 27, 0.85) 0%, rgba(20, 49, 44, 0.75) 100%),
				url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80');
			background-size: cover;
			background-position: center;
			overflow: hidden;
			z-index: 2;
			min-height: 250px;
		}

		@media (min-width: 768px) {
			.info-panel {
				padding: 40px 30px;
				min-height: 300px;
			}
		}

		@media (min-width: 992px) {
			.info-panel {
				flex: 0 0 var(--panel-width);
				max-width: var(--panel-width);
				padding: 50px;
				min-height: auto;
			}
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
			font-size: 1.8rem;
			font-weight: 600;
			line-height: 1.2;
			margin-bottom: 20px;
			position: relative;
			z-index: 1;
		}

		@media (min-width: 768px) {
			.info-panel h1 {
				font-size: 2.2rem;
			}
		}

		@media (min-width: 992px) {
			.info-panel h1 {
				font-size: 2.8rem;
			}
		}

		.info-panel .tagline {
			color: rgba(247, 242, 233, 0.8);
			font-size: 0.95rem;
			line-height: 1.7;
			margin-bottom: 35px;
			max-width: 400px;
			position: relative;
			z-index: 1;
		}

		.features-list {
			display: none;
			flex-direction: column;
			gap: 16px;
			position: relative;
			z-index: 1;
		}

		@media (min-width: 992px) {
			.features-list {
				display: flex;
			}
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
			flex: 0 0 100%;
			background: linear-gradient(180deg, #f7f2e9 0%, #efe7d8 100%);
			display: flex;
			align-items: center;
			justify-content: center;
			position: relative;
			overflow: visible;
			padding: 20px;
			min-height: auto;
		}

		@media (min-width: 768px) {
			.form-panel {
				padding: 30px;
			}
		}

		@media (min-width: 992px) {
			.form-panel {
				flex: 0 0 var(--panel-width);
				max-width: var(--panel-width);
				padding: 0;
			}
		}

		/* Sliding Overlay */
		.sliding-overlay {
			display: none !important;
		}

		@media (min-width: 992px) {
			.sliding-overlay {
				display: flex !important;
				position: absolute;
				top: 0;
				left: var(--panel-width);
				width: var(--panel-width);
				height: 100%;
				background: 
					linear-gradient(135deg, rgba(12, 26, 27, 0.9) 0%, rgba(20, 49, 44, 0.85) 100%),
					url('https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80');
				background-size: cover;
				background-position: center;
				flex-direction: column;
				justify-content: center;
				align-items: center;
				padding: 50px;
				text-align: center;
				transform: translateX(100%);
				transition: transform 0.7s cubic-bezier(0.68, -0.15, 0.32, 1.15);
				z-index: 20;
			}

			.sliding-overlay::before {
				content: '';
				position: absolute;
				inset: 0;
				backdrop-filter: blur(10px);
				z-index: -1;
			}

			.auth-container.signup-mode .sliding-overlay {
				transform: translateX(-100%);
			}
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
			padding: 25px;
			position: relative;
		}

		@media (min-width: 768px) {
			.form-container {
				padding: 35px;
			}
		}

		@media (min-width: 992px) {
			.form-container {
				padding: 40px;
			}
		}

		.form-wrapper {
			position: relative;
			width: 100%;
			min-height: 560px;
		}

		/* Login Form */
		.login-form,
		.signup-form {
			width: 100%;
			transition: opacity 0.45s cubic-bezier(0.68, -0.15, 0.32, 1.15), transform 0.45s cubic-bezier(0.68, -0.15, 0.32, 1.15);
		}

		.login-form {
			opacity: 1;
			transform: translateX(0);
			visibility: visible;
			position: relative;
		}

		.signup-form {
			position: absolute;
			top: 0;
			left: 0;
			opacity: 0;
			transform: translateX(50px);
			pointer-events: none;
			visibility: hidden;
		}

		.auth-container.signup-mode .login-form {
			opacity: 0;
			transform: translateX(-50px);
			pointer-events: none;
			visibility: hidden;
			position: absolute;
			top: 0;
			left: 0;
		}

		.auth-container.signup-mode .signup-form {
			opacity: 1;
			transform: translateX(0);
			pointer-events: auto;
			position: relative;
			visibility: visible;
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
			padding: 12px 14px;
			border-radius: 12px;
			border: 2px solid rgba(15, 27, 27, 0.1);
			background: #fff;
			color: var(--green-900);
			font-size: 0.95rem;
			transition: all 0.3s ease;
		}

		@media (min-width: 768px) {
			.form-group input {
				padding: 14px 18px;
				font-size: 1rem;
			}
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
			grid-template-columns: 1fr;
			gap: 12px;
		}

		@media (min-width: 768px) {
			.form-row {
				grid-template-columns: 1fr 1fr;
			}
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
			max-height: 400px;
			overflow-y: auto;
			padding-right: 5px;
			margin-right: -5px;
		}

		@media (min-width: 768px) {
			.signup-form-inner {
				max-height: 500px;
			}
		}

		@media (min-width: 992px) {
			.signup-form-inner {
				max-height: 500px;
			}
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
		@media (max-width: 991.98px) {
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
				visibility: visible !important;
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

			.form-header h2 {
				font-size: 1.5rem;
			}

			.signup-form-inner {
				max-height: 500px;
				overflow-y: auto;
			}
		}

		@media (min-width: 992px) {
			.mobile-overlay {
				display: none !important;
			}
		}

		.swal2-popup.landly-swal {
			background: var(--green-900);
			color: var(--cream-100);
			border: 1px solid rgba(202, 164, 110, 0.35);
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

		.swal2-popup.landly-swal .swal2-confirm:focus {
			box-shadow: 0 0 0 3px var(--glow);
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

		/* Bootstrap Override - Remove unwanted margins on small screens */
		@media (max-width: 767.98px) {
			.form-group {
				margin-bottom: 15px;
			}

			.role-toggle {
				margin-bottom: 20px;
			}

			.btn {
				padding: 14px 20px;
				font-size: 0.95rem;
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

		<!-- Sliding Overlay (moves from right half to left half in signup mode) -->
		<div class="sliding-overlay">
			<h2>Already have an account?</h2>
			<p>Sign in to access your portfolio, saved listings, and secure document vault.</p>
			<button class="overlay-btn" id="switchToLogin">Sign In</button>
		</div>

		<!-- Right Panel - Forms -->
		<div class="form-panel">
			<div class="form-container">
				<div class="form-wrapper">
					<!-- Login Form -->
					<div class="login-form">
						<div class="form-header">
							<h2>Welcome back</h2>
							<p>Enter your credentials to access your account</p>
						</div>
					<form id="loginForm" action="<?= base_url('auth/login') ?>" method="post">
						<?= csrf_field() ?>

						<div class="form-group">
							<label for="login-email">Email address</label>
							<input id="login-email" name="email" type="email" placeholder="you@example.com" required />
						</div>

						<div class="form-group">
							<label for="login-password">Password</label>
							<input id="login-password" name="password" type="password" placeholder="Enter your password" required />
						</div>

						

						<button class="btn btn-primary" type="submit">Sign In</button>
					</form>			
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
						<form id="signupForm" action="<?= base_url('auth/signup/buyer') ?>" method="post" enctype="multipart/form-data">
							<?= csrf_field() ?>
							<input type="hidden" name="role" id="selectedRole" value="buyer" />

							<!-- Buyer Fields -->
							<div class="buyer-fields active" id="buyerFields">
								<div class="form-row">
									<div class="form-group">
										<label for="buyer-firstname">First Name <span style="color:#e74c3c">*</span></label>
										<input id="buyer-firstname" name="first_name" type="text" placeholder="Juan" required />
									</div>
									<div class="form-group">
										<label for="buyer-lastname">Last Name <span style="color:#e74c3c">*</span></label>
										<input id="buyer-lastname" name="last_name" type="text" placeholder="Dela Cruz" required />
									</div>
								</div>
								<div class="form-group">
									<label for="buyer-email">Email Address <span style="color:#e74c3c">*</span></label>
									<input id="buyer-email" name="email" type="email" placeholder="you@example.com" required />
								</div>
								<div class="form-group">
									<label for="buyer-phone">Phone Number <span style="color:#e74c3c">*</span></label>
									<input id="buyer-phone" name="phone" type="tel" placeholder="+63 9XX XXX XXXX" required />
								</div>
								<div class="form-group">
									<label for="buyer-password">Password <span style="color:#e74c3c">*</span></label>
									<input id="buyer-password" name="password" type="password" placeholder="Create a strong password" required />
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
											<input type="file" id="buyer-profile-img" name="profile_image" accept="image/*" />
											<span>JPG, PNG up to 5MB</span>
										</div>
									</div>
								</div>

								<!-- Buyer Verification Section -->
								<div class="verification-section">
									<div class="verification-header">
										<svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
										<h4>Identity Verification</h4>
										<span>For account security</span>
									</div>
									<div class="upload-item">
										<div class="upload-label">
											Valid ID <span class="required">*</span>
										</div>
										<div class="file-upload" id="buyerValidIdUpload">
											<input type="file" id="buyer-valid-id" name="valid_id" accept="image/*,.pdf" required />
											<div class="file-upload-icon">
												<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect><line x1="7" y1="8" x2="7" y2="8"></line><line x1="7" y1="12" x2="17" y2="12"></line><line x1="7" y1="16" x2="13" y2="16"></line></svg>
											</div>
											<p><span>Click to upload</span> or drag and drop</p>
											<p class="file-upload-hint">Government-issued ID (Passport, Driver's License, etc.)</p>
										</div>
									</div>
								</div>

								<button class="btn btn-primary" type="submit" style="margin-top: 20px;">Create Buyer Account</button>
							</div>

							<!-- Seller Fields -->
							<div class="seller-fields" id="sellerFields">
								<div class="form-row">
									<div class="form-group">
										<label for="seller-firstname">First Name <span style="color:#e74c3c">*</span></label>
										<input id="seller-firstname" name="first_name" type="text" placeholder="Juan" required />
									</div>
									<div class="form-group">
										<label for="seller-lastname">Last Name <span style="color:#e74c3c">*</span></label>
										<input id="seller-lastname" name="last_name" type="text" placeholder="Dela Cruz" required />
									</div>
								</div>
								<div class="form-group">
									<label for="seller-email">Email Address <span style="color:#e74c3c">*</span></label>
									<input id="seller-email" name="email" type="email" placeholder="you@example.com" required />
								</div>
								<div class="form-group">
									<label for="seller-phone">Phone Number <span style="color:#e74c3c">*</span></label>
									<input id="seller-phone" name="phone" type="tel" placeholder="+63 9XX XXX XXXX" required />
								</div>
								<div class="form-group">
									<label for="seller-password">Password <span style="color:#e74c3c">*</span></label>
									<input id="seller-password" name="password" type="password" placeholder="Create a strong password" required />
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
											<input type="file" id="seller-profile-img" name="profile_image" accept="image/*" />
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
											<input type="file" id="seller-valid-id" name="valid_id" accept="image/*,.pdf" required />
											<div class="file-upload-icon">
												<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="2" ry="2"></rect><line x1="7" y1="8" x2="7" y2="8"></line><line x1="7" y1="12" x2="17" y2="12"></line><line x1="7" y1="16" x2="13" y2="16"></line></svg>
											</div>
											<p><span>Click to upload</span> or drag and drop</p>
											<p class="file-upload-hint">Government-issued ID (Passport, Driver's License, etc.)</p>
										</div>
									</div>

									<div class="upload-item">
										<div class="upload-label">
											Selfie With ID <span class="required">*</span>
										</div>
										<div class="file-upload" id="selfieUpload">
											<input type="file" id="seller-selfie" name="selfie_with_id" accept="image/*,.pdf" required />
											<div class="file-upload-icon">
												<svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
											</div>
											<p><span>Click to upload</span> or drag and drop</p>
											<p class="file-upload-hint">Any Government Valid ID</p>
										</div>
									</div>
								</div>

								<button class="btn btn-primary" type="submit" style="margin-top: 20px;">Create Seller Account</button>
							</div>
						</form>
						</div>

						<p class="switch-text">Already have an account? <a id="showLogin">Sign in</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
		const signupForm = document.getElementById('signupForm');
		const loginForm = document.getElementById('loginForm');

		function setAuthMode(mode) {
			if (!authContainer) {
				return;
			}

			authContainer.classList.toggle('signup-mode', mode === 'signup');
		}

		function setRole(role) {
			if (!buyerRoleBtn || !sellerRoleBtn || !buyerFields || !sellerFields || !selectedRole || !signupForm) {
				return;
			}

			const isSeller = role === 'seller';
			const activeFields = isSeller ? sellerFields : buyerFields;
			const inactiveFields = isSeller ? buyerFields : sellerFields;
			const activeAction = isSeller ? '<?= base_url('auth/signup/seller') ?>' : '<?= base_url('auth/signup/buyer') ?>';

			buyerRoleBtn.classList.toggle('active', !isSeller);
			sellerRoleBtn.classList.toggle('active', isSeller);
			buyerFields.classList.toggle('active', !isSeller);
			sellerFields.classList.toggle('active', isSeller);
			selectedRole.value = isSeller ? 'seller' : 'buyer';
			signupForm.action = activeAction;

			inactiveFields.querySelectorAll('input').forEach((input) => {
				input.disabled = true;
			});

			activeFields.querySelectorAll('input').forEach((input) => {
				input.disabled = false;
			});
		}

		// Check URL for signup mode parameter
		const urlParams = new URLSearchParams(window.location.search);
		setAuthMode(urlParams.get('mode') === 'signup' ? 'signup' : 'login');
		setRole(selectedRole ? selectedRole.value : 'buyer');

		if (showSignup) {
			showSignup.addEventListener('click', () => setAuthMode('signup'));
		}

		if (showLogin) {
			showLogin.addEventListener('click', () => setAuthMode('login'));
		}

		if (switchToLogin) {
			switchToLogin.addEventListener('click', () => setAuthMode('login'));
		}

		// Role toggle functionality
		if (buyerRoleBtn) {
			buyerRoleBtn.addEventListener('click', () => setRole('buyer'));
		}

		if (sellerRoleBtn) {
			sellerRoleBtn.addEventListener('click', () => setRole('seller'));
		}

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
							pElement.innerHTML = `<span style="color: var(--accent);">✓</span> ${fileName}`;
						}
						uploadArea.style.borderColor = 'var(--accent)';
						uploadArea.style.background = 'rgba(202, 164, 110, 0.08)';
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

		setupFileUpload('buyerValidIdUpload', 'buyer-valid-id');
		setupFileUpload('validIdUpload', 'seller-valid-id');
		setupFileUpload('selfieUpload', 'seller-selfie');
		setupFileUpload('birUpload', 'seller-bir');

		function getSwalBaseConfig() {
			return {
				customClass: {
					popup: 'landly-swal',
					confirmButton: 'landly-swal-confirm'
				},
				showClass: {
					popup: 'landly-swal-show'
				},
				hideClass: {
					popup: 'landly-swal-hide'
				}
			};
		}

		function showSubmitLoading(text) {
			if (typeof Swal === 'undefined') {
				return;
			}

			Swal.fire({
				...getSwalBaseConfig(),
				title: 'Please wait',
				html: text,
				allowOutsideClick: false,
				allowEscapeKey: false,
				showConfirmButton: false,
				iconColor: '#caa46e',
				didOpen: () => {
					Swal.showLoading();
				}
			});
		}

		function showGenericResult(type, title, text) {
			if (typeof Swal === 'undefined') {
				return Promise.resolve();
			}

			return Swal.fire({
				...getSwalBaseConfig(),
				icon: type,
				title,
				text,
				iconColor: '#caa46e',
				confirmButtonText: 'OK'
			});
		}

		async function submitWithFeedback(form, options) {
			if (!form) {
				return;
			}

			form.addEventListener('submit', async function(event) {
				event.preventDefault();

				const submitButton = form.querySelector('button[type="submit"]');
				if (submitButton) {
					submitButton.disabled = true;
				}

				showSubmitLoading(options.loadingText);

				try {
					const response = await fetch(form.action, {
						method: form.method || 'POST',
						body: new FormData(form),
						headers: {
							'X-Requested-With': 'XMLHttpRequest'
						}
					});

					if (response.ok) {
						await showGenericResult('success', options.successTitle, options.successText);

						if (options.onSuccess === 'redirect') {
							if (response.redirected && response.url) {
								window.location.href = response.url;
								return;
							}

							window.location.href = '<?= base_url('/') ?>';
							return;
						}

						if (options.onSuccess === 'switchToLogin') {
							form.reset();
							setRole('buyer');
							setAuthMode('login');
						}
					} else {
						const statusMessage = options.errorByStatus && options.errorByStatus[response.status]
							? options.errorByStatus[response.status]
							: options.errorText;

						await showGenericResult('error', options.errorTitle, statusMessage);
					}
				} catch (error) {
					await showGenericResult('error', options.errorTitle, options.errorText);
				} finally {
					if (submitButton) {
						submitButton.disabled = false;
					}
				}
			});
		}

		submitWithFeedback(loginForm, {
			loadingText: 'Checking login credentials...',
			successTitle: 'Login successful',
			successText: 'Taking you to your dashboard.',
			errorTitle: 'Login failed',
			errorText: 'Unable to sign in. Please check your credentials and try again.',
			errorByStatus: {
				403: 'Your seller account is still pending verification. Please wait for approval before signing in.'
			},
			onSuccess: 'redirect'
		});

		submitWithFeedback(signupForm, {
			loadingText: 'Creating your account and validating details...',
			successTitle: 'Account created',
			successText: 'Your registration was completed. You can now sign in.',
			errorTitle: 'Sign-up failed',
			errorText: 'Unable to create your account right now. Please review your details and try again.',
			onSuccess: 'switchToLogin'
		});
	</script>
	<!-- Bootstrap JS -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
