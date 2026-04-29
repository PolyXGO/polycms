@extends('layouts.app')

@section('title', 'Pricing Plans')

@section('content')
<section class="page-header-section">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-header-title">Choose Your Plan</h1>
            <p class="page-header-excerpt">Upgrade your blogging experience with our premium features and unlimited storage.</p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px;">
            <!-- Starter Plan -->
            <div class="card" style="text-align: center; padding: 40px 30px; border: 1px solid #eee;">
                <div style="font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">Free</div>
                <h3 style="font-size: 2rem; margin-bottom: 10px;">Starter</h3>
                <div style="font-size: 3rem; font-weight: 800; color: var(--primary); margin-bottom: 30px;">$0 <span style="font-size: 1rem; color: var(--gray); font-weight: 400;">/mo</span></div>
                
                <ul style="list-style: none; padding: 0; margin-bottom: 40px; text-align: left;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> Up to 5 posts per month</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> Basic templates</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> 1GB Storage</li>
                    <li style="padding: 10px 0; color: #ccc;"><i class="fas fa-times" style="margin-right: 10px;"></i> No custom domain</li>
                </ul>
                
                <a href="#" class="secondary-btn" style="width: 100%;">Get Started</a>
            </div>

            <!-- Pro Plan -->
            <div class="card" style="text-align: center; padding: 50px 30px; border: 2px solid var(--primary); position: relative; transform: translateY(-20px); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.1);">
                <div style="position: absolute; top: -15px; left: 50%; transform: translateX(-50%); background: var(--gradient); color: white; padding: 5px 20px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap;">MOST POPULAR</div>
                <div style="font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">Monthly</div>
                <h3 style="font-size: 2.5rem; margin-bottom: 10px;">Content Creator</h3>
                <div style="font-size: 3.5rem; font-weight: 800; color: var(--primary); margin-bottom: 30px;">$19 <span style="font-size: 1rem; color: var(--gray); font-weight: 400;">/mo</span></div>
                
                <ul style="list-style: none; padding: 0; margin-bottom: 40px; text-align: left;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> Unlimited posts</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> Premium templates</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> 50GB Storage</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> Custom domain</li>
                </ul>
                
                <a href="#" class="primary-btn" style="width: 100%;">Subscribe Now</a>
            </div>

            <!-- Business Plan -->
            <div class="card" style="text-align: center; padding: 40px 30px; border: 1px solid #eee;">
                <div style="font-weight: 700; color: var(--gray); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">Annual</div>
                <h3 style="font-size: 2rem; margin-bottom: 10px;">Agency</h3>
                <div style="font-size: 3rem; font-weight: 800; color: var(--primary); margin-bottom: 30px;">$49 <span style="font-size: 1rem; color: var(--gray); font-weight: 400;">/mo</span></div>
                
                <ul style="list-style: none; padding: 0; margin-bottom: 40px; text-align: left;">
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> All Pro features</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> Multi-user accounts</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> Priority support</li>
                    <li style="padding: 10px 0; border-bottom: 1px solid #f8f9fa;"><i class="fas fa-check" style="color: #4cc9f0; margin-right: 10px;"></i> Advanced SEO tools</li>
                </ul>
                
                <a href="#" class="secondary-btn" style="width: 100%;">Go Enterprise</a>
            </div>
        </div>
    </div>
</section>
@endsection
