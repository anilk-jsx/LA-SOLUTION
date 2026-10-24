-- ==============================================================================
-- LA SOLUTION PARTNERSHIP - SEED DATA
-- Default administrator account and baseline CRM inquiries
-- ==============================================================================

-- 1. Insert Default Administrator (Username: admin, Password: Admin@12345)
INSERT INTO `admin_users` (`id`, `username`, `email`, `password_hash`, `full_name`, `role`, `status`)
VALUES (
    1,
    'admin',
    'admin@lasolution.in',
    '$2y$10$rNDbSQ6RNN792qDEp2JYK.ymwVyZ6DbzeQZxRNiwOjFK.CZsY6Ek.',
    'Executive Administrator',
    'superadmin',
    'active'
) ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- 2. Insert Baseline Seed Inquiries (if table is empty)
INSERT IGNORE INTO `inquiries` (`id`, `lead_code`, `name`, `phone`, `email`, `interest_type`, `vertical`, `location`, `message`, `status`, `priority`, `notes`, `created_at`)
VALUES
(1, 'LEAD-101', 'Rajesh Mohanty', '+91 98610 22334', 'rajesh.mohanty@apexfit.in', 'Franchise / Territory Partner', 'FitnessGuru.org.in - Gym Management', 'Bhubaneswar, Odisha', 'We operate 4 gyms in Khordha and want to roll out Fitness Guru QR check-in across all branches.', 'New', 'High', 'Owner of 4 fitness clubs. Requested demo tomorrow morning.', '2026-10-21 09:15:00'),
(2, 'LEAD-102', 'Pooja Sharma', '+91 94370 88991', 'pooja.sharma@gramconnect.org', 'Strategic Business Investor', 'Gramsanjog.in - News & Media Platform', 'Cuttack, Odisha', 'Seeking state-level partnership to onboard 150 women journalists on Gramsanjog platform.', 'In Discussion', 'Urgent', 'Draft MOU shared after state media conclave.', '2026-10-20 16:40:00'),
(3, 'LEAD-103', 'Amitabh Tripathy', '+91 97760 11445', 'atripathy@smartretail.com', 'Franchise / Territory Partner', 'EzyBillingApp.in - Cloud Billing Solution', 'Rourkela, Sundargarh', 'Interested in acting as primary distributor for EzyBillingApp hardware and cloud licenses.', 'Contacted', 'Medium', 'Network of 250+ electronics and grocery shops.', '2026-10-19 11:20:00'),
(4, 'LEAD-104', 'Dr. Satyabrata Nayak', '+91 94391 77332', 'satyabrata.nayak@gramsewa.gov', 'Enterprise Client / Customer', 'SarpanchPrarthi.com - Election Management', 'Puri, Odisha', 'Looking for governance module for 32 Gram Panchayats to track citizen welfare scheme delivery.', 'MOU Signed', 'High', 'Pilot approved. Integration scheduled for next Monday.', '2026-10-18 14:05:00'),
(5, 'LEAD-105', 'Kunal Sahu', '+91 90400 33445', 'kunal@hungryhub.in', 'Technology & Integration', 'Food Ordering & Ecommerce Platform', 'Berhampur, Ganjam', 'Want to integrate 18 cloud kitchen partners into your hyperlocal ordering dispatch system.', 'In Discussion', 'Medium', 'Evaluating rider routing API and POS commission rates.', '2026-10-17 18:30:00'),
(6, 'LEAD-106', 'Debashis Rout', '+91 91240 88219', 'd.rout@orissaservices.com', 'Franchise / Territory Partner', 'LASA - Local Area Service & Assistant', 'Sambalpur, Odisha', 'Interested in city-level master franchise rights for Sambalpur and Bargarh districts.', 'New', 'High', 'Ready for initial deposit and onboarding 100 technicians.', '2026-10-16 10:15:00');
