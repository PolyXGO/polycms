@extends('layouts.app')

@section('title', 'Pricing Plans')

@section('content')
<section class="section-lg" style="background: linear-gradient(135deg, var(--geist-accents-1) 0%, var(--geist-background) 100%);">
    <div class="container">
        <div class="home-section__header" style="margin-bottom: 5rem;">
            <h1 class="home-section__title">Simple, Transparent Pricing</h1>
            <p class="home-section__subtitle">Choose the plan that's right for your business. No hidden fees, no surprises.</p>
        </div>

        <div class="pricing-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; align-items: stretch;">
            <!-- Free Plan -->
            <div class="card" style="padding: 3rem; display: flex; flex-direction: column; text-align: center; border: 1px solid var(--geist-accents-3);">
                <h3 style="margin-bottom: 0.5rem;">Starter</h3>
                <p style="color: var(--geist-accents-5); margin-bottom: 2rem;">Perfect for individuals just getting started.</p>
                <div style="margin-bottom: 2rem;">
                    <span style="font-size: 3rem; font-weight: 700;">$0</span>
                    <span style="color: var(--geist-accents-5);">/month</span>
                </div>
                <ul style="list-style: none; padding: 0; margin-bottom: 3rem; flex: 1; text-align: left;">
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" fill="currentColor" class="text-success" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Up to 3 projects
                    </li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" fill="currentColor" class="text-success" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Basic analytics
                    </li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; color: var(--geist-accents-4);">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        Custom domain
                    </li>
                </ul>
                <a href="#" class="btn btn-secondary">Get Started</a>
            </div>

            <!-- Pro Plan -->
            <div class="card" style="padding: 3rem; display: flex; flex-direction: column; text-align: center; border: 2px solid var(--color-primary); position: relative; transform: scale(1.05); z-index: 1; box-shadow: var(--shadow-xl);">
                <div style="position: absolute; top: -14px; left: 50%; transform: translateX(-50%); background: var(--color-primary); color: var(--geist-background); padding: 4px 12px; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">Most Popular</div>
                <h3 style="margin-bottom: 0.5rem;">Professional</h3>
                <p style="color: var(--geist-accents-5); margin-bottom: 2rem;">Best for growing teams and businesses.</p>
                <div style="margin-bottom: 2rem;">
                    <span style="font-size: 3rem; font-weight: 700;">$49</span>
                    <span style="color: var(--geist-accents-5);">/month</span>
                </div>
                <ul style="list-style: none; padding: 0; margin-bottom: 3rem; flex: 1; text-align: left;">
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" fill="currentColor" class="text-success" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Unlimited projects
                    </li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" fill="currentColor" class="text-success" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Advanced analytics
                    </li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" fill="currentColor" class="text-success" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Custom domain
                    </li>
                </ul>
                <a href="#" class="btn btn-primary">Start Free Trial</a>
            </div>

            <!-- Enterprise Plan -->
            <div class="card" style="padding: 3rem; display: flex; flex-direction: column; text-align: center; border: 1px solid var(--geist-accents-3);">
                <h3 style="margin-bottom: 0.5rem;">Enterprise</h3>
                <p style="color: var(--geist-accents-5); margin-bottom: 2rem;">Custom solutions for large scale organizations.</p>
                <div style="margin-bottom: 2rem;">
                    <span style="font-size: 3rem; font-weight: 700;">$99</span>
                    <span style="color: var(--geist-accents-5);">/month</span>
                </div>
                <ul style="list-style: none; padding: 0; margin-bottom: 3rem; flex: 1; text-align: left;">
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" fill="currentColor" class="text-success" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Dedicated support
                    </li>
                    <li style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" fill="currentColor" class="text-success" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        SLA guarantee
                    </li>
                </ul>
                <a href="#" class="btn btn-secondary">Contact Sales</a>
            </div>
        </div>

        <!-- FAQ Preview -->
        <div style="margin-top: 5rem; text-align: center;">
            <p style="color: var(--geist-accents-5);">Need a custom plan? <a href="#" style="color: var(--color-primary); font-weight: 500;">Let's talk.</a></p>
        </div>
    </div>
</section>
@endsection
