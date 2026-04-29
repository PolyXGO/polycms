@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<div class="page-header" style="padding: 120px 0 60px; background: var(--gradient); color: white; text-align: center;">
    <div class="container">
        <h1 style="font-size: 3.5rem; margin-bottom: 1rem; color: white;">The Story Behind FlexiMyTa</h1>
        <p style="font-size: 1.2rem; max-width: 700px; margin: 0 auto; opacity: 0.9;">Empowering content creators to share their voice with the world through beautiful design and powerful technology.</p>
    </div>
</div>

<section class="page-content-section">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 50px; align-items: center;">
            <div style="position: relative;">
                <div style="width: 100%; height: 450px; background: rgba(67, 97, 238, 0.05); border-radius: 20px; overflow: hidden; border: 1px solid #f0f2ff; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-quote-left" style="font-size: 80px; color: var(--primary); opacity: 0.1;"></i>
                </div>
                <div style="position: absolute; bottom: -20px; right: -20px; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); max-width: 250px;">
                    <p style="font-style: italic; margin-bottom: 15px; font-size: 14px; color: var(--gray);">"Our mission is to make professional blogging accessible to everyone, regardless of their technical skills."</p>
                    <div style="font-weight: 700; font-size: 14px;">- The Founder</div>
                </div>
            </div>
            
            <div>
                <h3 style="margin-bottom: 20px; font-size: 2rem;">Our Journey</h3>
                <p style="color: var(--gray); margin-bottom: 20px; line-height: 1.8;">FlexiMyTa was born out of a simple observation: most blogging platforms were either too basic or too complicated. We wanted to create a middle ground—a platform that offers enterprise-grade features with the simplicity of a drag-and-drop builder.</p>
                <p style="color: var(--gray); margin-bottom: 30px; line-height: 1.8;">Our team of 15+ experts works tirelessly to ensure that every pixel, every line of code, and every interaction is optimized for performance and user experience. We believe that your content deserves a home that is as beautiful as the ideas you share.</p>
                
                <div style="display: flex; flex-wrap: wrap; gap: 30px;">
                    <div>
                        <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">2024</div>
                        <div style="font-size: 14px; color: var(--gray); font-weight: 600; text-transform: uppercase;">Year Started</div>
                    </div>
                    <div>
                        <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary);">1M+</div>
                        <div style="font-size: 14px; color: var(--gray); font-weight: 600; text-transform: uppercase;">Lines of Code</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values -->
<section style="padding: 80px 0; background: #f8f9ff;">
    <div class="container" style="text-align: center;">
        <h2 style="margin-bottom: 50px;">Our Core Values</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
            <div class="card" style="padding: 40px 30px; text-align: center;">
                <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 20px;"><i class="fas fa-heart"></i></div>
                <h4 style="margin-bottom: 15px;">User First</h4>
                <p style="color: var(--gray); font-size: 15px;">Every feature we build starts with a user's need. We prioritize clarity over complexity.</p>
            </div>
            <div class="card" style="padding: 40px 30px; text-align: center;">
                <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 20px;"><i class="fas fa-rocket"></i></div>
                <h4 style="margin-bottom: 15px;">Performance</h4>
                <p style="color: var(--gray); font-size: 15px;">Speed isn't just a feature; it's a requirement. Our themes are built for lightning-fast loading.</p>
            </div>
            <div class="card" style="padding: 40px 30px; text-align: center;">
                <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 20px;"><i class="fas fa-shield-alt"></i></div>
                <h4 style="margin-bottom: 15px;">Security</h4>
                <p style="color: var(--gray); font-size: 15px;">Your data is your most valuable asset. We use bank-level encryption to keep it safe.</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section style="padding: 100px 0; background: white; text-align: center;">
    <div class="container">
        <h2 style="margin-bottom: 20px; font-size: 2.5rem;">Ready to join our community?</h2>
        <p style="color: var(--gray); margin-bottom: 40px; font-size: 1.1rem;">Join thousands of successful creators who have built their homes on FlexiMyTa.</p>
        <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ url('/contact') }}" class="primary-btn" style="padding: 16px 50px;">Start Your Journey</a>
            <a href="{{ url('/pricing') }}" class="secondary-btn" style="padding: 16px 50px;">View Pricing</a>
        </div>
    </div>
</section>
@endsection
