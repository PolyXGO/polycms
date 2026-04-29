@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<section class="page-header-section">
    <div class="container">
        <div class="page-header-content">
            <h1 class="page-header-title">Let's Connect</h1>
            <p class="page-header-excerpt">Have a question or feedback? We'd love to hear from you. Get in touch with our team today.</p>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 60px;">
            <!-- Form -->
            <div>
                <h3 style="margin-bottom: 30px;">Send us a message</h3>
                <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 25px;">
                    @csrf
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">YOUR NAME</label>
                        <input type="text" name="name" placeholder="Enter your name" required 
                               style="width: 100%; padding: 15px; border: 1px solid #eee; border-radius: 8px; font-family: inherit; background: #fafafa;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">EMAIL ADDRESS</label>
                        <input type="email" name="email" placeholder="example@email.com" required 
                               style="width: 100%; padding: 15px; border: 1px solid #eee; border-radius: 8px; font-family: inherit; background: #fafafa;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">MESSAGE</label>
                        <textarea name="message" rows="6" placeholder="How can we help?" required 
                                  style="width: 100%; padding: 15px; border: 1px solid #eee; border-radius: 8px; font-family: inherit; background: #fafafa; resize: none;"></textarea>
                    </div>
                    <button type="submit" class="primary-btn" style="width: fit-content; padding: 16px 40px;">Send Message</button>
                </form>
            </div>

            <!-- Info -->
            <div style="display: flex; flex-direction: column; gap: 40px;">
                <div>
                    <h3 style="margin-bottom: 30px;">Contact Information</h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 25px;">
                        <div style="display: flex; gap: 20px;">
                            <div style="width: 45px; height: 45px; background: rgba(67, 97, 238, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h5 style="margin-bottom: 5px;">Headquarters</h5>
                                <p style="color: var(--gray); font-size: 14px; margin: 0;">123 Creative Studio, Art District<br>Innovation City, 10110</p>
                            </div>
                        </div>

                        <div style="display: flex; gap: 20px;">
                            <div style="width: 45px; height: 45px; background: rgba(67, 97, 238, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h5 style="margin-bottom: 5px;">Support</h5>
                                <p style="color: var(--gray); font-size: 14px; margin: 0;">support@fleximyta.com<br>info@fleximyta.com</p>
                            </div>
                        </div>

                        <div style="display: flex; gap: 20px;">
                            <div style="width: 45px; height: 45px; background: rgba(67, 97, 238, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h5 style="margin-bottom: 5px;">Phone</h5>
                                <p style="color: var(--gray); font-size: 14px; margin: 0;">+1 234 567 890<br>Available 24/7</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="padding: 30px; background: var(--gradient); border-radius: 12px; color: white;">
                    <h4 style="margin-bottom: 10px; color: white;">Follow Our Story</h4>
                    <p style="font-size: 14px; opacity: 0.9; margin-bottom: 20px;">Join our community of over 10,000+ creators and stay inspired.</p>
                    <div style="display: flex; gap: 15px;">
                        <a href="#" style="color: white; font-size: 20px;"><i class="fab fa-twitter"></i></a>
                        <a href="#" style="color: white; font-size: 20px;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="color: white; font-size: 20px;"><i class="fab fa-linkedin"></i></a>
                        <a href="#" style="color: white; font-size: 20px;"><i class="fab fa-facebook"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
