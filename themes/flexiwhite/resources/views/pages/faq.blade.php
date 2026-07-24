@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

@section('content')
<section class="section-lg">
    <div class="container">
        <div class="home-section__header" style="margin-bottom: 5rem;">
            <h1 class="home-section__title">Frequently Asked Questions</h1>
            <p class="home-section__subtitle">Everything you need to know about our services. If you don't find the answer you're looking for, feel free to contact us.</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- FAQ Item 1 -->
            <details class="card" style="padding: 0; border: 1px solid var(--geist-accents-3); border-radius: 0.5rem; overflow: hidden; cursor: default;">
                <summary style="padding: 1.5rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    How do I get started with the platform?
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div style="padding: 0 1.5rem 1.5rem; color: var(--geist-accents-5);">
                    Getting started is easy! Simply sign up for a free account, follow our quick configuration guide, and you'll be up and running in less than 10 minutes. 
                </div>
            </details>

            <!-- FAQ Item 2 -->
            <details class="card" style="padding: 0; border: 1px solid var(--geist-accents-3); border-radius: 0.5rem; overflow: hidden; cursor: default;">
                <summary style="padding: 1.5rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    Can I change my plan later?
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div style="padding: 0 1.5rem 1.5rem; color: var(--geist-accents-5);">
                    Yes, you can upgrade or downgrade your plan at any time. Changes will be applied immediately to your account and prorated on your next billing cycle.
                </div>
            </details>

            <!-- FAQ Item 3 -->
            <details class="card" style="padding: 0; border: 1px solid var(--geist-accents-3); border-radius: 0.5rem; overflow: hidden; cursor: default;">
                <summary style="padding: 1.5rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    What forms of payment do you accept?
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div style="padding: 0 1.5rem 1.5rem; color: var(--geist-accents-5);">
                    We accept all major credit cards including Visa, Mastercard, and American Express, as well as PayPal and bank transfers for enterprise customers.
                </div>
            </details>

            <!-- FAQ Item 4 -->
            <details class="card" style="padding: 0; border: 1px solid var(--geist-accents-3); border-radius: 0.5rem; overflow: hidden; cursor: default;">
                <summary style="padding: 1.5rem; font-weight: 600; cursor: pointer; list-style: none; display: flex; justify-content: space-between; align-items: center;">
                    Do you offer discounts for educational institutions?
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </summary>
                <div style="padding: 0 1.5rem 1.5rem; color: var(--geist-accents-5);">
                    Absolutely! We offer special pricing for registered non-profits and educational organizations. Please contact our support team for more information.
                </div>
            </details>
        </div>

        <div style="margin-top: 5rem; text-align: center; background: var(--geist-accents-1); padding: 3rem; border-radius: 1rem;">
            <h3 style="margin-bottom: 0.5rem;">Still have questions?</h3>
            <p style="color: var(--geist-accents-5); margin-bottom: 2rem;">We're here to help you 24/7. Reach out to our friendly support team.</p>
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
        background-color: var(--geist-accents-1);
    }
</style>
@endsection
