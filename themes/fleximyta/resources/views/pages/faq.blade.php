@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
<section class="page-header-section">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-header-title">Help Center</h1>
            <p class="page-header-excerpt">Find answers to common questions about our platform and services.</p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto;">
            <!-- FAQ Group -->
            <div style="margin-bottom: 40px;">
                <h3 style="margin-bottom: 25px; border-left: 4px solid var(--primary); padding-left: 15px;">General Questions</h3>
                
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <details class="card" style="padding: 25px; cursor: pointer; border: 1px solid #eee;">
                        <summary style="font-size: 1.1rem; font-weight: 600; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                            What is FlexiMyTa?
                            <i class="fas fa-chevron-down" style="font-size: 0.9rem; color: var(--primary);"></i>
                        </summary>
                        <div style="margin-top: 15px; color: var(--gray); line-height: 1.7;">
                            FlexiMyTa is a modern, responsive CMS and blogging platform designed for content creators who value speed, SEO, and beautiful design.
                        </div>
                    </details>

                    <details class="card" style="padding: 25px; cursor: pointer; border: 1px solid #eee;">
                        <summary style="font-size: 1.1rem; font-weight: 600; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                            How do I customize my theme?
                            <i class="fas fa-chevron-down" style="font-size: 0.9rem; color: var(--primary);"></i>
                        </summary>
                        <div style="margin-top: 15px; color: var(--gray); line-height: 1.7;">
                            You can customize your theme through the 'Customize' menu in your dashboard. You can change colors, fonts, and layouts in real-time.
                        </div>
                    </details>
                </div>
            </div>

            <!-- FAQ Group 2 -->
            <div>
                <h3 style="margin-bottom: 25px; border-left: 4px solid var(--primary); padding-left: 15px;">Subscription & Billing</h3>
                
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <details class="card" style="padding: 25px; cursor: pointer; border: 1px solid #eee;">
                        <summary style="font-size: 1.1rem; font-weight: 600; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                            Is there a free trial?
                            <i class="fas fa-chevron-down" style="font-size: 0.9rem; color: var(--primary);"></i>
                        </summary>
                        <div style="margin-top: 15px; color: var(--gray); line-height: 1.7;">
                            Yes! We offer a 14-day free trial for all our premium plans. No credit card required to start.
                        </div>
                    </details>

                    <details class="card" style="padding: 25px; cursor: pointer; border: 1px solid #eee;">
                        <summary style="font-size: 1.1rem; font-weight: 600; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                            Can I cancel anytime?
                            <i class="fas fa-chevron-down" style="font-size: 0.9rem; color: var(--primary);"></i>
                        </summary>
                        <div style="margin-top: 15px; color: var(--gray); line-height: 1.7;">
                            Absolutely. You can cancel your subscription at any time from your account settings. You'll continue to have access until the end of your billing period.
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    details summary::-webkit-details-marker {
        display: none;
    }
    details[open] summary i {
        transform: rotate(180deg);
    }
    details[open] {
        border-color: var(--primary) !important;
        box-shadow: 0 10px 30px rgba(67, 97, 238, 0.05);
    }
</style>
@endsection
