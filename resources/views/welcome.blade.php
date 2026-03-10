<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ setting('site_name', 'School of Basic & Remedial Studies') }} - University of Maiduguri</title>
    @php
        $faviconPath = setting('favicon');
        $faviconUrl = $faviconPath ? asset('storage/' . $faviconPath) : url('/assets/images/favicon.png');
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}">
    @include('partials.meta')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('/assets/css/google-icon.css') }}">
    <link rel="stylesheet" href="{{ url('/assets/css/remixicon.css') }}">
    <style>
        :root {
            --primary: #006633;
            --primary-dark: #004d26;
            --secondary: #FFD700;
            --accent: #8B0000;
            --dark: #1a1a2e;
            --light: #f8f9fa;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; color: #333; overflow-x: hidden; }
        a { text-decoration: none; color: inherit; }

        /* Navbar */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: rgba(255,255,255,0.97); backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            padding: 0.75rem 0; transition: all 0.3s;
        }
        .navbar .container {
            max-width: 1200px; margin: 0 auto; padding: 0 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar-brand { display: flex; align-items: center; gap: 0.75rem; }
        .navbar-brand img { width: 45px; height: 45px; border-radius: 50%; }
        .navbar-brand .brand-text h1 { font-size: 1rem; font-weight: 700; color: var(--primary); line-height: 1.2; }
        .navbar-brand .brand-text span { font-size: 0.7rem; color: #666; }
        .nav-links { display: flex; gap: 0.5rem; align-items: center; }
        .nav-links a {
            padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.875rem;
            font-weight: 500; transition: all 0.3s;
        }
        .mobile-menu-btn { display: none; background: none; border: none; cursor: pointer; }
        .mobile-menu-btn .material-symbols-outlined { font-size: 1.5rem; color: var(--primary); }
        .mobile-login-cards { display: none; }
        .mobile-login-cards .m-login-card {
            flex: 1; text-align: center; padding: 0.75rem 0.5rem; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px); transition: all 0.3s; text-decoration: none;
        }
        .mobile-login-cards .m-login-card:hover { background: rgba(255,255,255,0.15); transform: translateY(-2px); }
        .mobile-login-cards .m-login-card .m-icon {
            width: 40px; height: 40px; border-radius: 10px; display: flex;
            align-items: center; justify-content: center; margin: 0 auto 0.4rem;
            font-size: 1.25rem; color: #fff;
        }
        .mobile-login-cards .m-login-card h4 { color: #fff; font-size: 0.75rem; font-weight: 600; margin: 0; }
        .mobile-login-cards .m-login-card p { color: rgba(255,255,255,0.6); font-size: 0.6rem; margin: 0.15rem 0 0; }
        .btn-outline { border: 2px solid var(--primary); color: var(--primary); }
        .btn-outline:hover { background: var(--primary); color: #fff; }
        .btn-primary-custom { background: var(--primary); color: #fff !important; }
        .btn-primary-custom:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .btn-accent { background: var(--accent); color: #fff !important; }
        .btn-accent:hover { background: #6d0000; }

        /* Hero */
        .hero {
            min-height: 100vh; display: flex; align-items: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 50%, var(--dark) 100%);
            position: relative; overflow: hidden; padding-top: 80px;
        }
        .hero::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-glow {
            position: absolute; width: 600px; height: 600px; border-radius: 50%;
            background: radial-gradient(circle, rgba(255,215,0,0.15), transparent 70%);
            top: -200px; right: -200px;
        }
        .hero .container {
            max-width: 1200px; margin: 0 auto; padding: 0 1.5rem;
            display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;
            position: relative; z-index: 2;
        }
        .hero-content h2 {
            font-size: 3rem; font-weight: 800; color: #fff; line-height: 1.15; margin-bottom: 1.5rem;
        }
        .hero-content h2 span { color: var(--secondary); }
        .hero-content p { font-size: 1.1rem; color: rgba(255,255,255,0.8); line-height: 1.7; margin-bottom: 2rem; }
        .hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
        .hero-actions a {
            padding: 0.875rem 2rem; border-radius: 10px; font-weight: 600;
            font-size: 0.95rem; transition: all 0.3s; display: inline-flex;
            align-items: center; gap: 0.5rem;
        }
        .hero-actions .btn-gold { background: var(--secondary); color: var(--dark); }
        .hero-actions .btn-gold:hover { background: #e6c200; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,215,0,0.3); }
        .hero-actions .btn-glass { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); }
        .hero-actions .btn-glass:hover { background: rgba(255,255,255,0.2); }

        .hero-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
        .hero-card {
            background: rgba(255,255,255,0.08); backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 16px;
            padding: 1.75rem; transition: all 0.3s; cursor: default;
        }
        .hero-card:hover { background: rgba(255,255,255,0.12); transform: translateY(-4px); }
        .hero-card .icon {
            width: 50px; height: 50px; border-radius: 12px; display: flex;
            align-items: center; justify-content: center; margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        .hero-card h3 { color: #fff; font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; }
        .hero-card p { color: rgba(255,255,255,0.65); font-size: 0.82rem; line-height: 1.5; }

        /* Sections */
        section { padding: 5rem 0; }
        section .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }
        .section-title { text-align: center; margin-bottom: 3.5rem; }
        .section-title h2 { font-size: 2rem; font-weight: 700; color: var(--dark); margin-bottom: 0.75rem; }
        .section-title p { color: #666; font-size: 1rem; max-width: 600px; margin: 0 auto; }
        .section-title .line { width: 60px; height: 4px; background: var(--primary); border-radius: 2px; margin: 0.75rem auto 0; }

        /* Programmes */
        .programmes-section { background: var(--light); }
        .programme-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        .programme-card {
            background: #fff; border-radius: 16px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: all 0.3s;
        }
        .programme-card:hover { transform: translateY(-5px); box-shadow: 0 12px 35px rgba(0,0,0,0.1); }
        .programme-card .card-header {
            padding: 2rem; color: #fff; position: relative;
        }
        .programme-card.ijmb .card-header { background: linear-gradient(135deg, var(--primary), #008844); }
        .programme-card.remedial .card-header { background: linear-gradient(135deg, var(--accent), #b30000); }
        .programme-card .card-header h3 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
        .programme-card .card-header p { font-size: 0.9rem; opacity: 0.9; }
        .programme-card .card-body { padding: 2rem; }
        .programme-card .card-body ul { list-style: none; padding: 0; }
        .programme-card .card-body ul li {
            padding: 0.6rem 0; display: flex; align-items: center; gap: 0.75rem;
            border-bottom: 1px solid #f0f0f0; font-size: 0.9rem;
        }
        .programme-card .card-body ul li:last-child { border-bottom: none; }
        .programme-card .card-body ul li .material-symbols-outlined { color: var(--primary); font-size: 1.1rem; }

        /* Steps */
        .steps-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; }
        .step-card { text-align: center; padding: 2rem 1.5rem; position: relative; }
        .step-number {
            width: 50px; height: 50px; border-radius: 50%; background: var(--primary);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.25rem; margin: 0 auto 1rem;
        }
        .step-card h3 { font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--dark); }
        .step-card p { font-size: 0.85rem; color: #666; line-height: 1.5; }

        /* Portals */
        .portals-section { background: var(--light); }
        .portal-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .portal-card {
            background: #fff; border-radius: 16px; padding: 2.5rem 2rem;
            text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            transition: all 0.3s; border-top: 4px solid transparent;
        }
        .portal-card:hover { transform: translateY(-5px); box-shadow: 0 12px 35px rgba(0,0,0,0.1); }
        .portal-card:nth-child(1) { border-top-color: var(--primary); }
        .portal-card:nth-child(2) { border-top-color: var(--secondary); }
        .portal-card:nth-child(3) { border-top-color: var(--accent); }
        .portal-card .portal-icon {
            width: 70px; height: 70px; border-radius: 16px; display: flex;
            align-items: center; justify-content: center; margin: 0 auto 1.5rem;
            font-size: 2rem; color: #fff;
        }
        .portal-card:nth-child(1) .portal-icon { background: linear-gradient(135deg, var(--primary), #008844); }
        .portal-card:nth-child(2) .portal-icon { background: linear-gradient(135deg, #e6ac00, var(--secondary)); }
        .portal-card:nth-child(3) .portal-icon { background: linear-gradient(135deg, var(--accent), #b30000); }
        .portal-card h3 { font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--dark); }
        .portal-card p { font-size: 0.875rem; color: #666; line-height: 1.6; margin-bottom: 1.5rem; }
        .portal-card .portal-btn {
            display: inline-block; padding: 0.65rem 2rem; border-radius: 8px;
            font-weight: 600; font-size: 0.875rem; transition: all 0.3s;
        }

        /* Info */
        .info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .info-card {
            background: #fff; border-radius: 12px; padding: 2rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05); text-align: center;
        }
        .info-card .material-symbols-outlined { font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem; }
        .info-card h3 { font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; }
        .info-card p { font-size: 0.85rem; color: #666; line-height: 1.6; }

        /* Footer */
        .main-footer {
            background: var(--dark); color: rgba(255,255,255,0.7); padding: 3rem 0 1.5rem;
        }
        .footer-grid {
            display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 3rem;
            margin-bottom: 2rem;
        }
        .footer-about h3 { color: #fff; font-size: 1.1rem; margin-bottom: 0.75rem; }
        .footer-about p { font-size: 0.85rem; line-height: 1.7; }
        .footer-links h4 { color: #fff; font-size: 0.95rem; margin-bottom: 1rem; }
        .footer-links ul { list-style: none; padding: 0; }
        .footer-links ul li { margin-bottom: 0.5rem; }
        .footer-links ul li a { font-size: 0.85rem; transition: color 0.3s; }
        .footer-links ul li a:hover { color: var(--secondary); }
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;
            text-align: center; font-size: 0.8rem;
        }

        @media (max-width: 768px) {
            .hero .container { grid-template-columns: 1fr; gap: 2rem; text-align: center; }
            .hero-content h2 { font-size: 2rem; }
            .hero-actions { justify-content: center; }
            .hero-cards { grid-template-columns: 1fr 1fr; gap: 0.75rem; justify-items: center; }
            .hero-card { text-align: center; }
            .hero-card .icon { margin: 0 auto 1rem; }
            .programme-cards, .portal-cards { grid-template-columns: 1fr; }
            .steps-grid { grid-template-columns: 1fr 1fr; }
            .info-grid { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
            .nav-links { display: none; }
            .mobile-menu-btn { display: none; }
            .mobile-login-cards { display: flex; gap: 0.75rem; margin-top: 1.5rem; }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">
            <a href="{{ url('/') }}" class="navbar-brand">
                <img src="{{ setting_image('main_logo') ?? url('/assets/images/favicon.png') }}" alt="Logo">
                <div class="brand-text">
                    <h1>{{ setting('site_name', 'SBRS Portal') }}</h1>
                    <span>University of Maiduguri</span>
                </div>
            </a>
            <div class="nav-links">
                <a href="{{ route('applicant.login') }}" class="btn-outline">Applicant Login</a>
                <a href="{{ route('student.login') }}" class="btn-primary-custom">Student Login</a>
                <a href="{{ route('login') }}" class="btn-accent">Admin</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-glow"></div>
        <div class="container">
            <div class="hero-content">
                <h2>
                    Welcome to the<br>
                    <span>School of Basic &<br>Remedial Studies</span>
                </h2>
                <p>
                    {{ setting('hero_description', 'Your gateway to academic excellence at the University of Maiduguri. Apply for IJMB and Remedial programmes and take the first step towards your degree.') }}
                </p>
                <div class="hero-actions">
                    <a href="{{ route('applicant.register') }}" class="btn-gold">
                        <span class="material-symbols-outlined" style="font-size:1.2rem">app_registration</span>
                        Apply Now
                    </a>
                    <a href="#programmes" class="btn-glass">
                        <span class="material-symbols-outlined" style="font-size:1.2rem">info</span>
                        Learn More
                    </a>
                </div>
                <div class="mobile-login-cards">
                    <a href="{{ route('applicant.login') }}" class="m-login-card">
                        <div class="m-icon" style="background:rgba(0,102,51,0.4)"><span class="material-symbols-outlined">person</span></div>
                        <h4>Applicant</h4>
                        <p>Login / Register</p>
                    </a>
                    <a href="{{ route('student.login') }}" class="m-login-card">
                        <div class="m-icon" style="background:rgba(255,215,0,0.3)"><span class="material-symbols-outlined">school</span></div>
                        <h4>Student</h4>
                        <p>Student Portal</p>
                    </a>
                    <a href="{{ route('login') }}" class="m-login-card">
                        <div class="m-icon" style="background:rgba(139,0,0,0.4)"><span class="material-symbols-outlined">admin_panel_settings</span></div>
                        <h4>Staff</h4>
                        <p>Admin Portal</p>
                    </a>
                </div>
            </div>
            <div class="hero-cards">
                <div class="hero-card">
                    <div class="icon" style="background:rgba(255,215,0,0.2)">
                        <span class="material-symbols-outlined" style="color:var(--secondary)">school</span>
                    </div>
                    <h3>IJMB Programme</h3>
                    <p>Interim Joint Matriculation Board programme for direct entry admission into 200 level.</p>
                </div>
                <div class="hero-card">
                    <div class="icon" style="background:rgba(139,0,0,0.2)">
                        <span class="material-symbols-outlined" style="color:#ff6b6b">biotech</span>
                    </div>
                    <h3>Remedial Programme</h3>
                    <p>Pre-degree programme for students seeking admission into science-based courses.</p>
                </div>
                <div class="hero-card">
                    <div class="icon" style="background:rgba(0,102,51,0.2)">
                        <span class="material-symbols-outlined" style="color:#4caf50">verified</span>
                    </div>
                    <h3>Online Application</h3>
                    <p>Complete your application from anywhere. Fast, secure, and paperless process.</p>
                </div>
                <div class="hero-card">
                    <div class="icon" style="background:rgba(33,150,243,0.2)">
                        <span class="material-symbols-outlined" style="color:#2196f3">payments</span>
                    </div>
                    <h3>Secure Payments</h3>
                    <p>Pay fees securely via Remita payment gateway. Get instant confirmation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Programmes Section -->
    <section class="programmes-section" id="programmes">
        <div class="container">
            <div class="section-title">
                <h2>Our Programmes</h2>
                <p>Choose from our accredited programmes designed to prepare you for university education</p>
                <div class="line"></div>
            </div>
            <div class="programme-cards">
                <div class="programme-card ijmb">
                    <div class="card-header">
                        <h3>IJMB Programme</h3>
                        <p>Interim Joint Matriculation Board</p>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li><span class="material-symbols-outlined">check_circle</span> Direct Entry into 200 Level</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Recognized by all Nigerian Universities</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Arts, Social Sciences & Sciences</li>
                            <li><span class="material-symbols-outlined">check_circle</span> 9-month intensive programme</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Experienced lecturers</li>
                            <li><span class="material-symbols-outlined">check_circle</span> O'Level with 5 credits required</li>
                        </ul>
                    </div>
                </div>
                <div class="programme-card remedial">
                    <div class="card-header">
                        <h3>Remedial Programme</h3>
                        <p>Pre-Degree Science Programme</p>
                    </div>
                    <div class="card-body">
                        <ul>
                            <li><span class="material-symbols-outlined">check_circle</span> Admission into 100 Level</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Science-based courses focus</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Medicine, Engineering & Sciences</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Well-equipped laboratories</li>
                            <li><span class="material-symbols-outlined">check_circle</span> Qualified teaching staff</li>
                            <li><span class="material-symbols-outlined">check_circle</span> O'Level with relevant credits</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Application Steps -->
    <section id="how-to-apply">
        <div class="container">
            <div class="section-title">
                <h2>How to Apply</h2>
                <p>Follow these simple steps to complete your application</p>
                <div class="line"></div>
            </div>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h3>Create Account</h3>
                    <p>Register with your email, name and select your preferred programme type (IJMB or Remedial).</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h3>Pay Application Fee</h3>
                    <p>Pay the application fee securely through Remita. You'll receive an RRR for payment.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h3>Fill Application Form</h3>
                    <p>Complete all sections of the application form including personal details, O'Level results, and referees.</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h3>Submit & Await Review</h3>
                    <p>Submit your completed application and wait for review. You'll be notified of the decision.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Portals Section -->
    <section class="portals-section" id="portals">
        <div class="container">
            <div class="section-title">
                <h2>Access Your Portal</h2>
                <p>Select the appropriate portal to access your account</p>
                <div class="line"></div>
            </div>
            <div class="portal-cards">
                <div class="portal-card">
                    <div class="portal-icon">
                        <span class="material-symbols-outlined">person_add</span>
                    </div>
                    <h3>Applicant Portal</h3>
                    <p>New applicants can register, pay fees, fill application forms, and track their admission status.</p>
                    <a href="{{ route('applicant.register') }}" class="portal-btn btn-primary-custom">Register / Login</a>
                </div>
                <div class="portal-card">
                    <div class="portal-icon">
                        <span class="material-symbols-outlined">school</span>
                    </div>
                    <h3>Student Portal</h3>
                    <p>Admitted students can complete registration, register courses, check results, and manage their profile.</p>
                    <a href="{{ route('student.login') }}" class="portal-btn" style="background:var(--secondary);color:var(--dark)">Student Login</a>
                </div>
                <div class="portal-card">
                    <div class="portal-icon">
                        <span class="material-symbols-outlined">admin_panel_settings</span>
                    </div>
                    <h3>Staff Portal</h3>
                    <p>Administrative staff can manage applications, students, results, and system settings.</p>
                    <a href="{{ route('login') }}" class="portal-btn btn-accent">Staff Login</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Info -->
    <section id="contact">
        <div class="container">
            <div class="section-title">
                <h2>Contact Information</h2>
                <p>Reach out to us for enquiries and support</p>
                <div class="line"></div>
            </div>
            <div class="info-grid">
                <div class="info-card">
                    <span class="material-symbols-outlined">location_on</span>
                    <h3>Our Address</h3>
                    <p>{{ setting('contact_address', 'School of Basic & Remedial Studies, University of Maiduguri, Borno State, Nigeria') }}</p>
                </div>
                <div class="info-card">
                    <span class="material-symbols-outlined">mail</span>
                    <h3>Email Us</h3>
                    <p>{{ setting('contact_email', 'sbrs@unimaid.edu.ng') }}</p>
                </div>
                <div class="info-card">
                    <span class="material-symbols-outlined">call</span>
                    <h3>Call Us</h3>
                    <p>{{ setting('contact_phone', '+234 000 000 0000') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container" style="max-width:1200px;margin:0 auto;padding:0 1.5rem;">
            <div class="footer-grid">
                <div class="footer-about">
                    <h3>{{ setting('site_name', 'SBRS Portal') }}</h3>
                    <p>{{ setting('footer_about', 'The School of Basic and Remedial Studies (SBRS) of the University of Maiduguri offers IJMB and Remedial programmes to prepare students for university education.') }}</p>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="#programmes">Programmes</a></li>
                        <li><a href="#how-to-apply">How to Apply</a></li>
                        <li><a href="#portals">Portals</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Portals</h4>
                    <ul>
                        <li><a href="{{ route('applicant.register') }}">Apply Now</a></li>
                        <li><a href="{{ route('applicant.login') }}">Applicant Login</a></li>
                        <li><a href="{{ route('student.login') }}">Student Login</a></li>
                        <li><a href="{{ route('login') }}">Staff Login</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>{{ setting('footer_text', '&copy; ' . date('Y') . ' School of Basic & Remedial Studies, University of Maiduguri. All Rights Reserved.') }}</p>
                @if(setting('footer_powered_by'))
                    <p style="margin-top:0.5rem">Powered by <strong>{{ setting('footer_powered_by') }}</strong></p>
                @endif
            </div>
        </div>
    </footer>
</body>
</html>
