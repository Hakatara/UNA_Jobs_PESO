<?php

namespace Database\Seeders;

use Botble\Base\Models\MetaBox;
use Botble\Base\Supports\BaseSeeder;
use Botble\Language\Models\LanguageMeta;
use Botble\Slug\Models\Slug;
use Botble\Testimonial\Models\Testimonial;
use Illuminate\Support\Facades\DB;

class TestimonialSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('testimonials');

        $testimonials = [
            [
                'name' => 'Ellis Kim',
                'company' => 'Digital Artist',
            ],
            [
                'name' => 'John Smith',
                'company' => 'Product designer',
            ],
            [
                'name' => 'Sayen Ahmod',
                'company' => 'Developer',
            ],
            [
                'name' => 'Tayla Swef',
                'company' => 'Graphic designer',
            ],
        ];

        Testimonial::query()->truncate();
        DB::table('testimonials_translations')->truncate();
        Slug::query()->where('reference_type', Testimonial::class)->delete();
        MetaBox::query()->where('reference_type', Testimonial::class)->delete();
        LanguageMeta::query()->where('reference_type', Testimonial::class)->delete();

        foreach ($testimonials as $index => $item) {
            Testimonial::query()->create([
                'name' => $item['name'],
                'company' => $item['company'],
                'image' => 'testimonials/' . ($index + 1) . '.png',
                'content' => fake()->realText(),
            ]);
        }
    }
}
