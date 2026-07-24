@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<!-- Hero Section -->
<section class="section-lg" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary) 100%); color: var(--geist-background); text-align: center;">
    <div class="container">
        <h1 style="color: var(--geist-background); margin-bottom: 1rem;">Building the Future of CMS</h1>
        <p style="font-size: 1.125rem; opacity: 0.9;">We are a team of passionate developers and designers dedicated to creating the most powerful and intuitive content management experience on the market.</p>
    </div>
</section>

<!-- Our Story -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 5rem; align-items: center;">
            <div>
                <h2 style="margin-bottom: 1.5rem;">Our Story</h2>
                <p style="color: var(--geist-accents-5); margin-bottom: 1rem;">Founded in 2024, PolyCMS started with a simple vision: to bridge the gap between powerful enterprise features and user-friendly design. We believed that managing complex websites shouldn't require complex tools.</p>
                <p style="color: var(--geist-accents-5);">Today, we help thousands of businesses around the world deliver exceptional digital experiences to their customers, powered by our cutting-edge technology and commitment to excellence.</p>
            </div>
            <div style="background: var(--geist-accents-2); border-radius: 1rem; height: 400px; display: flex; align-items: center; justify-content: center; color: var(--geist-accents-4);">
                <svg width="64" height="64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
            </div>
        </div>
    </div>
</section>

<!-- Stats / Mission -->
<section class="section" style="background: var(--geist-accents-1);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 3rem; text-align: center;">
            <div>
                <div style="font-size: 3rem; font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">2024</div>
                <div style="color: var(--geist-accents-5); font-weight: 500;">Founded</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">10k+</div>
                <div style="color: var(--geist-accents-5); font-weight: 500;">Users</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">50+</div>
                <div style="color: var(--geist-accents-5); font-weight: 500;">Themes</div>
            </div>
            <div>
                <div style="font-size: 3rem; font-weight: 700; color: var(--color-primary); margin-bottom: 5px;">99.9%</div>
                <div style="color: var(--geist-accents-5); font-weight: 500;">Uptime</div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="section">
    <div class="container">
        <div class="home-section__header" style="margin-bottom: 5rem;">
            <h2 class="home-section__title">Meet Our Team</h2>
            <p class="home-section__subtitle">The talented people behind the platform.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <!-- Team Member 1 -->
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="width: 120px; height: 120px; background: var(--geist-accents-3); border-radius: 50%; margin: 0 auto 1.5rem;"></div>
                <h4 style="margin-bottom: 2px;">Alex Johnson</h4>
                <p style="color: var(--color-primary); font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem;">CEO & Founder</p>
                <p style="color: var(--geist-accents-5); font-size: 0.875rem;">Visionary leader with 15+ years in tech.</p>
            </div>
            <!-- Team Member 2 -->
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="width: 120px; height: 120px; background: var(--geist-accents-3); border-radius: 50%; margin: 0 auto 1.5rem;"></div>
                <h4 style="margin-bottom: 2px;">Sarah Chen</h4>
                <p style="color: var(--color-primary); font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem;">Lead Designer</p>
                <p style="color: var(--geist-accents-5); font-size: 0.875rem;">Passionate about clean and minimal UI.</p>
            </div>
            <!-- Team Member 3 -->
            <div class="card" style="text-align: center; padding: 2rem;">
                <div style="width: 120px; height: 120px; background: var(--geist-accents-3); border-radius: 50%; margin: 0 auto 1.5rem;"></div>
                <h4 style="margin-bottom: 2px;">Marcus Tye</h4>
                <p style="color: var(--color-primary); font-size: 0.875rem; font-weight: 500; margin-bottom: 1rem;">CTO</p>
                <p style="color: var(--geist-accents-5); font-size: 0.875rem;">Master of architecture and performance.</p>
            </div>
        </div>
    </div>
</section>
@endsection
