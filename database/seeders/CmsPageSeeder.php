<?php

namespace Database\Seeders;

use App\Models\CmsPage;
use App\Traits\CmsPageContentHelper;
use Illuminate\Database\Seeder;

class CmsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    use CmsPageContentHelper;

    public function run()
    {
        $pages = [
            [
                'name' => 'Terms & Conditions',
                'key' => 'terms-conditions',
                'content' => $this->generateTermsContent(),
            ],
            [
                'name' => 'Privacy Policy',
                'key' => 'privacy-policy',
                'content' => $this->generatePrivacyContent(),
            ],
            [
                'name' => 'FAQ',
                'key' => 'faq',
                'content' => $this->generateFaqContent(),
            ],
            [
                'name' => 'How It Works',
                'key' => 'how-it-works',
                'content' => $this->generateHowItWorksContent(),
            ],
        ];

        foreach ($pages as $pageData) {
            CmsPage::updateOrCreate(
                ['key' => $pageData['key']],
                $pageData
            );
        }

        $this->command->info('Common CMS pages seeded successfully!');
    }
}
