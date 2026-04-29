@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<!-- Hero Section -->
<section class="section-lg" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%); color: white; text-align: center;">
    <div class="container">
        <h1 style="color: white; margin-bottom: var(--spacing-md);">Building the Future of CMS</h1>
        <p style="font-size: var(--font-size-lg); max-width: 800px; margin: 0 auto; opacity: 0.9;">We are a team of passionate developers and designers dedicated to creating the most powerful and intuitive content management experience on the market.</p>
    </div>
</section>

<!-- Our Story -->
<section class="section">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: var(--spacing-4xl); align-items: center;">
            <div>
                <h2 style="margin-bottom: var(--spacing-lg);">Our Story</h2>
                <p style="color: var(--color-gray-600); margin-bottom: var(--spacing-md);">Founded in 2024, PolyCMS started with a simple vision: to bridge the gap between powerful enterprise features and user-friendly design. We believed that managing complex websites shouldn't require complex tools.</p>
                <p style="color: var(--color-gray-600);">Today, we help thousands of businesses around the world deliver exceptional digital experiences to their customers, powered by our cutting-edge technology and commitment to excellence.</p>
            </div>
            <div style="background: var(--color-gray-100); border-radius: var(--radius-xl); height: 400px; display: flex; align-items: center; justify-content: center; color: var(--color-gray-400);">
                <svg width="64" height="64" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>
            </div>
        </div>
    </div>
</section>

<!-- Stats / Mission -->
<section class="section" style="background: var(--color-primary-50);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-2xl); text-align: center;">
            <div>
                <div style="font-size: var(--font-size-5xl); font-weight: var(--font-weight-bold); color: var(--color-primary); margin-bottom: 5px;">2024</div>
                <div style="color: var(--color-gray-600); font-weight: var(--font-weight-medium);">Founded</div>
            </div>
            <div>
                <div style="font-size: var(--font-size-5xl); font-weight: var(--font-weight-bold); color: var(--color-primary); margin-bottom: 5px;">10k+</div>
                <div style="color: var(--color-gray-600); font-weight: var(--font-weight-medium);">Users</div>
            </div>
            <div>
                <div style="font-size: var(--font-size-5xl); font-weight: var(--font-weight-bold); color: var(--color-primary); margin-bottom: 5px;">50+</div>
                <div style="color: var(--color-gray-600); font-weight: var(--font-weight-medium);">Themes</div>
            </div>
            <div>
                <div style="font-size: var(--font-size-5xl); font-weight: var(--font-weight-bold); color: var(--color-primary); margin-bottom: 5px;">99.9%</div>
                <div style="color: var(--color-gray-600); font-weight: var(--font-weight-medium);">Uptime</div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="section">
    <div class="container">
        <div class="home-section__header" style="margin-bottom: var(--spacing-4xl);">
            <h2 class="home-section__title">Meet Our Team</h2>
            <p class="home-section__subtitle">The talented people behind the platform.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-xl);">
            <!-- Team Member 1 -->
            <div class="card" style="text-align: center; padding: var(--spacing-xl);">
                <div style="width: 120px; height: 120px; background: var(--color-gray-200); border-radius: 50%; margin: 0 auto var(--spacing-lg);"></div>
                <h4 style="margin-bottom: 2px;">Alex Johnson</h4>
                <p style="color: var(--color-primary); font-size: var(--font-size-sm); font-weight: var(--font-weight-medium); margin-bottom: var(--spacing-md);">CEO & Founder</p>
                <p style="color: var(--color-gray-500); font-size: var(--font-size-sm);">Visionary leader with 15+ years in tech.</p>
            </div>
            <!-- Team Member 2 -->
            <div class="card" style="text-align: center; padding: var(--spacing-xl);">
                <div style="width: 120px; height: 120px; background: var(--color-gray-200); border-radius: 50%; margin: 0 auto var(--spacing-lg);"></div>
                <h4 style="margin-bottom: 2px;">Sarah Chen</h4>
                <p style="color: var(--color-primary); font-size: var(--font-size-sm); font-weight: var(--font-weight-medium); margin-bottom: var(--spacing-md);">Lead Designer</p>
                <p style="color: var(--color-gray-500); font-size: var(--font-size-sm);">Passionate about clean and minimal UI.</p>
            </div>
            <!-- Team Member 3 -->
            <div class="card" style="text-align: center; padding: var(--spacing-xl);">
                <div style="width: 120px; height: 120px; background: var(--color-gray-200); border-radius: 50%; margin: 0 auto var(--spacing-lg);"></div>
                <h4 style="margin-bottom: 2px;">Marcus Tye</h4>
                <p style="color: var(--color-primary); font-size: var(--font-size-sm); font-weight: var(--font-weight-medium); margin-bottom: var(--spacing-md);">CTO</p>
                <p style="color: var(--color-gray-500); font-size: var(--font-size-sm);">Master of architecture and performance.</p>
            </div>
        </div>
    </div>
</section>
@endsection
