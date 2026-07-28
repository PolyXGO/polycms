import { landingBlockRegistry } from './landingBlockRegistry';
import HeroBlock from '../components/editor/blocks/HeroBlock.vue';
import PricingMatrixBlock from '../components/editor/blocks/PricingMatrixBlock.vue';
import TextImageBlock from '../components/editor/blocks/TextImageBlock.vue';
import DividerBlock from '../components/editor/blocks/DividerBlock.vue';
import VideoBlock from '../components/editor/blocks/VideoBlock.vue';
import HtmlBlock from '../components/editor/blocks/HtmlBlock.vue';
import GalleryBlock from '../components/editor/blocks/GalleryBlock.vue';
import AccordionBlock from '../components/editor/blocks/AccordionBlock.vue';
import TabBlock from '../components/editor/blocks/TabBlock.vue';
import SectionBlock from '../components/editor/blocks/SectionBlock.vue';
import RowBlock from '../components/editor/blocks/RowBlock.vue';
import RowBlockSettings from '../components/editor/blocks/RowBlockSettings.vue';
import WhatYouGetBlock from '../components/editor/blocks/WhatYouGetBlock.vue';
import FeaturesGridBlock from '../components/editor/blocks/FeaturesGridBlock.vue';
import ShowcaseBlock from '../components/editor/blocks/ShowcaseBlock.vue';
import CtaSectionBlock from '../components/editor/blocks/CtaSectionBlock.vue';
import FwCtaSectionBlock from '../components/editor/blocks/FwCtaSectionBlock.vue';
import FwContactFormBlock from '../components/editor/blocks/FwContactFormBlock.vue';
import StatsBarBlock from '../components/editor/blocks/StatsBarBlock.vue';
import LatestPostsBlock from '../components/editor/blocks/LatestPostsBlock.vue';
import LatestProductsBlock from '../components/editor/blocks/LatestProductsBlock.vue';
import ProjectHubRoadmapBlock from '../components/editor/blocks/ProjectHubRoadmapBlock.vue';
import ProjectHubReleaseBannerBlock from '../components/editor/blocks/ProjectHubReleaseBannerBlock.vue';
import ProjectHubChartBlock from '../components/editor/blocks/ProjectHubChartBlock.vue';
import YoutubeGalleryBlock from '../components/editor/blocks/YoutubeGalleryBlock.vue';
import ModalLinkBlock from '../components/editor/blocks/ModalLinkBlock.vue';
import MermaidChartBlock from '../components/editor/blocks/MermaidChartBlock.vue';
import { DEFAULT_DEMO_SHOWCASE_IMAGE } from './landingPatternTemplates';

// Atomic Blocks
import HeadingBlock from '../components/editor/blocks/atomic/HeadingBlock.vue';
import TextBlock from '../components/editor/blocks/atomic/TextBlock.vue';
import ButtonBlock from '../components/editor/blocks/atomic/ButtonBlock.vue';
import ButtonSettings from '../components/editor/panels/blocks/ButtonSettings.vue';
import ImageBlock from '../components/editor/blocks/atomic/ImageBlock.vue';
import SpacerBlock from '../components/editor/blocks/atomic/SpacerBlock.vue';
import TestimonialBlock from '../components/editor/blocks/atomic/TestimonialBlock.vue';
import IconBoxBlock from '../components/editor/blocks/atomic/IconBoxBlock.vue';
import CounterBlock from '../components/editor/blocks/atomic/CounterBlock.vue';

export function registerDefaultLandingBlocks() {
    // Register Hero Block
    landingBlockRegistry.register({
        key: 'hero_section',
        label: 'Hero Section',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" /></svg>`,
        component: HeroBlock,
        defaultAttrs: {
            heading: 'Revolutionize Your Experience',
            subheading: 'Discover the next generation of solutions tailored for your business needs.',
            button_text: 'Get Started',
            button_link: '#',
        }
    });

    // Register Pricing Matrix Block
    landingBlockRegistry.register({
        key: 'pricing_matrix',
        label: 'Pricing Matrix',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>`,
        component: PricingMatrixBlock,
        defaultAttrs: {
            style: 'cards',
        }
    });

    // Register Text with Image Block
    landingBlockRegistry.register({
        key: 'text_image',
        label: 'Text with Image',
        category: 'general',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`,
        component: TextImageBlock,
        defaultAttrs: {
            heading: 'Our Excellence',
            content: 'We provide top-notch services with a focus on quality and innovation. Our team is dedicated to your success.',
            image_url: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
            image_position: 'left',
        }
    });

    // Register Divider Block
    landingBlockRegistry.register({
        key: 'divider',
        label: 'Divider',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>`,
        component: DividerBlock,
        defaultAttrs: {
            color: 'var(--color-border)',
            spacing: 'py-8',
            width: 'full',
            style: 'solid',
        }
    });

    // Register Video Block
    landingBlockRegistry.register({
        key: 'video',
        label: 'Video',
        category: 'marketing',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`,
        component: VideoBlock,
        defaultAttrs: {
            url: '',
            preview_image: '',
            aspect_ratio: '16/9',
            caption: '',
        }
    });

    // Register HTML Block
    landingBlockRegistry.register({
        key: 'html_block',
        label: 'Custom HTML',
        category: 'custom',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>`,
        component: HtmlBlock,
        defaultAttrs: {
            html: '',
            wrap_raw: false,
        }
    });

    // Register Gallery Block
    landingBlockRegistry.register({
        key: 'gallery',
        label: 'Gallery',
        category: 'general',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`,
        component: GalleryBlock,
        defaultAttrs: {
            images: [],
            columns: 3,
            gap: 'gap-4',
            rounded: 'rounded-xl',
        }
    });

    // Register Accordion Block
    landingBlockRegistry.register({
        key: 'accordion',
        label: 'Accordion',
        category: 'general',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>`,
        component: AccordionBlock,
        defaultAttrs: {
            items: [
                { title: 'New Question', content: 'Provide the answer here...', open: false }
            ],
            style: 'standard',
        }
    });

    // Register Tab Block
    landingBlockRegistry.register({
        key: 'tabs',
        label: 'Tabs',
        category: 'general',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 12h10M7 17h10" /></svg>`,
        component: TabBlock,
        defaultAttrs: {
            items: [
                { title: 'Tab 1', content: 'Tab 1 content here...' },
                { title: 'Tab 2', content: 'Tab 2 content here...' }
            ],
            style: 'underline',
            alignment: 'start',
        }
    });

    // Register Section Block
    landingBlockRegistry.register({
        key: 'section',
        label: 'Section',
        category: 'layout',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h14a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z" /></svg>`,
        component: SectionBlock,
        defaultAttrs: {
            bg_color: '',
            bg_image: '',
            padding: 'py-16',
            blocks: [],
        }
    });

    // Register Row Block
    landingBlockRegistry.register({
        key: 'row',
        label: 'Row',
        category: 'layout',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>`,
        component: RowBlock,
        settingsComponent: RowBlockSettings,
        defaultAttrs: {
            columns: 2,
            layout_preset: 'halves',
            column_widths: ['1/2', '1/2'],
            gap: 'gap-8',
            vertical_align: 'start',
            columns_data: [
                { blocks: [] }, { blocks: [] }
            ],
        }
    });

    // Register Atomic Blocks
    landingBlockRegistry.register({
        key: 'heading',
        label: 'Heading',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>`,
        component: HeadingBlock,
        defaultAttrs: {
            text: 'Heading Text',
            level: 2,
            alignment: 'left',
            font_weight: 'font-bold',
            color: ''
        }
    });

    landingBlockRegistry.register({
        key: 'text',
        label: 'Text',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>`,
        component: TextBlock,
        defaultAttrs: {
            content: '',
            font_size: 'text-base',
            alignment: 'left',
            color: ''
        }
    });

    landingBlockRegistry.register({
        key: 'button',
        label: 'Button',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 012-2h10a2 2 0 012 2v0a2 2 0 01-2 2H7a2 2 0 01-2-2v0z" /></svg>`,
        component: ButtonBlock,
        settingsComponent: ButtonSettings,
        defaultAttrs: {
            label: 'Click Here',
            url: '#',
            style: 'primary',
            alignment: 'left'
        }
    });

    landingBlockRegistry.register({
        key: 'image',
        label: 'Image',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`,
        component: ImageBlock,
        defaultAttrs: {
            src: '',
            alt: '',
            width: 'w-full',
            alignment: 'left',
            border_radius: 'rounded-xl'
        }
    });

    landingBlockRegistry.register({
        key: 'spacer',
        label: 'Spacer',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V9a2 2 0 012-2z" /></svg>`,
        component: SpacerBlock,
        defaultAttrs: {
            height: 40
        }
    });

    // Register FlexiBlog Theme Blocks
    landingBlockRegistry.register({
        key: 'what_you_get',
        label: 'What You Get',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>`,
        component: WhatYouGetBlock,
        defaultAttrs: {
            heading: "Here's Exactly What You Get",
            subheading: "A complete invoice SaaS solution that saves you 6+ months of development time",
            button_text: "Tour Our Tool",
            button_link: "/posts",
            features: [
                "Invoice creator with PDF export",
                "Quotation creator",
                "Payment links via Stripe",
                "Client management system",
                "Invoice dashboard + payment history",
                "Payment tracking with status updates",
                "Share via WhatsApp/email",
                "Extended License invoice templates",
                "Your logo, colors, and branding",
                "Custom business email setup",
                "Domain + hosting setup included",
                "Full source code + SQL database",
                "Admin dashboard to manage sales",
                "Custom WordPress website"
            ],
            show_highlight: true,
            highlight_title: "💰 Automated Subscription Payments",
            highlight_text: "We set up automated recurring billing for your SaaS so your customers get charged automatically every month. You get paid on autopilot!",
            banner_title: "After delivery → The entire software is 100% YOURS",
            banner_text: "No recurring fees. No royalties. You own everything and keep 100% of subscription revenue."
        }
    });

    landingBlockRegistry.register({
        key: 'features_grid',
        label: 'Features Grid',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>`,
        component: FeaturesGridBlock,
        defaultAttrs: {
            heading: "Why Choose FlexiBlog",
            subheading: "Everything you need to start your own profitable SaaS business",
            columns: 3,
            features: [
                { icon: 'fas fa-rocket', title: 'Fast Launch', description: 'Go from idea to revenue in 10-14 days instead of 6+ months of development time.' },
                { icon: 'fas fa-money-bill-wave', title: 'Automated Revenue', description: 'Subscription billing is already integrated. Your customers get charged automatically every month.' },
                { icon: 'fas fa-user-tie', title: 'Full White-Label', description: 'Your logo, colors, and domain. Clients will think you built it from scratch.' }
            ]
        }
    });

    landingBlockRegistry.register({
        key: 'showcase',
        label: 'Demo Showcase',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
        component: ShowcaseBlock,
        defaultAttrs: {
            heading: "See It In Action",
            subheading: "Section Description",
            demo_title: "From Zero to SaaS in 10 Days",
            steps: [
                { title: 'Branding & Setup', text: 'We customize everything with your logo and colors.' },
                { title: 'Training & Handover', text: 'We walk you through the admin panel.' }
            ],
            preview_image: DEFAULT_DEMO_SHOWCASE_IMAGE,
            video_link: '',
            image_position: 'right',
            button1_text: 'Start Now',
            button1_url: '#',
            button2_text: 'Learn More',
            button2_url: '#',
        }
    });

    landingBlockRegistry.register({
        key: 'cta_section',
        label: 'CTA Section',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" /></svg>`,
        component: CtaSectionBlock,
        defaultAttrs: {
            heading: "Ready to Launch Your SaaS Business?",
            text: "Stop building from scratch. Get a proven, ready-to-launch invoice SaaS with your branding in days, not months.",
            info_text: "Fill the form below and we'll personally contact you with pricing, demo access, and next steps.",
            form_html: "",
            stats: [
                { number: '10+', label: 'Successful Launches' },
                { number: '100%', label: 'White-Label Ready' },
                { number: '7-10', label: 'Days Delivery' }
            ]
        }
    });

    landingBlockRegistry.register({
        key: 'stats_bar',
        label: 'Stats Bar',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>`,
        component: StatsBarBlock,
        defaultAttrs: {
            stats: [
                { value: '99.9%', label: 'Uptime' },
                { value: '<200ms', label: 'Response Time' },
                { value: '50+', label: 'Hooks & Filters' },
                { value: '100%', label: 'Open Source' }
            ]
        }
    });

    landingBlockRegistry.register({
        key: 'fw_latest_posts',
        label: 'Latest Posts',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>`,
        component: LatestPostsBlock,
        defaultAttrs: {
            heading: "Latest Updates",
            count: 6,
            columns: 3,
            show_view_all: true
        }
    });

    landingBlockRegistry.register({
        key: 'fw_latest_products',
        label: 'Latest Products',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>`,
        component: LatestProductsBlock,
        defaultAttrs: {
            heading: "Featured Products",
            count: 6,
            columns: 3,
            show_view_all: true
        }
    });

    // ── New Atomic Blocks ──────────────────────────────

    landingBlockRegistry.register({
        key: 'testimonial',
        label: 'Testimonial',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>`,
        component: TestimonialBlock,
        defaultAttrs: {
            quote: 'This product changed the way we work. Highly recommended!',
            author_name: 'John Doe',
            author_role: 'CEO, Company',
            avatar_url: '',
            style: 'card',
            rating: 5,
            alignment: 'left',
        }
    });

    landingBlockRegistry.register({
        key: 'icon_box',
        label: 'Icon Box',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>`,
        component: IconBoxBlock,
        defaultAttrs: {
            icon: 'fas fa-star',
            title: 'Feature Title',
            description: 'Describe your feature or benefit here.',
            link_url: '',
            layout: 'centered',
            icon_color: '#ffffff',
            icon_bg: '#4f46e5',
        }
    });

    landingBlockRegistry.register({
        key: 'counter',
        label: 'Counter',
        category: 'atomic',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>`,
        component: CounterBlock,
        defaultAttrs: {
            value: '1000',
            label: 'Happy Customers',
            prefix: '',
            suffix: '+',
            icon: '',
            value_color: '',
            alignment: 'center',
        }
    });

    // ── Theme-prefixed blocks (FlexiWhite) ──────────────────────────────
    // These are independent from core blocks — own component, own schema.

    landingBlockRegistry.register({
        key: 'fw_cta_section',
        label: 'CTA Section (FlexiWhite)',
        category: 'patterns',
        isPattern: true,
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" /></svg>`,
        component: FwCtaSectionBlock,
        defaultAttrs: {
            heading: 'Ready to get started?',
            text: 'Set up your site in minutes. No credit card required.',
            button_text: 'Get Started Free',
            button_url: '/login',
        }
    });

    landingBlockRegistry.register({
        key: 'fw_contact_form',
        label: 'Contact Form',
        category: 'marketing',
        icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>`,
        component: FwContactFormBlock,
        defaultAttrs: {
            form_id: '',
        }
    });

    landingBlockRegistry.register({
        key: 'project_hub_roadmap',
        label: 'Project Roadmap',
        category: 'general',
        icon: `<svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>`,
        component: ProjectHubRoadmapBlock,
        defaultAttrs: {
            product_id: '',
            limit: 5
        }
    });

    landingBlockRegistry.register({
        key: 'project_hub_release_banner',
        label: 'Project Release Banner',
        category: 'general',
        icon: `<svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>`,
        component: ProjectHubReleaseBannerBlock,
        defaultAttrs: {
            product_id: '',
            style: 'text'
        }
    });

    landingBlockRegistry.register({
        key: 'project_hub_chart',
        label: 'Project Pulse Chart',
        category: 'general',
        icon: `<svg class="w-6 h-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>`,
        component: ProjectHubChartBlock,
        defaultAttrs: {
            product_id: ''
        }
    });

    // YouTube Video Gallery Block (from TipTap node view)
    landingBlockRegistry.register({
        key: 'youtubeGallery',
        label: 'YouTube Video Gallery',
        category: 'general',
        icon: `<svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>`,
        component: YoutubeGalleryBlock,
        defaultAttrs: {
            urls: [''],
            layout: 'grid',
            sliderVisibleItems: 1,
            sliderAutoPlay: false,
            sliderContinuous: false,
            sliderDirection: 'left'
        }
    });

    // Modal Link Block (from TipTap node view)
    landingBlockRegistry.register({
        key: 'modalLink',
        label: 'Landing Modal Link',
        category: 'general',
        icon: `<svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>`,
        component: ModalLinkBlock,
        defaultAttrs: {
            labelText: 'Click here',
            modalSize: 'lg',
            contentType: 'html',
            contentHtml: '',
            iframeUrl: '',
            displayMode: 'button'
        }
    });

    // Mermaid Diagram Block (from TipTap node view)
    landingBlockRegistry.register({
        key: 'mermaidChart',
        label: 'Mermaid Diagram',
        category: 'general',
        icon: `<svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" /></svg>`,
        component: MermaidChartBlock,
        defaultAttrs: {
            code: ''
        }
    });
}
