<?php
require_once __DIR__ . '/config.php';
$config = loadConfig();
$appTitle = htmlspecialchars($config['app_title'] ?? 'Balochistan Excise Vehicle Verification');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $appTitle ?> - Direct Search</title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #0d5f3a;
            --primary-dark: #083e25;
            --primary-light: #e8f5ed;
            --accent: #d4af37;
            --navy: #1a2530;
            --slate: #4a5568;
            --light-bg: #f4f7f6;
            --card-bg: #ffffff;
            --border: #e2e8f0;
            --danger: #dc2626;
            --success: #16a34a;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.07);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.05);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Header */
        header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            padding: 1.25rem 2rem;
            box-shadow: 0 4px 15px rgba(13, 95, 58, 0.25);
            position: relative;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-box {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            border: 2px solid var(--accent);
        }

        .brand-logo img {
            width: 80%;
            height: 80%;
            object-fit: contain;
        }

        .brand-text h1 {
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .brand-text p {
            font-size: 0.85rem;
            color: #d1fae5;
            font-weight: 500;
        }

        .status-badge {
            color: #d1fae5;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.4rem 0.9rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.12);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* Hero / Search Section */
        .hero {
            background: linear-gradient(180deg, #e8f5ed 0%, var(--light-bg) 100%);
            padding: 3rem 1.5rem 2rem;
            text-align: center;
        }

        .hero-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }

        .hero-subtitle {
            color: var(--slate);
            font-size: 1.05rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .search-card {
            max-width: 780px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr 180px auto;
            gap: 1rem;
            align-items: center;
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }
        }

        .input-group {
            position: relative;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--slate);
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap i {
            position: absolute;
            left: 1rem;
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 2.8rem;
            font-size: 1.05rem;
            font-weight: 600;
            border: 2px solid var(--border);
            border-radius: 10px;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
            color: #1e293b;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13, 95, 58, 0.15);
        }

        .form-select {
            padding-left: 2.5rem;
            appearance: none;
            background: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%234a5568' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 1rem center/14px auto;
            background-color: white;
            cursor: pointer;
        }

        .btn-search {
            margin-top: 1.35rem;
            padding: 0.88rem 1.8rem;
            font-size: 1.05rem;
            font-weight: 700;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            box-shadow: 0 4px 10px rgba(13, 95, 58, 0.3);
            font-family: inherit;
        }

        .btn-search:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(13, 95, 58, 0.4);
        }

        .btn-search:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .quick-hints {
            margin-top: 1rem;
            font-size: 0.82rem;
            color: var(--slate);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .hint-badge {
            background: #e2e8f0;
            color: #334155;
            padding: 0.2rem 0.5rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.78rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .hint-badge:hover {
            background: #cbd5e1;
        }

        /* Results Container */
        .main-content {
            max-width: 1100px;
            margin: 0 auto 3rem;
            padding: 0 1.5rem;
            flex: 1;
            width: 100%;
        }

        /* Loading Skeleton */
        .loading-box {
            display: none;
            text-align: center;
            padding: 3rem 1rem;
            background: white;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            margin-top: 2rem;
        }

        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1.25rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Error / Not Found Box */
        .alert-box {
            display: none;
            padding: 1.5rem;
            border-radius: 14px;
            margin-top: 2rem;
            align-items: flex-start;
            gap: 1rem;
            border: 1px solid transparent;
        }

        .alert-error {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .alert-warning {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        .alert-icon {
            font-size: 1.5rem;
            margin-top: 0.1rem;
        }

        .alert-text h3 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .alert-text p {
            font-size: 0.92rem;
            line-height: 1.4;
        }

        /* Verified Result Card */
        .result-container {
            display: none;
            margin-top: 2rem;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card {
            background: white;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .result-header {
            background: linear-gradient(135deg, #1a2530 0%, #2c3e50 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .veh-badge {
            display: inline-block;
            background: #f1c40f;
            color: #1a2530;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            padding: 0.35rem 1.25rem;
            border-radius: 8px;
            border: 2px solid white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .district-badge {
            background: rgba(255, 255, 255, 0.15);
            color: #e2e8f0;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 20px;
            margin-left: 0.75rem;
        }

        .btn-print {
            background: white;
            color: #1a2530;
            border: none;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-print:hover {
            background: #e2e8f0;
        }

        .section-title {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--primary-dark);
            padding: 1.25rem 2rem 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-bottom: 2px solid var(--primary-light);
        }

        /* Spec Grid */
        .spec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.25rem;
            padding: 1.5rem 2rem;
        }

        .spec-item {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            padding: 1rem 1.25rem;
            border-radius: 10px;
        }

        .spec-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.35rem;
        }

        .spec-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            word-break: break-word;
        }

        .tax-badge {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            margin-top: 0.3rem;
        }

        .tax-paid {
            background: #dcfce7;
            color: #166534;
        }

        .tax-unpaid {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Owner & History */
        .timeline {
            padding: 1.5rem 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.25rem;
        }

        .history-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid var(--primary);
            border-radius: 10px;
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
        }

        .history-card h4 {
            font-size: 1rem;
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .history-item {
            font-size: 0.88rem;
            margin-bottom: 0.4rem;
            color: #334155;
        }

        .history-item strong {
            color: #0f172a;
            display: inline-block;
            min-width: 90px;
        }

        /* Footer */
        footer {
            background: #0f172a;
            color: #94a3b8;
            padding: 2rem 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            margin-top: auto;
        }

        footer p {
            margin-bottom: 0.4rem;
        }

        /* Print Specifics */
        @media print {
            header, .hero, footer, .btn-print, .quick-hints {
                display: none !important;
            }
            body {
                background: white;
                color: black;
            }
            .card {
                border: 1px solid #000;
                box-shadow: none;
                page-break-inside: avoid;
            }
            .result-header {
                background: #000 !important;
                color: #fff !important;
            }
            .spec-item {
                background: white !important;
                border: 1px solid #ccc !important;
            }
            .veh-badge {
                border: 2px solid #000;
                background: #fff !important;
                color: #000 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="header-container">
            <div class="brand-box">
                <div class="brand-logo">
                    <img src="https://etanb.com/public/img/BALOCH EXCISE.png" alt="Emblem" onerror="this.src='https://etanb.com/public/img/logo.png'">
                </div>
                <div class="brand-text">
                    <h1>Excise, Taxation & Anti-Narcotics Department</h1>
                    <p>Government of Balochistan &bull; Vehicle Verification Portal</p>
                </div>
            </div>
            <div>
                <span class="status-badge">
                    <i class="fa-solid fa-circle-check" style="color: #4ade80;"></i> Official Portal
                </span>
            </div>
        </div>
    </header>

    <!-- Hero / Search Section -->
    <section class="hero">
        <h2 class="hero-title">Vehicle Verification & History</h2>
        <p class="hero-subtitle">Instant, real-time public vehicle records directly from Balochistan Excise Database. No login required.</p>

        <div class="search-card">
            <form id="searchForm" class="search-form" onsubmit="event.preventDefault(); executeSearch();">
                <!-- Vehicle Number -->
                <div class="input-group">
                    <label for="vehicleNo">Vehicle Registration No.</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-car"></i>
                        <input type="text" id="vehicleNo" class="form-control" placeholder="e.g. QTA-1234 or QA-999" required autocomplete="off" autofocus>
                    </div>
                </div>

                <!-- District Location -->
                <div class="input-group">
                    <label for="location">District / ETO</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-location-dot"></i>
                        <select id="location" class="form-control form-select">
                            <option value="1" selected>QUETTA</option>
                            <option value="2">HUB</option>
                        </select>
                    </div>
                </div>

                <!-- Search Button -->
                <div>
                    <button type="submit" id="searchBtn" class="btn-search">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                </div>
            </form>

            <div class="quick-hints">
                <span>Quick formats:</span>
                <span class="hint-badge" onclick="setVehicle('QTA-1234')">QTA-1234</span>
                <span class="hint-badge" onclick="setVehicle('QA-100')">QA-100</span>
                <span class="hint-badge" onclick="setVehicle('HUB-555', '2')">HUB-555</span>
                <span class="hint-badge" onclick="setVehicle('QAD-786')">QAD-786</span>
            </div>
        </div>
    </section>

    <!-- Main Results Area -->
    <main class="main-content">
        <!-- Loading -->
        <div id="loadingBox" class="loading-box">
            <div class="spinner"></div>
            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--primary-dark);">Querying Balochistan Excise Records...</h3>
            <p style="color: var(--slate); font-size: 0.9rem; margin-top: 0.35rem;">Connecting securely via background authenticated session...</p>
        </div>

        <!-- Alert Box -->
        <div id="alertBox" class="alert-box">
            <div class="alert-icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div class="alert-text">
                <h3 id="alertTitle">Not Found</h3>
                <p id="alertMessage">No matching record found.</p>
            </div>
        </div>

        <!-- Verification Results -->
        <div id="resultContainer" class="result-container">
            <!-- Header card -->
            <div class="card">
                <div class="result-header">
                    <div>
                        <span id="resVehNo" class="veh-badge">QTA-1234</span>
                        <span id="resDistrict" class="district-badge">QUETTA</span>
                    </div>
                    <div>
                        <button type="button" class="btn-print" onclick="window.print()">
                            <i class="fa-solid fa-print"></i> Print Verification Slip
                        </button>
                    </div>
                </div>

                <!-- Vehicle Specs -->
                <div class="section-title">
                    <i class="fa-solid fa-gauge-high"></i> Vehicle Specifications
                </div>
                <div class="spec-grid">
                    <div class="spec-item">
                        <div class="spec-label">Maker / Brand</div>
                        <div id="valMaker" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Class of Vehicle</div>
                        <div id="valClass" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Body Type</div>
                        <div id="valBodyType" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Year of Manufacture</div>
                        <div id="valYear" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Chassis Number</div>
                        <div id="valChassis" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Engine Number</div>
                        <div id="valEngine" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Engine Capacity (CC)</div>
                        <div id="valEngineCap" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Seating Capacity</div>
                        <div id="valSeatCap" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Registration Date</div>
                        <div id="valRegDate" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Last Tax Paid Upto</div>
                        <div id="valTaxPaid" class="spec-value">-</div>
                    </div>
                </div>
            </div>

            <!-- Current Owner Details -->
            <div class="card">
                <div class="section-title">
                    <i class="fa-solid fa-user-check"></i> Registered Owner Information
                </div>
                <div class="spec-grid">
                    <div class="spec-item">
                        <div class="spec-label">Owner Name</div>
                        <div id="valOwnerName" class="spec-value">-</div>
                    </div>
                    <div class="spec-item">
                        <div class="spec-label">Father / Husband Name</div>
                        <div id="valFatherName" class="spec-value">-</div>
                    </div>
                    <div class="spec-item" style="grid-column: 1 / -1;">
                        <div class="spec-label">Registered Address</div>
                        <div id="valAddress" class="spec-value">-</div>
                    </div>
                </div>
            </div>

            <!-- Ownership History -->
            <div id="ownerHistoryCard" class="card" style="display: none;">
                <div class="section-title">
                    <i class="fa-solid fa-users"></i> Ownership History
                </div>
                <div id="ownerTimeline" class="timeline"></div>
            </div>

            <!-- Transfer History -->
            <div id="transferHistoryCard" class="card" style="display: none;">
                <div class="section-title">
                    <i class="fa-solid fa-right-left"></i> Transfer & ETO Records
                </div>
                <div id="transferTimeline" class="timeline"></div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?= date('Y') ?> Government of Balochistan &bull; Excise, Taxation & Anti-Narcotics Department</p>
        <p>Secured background proxy verification system. Direct searches are rate-limited and logged.</p>
    </footer>

    <script>
        function setVehicle(no, loc = '1') {
            document.getElementById('vehicleNo').value = no;
            document.getElementById('location').value = loc;
            executeSearch();
        }

        function formatTaxDate(raw) {
            if (!raw || String(raw).trim() === '') return 'No paid tax record';
            const m = String(raw).trim().match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (m) {
                return m[3] + '/' + m[2] + '/' + m[1];
            }
            return raw;
        }

        async function executeSearch() {
            const vehInput = document.getElementById('vehicleNo');
            const locInput = document.getElementById('location');
            const searchBtn = document.getElementById('searchBtn');
            const loadingBox = document.getElementById('loadingBox');
            const alertBox = document.getElementById('alertBox');
            const resultContainer = document.getElementById('resultContainer');

            const vehicleNo = vehInput.value.trim().toUpperCase();
            const location = locInput.value;

            if (!vehicleNo) {
                showAlert('Validation Error', 'Please enter a vehicle registration number.', 'warning');
                return;
            }

            // Reset UI
            alertBox.style.display = 'none';
            resultContainer.style.display = 'none';
            loadingBox.style.display = 'block';
            searchBtn.disabled = true;

            try {
                const response = await fetch('search.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        vehicle_no: vehicleNo,
                        location: location
                    })
                });

                const data = await response.json();
                loadingBox.style.display = 'none';
                searchBtn.disabled = false;

                if (!data.success) {
                    showAlert('Search Error', data.message || 'Unable to fetch records.', 'error');
                    return;
                }

                if (!data.found) {
                    showAlert('Vehicle Not Found', data.message || `No records found for ${vehicleNo} in ${data.district}.`, 'warning');
                    return;
                }

                // Render Data
                renderVehicleData(data);
            } catch (err) {
                loadingBox.style.display = 'none';
                searchBtn.disabled = false;
                showAlert('Network Error', 'Failed to connect to the verification service. Please try again.', 'error');
            }
        }

        function renderVehicleData(data) {
            const info = data.vehicle_info || {};
            const resContainer = document.getElementById('resultContainer');

            document.getElementById('resVehNo').textContent = info.vehicleNo || data.vehicle_no;
            document.getElementById('resDistrict').textContent = data.district || 'QUETTA';

            // Specs
            document.getElementById('valMaker').textContent = info.maker || 'N/A';
            document.getElementById('valClass').textContent = info.class || 'N/A';
            document.getElementById('valBodyType').textContent = info.bodyType || 'N/A';
            document.getElementById('valYear').textContent = info.yearOfManufacture || 'N/A';
            document.getElementById('valChassis').textContent = info.chassisNo || 'N/A';
            document.getElementById('valEngine').textContent = info.engineNo || 'N/A';
            document.getElementById('valEngineCap').textContent = info.engineCap ? info.engineCap + ' CC' : 'N/A';
            document.getElementById('valSeatCap').textContent = info.seatCap ? info.seatCap + ' Persons' : 'N/A';
            document.getElementById('valRegDate').textContent = info.dateOfRegister || info.registration_date || 'N/A';
            
            // Tax
            const taxFormatted = formatTaxDate(info.lastTaxPaidUpto);
            document.getElementById('valTaxPaid').textContent = taxFormatted;

            // Owner
            document.getElementById('valOwnerName').textContent = info.ownerName || info.name_ || 'N/A';
            document.getElementById('valFatherName').textContent = info.father_or_husband_name || 'N/A';
            document.getElementById('valAddress').textContent = info.address || info.address_ || 'N/A';

            // Owners history
            const owners = data.owners || [];
            const ownerCard = document.getElementById('ownerHistoryCard');
            const ownerTimeline = document.getElementById('ownerTimeline');
            ownerTimeline.innerHTML = '';

            if (owners.length > 0 || info.name_) {
                ownerCard.style.display = 'block';

                // Initial Owner
                if (info.name_) {
                    ownerTimeline.innerHTML += `
                        <div class="history-card">
                            <h4><span><i class="fa-solid fa-user"></i> Initial Owner (1)</span></h4>
                            <div class="history-item"><strong>Name:</strong> ${info.name_ || 'N/A'}</div>
                            <div class="history-item"><strong>F/Name:</strong> ${info.father_or_husband_name || 'N/A'}</div>
                            <div class="history-item"><strong>Address:</strong> ${info.address_ || 'N/A'}</div>
                            <div class="history-item"><strong>Date:</strong> ${info.registration_date || 'N/A'}</div>
                        </div>
                    `;
                }

                owners.forEach((o, idx) => {
                    ownerTimeline.innerHTML += `
                        <div class="history-card">
                            <h4><span><i class="fa-solid fa-user"></i> Owner ${idx + 2}</span></h4>
                            <div class="history-item"><strong>Name:</strong> ${o.name || 'N/A'}</div>
                            <div class="history-item"><strong>F/Name:</strong> ${o.f_name || 'N/A'}</div>
                            <div class="history-item"><strong>Address:</strong> ${o.p_address || 'N/A'}</div>
                            <div class="history-item"><strong>Transfer:</strong> ${o.transfer_date || 'N/A'}</div>
                        </div>
                    `;
                });
            } else {
                ownerCard.style.display = 'none';
            }

            // Transfers history
            const transfers = data.transfers || [];
            const transferCard = document.getElementById('transferHistoryCard');
            const transferTimeline = document.getElementById('transferTimeline');
            transferTimeline.innerHTML = '';

            if (transfers.length > 0) {
                transferCard.style.display = 'block';
                transfers.forEach((t, idx) => {
                    transferTimeline.innerHTML += `
                        <div class="history-card">
                            <h4><span><i class="fa-solid fa-arrow-right-arrow-left"></i> Transfer ${idx + 1}</span></h4>
                            <div class="history-item"><strong>Old Reg No:</strong> ${t.old_reg_no || 'N/A'}</div>
                            <div class="history-item"><strong>ETO Location:</strong> ${t.eto_location_id || 'N/A'}</div>
                            <div class="history-item"><strong>Date:</strong> ${t.dates || 'N/A'}</div>
                        </div>
                    `;
                });
            } else {
                transferCard.style.display = 'none';
            }

            resContainer.style.display = 'block';
            resContainer.scrollIntoView({ behavior: 'smooth' });
        }

        function showAlert(title, message, type) {
            const alertBox = document.getElementById('alertBox');
            const alertTitle = document.getElementById('alertTitle');
            const alertMessage = document.getElementById('alertMessage');

            alertBox.className = 'alert-box ' + (type === 'error' ? 'alert-error' : 'alert-warning');
            alertTitle.textContent = title;
            alertMessage.textContent = message;
            alertBox.style.display = 'flex';
            alertBox.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
