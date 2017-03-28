<?php
/**
 * Simple Error Manager for Zend Framework 2
 *
 * @version   1.1
 * @link      http://github.com/corin-alex/zf2-error-manager
 * @copyright Copyright (c) 2017 Corin Alexandru
 * @license   MIT
 */

namespace ErrorManager;

use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;

class Module implements BootstrapListenerInterface
{
    public function onBootstrap(EventInterface $e)
    {
        if (PHP_SAPI === 'cli') return;

        $sm = $e->getApplication()->getEventManager()->getSharedManager();
        $sm->attach('*', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'handleError'));
        $sm->attach('*', MvcEvent::EVENT_RENDER_ERROR,   array($this, 'handleError'));
    }

    /**
     * Handle error
     * @param  MvcEvent $ev
     * @return void
     */
    public function handleError(MvcEvent $ev) {
        $ex = $ev->getParam('exception');

        // We let zend handle exceptions
        if (get_class($ex) == "Exception") return;

          echo "<!DOCTYPE html><html><head><title>An error has occured</title><style>
            .errWrap { padding: 0; margin: 1em; border-radius: 0.25em;  background: #FAFAFA; box-shadow: 0 0 3px rgba(0,0,0,0.2); font-family: Helvetiva, Verdana, SansSerif; }
            .errTitle { background-color: #DA4453; height: 2.5em; line-height: 2.5em; border-top-left-radius: 0.25em;  border-top-right-radius: 0.25em; color: white; padding: 0 1em; }
            .traceTitle { background-color: #F6BB42; }
            .traceContent { height: 300px; max-height: 300px; min-height: 300px; overflow-y: scroll; }
            .errFooter { text-align: center; color: #AAA; font-size: 0.8em; }
            a.errLink { color: #AAA; text-decoration: none; }
            a.errLink:hover { color: #666; }
            .errBody { padding: 1em; }
            .errBody em { font-size: 0.8em; color: #666;}
            .errBody span { color: #A00 }
            .errTracePane { margin: 1em 0; padding:1em; background-color: #FEFEFE; border: 1px solid #FFF; }
            .errTracePane pre { border:0; background:0; color #333; margin: 0; display: inline-block; vertical-align: middle;}
            </style></head><body>";

        echo '<div class="errWrap"><div class="errTitle">Error =(</div><div class="errBody">';
        echo '<span>' . $ex->getMessage() . '</span>' .
             "<em><br>In file : " . $ex->getFile() .
             "<br>Line : " . $ex->getLine() . '</em>';

        echo '<br><br><div class="errWrap"><div class="errTitle traceTitle">Trace</div><div class="errBody traceContent">';

        foreach ($ex->getTrace() as $trace) {
            echo '<div class="errTracePane">';
            if (!empty($trace['file'])) echo "File : " . $trace['file'] . "<br>";
            if (!empty($trace['line'])) echo "Line : " . $trace['line'] . "<br>";
            if (!empty($trace['function'])) echo "Function : " . $trace['function'] . "<br>";
            if (!empty($trace['class'])) echo "Class : " . $trace['class'] . "<br>";
            if (!empty($trace['type'])) echo "Type : " . $trace['type'] . "<br>";

            echo "Args :<br>";
            $this->errListArgs($trace['args']);
            echo "</div>";
        }
        echo '</div></div></div><div class="errFooter"><a class="errLink" href="https://github.com/corin-alex/zf2-error-manager" target="_blank">ZF2 Error Manager 1.1 by Qhorin</a></div><br></div>';
        echo "</body></html>";
        exit;
    }

    /**
     * List error args
     * @param array $args Error args list
     * @return void
     */
    private function errListArgs(array $args)
    {
        echo "<ul>";
        foreach ($args as $arg) {
            echo "<li>";
            switch (gettype($arg)) {
                case 'object' :
                    echo "Object : " . get_class($arg);
                    break;
                case 'array' :
                    echo "Array(" . count($arg) . ")";
                    $this->errListArgs($arg);
                    break;
                default :
                    var_dump($arg);
            }
            echo "</li>";
        }
        echo "</ul>";
    }
}
