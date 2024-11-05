<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\spark\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use putyourlightson\datastar\events\ConsoleEvent;
use putyourlightson\datastar\events\DeleteEvent;
use putyourlightson\datastar\events\EventInterface;
use putyourlightson\datastar\events\FragmentEvent;
use putyourlightson\datastar\events\RedirectEvent;
use putyourlightson\datastar\events\SignalEvent;
use putyourlightson\spark\Spark;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class EventsService extends Component
{
    /**
     * Merges a fragment into the DOM.
     */
    public function fragment(string $content, array $options = []): void
    {
        // Merge and remove empty values
        $options = array_filter(array_merge(
            Spark::getInstance()->settings->defaultFragmentOptions,
            $options
        ));

        $this->sendEvent(FragmentEvent::class, $content, $options);
    }

    /**
     * Sets store values.
     */
    public function store(array $values): void
    {
        $this->sendEvent(SignalEvent::class, '', ['store' => Json::encode($values)]);
    }

    /**
     * Removes all elements that match the selector from the DOM.
     */
    public function remove(string $selector): void
    {
        $this->sendEvent(DeleteEvent::class, '', ['selector' => $selector]);
    }

    /**
     * Redirects the page to the provided URI.
     */
    public function redirect(string $uri): void
    {
        $this->sendEvent(RedirectEvent::class, $uri);
    }

    /**
     * Outputs a message to the browser console.
     */
    public function console(string $message, string $mode = 'log'): void
    {
        $this->sendEvent(ConsoleEvent::class, $message, ['mode' => $mode]);
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     *
     * @phpstan-return never
     */
    public function throwException(Throwable|string $exception): void
    {
        Craft::$app->getRequest()->getHeaders()->set('Accept', 'text/html');
        Craft::$app->getResponse()->format = Response::FORMAT_HTML;

        if ($exception instanceof Throwable) {
            throw $exception;
        }

        throw new BadRequestHttpException($exception);
    }

    /**
     * Sends an event to the browser. This method is public so it can be mocked in tests.
     */
    public function sendEvent(string $class, string $content = '', array $options = []): void
    {
        /** @var EventInterface $event */
        $event = new $class();

        if (property_exists($event, 'content')) {
            $event->content = $content;
        }

        foreach ($options as $key => $value) {
            $event->{$key} = $value;
        }

        $this->flushEvent($event);
    }

    private function flushEvent(EventInterface $event): void
    {
        // Capture inline content before ending output buffers.
        $inlineContent = ob_get_contents();

        // Clean and end all existing output buffers.
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Output inline content as a fragment event.
        if ($inlineContent !== false) {
            $fragment = new FragmentEvent();
            $fragment->content = $inlineContent;
            echo $fragment->getOutput();
        }

        echo $event->getOutput();

        flush();

        // Start a new output buffer to capture any subsequent inline content.
        ob_start();
    }
}
