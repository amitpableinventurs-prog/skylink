<?php

/**
 * Inline monoline SVG icons for the service pages.
 * Matches the site's geometric, lime-accented icon language.
 *
 * Usage:  <?= svc_icon('search') ?>
 */

if (!function_exists('svc_icon')) {
    function svc_icon(string $name, string $class = 'svc-ic'): string
    {
        static $paths = [
            'search'       => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4-4"/>',
            'megaphone'    => '<path d="M5 9v6h3l5 4V5L8 9H5Z"/><path d="M16 8.5a4 4 0 0 1 0 7"/>',
            'reel'         => '<rect x="3" y="4" width="18" height="16" rx="3"/><path d="M10 8.5v7l6-3.5z"/>',
            'crosshair'    => '<circle cx="12" cy="12" r="7"/><path d="M12 3v3M12 18v3M3 12h3M18 12h3"/><circle cx="12" cy="12" r="1.6"/>',
            'compass'      => '<circle cx="12" cy="12" r="9"/><path d="m16 8-2.5 5.5L8 16l2.5-5.5z"/>',
            'layout'       => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18M9 9v11"/>',
            'devices'      => '<rect x="2" y="4" width="14" height="11" rx="1.5"/><path d="M6 19h7M9.5 15v4"/><rect x="17" y="8" width="5" height="11" rx="1.5"/>',
            'bolt'         => '<path d="M13 3 5 13h6l-2 8 10-12h-7z"/>',
            'code'         => '<path d="m9 8-4 4 4 4M15 8l4 4-4 4"/>',
            'shield'       => '<path d="M12 3 5 6v5.5c0 4.3 3 7.4 7 8.5 4-1.1 7-4.2 7-8.5V6z"/>',
            'shield-check' => '<path d="M12 3 5 6v5.5c0 4.3 3 7.4 7 8.5 4-1.1 7-4.2 7-8.5V6z"/><path d="m9 12 2 2 4-4"/>',
            'pen'          => '<path d="M14 4 20 10 9 21H4v-5z"/><path d="m12.5 5.5 6 6"/>',
            'tag'          => '<path d="M4 13 11 6a2 2 0 0 1 1.4-.6H18a2 2 0 0 1 2 2v5.6a2 2 0 0 1-.6 1.4L12 21a2 2 0 0 1-2.8 0L4 15.8a2 2 0 0 1 0-2.8Z"/><circle cx="15.5" cy="8.5" r="1.3"/>',
            'chart'        => '<path d="M4 4v16h16"/><path d="M8 20v-6M13 20V9M18 20v-10"/>',
            'target'       => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/>',
            'grid'         => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
            'pin'          => '<path d="M12 21c4.5-4.5 7-8 7-11a7 7 0 1 0-14 0c0 3 2.5 6.5 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
            'clipboard'    => '<rect x="5" y="5" width="14" height="16" rx="2"/><path d="M9 5V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1"/><path d="m9 13 2 2 4-4"/>',
            'instagram'    => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor" stroke="none"/>',
            'facebook'     => '<path d="M14 21v-7h2.5l.5-3H14v-2c0-.83.67-1.5 1.5-1.5H17V5h-2c-2.2 0-4 1.8-4 4v2H9v3h2v7"/>',
            'mail'         => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/>',
            'phone'        => '<path d="M5 4h3.5l1.5 5-2.5 1.5a11 11 0 0 0 5 5L14 13l5 1.5V18a2 2 0 0 1-2 2A15 15 0 0 1 3 6a2 2 0 0 1 2-2Z"/>',
        ];

        $d = $paths[$name] ?? $paths['target'];

        return '<svg class="' . htmlspecialchars($class, ENT_QUOTES) . '" viewBox="0 0 24 24" fill="none" '
            . 'stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" '
            . 'aria-hidden="true" focusable="false">' . $d . '</svg>';
    }
}
