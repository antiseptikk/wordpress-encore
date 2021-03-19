<?php

declare(strict_types=1);

namespace Antiseptikk;

class Encore
{
    /**
     * Output path relative to the root of this plugin/theme.
     */
    private string $outputPath;

    private string $version = '';

    /**
     * Manifest cache to prevent multiple reading from filesystem.
     */
    private array $manifestCache = [];

    /**
     * Entrypoint cache to prevent multiple reading from filesystem.
     */
    private array $entryPointCache = [];

    /**
     * Root absolute path to the output directory. With forward slash.
     */
    private string $rootPath = '';

    /**
     * Root URL to the output directory. With forward slash.
     */
    private string $rootUrl = '';

    public function __construct(string $outputPath, string $version, string $url, ?string $themePath = null)
    {
        $this->outputPath = $outputPath;
        $this->version = $version;

        if (!$themePath) {
            $themePath = \get_template_directory();
        }

        // Set the root path and URL
        $filepath = \trailingslashit($themePath) . $this->outputPath . '/';
        $this->rootPath = $filepath;
        $this->rootUrl = $url;
    }

    public function register(string $name, string $entryPoint, array $config): array
    {
        $config = $this->normalizeAssetConfig($config);

        $assets = $this->getAssets($name, $entryPoint, $config);

        $jses = $assets['js'];
        $csses = $assets['css'];

        $js_deps = [];
        $css_deps = [];

        if ($config['js']) {
            foreach ($jses as $js) {
                \wp_register_script(
                    $js['handle'],
                    $js['url'],
                    array_merge($config['js_dep'], $js_deps),
                    $this->version,
                    $config['in_footer']
                );
                // The next one depends on this one
                $js_deps[] = $js['handle'];
            }
        }

        // Register CSS files
        if ($config['css']) {
            foreach ($csses as $css) {
                \wp_register_style(
                    $css['handle'],
                    $css['url'],
                    array_merge($config['css_dep'], $css_deps),
                    $this->version,
                    $config['media']
                );
                // The next one depends on this one
                $css_deps[] = $css['handle'];
            }
        }

        return $assets;
    }

    public function enqueue(string $name, string $entryPoint, array $config): array
    {
        $config = $this->normalizeAssetConfig($config);

        $assets = $this->register($name, $entryPoint, $config);

        $jses = $assets['js'];
        $csses = $assets['css'];

        if ($config['js']) {
            foreach ($jses as $js) {
                \wp_enqueue_script($js['handle']);
            }
        }

        if ($config['css']) {
            foreach ($csses as $css) {
                \wp_enqueue_style($css['handle']);
            }
        }

        return $assets;
    }

    public function getHandle(string $name, string $path, string $type = 'script'): string
    {
        if (!\in_array($type, ['script', 'style'], true)) {
            throw new \LogicException('Type has to be either script or style.');
        }

        return 'symfony_encore_'
            . $name
            . '_'
            . $path
            . '_'
            . $type;
    }

    public function getAssets(string $name, string $entryPoint, array $config): array
    {
        $config = $this->normalizeAssetConfig($config);

        $entrypoints = $this->getEntrypoint($name);
        if (!isset($entrypoints['entrypoints'][$entryPoint])) {
            throw new \LogicException('No entry point found in the entrypoint.js');
        }

        $enqueue = $entrypoints['entrypoints'][$entryPoint];

        $js = [];
        $css = [];

        if ($config['js'] && isset($enqueue['js']) && count((array) $enqueue['js'])) {
            foreach ($enqueue['js'] as $index => $url) {
                $js[] = [
                    'handle' => $this->getHandle($name, $url, 'script'),
                    'url' => $this->getUrl($url),
                ];
            }
        }

        if ($config['css'] && isset($enqueue['css']) && count((array) $enqueue['css'])) {
            foreach ($enqueue['css'] as $index => $url) {
                $css[] = [
                    'handle' => $this->getHandle($name, $url, 'style'),
                    'url' => $this->getUrl($url),
                ];
            }
        }

        return [
            'css' => $css,
            'js' => $js,
        ];
    }

    public function getUrl(string $asset)
    {
        if (strpos('localhost', $asset)) {
            return $asset;
        }

        return $this->rootUrl . $asset;
    }

    public function normalizeAssetConfig(array $config): array
    {
        return wp_parse_args(
            $config,
            [
                'js' => true,
                'css' => true,
                'js_dep' => [],
                'css_dep' => [],
                'in_footer' => true,
                'media' => 'all',
            ]
        );
    }

    public function getManifest(string $dir): array
    {
        if (isset($this->manifestCache[$this->outputPath][$dir])) {
            return $this->manifestCache[$this->outputPath][$dir];
        }

        $filepath = $this->rootPath . '/manifest.json';

        if (!file_exists($filepath)) {
            throw new \LogicException(sprintf('Manifest %s does not exist.', $filepath));
        }

        $manifest = json_decode(file_get_contents($filepath), true);
        if ($manifest === null) {
            throw new \LogicException(sprintf('Invalid manifest file at %s. Either it is not valid JSON.', $filepath));
        }
        if (!isset($this->manifestCache[$this->outputPath])) {
            $this->manifestCache[$this->outputPath] = [];
        }
        $this->manifestCache[$this->outputPath][$dir] = $manifest;

        return $this->manifestCache[$this->outputPath][$dir];
    }

    public function getEntrypoint(string $dir): array
    {
        if (isset($this->entryPointCache[$this->outputPath][$dir])) {
            return $this->entryPointCache[$this->outputPath][$dir];
        }

        $filepath = $this->rootPath . '/entrypoints.json';

        if (!file_exists($filepath)) {
            throw new \LogicException(sprintf('Entrypoint %s does not exist.', $filepath));
        }

        $entrypoint = json_decode(file_get_contents($filepath), true);

        if ($entrypoint === null) {
            throw new \LogicException(sprintf('Invalid entrypoint file at %s. Either it is not valid JSON.', $filepath));
        }

        if (!isset($this->entryPointCache[$this->outputPath])) {
            $this->entryPointCache[$this->outputPath] = [];
        }

        $this->entryPointCache[$this->outputPath][$dir] = $entrypoint;

        return $this->entryPointCache[$this->outputPath][$dir];
    }
}
