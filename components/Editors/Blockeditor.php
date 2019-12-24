<?php
/**
 * Blockeditor
 *
 * PHP version 7
 *
 * @category    Blockeditor
 * @package     Xpressengine\Plugins\Blockeditor
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */

namespace Xpressengine\Plugins\Blockeditor\Components\Editors;

use Illuminate\Contracts\Routing\UrlGenerator;
use Xpressengine\Editor\AbstractEditor;
use Route;
use Xpressengine\Plugins\Blockeditor\plugin;
use Xpressengine\Plugin\PluginRegister;
use Xpressengine\Plugins\Blockeditor\BlockeditorPluginInterface;
use Illuminate\Contracts\Auth\Access\Gate;

/**
 * Blockeditor
 *
 * @category    Blockeditor
 * @package     Xpressengine\Plugins\Blockeditor
 * @author      XE Developers <developers@xpressengine.com>
 * @copyright   2019 Copyright XEHub Corp. <https://www.xehub.io>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        https://xpressengine.io
 */
class Blockeditor extends AbstractEditor
{
    protected static $loaded = false;

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        $this->initAssets();

        $script = <<<'DDD'
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Laraberg.init('xeContentEditor', {
                    laravelFilemanager: false,
                    searchCb: null
                })
            })
        </script>
DDD;
        $content = '<div class="write_form_editor" style="width: 100vw; left: 50%; margin-left: -50vw; position: relative;">';
        $content .= parent::render();
        $content .= '</div>';
        $content .= $script;

        $this->arguments['content'] = str_replace(['&lt;', '&gt;'], ['&amp;lt;', '&amp;gt;'], $this->arguments['content']);
        return $this->renderPlugins($content, $this->scriptOnly);
    }

    protected function renderPlugins($content, $scriptOnly)
    {
        // return $content;

        /** @var BlockeditorPluginInterface $plugin */
        foreach ($this->getPlugins() as $plugin) {
            $content = $plugin::render($content, $scriptOnly);
        }

        return $content;
    }

    protected function getPlugins()
    {
        return app('xe.pluginRegister')->get(self::getId() . PluginRegister::KEY_DELIMITER . 'plugin') ?: [];
    }

    /**
     * initAssets
     *
     * @return void
     */
    protected function initAssets()
    {
        if (self::$loaded === false) {
            self::$loaded = true;

            $this->frontend->js([
                'https://unpkg.com/react@16.8.6/umd/react.production.min.js',
                'https://unpkg.com/react-dom@16.8.6/umd/react-dom.production.min.js',
                plugin::asset('assets/laraberg.js')
            ])->before('assets/core/editor/editor.bundle.js')->load();

            $this->frontend->css([
                plugin::asset('assets/laraberg.css')
            ])->load();

            $lang = require realpath(__DIR__.'/../../langs') . '/lang.php';

            $keywords = array_keys($lang);

            expose_route('media_library.index');
            expose_route('media_library.drop');
            expose_route('media_library.get_folder');
            expose_route('media_library.store_folder');
            expose_route('media_library.update_folder');
            expose_route('media_library.move_folder');
            expose_route('media_library.get_file');
            expose_route('media_library.update_file');
            expose_route('media_library.modify_file');
            expose_route('media_library.move_file');
            expose_route('media_library.upload');
            expose_route('media_library.download_file');

            $this->frontend->translation(array_map(function ($keyword) {
                return 'blockeditor::' . $keyword;
            }, $keywords));
        }
    }

    /**
     * Get a editor name
     *
     * @return string
     */
    public function getName()
    {
        return 'XEblockeditor';
    }

    /**
     * Determine if a editor html usable.
     *
     * @return boolean
     */
    public function htmlable()
    {
        return true;
    }

    /**
     * Compile content body
     *
     * @param string $content content
     * @return string
     */
    protected function compileBody($content)
    {
        $this->frontend->css([
            plugin::asset('assets/css/content.css')
        ])->load();

        // @deprecated `.__xe_contents_compiler` https://github.com/xpressengine/xpressengine/issues/867
        return sprintf('<div class="__xe_contents_compiler">%s</div>', $this->compilePlugins($content));
    }

    protected function compilePlugins($content)
    {
        /** @var BlockeditorPluginInterface $plugin */
        foreach ($this->getPlugins() as $plugin) {
            $content = $plugin::compile($content);
        }

        return $content;
    }
}
