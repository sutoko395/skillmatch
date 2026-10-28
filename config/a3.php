<?php

return [
    'retention_enabled' => (bool) env('A3_RETENTION_ENABLED', false),
    // Demo baseline only; approve a production retention policy before public use.
    'draft_days' => (int) env('A3_DRAFT_RETENTION_DAYS', 7),
    'document_days' => (int) env('A3_DOCUMENT_RETENTION_DAYS', 90),
];
