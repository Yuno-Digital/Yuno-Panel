<?php

return [
    /*
     * The current panel version. Compared against the latest GitHub release to
     * tell admins when an update is available.
     */
    'version' => '1.0.0-alpha9',

    /*
     * GitHub repository (owner/name) used for update checks.
     */
    'repository' => env('YUNO_REPOSITORY', 'Yuno-Digital/Yuno-Panel'),

    /*
     * Wings (node daemon) repository, used to check for daemon updates.
     */
    'wings_repository' => env('YUNO_WINGS_REPOSITORY', 'Yuno-Digital/Yuno-Panel-Wings'),

    /*
     * Maximum number of backups a server may keep.
     */
    'backup_limit' => (int) env('YUNO_BACKUP_LIMIT', 5),

    /*
     * Plugins repository (owner/name and branch) browsed by the in-panel
     * plugin installer.
     */
    'plugins_repository' => env('YUNO_PLUGINS_REPOSITORY', 'Yuno-Digital/Yuno-Panel-Plugins'),
    'plugins_branch' => env('YUNO_PLUGINS_BRANCH', 'main'),
];
