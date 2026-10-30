<?php

namespace Shaygan\AffiliateBundle\Tests;

use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\DeprecationTriggeredSubscriber;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggeredSubscriber;
use PHPUnit\Runner\ErrorHandler;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Symfony\Bridge\PhpUnit\DeprecationErrorHandler\Deprecation;

final class DeprecationGateExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscribers(new class implements DeprecationTriggeredSubscriber {
            public function notify(DeprecationTriggered $event): void
            {
                DeprecationGateExtension::check($event);
            }
        }, new class implements PhpDeprecationTriggeredSubscriber {
            public function notify(PhpDeprecationTriggered $event): void
            {
                DeprecationGateExtension::check($event);
            }
        });
    }

    public static function check(DeprecationTriggered|PhpDeprecationTriggered $event): void
    {
        // Native events are synchronous: retain the original error-handler frame
        // so Symfony's existing project/vendor classifier sees the actual caller.
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
        foreach ($trace as $index => $frame) {
            if (($frame['class'] ?? '') === ErrorHandler::class && ($frame['function'] ?? '') === '__invoke') {
                $trace = array_slice($trace, $index);
                break;
            }
        }
        $deprecation = new Deprecation($event->message(), $trace, $event->file(), $event instanceof PhpDeprecationTriggered);
        if (in_array($deprecation->getType(), [Deprecation::TYPE_SELF, Deprecation::TYPE_DIRECT], true)) {
            // Runner warnings participate in PHPUnit's native failOnWarning exit
            // policy, while its regular deprecation report keeps vendor notices visible.
            EventFacade::emitter()->testRunnerTriggeredPhpunitWarning('Project deprecation: '.$event->message().' at '.$event->file().':'.$event->line());
        }
    }
}
