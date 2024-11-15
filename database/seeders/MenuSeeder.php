<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Blog\Models\Post;
use Botble\JobBoard\Enums\AccountTypeEnum;
use Botble\JobBoard\Models\Account;
use Botble\JobBoard\Models\Company;
use Botble\JobBoard\Models\Job;
use Botble\Language\Models\LanguageMeta;
use Botble\Menu\Facades\Menu;
use Botble\Menu\Models\Menu as MenuModel;
use Botble\Menu\Models\MenuLocation;
use Botble\Menu\Models\MenuNode;
use Botble\Page\Models\Page;
use Illuminate\Support\Arr;

class MenuSeeder extends BaseSeeder
{
    public function run(): void
    {
        $candidate = Account::query()
            ->where('is_public_profile', true)
            ->where('type', AccountTypeEnum::JOB_SEEKER)
            ->inRandomOrder()
            ->first();

        $data = [
            [
                'name' => 'Main menu',
                'slug' => 'main-menu',
                'location' => 'main-menu',
                'items' => [
                    [
                        'title' => 'Home',
                        'url' => '/',
                        'children' => [
                            [
                                'title' => 'Home 1',
                                'reference_id' => 1,
                                'reference_type' => Page::class,
                                'position' => 1,
                            ],
                            [
                                'title' => 'Home 2',
                                'reference_id' => 2,
                                'reference_type' => Page::class,
                                'position' => 2,
                            ],
                            [
                                'title' => 'Home 3',
                                'reference_id' => 3,
                                'reference_type' => Page::class,
                                'position' => 3,
                            ],
                            [
                                'title' => 'Home 4',
                                'reference_id' => 4,
                                'reference_type' => Page::class,
                                'position' => 4,
                            ],
                            [
                                'title' => 'Home 5',
                                'reference_id' => 5,
                                'reference_type' => Page::class,
                                'position' => 5,
                            ],
                            [
                                'title' => 'Home 6',
                                'reference_id' => 6,
                                'reference_type' => Page::class,
                                'position' => 6,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Find a Job',
                        'reference_id' => 7,
                        'reference_type' => Page::class,
                        'children' => [
                            [
                                'title' => 'Jobs Grid',
                                'url' => '/jobs?layout=grid',
                            ],
                            [
                                'title' => 'Jobs List',
                                'url' => '/jobs',
                            ],
                            [
                                'title' => 'Job Details',
                                'url' => Job::first()->url,
                            ],
                            [
                                'title' => 'Job External',
                                'url' => Job::skip(1)->first()->url,
                            ],
                            [
                                'title' => 'Job Hide Company',
                                'url' => Job::skip(2)->first()->url,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Companies',
                        'reference_id' => 8,
                        'reference_type' => Page::class,
                        'children' => [
                            [
                                'title' => 'Companies',
                                'reference_id' => 8,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Company Details',
                                'url' => Company::first()->url,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Candidates',
                        'reference_id' => 9,
                        'reference_type' => Page::class,
                        'children' => [
                            [
                                'title' => 'Candidates Grid',
                                'reference_id' => 9,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Candidate Details',
                                'url' => $candidate->url,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Pages',
                        'url' => '#',
                        'children' => [
                            [
                                'title' => 'About Us',
                                'reference_id' => 10,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Pricing Plan',
                                'reference_id' => 11,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Contact Us',
                                'reference_id' => 12,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Register',
                                'url' => route('public.account.register'),
                            ],
                            [
                                'title' => 'Sign in',
                                'url' => route('public.account.login'),
                            ],
                            [
                                'title' => 'Reset Password',
                                'url' => route('public.account.password.request'),
                            ],
                        ],
                    ],
                    [
                        'title' => 'Blog',
                        'reference_id' => 13,
                        'reference_type' => Page::class,
                        'children' => [
                            [
                                'title' => 'Blog Grid',
                                'reference_id' => 13,
                                'reference_type' => Page::class,
                            ],
                            [
                                'title' => 'Blog Single',
                                'url' => Post::first()->url,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Resources',
                'slug' => 'resources',
                'items' => [
                    [
                        'title' => 'About Us',
                        'reference_id' => 10,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Our Team',
                        'url' => '#',
                    ],
                    [
                        'title' => 'Products',
                        'url' => '#',
                    ],
                    [
                        'title' => 'Contact',
                        'reference_id' => 12,
                        'reference_type' => Page::class,
                    ],
                ],
            ],
            [
                'name' => 'Community',
                'slug' => 'community',
                'items' => [
                    [
                        'title' => 'Feature',
                        'reference_id' => 10,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Pricing',
                        'reference_id' => 11,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Credit',
                        'url' => '#',
                    ],
                    [
                        'title' => 'FAQ',
                        'reference_id' => 15,
                        'reference_type' => Page::class,
                    ],
                ],
            ],
            [
                'name' => 'Quick links',
                'slug' => 'quick-links',
                'items' => [
                    [
                        'title' => 'iOS',
                        'url' => '#',
                    ],
                    [
                        'title' => 'Android',
                        'url' => '#',
                    ],
                    [
                        'title' => 'Microsoft',
                        'url' => '#',
                    ],
                    [
                        'title' => 'Desktop',
                        'url' => '#',
                    ],
                ],
            ],
            [
                'name' => 'More',
                'slug' => 'more',
                'items' => [
                    [
                        'title' => 'Cookie Policy',
                        'reference_id' => 14,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'Terms',
                        'reference_id' => 17,
                        'reference_type' => Page::class,
                    ],
                    [
                        'title' => 'FAQ',
                        'reference_id' => 5,
                        'reference_type' => Page::class,
                    ],
                ],
            ],
        ];

        MenuModel::query()->truncate();
        MenuLocation::query()->truncate();
        MenuNode::query()->truncate();

        foreach ($data as $index => $item) {
            $menu = MenuModel::query()->create(Arr::except($item, ['items', 'location']));

            if (isset($item['location'])) {
                $menuLocation = MenuLocation::query()->create([
                    'menu_id' => $menu->id,
                    'location' => $item['location'],
                ]);

                LanguageMeta::saveMetaData($menuLocation);
            }

            foreach ($item['items'] as $menuNode) {
                $this->createMenuNode($index, $menuNode, $menu->id);
            }

            LanguageMeta::saveMetaData($menu);
        }

        Menu::clearCacheMenuItems();
    }

    protected function createMenuNode(int $index, array $menuNode, int|string $menuId, int|string $parentId = 0): void
    {
        $menuNode['menu_id'] = $menuId;
        $menuNode['parent_id'] = $parentId;

        if (isset($menuNode['url'])) {
            $menuNode['url'] = str_replace(url(''), '', $menuNode['url']);
        }

        if (Arr::has($menuNode, 'children')) {
            $children = $menuNode['children'];
            $menuNode['has_child'] = true;

            unset($menuNode['children']);
        } else {
            $children = [];
            $menuNode['has_child'] = false;
        }

        $createdNode = MenuNode::query()->create($menuNode);

        if ($children) {
            foreach ($children as $child) {
                $this->createMenuNode($index, $child, $menuId, $createdNode->id);
            }
        }
    }
}
