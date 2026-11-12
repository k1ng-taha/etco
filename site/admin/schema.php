<?php
/**
 * Describes every editable part of the website: which fields it has and how they look in the admin panel.
 * type "single" = one set of fields (e.g. company info). type "list" = a list of items (e.g. projects).
 */

function opts_from($path, $labelKey = 'name') {
    $C = content();
    $list = $C;
    foreach ($path as $p) $list = $list[$p] ?? [];
    $o = [];
    foreach ((array) $list as $i) $o[$i['id']] = t($i[$labelKey] ?? $i['id'], 'en');
    return $o;
}

function admin_sections() {
    $icon = ['type' => 'icon', 'key' => 'icon', 'label' => 'Icon'];
    $vis = ['type' => 'checkbox', 'key' => 'visible', 'label' => 'Show on website', 'default' => true];
    $hlHelp = 'Tip: put *stars* around a word to show it in blue, e.g. Discover our *sectors*';

    return [
    /* ---------------- Company ---------------- */
    'settings' => ['title' => 'Company info & contact', 'type' => 'single', 'path' => ['settings'], 'icon' => 'facility',
        'intro' => 'These details appear in the top bar, the footer, the Contact and Locations pages.',
        'fields' => [
            ['type' => 'heading', 'label' => 'Company'],
            ['type' => 'i18n', 'key' => 'company_name', 'label' => 'Company name'],
            ['type' => 'i18n', 'key' => 'short_name', 'label' => 'Short name'],
            ['type' => 'i18n', 'key' => 'tagline', 'label' => 'Tagline (footer)'],
            ['type' => 'text', 'key' => 'founded', 'label' => 'Year founded', 'help' => 'Used to calculate "years of experience" automatically.', 'width' => 'sm'],
            ['type' => 'image', 'key' => 'logo', 'label' => 'Logo (for white backgrounds)', 'svg' => true, 'help' => 'Used in the white header. SVG or PNG with a transparent background.'],
            ['type' => 'image', 'key' => 'logo_light', 'label' => 'Logo (for dark backgrounds)', 'svg' => true, 'help' => 'White/light version used over the home-page photo, in the footer and in this admin panel. Leave empty to use the normal logo.'],
            ['type' => 'image', 'key' => 'favicon', 'label' => 'Browser tab icon (optional)', 'svg' => true, 'help' => 'Square image. Leave empty to use the default ETCO icon.'],
            ['type' => 'heading', 'label' => 'Contact'],
            ['type' => 'text', 'key' => 'phone_display', 'label' => 'Phone number (as shown)', 'help' => 'Example: +961 3 338 648', 'width' => 'md'],
            ['type' => 'text', 'key' => 'phone_link', 'label' => 'Phone number (for the call button)', 'help' => 'Digits only with country code, no spaces. Example: +9613338648', 'width' => 'md'],
            ['type' => 'text', 'key' => 'email', 'label' => 'Email address shown on the website', 'help' => 'The envelope icon in the top bar opens an email to this address.', 'width' => 'md'],
            ['type' => 'i18n', 'key' => 'address', 'label' => 'Address'],
            ['type' => 'i18n', 'key' => 'city', 'label' => 'City / country'],
            ['type' => 'i18n', 'key' => 'hours_week', 'label' => 'Working hours (weekdays)'],
            ['type' => 'i18n', 'key' => 'hours_sat', 'label' => 'Working hours (Saturday)'],
            ['type' => 'text', 'key' => 'map_query', 'label' => 'Map location', 'help' => 'What to search on Google Maps, e.g. "Raouche, Beirut, Lebanon" or GPS coordinates like "33.8869,35.4797".', 'width' => 'md'],
            ['type' => 'heading', 'label' => 'Website messages & Google'],
            ['type' => 'text', 'key' => 'notify_email', 'label' => 'Send website messages to', 'help' => 'Contact-form messages and job applications are emailed here (they are also always saved in this admin panel).', 'width' => 'md'],
            ['type' => 'text', 'key' => 'mail_from', 'label' => 'Sender address for those emails', 'help' => 'Should be an address on your own domain, e.g. no-reply@etcocontracting.com, so emails do not go to spam.', 'width' => 'md'],
            ['type' => 'i18n_area', 'key' => 'meta_description', 'label' => 'Google description', 'help' => 'Short description shown in Google search results.'],
        ]],

    /* ---------------- Home ---------------- */
    'home' => ['title' => 'Home page texts', 'type' => 'single', 'path' => ['home'], 'group' => 'home', 'tab' => 'Texts',
        'fields' => [
            ['type' => 'heading', 'label' => 'Top banner'],
            ['type' => 'file', 'key' => 'hero_video', 'label' => 'Background video (optional)', 'accept' => 'video/mp4', 'exts' => ['mp4'], 'help' => 'MP4 file. If set, it plays behind the banner instead of the photo slideshow. Keep it short (10–30 s) and under 15 MB. The photos are still used as the video poster.'],
            ['type' => 'heading', 'label' => 'Introduction'],
            ['type' => 'i18n', 'key' => 'intro_eyebrow', 'label' => 'Small title'],
            ['type' => 'i18n', 'key' => 'intro_title', 'label' => 'Big title', 'help' => $hlHelp],
            ['type' => 'i18n_area', 'key' => 'intro_text', 'label' => 'Text'],
            ['type' => 'heading', 'label' => 'Sections'],
            ['type' => 'i18n', 'key' => 'sectors_title', 'label' => 'Sectors title', 'help' => $hlHelp],
            ['type' => 'i18n', 'key' => 'sectors_text', 'label' => 'Sectors subtitle'],
            ['type' => 'i18n', 'key' => 'projects_title', 'label' => 'Projects title', 'help' => 'Projects marked "Show on home page" appear in this section.'],
            ['type' => 'i18n', 'key' => 'projects_text', 'label' => 'Projects subtitle'],
            ['type' => 'i18n', 'key' => 'values_title', 'label' => 'Values title', 'help' => 'The first 4 values from the Values page are shown.'],
            ['type' => 'heading', 'label' => 'Bottom blocks (shown on most pages)'],
            ['type' => 'i18n', 'key' => 'cta_contact_title', 'label' => '"Have a project?" title'],
            ['type' => 'i18n', 'key' => 'cta_contact_text', 'label' => '"Have a project?" text'],
            ['type' => 'image', 'key' => 'cta_contact_image', 'label' => '"Have a project?" background photo'],
            ['type' => 'i18n', 'key' => 'cta_join_title', 'label' => '"Join our team" title'],
            ['type' => 'i18n', 'key' => 'cta_join_text', 'label' => '"Join our team" text'],
            ['type' => 'image', 'key' => 'cta_join_image', 'label' => '"Join our team" background photo'],
        ]],
    'slides' => ['title' => 'Banner slides', 'type' => 'list', 'path' => ['slides'], 'group' => 'home', 'tab' => 'Banner slides', 'item' => 'slide',
        'intro' => 'The large rotating photos at the top of the home page. Use wide, sharp photos (at least 1600 px wide).',
        'thumb' => 'image', 'name' => 'title', 'sub' => 'highlight',
        'fields' => [$vis,
            ['type' => 'image', 'key' => 'image', 'label' => 'Photo', 'required' => true],
            ['type' => 'i18n', 'key' => 'title', 'label' => 'Title line 1 (white)'],
            ['type' => 'i18n', 'key' => 'highlight', 'label' => 'Title line 2 (blue)'],
            ['type' => 'i18n', 'key' => 'text', 'label' => 'Short text'],
        ]],
    'stats' => ['title' => 'Key figures', 'type' => 'list', 'path' => ['stats'], 'group' => 'home', 'tab' => 'Key figures', 'item' => 'figure',
        'intro' => 'The big numbers on the home and About pages. Write {years} or {projects} to have the number calculated automatically.',
        'name' => 'label', 'sub' => 'value',
        'fields' => [$vis,
            ['type' => 'text', 'key' => 'value', 'label' => 'Number', 'width' => 'sm', 'help' => 'e.g. 140,000 – or {years} / {projects} for automatic numbers'],
            ['type' => 'text', 'key' => 'suffix', 'label' => 'After the number (optional)', 'width' => 'sm', 'help' => 'e.g. + or m²'],
            ['type' => 'i18n', 'key' => 'label', 'label' => 'Label'],
        ]],

    /* ---------------- Sectors ---------------- */
    'sectors' => ['title' => 'Sectors', 'type' => 'list', 'path' => ['sectors'], 'group' => 'sectors', 'tab' => 'Sectors', 'item' => 'sector',
        'intro' => 'Your areas of expertise. They appear in the "Sectors" menu, on the home page and on the Sectors page.',
        'thumb' => 'image', 'name' => 'title', 'sub' => 'summary', 'slug' => true, 'view' => 'sectors',
        'fields' => [$vis,
            ['type' => 'i18n', 'key' => 'title', 'label' => 'Name', 'required' => true],
            ['type' => 'i18n', 'key' => 'summary', 'label' => 'One-line summary'],
            ['type' => 'i18n_area', 'key' => 'body', 'label' => 'Full description', 'rows' => 8, 'help' => 'Leave an empty line between paragraphs.'],
            ['type' => 'points', 'key' => 'points', 'label' => '"What we do" list', 'help' => 'One item per line. Keep the same number of lines in English and Arabic.'],
            ['type' => 'image', 'key' => 'image', 'label' => 'Main photo', 'required' => true],
            ['type' => 'image', 'key' => 'image2', 'label' => 'Second photo (optional)', 'help' => 'Shown under the description.'],
            $icon,
            ['type' => 'slug', 'key' => 'slug', 'label' => 'Web address', 'help' => 'Part of the page address, e.g. /en/sectors/mep-services/. Letters, numbers and dashes only.'],
        ]],
    'page_sectors' => ['title' => 'Sectors page texts', 'type' => 'single', 'path' => ['pages', 'sectors'], 'group' => 'sectors', 'tab' => 'Page texts', 'view' => 'sectors', 'fields' => page_fields()],

    /* ---------------- Projects ---------------- */
    'projects' => ['title' => 'Projects', 'type' => 'list', 'path' => ['projects'], 'group' => 'projects', 'tab' => 'Projects', 'item' => 'project',
        'intro' => 'Every project has its own page. Tick "Show on home page" to feature it in the home-page carousel.',
        'thumb' => 'image', 'name' => 'title', 'sub' => 'location', 'cols' => ['year' => 'Year', 'category' => 'Category'], 'slug' => true, 'view' => 'projects', 'toggles' => ['featured' => 'Home'],
        'fields' => [$vis,
            ['type' => 'checkbox', 'key' => 'featured', 'label' => 'Show on home page'],
            ['type' => 'i18n', 'key' => 'title', 'label' => 'Project name', 'required' => true],
            ['type' => 'select', 'key' => 'category', 'label' => 'Category', 'options' => function () { return opts_from(['categories']); }, 'help' => 'Used for the filter buttons. Manage categories in the "Categories" tab.'],
            ['type' => 'i18n', 'key' => 'location', 'label' => 'Location', 'help' => 'e.g. Beirut, Lebanon'],
            ['type' => 'select', 'key' => 'region', 'label' => 'Region (Locations page)', 'options' => function () { return opts_from(['regions']); }],
            ['type' => 'text', 'key' => 'year', 'label' => 'Completion year', 'width' => 'sm', 'help' => 'e.g. 2017. Write "ongoing" for continuing contracts, or leave empty.'],
            ['type' => 'i18n', 'key' => 'type', 'label' => 'Type of building', 'help' => 'e.g. Hotel renovation'],
            ['type' => 'i18n', 'key' => 'scope', 'label' => 'Scope of work', 'help' => 'e.g. Electromechanical'],
            ['type' => 'i18n_area', 'key' => 'description', 'label' => 'Description', 'rows' => 8, 'help' => 'Leave an empty line between paragraphs.'],
            ['type' => 'image', 'key' => 'image', 'label' => 'Main photo', 'required' => true],
            ['type' => 'select', 'key' => 'image_fit', 'label' => 'Photo display', 'options' => ['cover' => 'Fill the frame (photos)', 'contain' => 'Show whole image on white (logos)']],
            ['type' => 'images', 'key' => 'gallery', 'label' => 'More photos', 'help' => 'Extra photos shown on the project page. You can select several files at once.'],
            ['type' => 'multi', 'key' => 'sectors', 'label' => 'Related sectors', 'options' => function () { return opts_from(['sectors'], 'title'); }],
            ['type' => 'slug', 'key' => 'slug', 'label' => 'Web address'],
        ]],
    'categories' => ['title' => 'Project categories', 'type' => 'list', 'path' => ['categories'], 'group' => 'projects', 'tab' => 'Categories', 'item' => 'category', 'name' => 'name',
        'intro' => 'The filter buttons on the Projects page.',
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'name', 'label' => 'Name', 'required' => true]]],
    'page_projects' => ['title' => 'Projects page texts', 'type' => 'single', 'path' => ['pages', 'projects'], 'group' => 'projects', 'tab' => 'Page texts', 'view' => 'projects',
        'fields' => array_merge(page_fields(), [['type' => 'i18n_area', 'key' => 'gallery_intro', 'label' => 'Photo gallery introduction']])],

    /* ---------------- Gallery ---------------- */
    'gallery' => ['title' => 'Photo gallery', 'type' => 'list', 'path' => ['gallery'], 'group' => 'gallery', 'tab' => 'Photos', 'item' => 'photo', 'grid' => true,
        'intro' => 'Photos shown in the "Photo gallery" tab of the Projects page.',
        'thumb' => 'image', 'name' => 'caption', 'cols' => ['category' => 'Category'],
        'fields' => [$vis,
            ['type' => 'image', 'key' => 'image', 'label' => 'Photo', 'required' => true],
            ['type' => 'select', 'key' => 'category', 'label' => 'Category', 'options' => function () { return opts_from(['gallery_categories']); }],
            ['type' => 'i18n', 'key' => 'caption', 'label' => 'Caption (optional)'],
        ]],
    'gallery_categories' => ['title' => 'Gallery categories', 'type' => 'list', 'path' => ['gallery_categories'], 'group' => 'gallery', 'tab' => 'Categories', 'item' => 'category', 'name' => 'name',
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'name', 'label' => 'Name', 'required' => true]]],

    /* ---------------- About ---------------- */
    'page_about' => ['title' => 'About us page', 'type' => 'single', 'path' => ['pages', 'about'], 'group' => 'about', 'tab' => 'Page texts', 'view' => 'about',
        'fields' => array_merge(page_fields(), [
            ['type' => 'heading', 'label' => 'Our story'],
            ['type' => 'i18n', 'key' => 'story_title', 'label' => 'Story title', 'help' => $hlHelp],
            ['type' => 'i18n_area', 'key' => 'story', 'label' => 'Story text', 'rows' => 8],
            ['type' => 'image', 'key' => 'story_image', 'label' => 'Story photo'],
            ['type' => 'i18n_area', 'key' => 'mission', 'label' => 'Mission'],
            ['type' => 'i18n_area', 'key' => 'vision', 'label' => 'Vision'],
            ['type' => 'i18n', 'key' => 'features_title', 'label' => '"Why ETCO" title', 'help' => $hlHelp],
        ])],
    'features' => ['title' => 'Why ETCO', 'type' => 'list', 'path' => ['features'], 'group' => 'about', 'tab' => 'Why ETCO', 'item' => 'point', 'name' => 'title', 'sub' => 'text', 'iconcol' => true,
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'title', 'label' => 'Title'], ['type' => 'i18n_area', 'key' => 'text', 'label' => 'Text', 'rows' => 2], $icon]],

    /* ---------------- Values ---------------- */
    'page_values' => ['title' => 'Values page', 'type' => 'single', 'path' => ['pages', 'values'], 'group' => 'values', 'tab' => 'Page texts', 'view' => 'values',
        'fields' => array_merge(page_fields(), [['type' => 'i18n', 'key' => 'commitments_title', 'label' => 'Commitments title', 'help' => $hlHelp]])],
    'values' => ['title' => 'Values', 'type' => 'list', 'path' => ['values'], 'group' => 'values', 'tab' => 'Values', 'item' => 'value', 'name' => 'title', 'sub' => 'text', 'iconcol' => true,
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'title', 'label' => 'Title'], ['type' => 'i18n_area', 'key' => 'text', 'label' => 'Text', 'rows' => 3], $icon]],
    'commitments' => ['title' => 'Commitments', 'type' => 'list', 'path' => ['commitments'], 'group' => 'values', 'tab' => 'Commitments', 'item' => 'commitment', 'name' => 'title', 'sub' => 'text',
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'title', 'label' => 'Title'], ['type' => 'i18n_area', 'key' => 'text', 'label' => 'Text', 'rows' => 3]]],

    /* ---------------- Locations ---------------- */
    'page_locations' => ['title' => 'Locations page', 'type' => 'single', 'path' => ['pages', 'locations'], 'group' => 'locations', 'tab' => 'Page texts', 'view' => 'locations',
        'intro' => 'The office address, hours and map come from "Company info & contact".', 'fields' => page_fields()],
    'regions' => ['title' => 'Regions', 'type' => 'list', 'path' => ['regions'], 'group' => 'locations', 'tab' => 'Regions', 'item' => 'region', 'name' => 'name', 'sub' => 'text',
        'intro' => 'Regions listed under "Where we have worked". Each project is linked to a region in its own settings; empty regions are hidden automatically.',
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'name', 'label' => 'Name', 'required' => true], ['type' => 'i18n_area', 'key' => 'text', 'label' => 'Short text', 'rows' => 2]]],

    /* ---------------- Join us ---------------- */
    'page_careers' => ['title' => 'Join us page', 'type' => 'single', 'path' => ['pages', 'careers'], 'group' => 'careers', 'tab' => 'Page texts', 'view' => 'careers',
        'fields' => array_merge(page_fields(), [['type' => 'i18n', 'key' => 'profiles_title', 'label' => 'Profiles box title']])],
    'positions' => ['title' => 'Open positions', 'type' => 'list', 'path' => ['positions'], 'group' => 'careers', 'tab' => 'Open positions', 'item' => 'position', 'name' => 'title', 'sub' => 'location',
        'intro' => 'Add a job here and it appears on the Join us page. Untick "Show on website" (or delete it) once the job is filled.',
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'title', 'label' => 'Job title', 'required' => true], ['type' => 'i18n', 'key' => 'location', 'label' => 'Location'],
            ['type' => 'i18n', 'key' => 'type', 'label' => 'Type', 'help' => 'e.g. Full time'], ['type' => 'i18n_area', 'key' => 'description', 'label' => 'Description', 'rows' => 5]]],
    'join_reasons' => ['title' => 'Why join', 'type' => 'list', 'path' => ['join_reasons'], 'group' => 'careers', 'tab' => 'Why join', 'item' => 'reason', 'name' => 'title', 'sub' => 'text', 'iconcol' => true,
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'title', 'label' => 'Title'], ['type' => 'i18n_area', 'key' => 'text', 'label' => 'Text', 'rows' => 2], $icon]],
    'profiles' => ['title' => 'Profiles we look for', 'type' => 'list', 'path' => ['profiles'], 'group' => 'careers', 'tab' => 'Profiles', 'item' => 'profile', 'name' => 'name',
        'fields' => [$vis, ['type' => 'i18n', 'key' => 'name', 'label' => 'Profile']]],

    /* ---------------- Contact ---------------- */
    'page_contact' => ['title' => 'Contact page', 'type' => 'single', 'path' => ['pages', 'contact'], 'group' => 'contact', 'view' => 'contact',
        'intro' => 'Phone, email, address and hours come from "Company info & contact".', 'fields' => page_fields()],
    ];
}

function page_fields() {
    return [
        ['type' => 'heading', 'label' => 'Page banner'],
        ['type' => 'i18n', 'key' => 'title', 'label' => 'Page title'],
        ['type' => 'i18n', 'key' => 'subtitle', 'label' => 'Subtitle'],
        ['type' => 'image', 'key' => 'image', 'label' => 'Banner photo', 'help' => 'Wide photo, at least 1600 px wide. It is shown darkened behind the title.'],
        ['type' => 'heading', 'label' => 'Content'],
        ['type' => 'i18n_area', 'key' => 'intro', 'label' => 'Introduction text', 'rows' => 4],
        ['type' => 'i18n_area', 'key' => 'meta', 'label' => 'Google description', 'rows' => 2, 'help' => 'Short description shown in Google search results.'],
    ];
}

/* Sidebar menu */
function admin_menu() {
    return [
        'Overview' => [
            ['dashboard', 'Dashboard', 'projects'],
            ['messages', 'Messages', 'mail'],
            ['applications', 'Job applications', 'expertise'],
        ],
        'Website pages' => [
            ['home', 'Home page', 'facility'],
            ['sectors', 'Sectors', 'mep'],
            ['projects', 'Projects', 'structural'],
            ['gallery', 'Photo gallery', 'image'],
            ['values', 'Values', 'quality'],
            ['page_locations', 'Locations', 'pin'],
            ['page_about', 'About us', 'approach'],
            ['page_contact', 'Contact us', 'phone'],
            ['page_careers', 'Join us', 'integrity'],
        ],
        'Settings' => [
            ['settings', 'Company info & contact', 'wrench'],
            ['labels', 'Menu & button texts', 'tag'],
            ['account', 'My account & users', 'satisfaction'],
            ['backups', 'Backups', 'clock'],
            ['tools', 'Tools & server check', 'safety'],
        ],
    ];
}

/** Which menu entry is highlighted for a section */
function admin_menu_key($sec) {
    $all = admin_sections();
    $g = $all[$sec]['group'] ?? null;
    $map = ['home' => 'home', 'sectors' => 'sectors', 'projects' => 'projects', 'gallery' => 'gallery', 'values' => 'values', 'locations' => 'page_locations', 'about' => 'page_about', 'careers' => 'page_careers', 'contact' => 'page_contact'];
    return $g ? $map[$g] : $sec;
}
