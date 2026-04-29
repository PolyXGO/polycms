@extends('layouts.app')

@section('title', $site_title ?? config('app.name', 'FlexiMyTa'))

@section('content')
<!-- Hero Section -->
<section class="hero" id="home">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Launch Your Own <span class="highlight">White-Label</span> Invoice SaaS in 10 Days</h1>
                <p>Skip 6 months of development. We give you a complete, ready-to-launch invoice SaaS with your branding. Start earning subscription revenue immediately.</p>
                
                <div class="hero-buttons">
                    <a href="#pricing" class="primary-btn">Start Your SaaS</a>
                    <a href="#demo" class="secondary-btn">Watch Demo</a>
                </div>
                
                <div class="hero-features">
                    <div class="hero-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Full Source Code Included</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>100% White-Label Ready</span>
                    </div>
                    <div class="hero-feature">
                        <i class="fas fa-check-circle"></i>
                        <span>Setup in 7-10 Days</span>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Invoice SaaS Dashboard">
            </div>
        </div>
    </div>
</section>

<!-- What You Get Section -->
<section class="what-you-get" id="what-you-get">
    <div class="container">
            <div class="get-title">
                <h2>Here's Exactly What You Get</h2>
                <p>A complete invoice SaaS solution that saves you 6+ months of development time</p>
            </div>
            <div class="hero-buttons">
                <a href="{{ url('/posts') }}" target="_blank" class="primary-btn">Tour Our Tool</a>
            </div>
            &nbsp;
            <div class="features-list">
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Invoice creator with PDF export</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Quotation creator</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Payment links via Stripe</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Client management system</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Invoice dashboard + payment history</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Payment tracking with status updates</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Share via WhatsApp/email</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Extended License invoice templates</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Your logo, colors, and branding</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Custom business email setup</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Domain + hosting setup included</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Full source code + SQL database</strong>
                    </div>
                </div>
                
                <!-- Subscription Billing Highlight -->
                <div class="subscription-highlight">
                    <h4>💰 Automated Subscription Payments</h4>
                    <p>We set up <strong>automated recurring billing</strong> for your SaaS so your customers get charged automatically every month. You get paid on autopilot!</p>
                </div>
                
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Admin dashboard to manage sales, refunds, and customer data</strong>
                    </div>
                </div>
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Custom WordPress website for your SaaS homepage</strong>
                    </div>
                </div>
            </div>
            
            <div class="ownership-banner">
                <h3>After delivery → The entire software is 100% YOURS</h3>
                <p>No recurring fees. No royalties. You own everything and keep 100% of subscription revenue.</p>
            </div>
    </div>
</section>

<!-- Features Section -->
<section class="features" id="features">
    <div class="container">
        <div class="section-title">
            <h2>Why Choose FlexiMyTa</h2>
            <p>Everything you need to start your own profitable SaaS business</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h3>Fast Launch</h3>
                <p>Go from idea to revenue in 10-14 days instead of 6+ months of development time.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3>Automated Revenue</h3>
                <p>Subscription billing is already integrated. Your customers get charged automatically every month.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3>Full White-Label</h3>
                <p>Your logo, colors, and domain. Clients will think you built it from scratch.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section class="pricing" id="pricing">
    <div class="container">
        <div class="section-title">
            <h2>Simple, Transparent Pricing</h2>
            <p>One-time payment. No hidden fees. Everything you need to start your SaaS business.</p>
        </div>
        <div class="pricing-cards">
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3>Standard License</h3>
                    <div class="price"><a href="#contact" class="primary-btn" style="display:inline-block;margin-top:10px;">Contact for Pricing</a></div>
                    <p>One-time payment</p>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Complete Invoice SaaS</li>
                    <li><i class="fas fa-check"></i> Full Source Code + SQL</li>
                    <li><i class="fas fa-check"></i> Your Branding & Domain</li>
                    <li><i class="fas fa-check"></i> Hosting Setup</li>
                    <li><i class="fas fa-check"></i> 30 Days Basic Support</li>
                </ul>
                <a href="#contact" class="primary-btn" style="margin-top:15px;display:inline-block;">Contact for Pricing</a>
            </div>
            
            <div class="pricing-card featured">
                <div class="pricing-header">
                    <h3>Extended License</h3>
                    <div class="price"></div>
                    <p>Most Popular</p>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Everything in Standard License</li>
                    <li><i class="fas fa-check"></i> Custom Business Email</li>
                    <li><i class="fas fa-check"></i> WordPress Marketing Site</li>
                    <li><i class="fas fa-check"></i> 6 Months Priority Support</li>
                    <li><i class="fas fa-check"></i> Marketing Materials Pack</li>
                    <li><i class="fas fa-check"></i> Go-to-Market Strategy Guide</li>
                </ul>
                <a href="#contact" class="primary-btn" style="margin-top:15px;display:inline-block;">Contact for Pricing</a>
            </div>
            
            <div class="pricing-card">
                <div class="pricing-header">
                    <h3>White Label License</h3>
                    <div class="price"></div>
                    <p>For Serious Entrepreneurs</p>
                </div>
                <ul class="pricing-features">
                    <li><i class="fas fa-check"></i> Everything in Extended License</li>
                    <li><i class="fas fa-check"></i> Custom Feature Development</li>
                    <li><i class="fas fa-check"></i> 1 Year Premium Support</li>
                    <li><i class="fas fa-check"></i> Dedicated Setup Assistance</li>
                    <li><i class="fas fa-check"></i> Sales & Marketing Consultation</li>
                </ul>
                <a href="#contact" class="primary-btn" style="margin-top:15px;display:inline-block;">Contact for Pricing</a>
            </div>
        </div>
        <div style="text-align: center; margin-top: 35px; color: var(--gray); font-size: 15px;">
            <p><i class="fas fa-sync-alt"></i> All plans include 7-10 day delivery • 100% White-Label • Full Ownership</p>
        </div>
    </div>
</section>

<!-- Demo Section -->
<section class="demo" id="demo">
    <div class="container">
        <div class="section-title">
            <h2>See It In Action</h2>
            <p>Watch how you can launch your own invoice SaaS business in days.</p>
        </div>
        <div class="demo-content">
            <div class="demo-text">
                <h3 style="font-size: 30px; margin-bottom: 18px;">From Zero to SaaS in 10 Days</h3>
                <p style="margin-bottom: 20px; font-size: 16px;">Our complete white-label solution includes everything you see here, customized with your branding.</p>
                <div style="margin-bottom: 28px;">
                    <div style="display: flex; align-items: center; margin-bottom: 14px;">
                        <div style="width: 28px; height: 28px; background: var(--gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 14px; font-size: 14px;">1</div>
                        <div>
                            <h4 style="margin-bottom: 5px; font-size: 17px;">Branding & Setup</h4>
                            <p style="color: var(--gray); font-size: 14px;">We customize everything with your logo, colors, and domain.</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; margin-bottom: 14px;">
                        <div style="width: 28px; height: 28px; background: var(--gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 14px; font-size: 14px;">2</div>
                        <div>
                            <h4 style="margin-bottom: 5px; font-size: 17px;">Training & Handover</h4>
                            <p style="color: var(--gray); font-size: 14px;">We walk you through the admin panel and how to manage your SaaS.</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <div style="width: 28px; height: 28px; background: var(--gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; margin-right: 14px; font-size: 14px;">3</div>
                        <div>
                            <h4 style="margin-bottom: 5px; font-size: 17px;">Launch & Scale</h4>
                            <p style="color: var(--gray); font-size: 14px;">Start selling subscriptions to businesses in your niche.</p>
                        </div>
                    </div>
                </div>
                <a href="#contact" class="primary-btn">Schedule a Live Demo</a>
            </div>
            <div class="demo-video">
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Dashboard Preview">
                <div class="play-button" id="playDemo">
                    <i class="fas fa-play"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="faq" id="faq">
    <div class="container">
        <div class="section-title">
            <h2>Frequently Asked Questions</h2>
            <p>Get answers to common questions about our white-label invoice SaaS.</p>
        </div>
        <div class="faq-container">
            <div class="faq-item">
                <div class="faq-question">
                    <span>Do I own the source code?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes! After purchase, you receive 100% ownership of the complete source code and SQL database. You can modify, resell subscriptions, and host it anywhere.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>How does the subscription billing work?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>We set up Stripe for you. Your customers enter their payment details once, then get charged automatically every month. You get paid on autopilot with a beautiful admin dashboard to manage all subscriptions.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>How long does setup take?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Typically 7-10 days from payment to delivery. This includes branding customization, domain setup, hosting configuration, and training.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>Can I resell this to multiple clients?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes! You can either: 1) Create your own SaaS business and sell subscriptions, or 2) White-label it for individual clients (one installation per client).</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>What support do you provide?</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    <p>All plans include setup support. Extended License includes 6 months of priority support for bugs and technical issues. We also offer training on how to use and sell the software.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-section" id="contact">
    <div class="container">
        <h2>Ready to Launch Your SaaS Business?</h2>
        <p>Stop building from scratch. Get a proven, ready-to-launch invoice SaaS with your branding in days, not months.</p>
        
        <p style="font-size:16px; opacity:0.95; margin-bottom:20px; text-align:center;">
            Fill the form below and we'll personally contact you with pricing, demo access, and next steps.
        </p>
        
        <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 25px; color:#212529">
            iframe Subscribe form
        </div>

        <div class="stats">
            <div class="stat-item">
                <div class="stat-number">10+</div>
                <div>Successful Launches</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">100%</div>
                <div>White-Label Ready</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">7-10</div>
                <div>Days Delivery</div>
            </div>
        </div>
    </div>
</section>
@endsection
