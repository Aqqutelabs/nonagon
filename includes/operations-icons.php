<?php
function operation_icon(string $name): string
{
    $paths = [
        'home'=>'<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-7h6v7"/>',
        'equipment'=>'<path d="m12 3 9 5v9l-9 5-9-5V8zM3 8l9 5 9-5M12 13v9M7.5 5.5l9 5"/>',
        'maintenance'=>'<path d="M14 6a5 5 0 0 0-6 6L3 17a3 3 0 0 0 4 4l5-5a5 5 0 0 0 6-6l-3 3-4-4z"/>',
        'users'=>'<circle cx="9" cy="7" r="3"/><path d="M3 20v-3a6 6 0 0 1 12 0v3M16 4a3 3 0 0 1 0 6M18 14a5 5 0 0 1 3 4v2"/>',
        'alert'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 17h.01"/>',
        'bell'=>'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/>',
        'search'=>'<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>',
        'activity'=>'<path d="M2 12h5l3-8 4 16 3-8h5"/>',
        'clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 6v6l4 2"/>',
        'settings'=>'<path d="m9 3-1 3-3 1-2 3 2 2-1 4 3 2 3-1 3 3 3-1 1-4 3-2-1-3-3-1-1-4z"/><circle cx="11" cy="11" r="3"/>',
        'chevron'=>'<path d="m6 9 6 6 6-6"/>',
        'logout'=>'<path d="M9 4H4v16h5M9 12h12m-5-5 5 5-5 5"/>',
    ];
    return '<svg class="ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['equipment']).'</svg>';
}
