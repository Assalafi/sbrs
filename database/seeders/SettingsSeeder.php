<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['group' => 'general', 'key' => 'site_name', 'value' => 'SBRS Portal', 'type' => 'text', 'description' => 'Site name displayed in header and title'],
            ['group' => 'general', 'key' => 'main_logo', 'value' => null, 'type' => 'image', 'description' => 'Main logo image'],
            ['group' => 'general', 'key' => 'favicon', 'value' => null, 'type' => 'image', 'description' => 'Favicon image'],
            ['group' => 'general', 'key' => 'hero_description', 'value' => 'Your gateway to academic excellence at the University of Maiduguri. Apply for IJMB and Remedial programmes and take the first step towards your degree.', 'type' => 'textarea', 'description' => 'Hero section description on landing page'],

            // SEO & Meta
            ['group' => 'seo', 'key' => 'meta_title', 'value' => 'School of Basic & Remedial Studies - University of Maiduguri', 'type' => 'text', 'description' => 'Default page title for SEO (shown in browser tab and search results)'],
            ['group' => 'seo', 'key' => 'meta_description', 'value' => 'Official portal of the School of Basic and Remedial Studies (SBRS), University of Maiduguri. Apply for IJMB and Remedial programmes online.', 'type' => 'textarea', 'description' => 'Default meta description for search engines (150-160 chars recommended)'],
            ['group' => 'seo', 'key' => 'meta_keywords', 'value' => 'SBRS, University of Maiduguri, IJMB, Remedial, admission, UNIMAID, pre-degree, direct entry', 'type' => 'text', 'description' => 'Comma-separated SEO keywords'],
            ['group' => 'seo', 'key' => 'meta_author', 'value' => 'School of Basic and Remedial Studies, University of Maiduguri', 'type' => 'text', 'description' => 'Author meta tag'],
            ['group' => 'seo', 'key' => 'og_site_name', 'value' => 'SBRS Portal - University of Maiduguri', 'type' => 'text', 'description' => 'Open Graph site name (shown when shared on social media)'],
            ['group' => 'seo', 'key' => 'og_image', 'value' => null, 'type' => 'image', 'description' => 'Social media share image (recommended 1200x630px). Shown when the site link is shared on Facebook, WhatsApp, Twitter, etc.'],
            ['group' => 'seo', 'key' => 'twitter_handle', 'value' => null, 'type' => 'text', 'description' => 'Twitter/X handle (e.g. @unabornostate)'],
            ['group' => 'seo', 'key' => 'google_site_verification', 'value' => null, 'type' => 'text', 'description' => 'Google Search Console verification code (optional)'],

            // Contact
            ['group' => 'contact', 'key' => 'contact_email', 'value' => 'sbrs@unimaid.edu.ng', 'type' => 'text', 'description' => 'Contact email address'],
            ['group' => 'contact', 'key' => 'contact_phone', 'value' => '+234 000 000 0000', 'type' => 'text', 'description' => 'Contact phone number'],
            ['group' => 'contact', 'key' => 'contact_address', 'value' => 'School of Basic & Remedial Studies, University of Maiduguri, Borno State, Nigeria', 'type' => 'textarea', 'description' => 'Physical address'],

            // Application - IJMB
            ['group' => 'ijmb', 'key' => 'ijmb_application_open', 'value' => '1', 'type' => 'boolean', 'description' => 'Whether IJMB applications are currently open'],
            ['group' => 'ijmb', 'key' => 'ijmb_application_deadline', 'value' => null, 'type' => 'text', 'description' => 'IJMB application deadline date'],
            ['group' => 'ijmb', 'key' => 'ijmb_application_fee', 'value' => '5000', 'type' => 'text', 'description' => 'IJMB application fee amount (Naira)'],
            ['group' => 'ijmb', 'key' => 'ijmb_admission_fee', 'value' => '25000', 'type' => 'text', 'description' => 'IJMB admission/acceptance fee amount (Naira)'],
            ['group' => 'ijmb', 'key' => 'ijmb_registration_fee', 'value' => '50000', 'type' => 'text', 'description' => 'IJMB registration fee amount (Naira)'],
            ['group' => 'ijmb', 'key' => 'ijmb_exam_fee', 'value' => '15000', 'type' => 'text', 'description' => 'IJMB examination fee amount (Naira)'],
            ['group' => 'ijmb', 'key' => 'ijmb_instructions', 'value' => null, 'type' => 'textarea', 'description' => 'Special instructions for IJMB applicants'],

            // Application - Remedial
            ['group' => 'remedial', 'key' => 'remedial_application_open', 'value' => '1', 'type' => 'boolean', 'description' => 'Whether Remedial applications are currently open'],
            ['group' => 'remedial', 'key' => 'remedial_application_deadline', 'value' => null, 'type' => 'text', 'description' => 'Remedial application deadline date'],
            ['group' => 'remedial', 'key' => 'remedial_application_fee', 'value' => '5000', 'type' => 'text', 'description' => 'Remedial application fee amount (Naira)'],
            ['group' => 'remedial', 'key' => 'remedial_admission_fee', 'value' => '25000', 'type' => 'text', 'description' => 'Remedial admission/acceptance fee amount (Naira)'],
            ['group' => 'remedial', 'key' => 'remedial_registration_fee', 'value' => '50000', 'type' => 'text', 'description' => 'Remedial registration fee amount (Naira)'],
            ['group' => 'remedial', 'key' => 'remedial_exam_fee', 'value' => '15000', 'type' => 'text', 'description' => 'Remedial examination fee amount (Naira)'],
            ['group' => 'remedial', 'key' => 'remedial_instructions', 'value' => null, 'type' => 'textarea', 'description' => 'Special instructions for Remedial applicants'],

            // Remita Payment Gateway
            ['group' => 'remita', 'key' => 'remita_live', 'value' => '0', 'type' => 'boolean', 'description' => 'Enable live mode (disable for demo/test mode)'],
            ['group' => 'remita', 'key' => 'remita_merchant_id', 'value' => '2547916', 'type' => 'text', 'description' => 'Remita Merchant ID'],
            ['group' => 'remita', 'key' => 'remita_api_key', 'value' => '1946', 'type' => 'text', 'description' => 'Remita API Key'],
            ['group' => 'remita', 'key' => 'remita_public_key', 'value' => 'QzAwMDAyNzEyNTl8MTEwNjE4NjF8OWZjOWYwNmMyZDk3MDRhYWM3YThiOThlNTNjZTE3ZjYxOTY5NDdmZWE1YzU3NDc0ZjE2ZDZjNTg1YWYxNWY3NWM4ZjMzNzZhNjNhZWZlOWQwNmJhNTFkMjIxYTRiMjYzZDkzNGQ3NTUxNDIxYWNlOGY4ZWEyODY3ZjlhNGUwYTY=', 'type' => 'textarea', 'description' => 'Remita Inline Payment Public Key (from Remita dashboard)'],
            ['group' => 'remita', 'key' => 'remita_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Default Remita Service Type ID (used as fallback)'],
            ['group' => 'remita', 'key' => 'remita_ijmb_application_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Service Type ID for IJMB Application Fee'],
            ['group' => 'remita', 'key' => 'remita_ijmb_admission_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Service Type ID for IJMB Admission/Acceptance Fee'],
            ['group' => 'remita', 'key' => 'remita_ijmb_registration_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Service Type ID for IJMB Registration Fee'],
            ['group' => 'remita', 'key' => 'remita_ijmb_exam_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Service Type ID for IJMB Exam Fee'],
            ['group' => 'remita', 'key' => 'remita_remedial_application_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Service Type ID for Remedial Application Fee'],
            ['group' => 'remita', 'key' => 'remita_remedial_admission_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Service Type ID for Remedial Admission/Acceptance Fee'],
            ['group' => 'remita', 'key' => 'remita_remedial_registration_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Service Type ID for Remedial Registration Fee'],
            ['group' => 'remita', 'key' => 'remita_remedial_exam_service_type_id', 'value' => '4430731', 'type' => 'text', 'description' => 'Service Type ID for Remedial Exam Fee'],

            // Auth Page Images
            ['group' => 'appearance', 'key' => 'auth_applicant_login_image', 'value' => null, 'type' => 'image', 'description' => 'Image displayed on the Applicant Login page'],
            ['group' => 'appearance', 'key' => 'auth_applicant_register_image', 'value' => null, 'type' => 'image', 'description' => 'Image displayed on the Applicant Registration page'],
            ['group' => 'appearance', 'key' => 'auth_student_login_image', 'value' => null, 'type' => 'image', 'description' => 'Image displayed on the Student Login page'],
            ['group' => 'appearance', 'key' => 'auth_admin_login_image', 'value' => null, 'type' => 'image', 'description' => 'Image displayed on the Admin/Staff Login page'],

            // Footer
            ['group' => 'footer', 'key' => 'footer_text', 'value' => '© ' . date('Y') . ' School of Basic & Remedial Studies, University of Maiduguri. All Rights Reserved.', 'type' => 'textarea', 'description' => 'Footer copyright text'],
            ['group' => 'footer', 'key' => 'footer_about', 'value' => 'The School of Basic and Remedial Studies (SBRS) of the University of Maiduguri offers IJMB and Remedial programmes to prepare students for university education.', 'type' => 'textarea', 'description' => 'Footer about text'],
            ['group' => 'footer', 'key' => 'footer_powered_by', 'value' => null, 'type' => 'text', 'description' => 'Powered by text in footer'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
