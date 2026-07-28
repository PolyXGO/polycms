<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\LayoutAssetManager;
use Illuminate\Support\Str;

/**
 * Core Templates Seeder
 *
 * Registers full industry suites (4 layouts per industry) built from core template parts.
 */
class CoreTemplatesSeeder
{
    public function __construct(
        protected LayoutAssetManager $manager
    ) {}

    public function seed(): void
    {
        $this->seedSaasSuite();
        $this->seedEcommerceSuite();
        $this->seedAgencySuite();
        $this->seedRealEstateSuite();
        $this->seedEducationSuite();
        $this->seedHealthcareSuite();
        $this->seedCorporateSuite();
        $this->seedRestaurantSuite();
    }

    /**
     * Helper to pull all blocks directly from a registered part.
     */
    protected function getPartBlocks(string $partKey): array
    {
        $parts = $this->manager->getRegistered('part');
        if (!isset($parts[$partKey])) {
            return [];
        }

        $contentRaw = $parts[$partKey]['content_raw'] ?? null;
        if (!$contentRaw || empty($contentRaw['content'])) {
            return [];
        }

        $blocks = [];
        foreach ($contentRaw['content'] as $block) {
            if (isset($block['attrs']['id'])) {
                $block['attrs']['id'] = (string) Str::uuid();
            }
            $blocks[] = $block;
        }
        
        return $blocks;
    }

    protected function doc(array $blocksList): array
    {
        $flattened = [];
        foreach ($blocksList as $blocks) {
            foreach ($blocks as $block) {
                $flattened[] = $block;
            }
        }
        return [
            'type' => 'doc',
            'content' => $flattened,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // 1. SaaS & Tech Startup (Category: Marketing)
    // ─────────────────────────────────────────────────────────────────
    protected function seedSaasSuite(): void
    {
        $category = 'marketing';
        
        $this->manager->registerTemplate('saas.homepage', [
            'name' => 'SaaS: Homepage', 'slug' => 'saas-homepage', 'category' => $category,
            'description' => 'High conversion homepage for tech products.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.logo_cloud'), $this->getPartBlocks('core.features_3col'), $this->getPartBlocks('core.testimonials'), $this->getPartBlocks('core.cta_gradient')]),
        ]);
        $this->manager->registerTemplate('saas.sales', [
            'name' => 'SaaS: Sales & Pricing', 'slug' => 'saas-sales', 'category' => $category,
            'description' => 'Convert visitors with clear pricing and comparisons.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_gradient'), $this->getPartBlocks('core.comparison_table'), $this->getPartBlocks('core.pricing_cards'), $this->getPartBlocks('core.faq_section')]),
        ]);
        $this->manager->registerTemplate('saas.features', [
            'name' => 'SaaS: Product Features', 'slug' => 'saas-features', 'category' => $category,
            'description' => 'Deep dive into your software capabilities.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.features_icon_grid'), $this->getPartBlocks('core.demo_showcase'), $this->getPartBlocks('core.cta_newsletter')]),
        ]);
        $this->manager->registerTemplate('saas.about', [
            'name' => 'SaaS: About Us', 'slug' => 'saas-about', 'category' => $category,
            'description' => 'Company profile, team, and stats.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.stats_counter'), $this->getPartBlocks('core.content_2col'), $this->getPartBlocks('core.contact_info')]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 2. E-Commerce (Category: E-Commerce)
    // ─────────────────────────────────────────────────────────────────
    protected function seedEcommerceSuite(): void
    {
        $category = 'ecommerce';

        $this->manager->registerTemplate('ecom.storefront', [
            'name' => 'E-Com: Storefront', 'slug' => 'ecom-storefront', 'category' => $category,
            'description' => 'Main entry point for your online store.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_gradient'), $this->getPartBlocks('core.service_showcase'), $this->getPartBlocks('core.features_icon_grid'), $this->getPartBlocks('core.cta_newsletter')]),
        ]);
        $this->manager->registerTemplate('ecom.product_launch', [
            'name' => 'E-Com: Product Launch', 'slug' => 'ecom-product-launch', 'category' => $category,
            'description' => 'Dedicated landing page for a new product drop.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.benefits_checklist'), $this->getPartBlocks('core.demo_showcase'), $this->getPartBlocks('core.testimonials'), $this->getPartBlocks('core.cta_gradient')]),
        ]);
        $this->manager->registerTemplate('ecom.customer_care', [
            'name' => 'E-Com: Customer Care', 'slug' => 'ecom-customer-care', 'category' => $category,
            'description' => 'FAQ and contact portal for buyers.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.faq_section'), $this->getPartBlocks('core.contact_form')]),
        ]);
        $this->manager->registerTemplate('ecom.brand_story', [
            'name' => 'E-Com: Brand Story', 'slug' => 'ecom-brand-story', 'category' => $category,
            'description' => 'Connect with customers by sharing your origin.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.content_2col'), $this->getPartBlocks('core.stats_counter'), $this->getPartBlocks('core.testimonials')]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 3. Creative Agency (Category: Portfolio)
    // ─────────────────────────────────────────────────────────────────
    protected function seedAgencySuite(): void
    {
        $category = 'portfolio';

        $this->manager->registerTemplate('agency.homepage', [
            'name' => 'Agency: Homepage', 'slug' => 'agency-homepage', 'category' => $category,
            'description' => 'Striking intro for creatives and studios.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.service_showcase'), $this->getPartBlocks('core.stats_counter'), $this->getPartBlocks('core.contact_form')]),
        ]);
        $this->manager->registerTemplate('agency.portfolio', [
            'name' => 'Agency: Portfolio', 'slug' => 'agency-portfolio', 'category' => $category,
            'description' => 'Showcase past projects and clients.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.demo_showcase'), $this->getPartBlocks('core.logo_cloud'), $this->getPartBlocks('core.testimonials')]),
        ]);
        $this->manager->registerTemplate('agency.services', [
            'name' => 'Agency: Service Detail', 'slug' => 'agency-services', 'category' => $category,
            'description' => 'Explain a specific service offering and pricing.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_gradient'), $this->getPartBlocks('core.benefits_checklist'), $this->getPartBlocks('core.pricing_simple'), $this->getPartBlocks('core.cta_gradient')]),
        ]);
        $this->manager->registerTemplate('agency.booking', [
            'name' => 'Agency: Consultation', 'slug' => 'agency-booking', 'category' => $category,
            'description' => 'Dedicated page to book meetings or request quotes.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.contact_info'), $this->getPartBlocks('core.contact_form'), $this->getPartBlocks('core.faq_section')]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 4. Real Estate (Category: Real Estate)
    // ─────────────────────────────────────────────────────────────────
    protected function seedRealEstateSuite(): void
    {
        $category = 'real-estate';

        $this->manager->registerTemplate('realestate.project', [
            'name' => 'Real Estate: Project Home', 'slug' => 'realestate-project', 'category' => $category,
            'description' => 'Main landing page for a property or development project.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.features_3col'), $this->getPartBlocks('core.demo_showcase'), $this->getPartBlocks('core.cta_gradient')]),
        ]);
        $this->manager->registerTemplate('realestate.sales', [
            'name' => 'Real Estate: Open Sale', 'slug' => 'realestate-sales', 'category' => $category,
            'description' => 'Drive urgency for bookings or deposits.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_gradient'), $this->getPartBlocks('core.pricing_simple'), $this->getPartBlocks('core.faq_section'), $this->getPartBlocks('core.contact_form')]),
        ]);
        $this->manager->registerTemplate('realestate.profile', [
            'name' => 'Real Estate: Developer Profile', 'slug' => 'realestate-profile', 'category' => $category,
            'description' => 'Build trust with investor/developer background.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.content_2col'), $this->getPartBlocks('core.stats_counter'), $this->getPartBlocks('core.logo_cloud')]),
        ]);
        $this->manager->registerTemplate('realestate.contact', [
            'name' => 'Real Estate: Site Visit', 'slug' => 'realestate-contact', 'category' => $category,
            'description' => 'Contact layout optimized for scheduling site visits.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.contact_info'), $this->getPartBlocks('core.contact_form')]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 5. Education & Online Course (Category: Education)
    // ─────────────────────────────────────────────────────────────────
    protected function seedEducationSuite(): void
    {
        $category = 'education';

        $this->manager->registerTemplate('edu.center', [
            'name' => 'Edu: Academy Home', 'slug' => 'edu-center', 'category' => $category,
            'description' => 'Homepage for schools, academies, or course platforms.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.service_showcase'), $this->getPartBlocks('core.stats_counter'), $this->getPartBlocks('core.testimonials')]),
        ]);
        $this->manager->registerTemplate('edu.course_sale', [
            'name' => 'Edu: Course Sales Page', 'slug' => 'edu-course-sale', 'category' => $category,
            'description' => 'High-converting layout for a specific course.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_gradient'), $this->getPartBlocks('core.benefits_checklist'), $this->getPartBlocks('core.pricing_cards'), $this->getPartBlocks('core.faq_section'), $this->getPartBlocks('core.cta_gradient')]),
        ]);
        $this->manager->registerTemplate('edu.instructor', [
            'name' => 'Edu: Instructor Profile', 'slug' => 'edu-instructor', 'category' => $category,
            'description' => 'Build authority for teachers and experts.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.content_2col'), $this->getPartBlocks('core.features_icon_grid'), $this->getPartBlocks('core.testimonials')]),
        ]);
        $this->manager->registerTemplate('edu.webinar', [
            'name' => 'Edu: Webinar Registration', 'slug' => 'edu-webinar', 'category' => $category,
            'description' => 'Lead capture for online workshops and webinars.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.benefits_checklist'), $this->getPartBlocks('core.contact_form')]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 6. Healthcare & Clinic (Category: Healthcare)
    // ─────────────────────────────────────────────────────────────────
    protected function seedHealthcareSuite(): void
    {
        $category = 'healthcare';

        $this->manager->registerTemplate('health.clinic', [
            'name' => 'Health: Clinic Homepage', 'slug' => 'health-clinic', 'category' => $category,
            'description' => 'Trust-building homepage for clinics and hospitals.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.service_showcase'), $this->getPartBlocks('core.testimonials'), $this->getPartBlocks('core.contact_info')]),
        ]);
        $this->manager->registerTemplate('health.service', [
            'name' => 'Health: Service Package', 'slug' => 'health-service', 'category' => $category,
            'description' => 'Layout for specific medical packages or screenings.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.benefits_checklist'), $this->getPartBlocks('core.pricing_simple'), $this->getPartBlocks('core.cta_gradient')]),
        ]);
        $this->manager->registerTemplate('health.doctor', [
            'name' => 'Health: Doctor Profile', 'slug' => 'health-doctor', 'category' => $category,
            'description' => 'Showcase specialist expertise and qualifications.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.content_2col'), $this->getPartBlocks('core.faq_section')]),
        ]);
        $this->manager->registerTemplate('health.booking', [
            'name' => 'Health: Appointment Booking', 'slug' => 'health-booking', 'category' => $category,
            'description' => 'Frictionless appointment booking flow.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.contact_info'), $this->getPartBlocks('core.contact_form'), $this->getPartBlocks('core.logo_cloud')]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 7. Corporate B2B (Category: Corporate B2B)
    // ─────────────────────────────────────────────────────────────────
    protected function seedCorporateSuite(): void
    {
        $category = 'corporate';

        $this->manager->registerTemplate('corp.homepage', [
            'name' => 'Corp: B2B Homepage', 'slug' => 'corp-homepage', 'category' => $category,
            'description' => 'Professional corporate hub.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.logo_cloud'), $this->getPartBlocks('core.service_showcase'), $this->getPartBlocks('core.cta_gradient')]),
        ]);
        $this->manager->registerTemplate('corp.solutions', [
            'name' => 'Corp: Enterprise Solutions', 'slug' => 'corp-solutions', 'category' => $category,
            'description' => 'Detailed breakdown of B2B services.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.content_2col'), $this->getPartBlocks('core.benefits_checklist'), $this->getPartBlocks('core.contact_form')]),
        ]);
        $this->manager->registerTemplate('corp.pricing', [
            'name' => 'Corp: Procurement Pricing', 'slug' => 'corp-pricing', 'category' => $category,
            'description' => 'Clear tier-based or module-based pricing.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_gradient'), $this->getPartBlocks('core.pricing_cards'), $this->getPartBlocks('core.comparison_table'), $this->getPartBlocks('core.faq_section')]),
        ]);
        $this->manager->registerTemplate('corp.leadgen', [
            'name' => 'Corp: Whitepaper/LeadGen', 'slug' => 'corp-leadgen', 'category' => $category,
            'description' => 'Optimized for B2B lead capture.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.logo_cloud'), $this->getPartBlocks('core.testimonials'), $this->getPartBlocks('core.cta_newsletter')]),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // 8. F&B / Restaurant (Category: F&B Restaurant)
    // ─────────────────────────────────────────────────────────────────
    protected function seedRestaurantSuite(): void
    {
        $category = 'restaurant';

        $this->manager->registerTemplate('fb.homepage', [
            'name' => 'F&B: Restaurant Home', 'slug' => 'fb-homepage', 'category' => $category,
            'description' => 'Appetizing layout for restaurants and cafes.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.service_showcase'), $this->getPartBlocks('core.testimonials'), $this->getPartBlocks('core.cta_gradient')]),
        ]);
        $this->manager->registerTemplate('fb.offers', [
            'name' => 'F&B: Special Offers', 'slug' => 'fb-offers', 'category' => $category,
            'description' => 'Promote seasonal menus and discounts.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_gradient'), $this->getPartBlocks('core.features_icon_grid'), $this->getPartBlocks('core.cta_newsletter')]),
        ]);
        $this->manager->registerTemplate('fb.story', [
            'name' => 'F&B: Chef & Story', 'slug' => 'fb-story', 'category' => $category,
            'description' => 'Share the culinary journey and atmosphere.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_center'), $this->getPartBlocks('core.content_2col'), $this->getPartBlocks('core.stats_counter'), $this->getPartBlocks('core.demo_showcase')]),
        ]);
        $this->manager->registerTemplate('fb.reservation', [
            'name' => 'F&B: Table Reservation', 'slug' => 'fb-reservation', 'category' => $category,
            'description' => 'Drive bookings and private events.',
            'content_raw' => $this->doc([$this->getPartBlocks('core.hero_split'), $this->getPartBlocks('core.contact_info'), $this->getPartBlocks('core.contact_form'), $this->getPartBlocks('core.faq_section')]),
        ]);
    }
}
