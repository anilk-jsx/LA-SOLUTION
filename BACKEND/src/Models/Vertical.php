<?php
/**
 * LA SOLUTION PARTNERSHIP - VERTICALS CONSTANTS
 * Hardcoded configuration of the 8 official ecosystem verticals.
 */

class Vertical {

    /**
     * Canonical 8 Verticals Definition (Hardcoded)
     */
    public const VERTICALS = [
        [
            'id'          => 1,
            'slug'        => 'gramsanjog',
            'name'        => 'Gramsanjog.in',
            'category'    => 'Civic Journalism',
            'color'       => '#1e40af',
            'description' => 'Hyperlocal Rural Media & Grassroots Civic News Platform.',
            'website_url' => 'https://gramsanjog.in'
        ],
        [
            'id'          => 2,
            'slug'        => 'lasa',
            'name'        => 'LASA Services',
            'category'    => 'Hyperlocal Services',
            'color'       => '#0284c7',
            'description' => 'Local Area Service & Assistant for quick doorstep utility bookings.',
            'website_url' => 'https://lasa.co.in'
        ],
        [
            'id'          => 3,
            'slug'        => 'lasolution',
            'name'        => 'LASolution.in',
            'category'    => 'Digital Transformation',
            'color'       => '#15803d',
            'description' => 'Enterprise IT Consulting, Custom Software, and Digital Training.',
            'website_url' => 'https://lasolution.in'
        ],
        [
            'id'          => 4,
            'slug'        => 'ezybilling',
            'name'        => 'EzyBillingApp.in',
            'category'    => 'Cloud Billing SaaS',
            'color'       => '#ea580c',
            'description' => 'Cloud-based POS, GST Invoicing, and Inventory Management.',
            'website_url' => 'https://ezybillingapp.in'
        ],
        [
            'id'          => 5,
            'slug'        => 'political',
            'name'        => 'Political Party Member Support',
            'category'    => 'Campaign Ops & Cadre Tech',
            'color'       => '#dc2626',
            'description' => 'Cadre mobilization, constituency analytics, and digital ground support.',
            'website_url' => null
        ],
        [
            'id'          => 6,
            'slug'        => 'fitnessguru',
            'name'        => 'FitnessGuru.org.in',
            'category'    => 'Health & Gym SaaS',
            'color'       => '#7e22ce',
            'description' => 'Modern gym management, member check-in, and workout planning.',
            'website_url' => 'https://fitnessguru.org.in/'
        ],
        [
            'id'          => 7,
            'slug'        => 'sarpanch',
            'name'        => 'SarpanchPrarthi.com',
            'category'    => 'Rural Governance SaaS',
            'color'       => '#0369a1',
            'description' => 'Panchayat election campaign analytics and citizen welfare tracking.',
            'website_url' => 'https://sarpanchprarthi.com'
        ],
        [
            'id'          => 8,
            'slug'        => 'food-ecommerce',
            'name'        => 'Food Ordering & Ecommerce Platform',
            'category'    => 'Hyperlocal Commerce',
            'color'       => '#0d9488',
            'description' => 'Direct merchant food ordering and multi-vendor delivery engine.',
            'website_url' => null
        ]
    ];

    /**
     * Get all hardcoded verticals
     */
    public static function getAll(): array {
        return self::VERTICALS;
    }
}
