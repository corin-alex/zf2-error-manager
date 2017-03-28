<?php
/**
 * Simple Error Manager for Zend Framework 2
 *
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
        if (PHP_SAPI === 'cli') {
            return;
        }

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

        echo "<style>
         .errPane { background: #DDD; border: 1px solid #EEE; border-radius:0.25em; padding: 1em; }
         .errMsg { color: #F00; }
         .errWrap { margin: 5em 1em; padding: 1em; background: #CCC; color:#333; border-radius:0.25em;}
         .errTracePane { background: #DDD; margin-bottom: 1em; margin-right:1em;}
         .errTracePane pre { border:0; background:0; color #333; margin: 0; display: inline-block; vertical-align: middle;}
         .errTraceWrap { height: 300px; overflow-y: scroll; }
         </style>";

        echo '<div class="errWrap"><h2>Too bad... =(</h2><div class="errPane">';
        echo '<span class="errMsg">' . $ex->getMessage() . '</span>' .
             "<br>In file : " . $ex->getFile() .
             "<br>Line : " . $ex->getLine();

        echo '</div><br><h3>Trace</h3><div class="errTraceWrap">';

        foreach ($ex->getTrace() as $trace) {
            echo '<div class="errPane errTracePane">';
            if (!empty($trace['file'])) echo "File : " . $trace['file'] . "<br>";
            if (!empty($trace['line'])) echo "Line : " . $trace['line'] . "<br>";
            if (!empty($trace['function'])) echo "Function : " . $trace['function'] . "<br>";
            if (!empty($trace['class'])) echo "Class : " . $trace['class'] . "<br>";
            if (!empty($trace['type'])) echo "Type : " . $trace['type'] . "<br>";

            echo "Args :<br>";
            $this->errListArgs($trace['args']);
            echo "</div>";
        }
        echo "</div></div>";
    }

    /**
     * List error args
     * @param array $args Error args list
     * @return void
     */
    private function errListArgs($args)
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
