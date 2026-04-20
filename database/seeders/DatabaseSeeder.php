<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;

class DatabaseSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->prepareRun();

        $this->call([
            UserSeeder::class,
            LanguageSeeder::class,
            PageSeeder::class,
            BlogSeeder::class,
            // GallerySeeder::class,  // Commented out - missing Gallery model
            // ContactSeeder::class,  // Commented out - may have missing dependencies
            // WidgetSeeder::class,   // Commented out - may have missing dependencies
            ThemeOptionSeeder::class,
            SettingSeeder::class,
            LocationSeeder::class,
            CareerLevelSeeder::class,
            DegreeLevelSeeder::class,
            DegreeTypeSeeder::class,
            FunctionalAreaSeeder::class,
            JobCategorySeeder::class,
            JobExperienceSeeder::class,
            JobShiftSeeder::class,
            JobSkillSeeder::class,
            JobTypeSeeder::class,
            CompanySeeder::class,
            LanguageLevelSeeder::class,
            JobSeeder::class,
            CurrencySeeder::class,
            AccountSeeder::class,
            PackageSeeder::class,
            // ReviewSeeder::class,   // Commented out - may have missing dependencies
            TeamSeeder::class,
            // TestimonialSeeder::class,  // Commented out - may have missing dependencies
            // FaqSeeder::class,       // Commented out - may have missing dependencies
            MenuSeeder::class,
            JobApplicationSeeder::class,
        ]);

        $this->finished();
    }
}
