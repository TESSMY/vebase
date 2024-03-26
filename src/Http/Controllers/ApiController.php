<?php

namespace Vecapital\Vebase\Http\Controllers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller;

class ApiController extends Controller
{
    const DEFAULT_MAX_LIMIT = 1000;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected int $statusCode = 200;

    protected string $message = '';

    protected bool $error = false;

    protected array $debugInfo = [];

    protected int $errorCode = 0;

    public int $defaultPaginateLimit = 10;

    /**
     * Function to return an error response.
     */
    public function respondWithError($message): mixed
    {
        $this->error = true;
        $this->message = $message;

        return $this->respond([]);
    }

    /**
     * Function to return an unauthorized response.
     *
     * @param string $message
     * @return mixed
     */
    public function respondUnauthorizedError(string $message = 'Unauthorized!'): mixed
    {
        $this->statusCode = Response::HTTP_UNAUTHORIZED;

        return $this->respondWithError($message);
    }

    /**
     * Function to return a bad request response.
     *
     * @param string $message
     * @return mixed
     */
    public function respondBadRequestError(string $message = 'Bad Request!'): mixed
    {
        $this->statusCode = Response::HTTP_BAD_REQUEST;

        return $this->respondWithError($message);
    }

    /**
     * Function to return forbidden error response.
     *
     * @param string $message
     * @return mixed
     */
    public function respondForbiddenError(string $message = 'Forbidden!'): mixed
    {
        $this->statusCode = Response::HTTP_FORBIDDEN;

        return $this->respondWithError($message);
    }

    /**
     * Function to return a Not Found response.
     *
     * @param string $message
     * @return mixed
     */
    public function respondNotFound(string $message = 'Resource Not Found'): mixed
    {
        $this->statusCode = Response::HTTP_NOT_FOUND;

        return $this->respondWithError($message);
    }

    /**
     * Function to return an internal error response.
     *
     * @param string $message
     * @param  null  $errors
     * @return mixed
     */
    public function respondInternalError(string $message = 'Internal Server Error!', $errors = null): mixed
    {
        $this->statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        $this->addDebugInfo($errors);

        return $this->respondWithError($message);
    }

    /**
     * Function to return an internal error response.
     *
     * @param string $message
     * @return mixed
     */
    public function respondMethodNotAllowed(string $message = 'Method not allowed!'): mixed
    {
        $this->statusCode = Response::HTTP_METHOD_NOT_ALLOWED;

        return $this->respondWithError($message);
    }

    /**
     * Function to return a service unavailable response.
     *
     * @param string $message
     * @return mixed
     */
    public function respondServiceUnavailable(string $message = 'Service Unavailable!'): mixed
    {
        $this->statusCode = Response::HTTP_SERVICE_UNAVAILABLE;

        return $this->respondWithError($message);
    }

    /**
     * Throws a bad request exception with the validator's error messages.
     *
     * @param Validator $validator The validator to get the message from
     * @return mixed
     */
    public function showValidationError(Validator $validator): mixed
    {
        $this->error = true;
        $this->statusCode = Response::HTTP_BAD_REQUEST;
        $this->message = implode('|', $validator->errors()->all());

        return $this->respond();
    }

    /**
     * Function to return a created response
     *
     * @param $data mixed The data to be included
     * @return mixed
     */
    public function respondCreated(mixed $data): mixed
    {
        $this->statusCode = Response::HTTP_CREATED;

        return $this->respond($data);
    }

    /**
     * Function to return a response with a message
     *
     * @param $data mixed The data to be included
     * @param $message string The message to be shown in the meta of the response
     * @return mixed
     */
    public function respondWithMessage(mixed $data, string $message): mixed
    {
        $this->statusCode = Response::HTTP_OK;
        $this->message = $message;

        return $this->respond($data);
    }

    /**
     * Adds debugging information to the response
     */
    public function addDebugInfo($data): void
    {
        $this->debugInfo[] = $data;
        //    if (config('app.debug')) {
        //        $this->debugInfo[] = $data;
        //    }
    }

    /**
     * Function to return a generic response.
     *
     * @param $data mixed to be used in response.
     * @param array $headers Headers to b used in response.
     * @return mixed Return the response.
     */
    public function respond(mixed $data = [], array $headers = []): mixed
    {
        $meta = [
            'meta' => [
                'error' => $this->error,
                'message' => $this->message,
                'status_code' => $this->statusCode,
            ],
        ];
        if (empty($data) && ! is_array($data)) {
            $data = array_merge($meta, ['response' => null]);
        } else {
            $data = array_merge($meta, ['response' => $data]);
        }

        if (! empty($this->debugInfo)) {
            $data = array_merge($data, ['debug' => $this->debugInfo]);
        }

        return response()->json($data, $this->statusCode, $headers);
    }

    /**
     * Returns a LengthAwarePaginator for an array collection
     *
     * @param array $items
     * @return LengthAwarePaginator
     */
    public function paginate(Request $request, array $items): LengthAwarePaginator
    {
        $limit = min(intval($request->get('limit', $this->defaultPaginateLimit)), self::DEFAULT_MAX_LIMIT);
        $page = (int) $request->get('page', 1);
        $offset = ($page - 1) * $limit;
        $items = new LengthAwarePaginator(array_slice($items, $offset, $limit), count($items), $limit, $page);

        return $items;
    }

    /**
     * Responds paginated items
     *
     * @param Request $request
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator|array $items
     * @return JsonResponse
     */
    public function respondPagination(Request $request, \Illuminate\Contracts\Pagination\LengthAwarePaginator|array $items): \Illuminate\Http\JsonResponse
    {
        if (! ($items instanceof LengthAwarePaginator)) {
            $pagination = $this->paginate($request, $items);
        } else {
            $pagination = $items;
        }

        return $this->respond(['pagination' => $this->getPagination($pagination), 'items' => $pagination->items()]);
    }

    /**
     * Retrieves the pagination meta in an array format
     */
    public function getPagination(LengthAwarePaginator $item): array
    {
        return [
            'total' => $item->total(),
            'current_page' => $item->currentPage(),
            'last_page' => $item->lastPage(),
            'from' => $item->firstItem(),
            'to' => $item->lastItem(),
        ];
    }
}
