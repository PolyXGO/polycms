@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
<section class="section-lg">
    <div class="container">
        <div class="home-section__header" style="margin-bottom: var(--spacing-4xl);">
            <h1 class="home-section__title">Frequently Asked Questions</h1>
            <p class="home-section__subtitle">Everything you need to know about our services. If you don't find the answer you're looking for, feel free to contact us.</p>
        </div>

        <div style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: var(--spacing-lg);">
            <!-- FAQ Item 1 -->
            <details class="card" style="padding: 0; border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); overflow: hidden; cursor: default;">
                <summary style="padding: var(--spacing-lg); font-weight: var(--font-weight-semibold); cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    How do I get started with the platform?
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div style="padding: 0 var(--spacing-lg) var(--spacing-lg); color: var(--color-gray-600);">
                    Getting started is easy! Simply sign up for a free account, follow our quick configuration guide, and you'll be up and running in less than 10 minutes. 
                </div>
            </details>

            <!-- FAQ Item 2 -->
            <details class="card" style="padding: 0; border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); overflow: hidden; cursor: default;">
                <summary style="padding: var(--spacing-lg); font-weight: var(--font-weight-semibold); cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    Can I change my plan later?
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div style="padding: 0 var(--spacing-lg) var(--spacing-lg); color: var(--color-gray-600);">
                    Yes, you can upgrade or downgrade your plan at any time. Changes will be applied immediately to your account and prorated on your next billing cycle.
                </div>
            </details>

            <!-- FAQ Item 3 -->
            <details class="card" style="padding: 0; border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); overflow: hidden; cursor: default;">
                <summary style="padding: var(--spacing-lg); font-weight: var(--font-weight-semibold); cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    What forms of payment do you accept?
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div style="padding: 0 var(--spacing-lg) var(--spacing-lg); color: var(--color-gray-600);">
                    We accept all major credit cards including Visa, Mastercard, and American Express, as well as PayPal and bank transfers for enterprise customers.
                </div>
            </details>

            <!-- FAQ Item 4 -->
            <details class="card" style="padding: 0; border: 1px solid var(--color-gray-200); border-radius: var(--radius-lg); overflow: hidden; cursor: default;">
                <summary style="padding: var(--spacing-lg); font-weight: var(--font-weight-semibold); cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    Do you offer discounts for educational institutions?
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div style="padding: 0 var(--spacing-lg) var(--spacing-lg); color: var(--color-gray-600);">
                    Absolutely! We offer special pricing for registered non-profits and educational organizations. Please contact our support team for more information.
                </div>
            </details>
        </div>

        <div style="margin-top: var(--spacing-4xl); text-align: center; background: var(--color-gray-50); padding: var(--spacing-2xl); border-radius: var(--radius-xl);">
            <h3 style="margin-bottom: var(--spacing-sm);">Still have questions?</h3>
            <p style="color: var(--color-gray-500); margin-bottom: var(--spacing-xl);">We're here to help you 24/7. Reach out to our friendly support team.</p>
            <a href="{{ url('/contact') }}" class="btn btn-primary">Contact Support</a>
        </div>
    </div>
</section>

<style>
    details summary::-webkit-details-marker {
        display: none;
    }
    details[open] summary svg {
        transform: rotate(180deg);
    }
    details summary {
        transition: background-color 0.2s;
    }
    details summary:hover {
        background-color: var(--color-gray-50);
    }
</style>
@endsection
