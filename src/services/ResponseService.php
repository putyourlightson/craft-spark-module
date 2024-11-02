<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\spark\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use craft\web\View;
use putyourlightson\spark\models\ConfigModel;
use putyourlightson\spark\models\StoreModel;
use putyourlightson\spark\Spark;
use Throwable;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class ResponseService extends Component
{
    /**
     * @var string|null The CSRF token to include in the response.
     */
    private ?string $csrfToken = null;

    /**
     * Streams the response and returns an empty array.
     */
    public function stream(string $config, array $store): array
    {
        $config = $this->getConfigForResponse($config);
        Craft::$app->getSites()->setCurrentSite($config->siteId);
        $this->csrfToken = $config->csrfToken;

        $store = new StoreModel($store);
        $variables = array_merge(
            [Spark::getInstance()->settings->storeVariableName => $store],
            $config->variables,
        );

        $content = $this->renderTemplate($config->template, $variables);

        // Output any rendered content in a fragment event.
        if (!empty($content)) {
            Spark::getInstance()->events->fragment($content);
        }

        return [];
    }

    /**
     * Runs an action and returns the response.
     */
    public function runAction(string $route, array $params = []): Response
    {
        $request = Craft::$app->getRequest();
        $request->getHeaders()->set('Accept', 'application/json');

        if ($this->csrfToken !== null) {
            $params[$request->csrfParam] = $this->csrfToken;
        }

        if ($request->getIsGet()) {
            $request->setQueryParams($params);
        } else {
            $request->setBodyParams($params);
        }

        $response = Craft::$app->runAction($route);

        $request->setQueryParams([]);
        $request->setBodyParams([]);

        return $response;
    }

    private function getConfigForResponse(string $config): ConfigModel
    {
        $data = Craft::$app->getSecurity()->validateData($config);
        if ($data === false) {
            $this->throwException('Submitted data was tampered.');
        }

        return new ConfigModel(Json::decodeIfJson($data));
    }

    private function renderTemplate(string $template, array $variables): string
    {
        if (!Craft::$app->getView()->doesTemplateExist($template, View::TEMPLATE_MODE_SITE)) {
            $this->throwException('Template `' . $template . '` does not exist.');
        }

        try {
            return Craft::$app->getView()->renderTemplate($template, $variables, View::TEMPLATE_MODE_SITE);
        } catch (Throwable $exception) {
            $this->throwException($exception);
        }
    }

    /**
     * Throws an exception with the appropriate formats for easier debugging.
     */
    private function throwException(Throwable|string $exception): void
    {
        Craft::$app->getRequest()->getHeaders()->set('Accept', 'text/html');
        Craft::$app->getResponse()->format = Response::FORMAT_HTML;

        if ($exception instanceof Throwable) {
            throw $exception;
        }

        throw new BadRequestHttpException($exception);
    }
}
