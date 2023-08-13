<?php
/**
 * ImageBox's action component for supporting Move plugin.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     FFTiger <fftiger@wikisquare.com>, myst6re <myst6re@wikisquare.com>
 */

class action_plugin_imagebox extends DokuWiki_Action_Plugin {

    function register(Doku_Event_handler $controller) {
        $controller->register_hook('PLUGIN_MOVE_HANDLERS_REGISTER', 'BEFORE', $this, 'handle_move_register');
    }

    public function handle_move_register(Doku_Event $event, $params) {
        $event->data['handlers']['imagebox'] = array($this, 'rewrite_imagebox');
    }

    public function rewrite_imagebox($match, $state, $pos, $plugin, helper_plugin_move_handler $handler) {

        // Only work on enter pattern. (Do not change description and exit pattern.)
        if (substr($match, 0, 3) != '[{{') return $match;

        // Get pure syntax without markup.
        if (substr($match, -1) == '|') {
            $syntax = substr($match, 3, -1);
        } else {
            $syntax = substr($match, 3);
        }

        $left_blank = false;
        $right_blank = false;
        if (substr($syntax, 0, 1) == ' ') {
            $left_blank = true;
            $syntax = substr($syntax, 1);
        }
        if (substr($syntax, -1) == ' ') {
            $right_blank = true;
            $syntax = substr($syntax, 0, -1);
        }

        list($src, $option) = array_pad(explode('?', $syntax, 2), 2, '');

        // Resolve new source.
        if (method_exists($handler, 'adaptRelativeId')) {
            $new_src = $handler->adaptRelativeId($src);
        } else {
            $new_src = $handler->resolveMoves($src, 'media');
            $new_src = $handler->relativeLink($src, $new_src, 'media');
        }

        if ($src == $new_src) {
            return $match;
        } else {
            // Construct result.
            $result = '[{{';
            if ($left_blank) $result .= ' ';
            $result .= $new_src;
            if ($option) $result .= "?".$option;
            if ($right_blank) $result .= ' ';
            $result .= "|";

            return $result;
        }

    }

}
?>