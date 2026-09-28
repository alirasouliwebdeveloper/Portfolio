<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Support\TipTapDocument;
use Illuminate\Database\Seeder;

/** Fixed pages with the copy from the design (mock — editable in the admin under Pages). */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->pages() as $key => $page) {
            Page::query()->firstOrCreate(['key' => $key], $page);
        }
    }

    /** @return array<string, array<string, mixed>> */
    private function pages(): array
    {
        $doc = fn (string ...$paragraphs) => TipTapDocument::fromParagraphs($paragraphs);

        return [
            'home' => [
                'title' => "Hi, I’m Ali",
                'meta_title' => 'Ali — Laravel & Next.js Developer',
                'meta_description' => "Full-stack developer building fast websites, online stores and web apps with Laravel, React and Next.js. Based in Muscat, Oman — let’s build yours.",
                'focus_keyword' => 'Laravel developer',
                'content' => [
                    'hero' => [
                        'chip' => "I’M A WEB DEVELOPER",
                        'greeting' => "Hi, I’m",
                        'lead' => "I’m a full-stack developer building fast websites, online stores and web apps with Laravel, React and Next.js.",
                        'tech_label' => 'Technologies I work with',
                        'technologies' => ['HTML', 'JavaScript', 'TypeScript', 'React', 'Node.js', 'Git'],
                        'code_stack' => ['Laravel', 'React', 'Next.js', 'Docker'],
                        'code_passion' => 'Building things for the web',
                    ],
                    'about_teaser' => [
                        'eyebrow' => 'ABOUT ME',
                        'heading' => "I’m passionate about creating digital solutions",
                        'text' => $doc('With 4+ years of experience in web development, I help businesses and individuals bring their ideas to life through clean, efficient, and user-friendly code.'),
                    ],
                    'stack' => [
                        'eyebrow' => 'MY STACK',
                        'heading' => 'What I Build With',
                        'text' => 'The tools I use every day, grouped by the part of the product they power.',
                        'groups' => [
                            ['icon' => 'db', 'title' => 'Backend', 'text' => 'APIs, admin panels and business logic for stores, marketplaces and internal tools.', 'tags' => ['Laravel', 'PHP', 'MySQL', 'REST APIs', 'Filament'], 'service_slug' => 'laravel-development'],
                            ['icon' => 'screen', 'title' => 'Frontend', 'text' => 'Fast, SEO-friendly websites and dashboards that are easy to use on any screen.', 'tags' => ['React', 'Next.js', 'TypeScript', 'Tailwind CSS'], 'service_slug' => 'nextjs-website-development'],
                            ['icon' => 'server', 'title' => 'DevOps', 'text' => 'Containerised deployments, servers and release workflows that stay reliable.', 'tags' => ['Docker', 'Linux', 'Nginx', 'Git'], 'service_slug' => 'api-development'],
                            ['icon' => 'flow', 'title' => 'Automation', 'text' => 'Workflows that connect your tools and take repetitive work off your team.', 'tags' => ['n8n', 'Webhooks', 'API integrations'], 'service_slug' => 'n8n-automation'],
                        ],
                    ],
                    'projects' => ['eyebrow' => 'FEATURED PROJECTS', 'heading' => 'Some of My Recent Work'],
                    'testimonials' => ['eyebrow' => 'TESTIMONIALS', 'heading' => 'What Clients Say'],
                    'blog' => ['eyebrow' => 'LATEST ARTICLES', 'heading' => 'From the Blog'],
                    'contact' => [
                        'eyebrow' => "LET’S WORK TOGETHER",
                        'heading' => 'Have a project in mind?',
                        'text' => "Tell me what you’re building and where you’re stuck. I usually reply within one working day.",
                    ],
                ],
            ],
            'about' => [
                'title' => "I’m Ali — a full-stack developer who builds for the web.",
                'meta_title' => 'About Ali — Full-Stack Laravel & Next.js Developer',
                'meta_description' => 'Meet Ali, a full-stack developer in Muscat building websites, online stores and web apps with Laravel and Next.js — from idea to launch in one set of hands.',
                'focus_keyword' => 'full-stack developer',
                'content' => [
                    'hero' => [
                        'chip' => 'ABOUT ME',
                        'text' => $doc(
                            'I build websites, online stores and web apps with Laravel on the backend and React and Next.js on the frontend. I also handle the parts around the code — servers, deployments and automations — so a project goes from idea to launch in one set of hands.',
                            'I started with small PHP sites for local businesses and now work with startups and companies on products that have to be fast, easy to manage and ready to grow.',
                        ),
                    ],
                    'how' => ['eyebrow' => 'HOW I WORK', 'heading' => 'A simple, predictable process', 'text' => "You always know what’s being built, what it costs and when it ships."],
                    'experience' => ['eyebrow' => 'EXPERIENCE', 'heading' => "Where I’ve worked"],
                    'toolbox' => [
                        'eyebrow' => 'TOOLBOX',
                        'heading' => 'Tools I use every day',
                        'groups' => [
                            ['icon' => 'db', 'title' => 'Backend', 'tags' => ['Laravel', 'PHP', 'MySQL', 'REST APIs', 'Filament']],
                            ['icon' => 'screen', 'title' => 'Frontend', 'tags' => ['React', 'Next.js', 'TypeScript', 'Tailwind CSS']],
                            ['icon' => 'server', 'title' => 'DevOps', 'tags' => ['Docker', 'Linux', 'Nginx', 'Git']],
                            ['icon' => 'flow', 'title' => 'Automation', 'tags' => ['n8n', 'Webhooks', 'API integrations']],
                        ],
                    ],
                    'cta' => [
                        'heading' => 'Have a project in mind?',
                        'text' => "Tell me what you’re building. I usually reply within one working day.",
                        'button' => 'Start a Conversation',
                    ],
                ],
            ],
            'contact' => [
                'title' => "Let’s talk about your project",
                'meta_title' => 'Contact Ali — Hire a Laravel & Next.js Developer',
                'meta_description' => 'Tell me what you are building and get a reply within one working day. Websites, online stores, APIs and automations from a developer in Muscat, Oman.',
                'focus_keyword' => 'hire a Laravel developer',
                'content' => [
                    'hero' => [
                        'eyebrow' => 'CONTACT',
                        'text' => "Tell me what you’re building and where you’re stuck. The more you share, the more useful my first reply will be.",
                    ],
                    'faq' => [
                        'eyebrow' => 'FAQ',
                        'heading' => 'Before you write',
                        'text' => "Answers to what clients ask most often. Can’t find yours? Ask in the form above.",
                    ],
                ],
            ],
            'blog' => [
                'title' => 'Articles & Notes',
                'meta_title' => 'Articles & Notes on Laravel, Next.js and DevOps',
                'meta_description' => 'Practical write-ups on Laravel, Next.js, DevOps and automation — what worked in real projects and what did not, from a working full-stack developer.',
                'focus_keyword' => 'Laravel and Next.js articles',
                'content' => [
                    'eyebrow' => 'BLOG',
                    'description' => "Practical write-ups on Laravel, Next.js, DevOps and automation — what worked in real projects and what didn’t.",
                ],
            ],
            'not_found' => [
                'title' => "This page doesn’t exist",
                'content' => [
                    'text' => 'The link may be old or the page may have moved. Try searching, or head back to somewhere familiar.',
                ],
            ],
            'privacy' => [
                'title' => 'Privacy Policy',
                'meta_description' => 'How this website handles the information you send through the contact form and the analytics it may use.',
                'body' => $doc(
                    'This site keeps the personal information it collects to a minimum. When you send a message through the contact form, the details you enter (name, email, optional company and phone, your project description and any files you attach) are stored so I can reply to you. Your IP address is stored only as a hashed value to limit spam.',
                    'Files that you upload but never send are deleted automatically within 24 hours. Messages are kept only as long as needed to answer you and to keep a record of the conversation. To have your message removed, email me and I will delete it.',
                    'If analytics is enabled in the site settings, it is used only to understand which pages are read. It does not identify you personally.',
                ),
            ],
            'terms' => [
                'title' => 'Terms of Service',
                'meta_description' => 'Terms for using this website and for requesting web development services.',
                'body' => $doc(
                    'By using this website you agree to these terms. The content on the site is provided for information about the services offered and may change without notice.',
                    'Quotes, scope and prices for any project are agreed in writing before work starts. A message sent through the contact form is not a contract.',
                    'The articles and code samples published on this site are shared in good faith; use them at your own risk.',
                ),
            ],
        ];
    }
}
