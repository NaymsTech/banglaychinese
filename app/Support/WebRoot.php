<?php

namespace App\Support;

/**
 * Resolves the directory Laravel should treat as its public web root.
 *
 * Shared-hosting deployments (e.g. Hostinger) usually keep the application
 * outside the document root, so the web root is a sibling of the app root
 * (laravel-app/ next to public_html/) instead of <base>/public. The resolved
 * directory feeds Application::usePublicPath() so public_path(), Vite's
 * manifest lookup and `storage:link` all agree on where the web root lives.
 */
class WebRoot
{
    /**
     * Determine the public directory for the given application base path.
     *
     * Priority:
     *  1. An explicit APP_PUBLIC_PATH override — absolute paths are used
     *     verbatim; relative paths resolve against the base path, so a shared
     *     hosting deployment can use APP_PUBLIC_PATH=../public_html without
     *     hard-coding the server account path.
     *  2. A sibling public_html directory that contains index.php, matching the
     *     shared-hosting layout without any configuration.
     *  3. The standard <base path>/public directory.
     */
    public static function resolve(string $basePath, ?string $override = null): string
    {
        if (filled($override)) {
            return self::isAbsolutePath($override)
                ? $override
                : $basePath.'/'.$override;
        }

        $siblingWebRoot = dirname($basePath).'/public_html';

        if (is_dir($siblingWebRoot) && is_file($siblingWebRoot.'/index.php')) {
            return $siblingWebRoot;
        }

        return $basePath.'/public';
    }

    /**
     * Determine whether the given path is absolute.
     */
    private static function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || (bool) preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }
}
