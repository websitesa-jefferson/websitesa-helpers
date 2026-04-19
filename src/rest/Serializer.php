<?php

declare(strict_types=1);

namespace Websitesa\Yii2\Helpers\Rest;

use Websitesa\Yii2\Helpers\Helper\CheckHelper;
use Yii;
use yii\base\Arrayable;
use yii\base\Component;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\web\Link;
use yii\web\Request;
use yii\web\Response;

class Serializer extends Component
{
    /**
     * @var string the name of the query parameter containing the information about which fields should be returned
     *             for a [[Model]] object. If the parameter is not provided or empty, the default set of fields as defined
     *             by [[Model::fields()]] will be returned.
     */
    public $fieldsParam = 'fields';
    /**
     * @var string the name of the query parameter containing the information about which fields should be returned
     *             in addition to those listed in [[fieldsParam]] for a resource object.
     */
    public $expandParam = 'expand';
    /**
     * @var string the name of the HTTP header containing the information about total number of data items.
     *             This is used when serving a resource collection with pagination.
     */
    public $totalCountHeader = 'X-Pagination-Total-Count';
    /**
     * @var string the name of the HTTP header containing the information about total number of pages of data.
     *             This is used when serving a resource collection with pagination.
     */
    public $pageCountHeader = 'X-Pagination-Page-Count';
    /**
     * @var string the name of the HTTP header containing the information about the current page number (1-based).
     *             This is used when serving a resource collection with pagination.
     */
    public $currentPageHeader = 'X-Pagination-Current-Page';
    /**
     * @var string the name of the HTTP header containing the information about the number of data items in each page.
     *             This is used when serving a resource collection with pagination.
     */
    public $perPageHeader = 'X-Pagination-Per-Page';
    /**
     * @var string|null the name of the envelope (e.g. `items`) for returning the resource objects in a collection.
     *                  This is used when serving a resource collection. When this is set and pagination is enabled, the serializer
     *                  will return a collection in the following format:
     *
     * ```php
     * [
     *     'items' => [...],  // assuming collectionEnvelope is "items"
     *     '_links' => {  // pagination links as returned by Pagination::getLinks()
     *         'self' => '...',
     *         'next' => '...',
     *         'last' => '...',
     *     },
     *     '_meta' => {  // meta information as returned by Pagination::toArray()
     *         'totalCount' => 100,
     *         'pageCount' => 5,
     *         'currentPage' => 1,
     *         'perPage' => 20,
     *     },
     * ]
     * ```
     *
     * If this property is not set, the resource arrays will be directly returned without using envelope.
     * The pagination information as shown in `_links` and `_meta` can be accessed from the response HTTP headers.
     */
    public $collectionEnvelope;
    /**
     * @var string the name of the envelope (e.g. `_links`) for returning the links objects.
     *             It takes effect only, if `collectionEnvelope` is set.
     * @since 2.0.4
     */
    public $linksEnvelope = '_links';
    /**
     * @var string the name of the envelope (e.g. `_meta`) for returning the pagination object.
     *             It takes effect only, if `collectionEnvelope` is set.
     * @since 2.0.4
     */
    public $metaEnvelope = '_meta';
    /**
     * @var Request|null the current request. If not set, the `request` application component will be used.
     */
    public $request;
    /**
     * @var Response|null the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;
    /**
     * @var bool whether to preserve array keys when serializing collection data.
     *           Set this to `true` to allow serialization of a collection as a JSON object where array keys are
     *           used to index the model objects. The default is to serialize all collections as array, regardless
     *           of how the array is indexed.
     * @see serializeDataProvider()
     * @since 2.0.10
     */
    public $preserveKeys = false;


    public function init(): void
    {
        if (CheckHelper::valueExists($this->request) === false) {
            $this->request = Yii::$app->getRequest();
        }

        if (CheckHelper::valueExists($this->response) === false) {
            $this->response = Yii::$app->getResponse();
        }
    }

    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]], [[DataProviderInterface]] and [\JsonSerializable](https://www.php.net/manual/en/class.jsonserializable.php).
     * You may override this method to support more object types.
     * @param mixed $data the data to be serialized.
     * @return mixed the converted data.
     */
    public function serialize($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            return $this->serializeModel($data);
        } elseif ($data instanceof \JsonSerializable) {
            return $data->jsonSerialize();
        } elseif ($data instanceof DataProviderInterface) {
            return $this->serializeDataProvider($data);
        } elseif (is_array($data)) {
            $serializedArray = [];
            foreach ($data as $key => $value) {
                $serializedArray[$key] = $this->serialize($value);
            }
            return $serializedArray;
        }

        return $data;
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    protected function getRequestedFields(): array
    {
        if (!$this->request instanceof Request) {
            return [[], []];
        }
        $fields = $this->request->get($this->fieldsParam);
        $expand = $this->request->get($this->expandParam);

        $fieldsArray = is_string($fields) ? preg_split('/\s*,\s*/', $fields, -1, PREG_SPLIT_NO_EMPTY) : [];
        $expandArray = is_string($expand) ? preg_split('/\s*,\s*/', $expand, -1, PREG_SPLIT_NO_EMPTY) : [];

        return [
            is_array($fieldsArray) ? $fieldsArray : [],
            is_array($expandArray) ? $expandArray : [],
        ];
    }

    /**
     * Serializes a data provider.
     * @param DataProviderInterface $dataProvider
     * @return array<string, mixed>|array<int, mixed>|null the array representation of the data provider.
     */
    protected function serializeDataProvider($dataProvider): array|null
    {
        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }
        $models = $this->serializeModels($models);

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }

        if ($this->request instanceof Request && $this->request->getIsHead()) {
            return null;
        } elseif (CheckHelper::valueExists($this->collectionEnvelope) === false) {
            return $models;
        }

        $result = [
            $this->collectionEnvelope => $models,
        ];
        if ($pagination !== false) {
            return array_merge($result, $this->serializePagination($pagination));
        }

        return $result;
    }

    /**
     * Serializes a pagination into an array.
     * @param Pagination $pagination
     * @return array<string, mixed> the array representation of the pagination
     * @see addPaginationHeaders()
     */
    protected function serializePagination($pagination): array
    {
        return [
            $this->linksEnvelope => Link::serialize($pagination->getLinks(true)),
            $this->metaEnvelope  => [
                'totalCount'  => $pagination->totalCount,
                'pageCount'   => $pagination->getPageCount(),
                'currentPage' => $pagination->getPage() + 1,
                'perPage'     => $pagination->getPageSize(),
            ],
        ];
    }

    /**
     * Adds HTTP headers about the pagination to the response.
     * @param Pagination $pagination
     */
    protected function addPaginationHeaders($pagination): void
    {
        $links = [];
        foreach ($pagination->getLinks(true) as $rel => $url) {
            $links[] = "<$url>; rel=$rel";
        }

        if ($this->response instanceof Response) {
            $this->response->getHeaders()
                ->set($this->totalCountHeader, (string) $pagination->totalCount)
                ->set($this->pageCountHeader, (string) $pagination->getPageCount())
                ->set($this->currentPageHeader, (string) ($pagination->getPage() + 1))
                ->set($this->perPageHeader, (string) $pagination->pageSize)
                ->set('Link', implode(', ', $links));
        }
    }

    /**
     * Serializes a model object.
     * @param Arrayable $model
     * @return array<string, mixed>|null the array representation of the model
     */
    protected function serializeModel($model): array|null
    {
        if ($this->request instanceof Request && $this->request->getIsHead()) {
            return null;
        }

        list($fields, $expand) = $this->getRequestedFields();
        return $model->toArray($fields, $expand);
    }

    /**
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array<int, array<string, mixed>> the array representation of the errors
     */
    protected function serializeModelErrors($model): array
    {
        $result = [];

        if ($this->response instanceof Response) {
            $this->response->setStatusCode(422, 'Unprocessable Entity');
        }

        foreach ($model->getFirstErrors() as $name => $message) {

            $code = 0;
            $msg = $message;

            if (preg_match('/^(\d+)\s*-\s*(.*)$/su', $message, $matches) === 1) {
                $code = (int) $matches[1];
                $msg = $matches[2];
            }

            $result[] = [
                'name'    => 'Unprocessable Entity',
                'message' => $msg,
                'code'    => $code,
                'status'  => 422,
            ];
        }

        return $result;
    }

    /**
     * Serializes a set of models.
     * @param array<int, mixed> $models
     * @return array<int, mixed> the array representation of the models
     */
    protected function serializeModels(array $models): array
    {
        list($fields, $expand) = $this->getRequestedFields();
        foreach ($models as $i => $model) {
            if ($model instanceof Arrayable) {
                $models[$i] = $model->toArray($fields, $expand);
            } elseif (is_array($model)) {
                $models[$i] = ArrayHelper::toArray($model);
            }
        }

        return $models;
    }
}
