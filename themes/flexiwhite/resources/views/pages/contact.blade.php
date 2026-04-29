@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<section class="section-lg">
    <div class="container">
        <div class="home-section__header" style="margin-bottom: var(--spacing-4xl);">
            <h1 class="home-section__title">Get in Touch</h1>
            <p class="home-section__subtitle">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: var(--spacing-4xl); align-items: start;">
            <!-- Contact Form Card -->
            <div class="card" style="padding: var(--spacing-2xl);">
                <form action="#" method="POST" style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-md);">
                        <div style="display: flex; flex-direction: column; gap: var(--spacing-xs);">
                            <label for="name" style="font-weight: var(--font-weight-medium); color: var(--color-gray-700);">Full Name</label>
                            <input type="text" id="name" name="name" placeholder="John Doe" required 
                                style="padding: var(--spacing-sm) var(--spacing-md); border: 1px solid var(--color-gray-300); border-radius: var(--radius-md); font-family: inherit;">
                        </div>
                        <div style="display: flex; flex-direction: column; gap: var(--spacing-xs);">
                            <label for="email" style="font-weight: var(--font-weight-medium); color: var(--color-gray-700);">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="john@example.com" required
                                style="padding: var(--spacing-sm) var(--spacing-md); border: 1px solid var(--color-gray-300); border-radius: var(--radius-md); font-family: inherit;">
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: var(--spacing-xs);">
                        <label for="subject" style="font-weight: var(--font-weight-medium); color: var(--color-gray-700);">Subject</label>
                        <select id="subject" name="subject" 
                            style="padding: var(--spacing-sm) var(--spacing-md); border: 1px solid var(--color-gray-300); border-radius: var(--radius-md); font-family: inherit; background-color: white;">
                            <option value="general">General Inquiry</option>
                            <option value="support">Technical Support</option>
                            <option value="sales">Sales & Pricing</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: var(--spacing-xs);">
                        <label for="message" style="font-weight: var(--font-weight-medium); color: var(--color-gray-700);">Message</label>
                        <textarea id="message" name="message" rows="5" placeholder="How can we help you?" required
                            style="padding: var(--spacing-sm) var(--spacing-md); border: 1px solid var(--color-gray-300); border-radius: var(--radius-md); font-family: inherit; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="align-self: flex-start; margin-top: var(--spacing-sm);">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- Contact Information -->
            <div style="display: flex; flex-direction: column; gap: var(--spacing-2xl);">
                <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
                    <h3 style="margin-bottom: var(--spacing-sm);">Contact Information</h3>
                    
                    <div style="display: flex; align-items: flex-start; gap: var(--spacing-md);">
                        <div style="color: var(--color-primary); margin-top: 4px;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 2px;">Our Office</h4>
                            <p style="color: var(--color-gray-600);">123 Innovation Drive, Suite 100<br>Silicon Valley, CA 94043</p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: var(--spacing-md);">
                        <div style="color: var(--color-primary); margin-top: 4px;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 2px;">Email Us</h4>
                            <p style="color: var(--color-gray-600);">hello@polycms.com<br>support@polycms.com</p>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: var(--spacing-md);">
                        <div style="color: var(--color-primary); margin-top: 4px;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </div>
                        <div>
                            <h4 style="margin-bottom: 2px;">Call Us</h4>
                            <p style="color: var(--color-gray-600);">+1 (555) 123-4567<br>Mon-Fri, 9am - 6pm EST</p>
                        </div>
                    </div>
                </div>

                <div style="background: var(--color-primary-50); padding: var(--spacing-lg); border-radius: var(--radius-lg);">
                    <h4 style="margin-bottom: var(--spacing-xs);">Connect With Us</h4>
                    <p style="color: var(--color-gray-600); margin-bottom: var(--spacing-md); font-size: var(--font-size-sm);">Follow us on social media for the latest updates and news.</p>
                    <div style="display: flex; gap: var(--spacing-md);">
                        <!-- Social Icons Placeholder -->
                        <div style="width: 32px; height: 32px; background: var(--color-primary); border-radius: 50%;"></div>
                        <div style="width: 32px; height: 32px; background: var(--color-primary); border-radius: 50%;"></div>
                        <div style="width: 32px; height: 32px; background: var(--color-primary); border-radius: 50%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
