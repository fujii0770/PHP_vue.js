<?php

return [

    'directories' => [

        /*
         * Here you can specify which directories need to be cleanup. All files older than
         * the specified amount of minutes will be deleted.
         */
        '/var/www/pac/pac_user/storage/app/uploads' => [
            'deleteAllOlderThanMinutes' => 60 * 24,
        ],
        '/var/www/pac/pac_user/storage/app/tmp' => [
            'deleteAllOlderThanMinutes' => 60 * 24,
        ],

        '/var/www/pac/pac_user/storage/app/template' => [
            'deleteAllOlderThanMinutes' => 60 * 24,
        ],

        '/var/www/pac/pac_user/storage/app/attachmentUploads' => [
            'deleteAllOlderThanMinutes' => 60 * 24,
        ],
        '/var/www/pac/pac_user/storage/app/fileMailUploads' => [
            'deleteAllOlderThanMinutes' => 60 * 24,
        ],

    ],

    /*
     * If a file is older than the amount of minutes specified, a cleanup policy will decide if that file
     * should be deleted. By default every file that is older that the specified amount of minutes
     * will be deleted.
     *
     * You can customize this behaviour by writing your own clean up policy.  A valid policy
     * is any class that implements `Spatie\DirectoryCleanup\Policies\CleanupPolicy`.
     */
    'cleanup_policy' => \Spatie\DirectoryCleanup\Policies\DeleteEverything::class,
];
