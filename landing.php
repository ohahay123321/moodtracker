<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodTrail - Track Your Emotional Journey</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌈</text></svg>">
</head>
<body>
    <header class="landing-header">
        <nav class="nav-container">
            <a href="login.php" class="logo">
                <span class="logo-icon">🌈</span>
                <span class="logo-text">Mood<span>Trail</span></span>
            </a>
            <div class="nav-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#testimonials">Testimonials</a>
            </div>
            <div class="nav-actions">
                <a href="login.php" class="btn btn-secondary">Sign In</a>
                <a href="register.php" class="btn btn-primary">Get Started</a>
            </div>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-bg">
            <div class="hero-orb orb-1"></div>
            <div class="hero-orb orb-2"></div>
            <div class="hero-orb orb-3"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <span class="hero-badge">✨ Track your emotions daily</span>
                <h1>Understand Your <span class="gradient-text">Emotional Journey</span></h1>
                <p>MoodTrail helps you track, understand, and improve your emotional well-being. Journal your moods, discover patterns, and gain insights through beautiful visualizations.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-primary btn-lg">
                        Start Tracking Free
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="#features" class="btn btn-outline btn-lg">Learn More</a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="phone-mockup">
                    <div class="phone-screen">
                        <div class="mockup-header">
                            <span class="mockup-date">Today</span>
                            <span class="mockup-emoji">😊</span>
                        </div>
                        <div class="mockup-content">
                            <div class="mockup-mood selected">😄</div>
                            <div class="mockup-mood">😊</div>
                            <div class="mockup-mood">😐</div>
                            <div class="mockup-mood">😔</div>
                            <div class="mockup-mood">😢</div>
                        </div>
                        <div class="mockup-chart">
                            <div class="chart-bar" style="height:60%"></div>
                            <div class="chart-bar" style="height:80%"></div>
                            <div class="chart-bar" style="height:45%"></div>
                            <div class="chart-bar" style="height:90%"></div>
                            <div class="chart-bar" style="height:70%"></div>
                            <div class="chart-bar" style="height:85%"></div>
                            <div class="chart-bar" style="height:55%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Features</span>
                <h2>Everything You Need to <span class="gradient-text">Understand Yourself</span></h2>
                <p>Powerful tools to help you track and improve your emotional well-being</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Mood Tracking</h3>
                    <p>Log your emotions daily with our intuitive mood selector. Choose from 8 different emotions and rate their intensity.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>Calendar View</h3>
                    <p>See your mood history at a glance with our interactive calendar. Click any day to view or add entries.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📝</div>
                    <h3>Journal Notes</h3>
                    <p>Add personal notes to each mood entry. Write down what's on your mind and reflect on your feelings.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Private & Secure</h3>
                    <p>Your data is yours alone. We don't share your information. All mood data is stored securely.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Easy to Use</h3>
                    <p>Simple, intuitive interface designed for daily use. Track your mood in seconds, not minutes.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">How It Works</span>
                <h2>Start Tracking in <span class="gradient-text">3 Simple Steps</span></h2>
            </div>
            <div class="steps">
                <div class="step">
                    <div class="step-number">01</div>
                    <h3>Create Account</h3>
                    <p>Sign up for free in seconds. Just enter your email and create a password.</p>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">02</div>
                    <h3>Log Your Mood</h3>
                    <p>Select how you're feeling and add an optional note about your day.</p>
                </div>
                <div class="step-connector"></div>
                <div class="step">
                    <div class="step-number">03</div>
                    <h3>Gain Insights</h3>
                    <p>View your mood patterns over time and understand what affects your well-being.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Testimonials</span>
                <h2>What People Are <span class="gradient-text">Saying</span></h2>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-avatar">👩‍💼</div>
                    <p class="testimonial-text">"MoodTrail has helped me understand my emotional patterns. I never realized how my sleep affects my mood until I saw the data!"</p>
                    <div class="testimonial-author">
                        <strong>Sarah J.</strong>
                        <span>Marketing Manager</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-avatar">👨‍💻</div>
                    <p class="testimonial-text">"Simple yet powerful. I use it every morning to check in with myself. The insights have been life-changing."</p>
                    <div class="testimonial-author">
                        <strong>Michael R.</strong>
                        <span>Software Developer</span>
                    </div>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-avatar">👩‍🎨</div>
                    <p class="testimonial-text">"Finally, a mood tracker that doesn't feel like a chore. The interface is beautiful and logging takes less than 30 seconds."</p>
                    <div class="testimonial-author">
                        <strong>Emma L.</strong>
                        <span>Freelance Designer</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Understand Your Emotions?</h2>
                <p>Join thousands of people who are tracking their moods and improving their well-being.</p>
                <a href="register.php" class="btn btn-primary btn-lg">Get Started for Free</a>
            </div>
        </div>
    </section>

    <footer class="landing-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <span class="logo-icon">🌈</span>
                    <span>Mood<span>Trail</span></span>
                    <p>Track your emotions, understand yourself better.</p>
                </div>
                <div class="footer-links">
                    <div class="footer-column">
                        <h4>Product</h4>
                        <a href="#features">Features</a>
                        <a href="#how-it-works">How It Works</a>
                        <a href="register.php">Sign Up</a>
                    </div>
                    <div class="footer-column">
                        <h4>Account</h4>
                        <a href="login.php">Sign In</a>
                        <a href="register.php">Register</a>
                        <a href="forgot-password.php">Reset Password</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 MoodTrail. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>