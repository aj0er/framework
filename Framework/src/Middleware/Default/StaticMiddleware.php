<?php

namespace Framework\Middleware\Default;

use Closure;
use Framework\Http\HttpRequest;
use Framework\Middleware\Middleware;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

/**
 * Default middleware som serverar statiska filer på en viss sökväg.
 */
class StaticMiddleware extends Middleware
{

    private array $allowedFiles;
    private const BACKSLASH = "\\";

    public function __construct(string $path, string $realPath)
    {
        // Skapa en lista med alla accepterade filer och underfiler för att undvika sårbarheter med relativa sökvägar.
        $this->allowedFiles = $this->getFiles($realPath, $path);
    }

    public function handle(HttpRequest $request, Closure $next, ?array $args): mixed
    {
        $requestedResource = $request->rawRoute;
        $resourceFile = $this->allowedFiles[$requestedResource] ?? null;

        if ($resourceFile != null) {
            return parent::file($resourceFile);
        }

        return $next($request);
    }

    /**
     * Metod som hämtar en lista med filer och subfiler, mappade med den statiska sökvägen som används i denna klassen.
     *
     * @param string $path Den faktiska sökvägen att leta filer i.
     * @param string $routedPath Den "virtuella" sökvägen som webbläsaren besöker.
     * @return array En associativ array, virtuell sökväg => riktig sökväg
     */
    private function getFiles(string $path, string $routedPath): array // Använder kod från https://stackoverflow.com/a/35315903
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        $files = array();
        foreach ($iterator as $file){
            if (!$file->isDir()) {
                $realName = $file->getPathname();

                $pathName = str_replace($path, $routedPath, $realName);
                $pathName = strtr($pathName, $this::BACKSLASH, '/');

                $files[$pathName] = $realName;
            }
        }

        return $files;
    }

}