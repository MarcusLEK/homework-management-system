<?php

namespace App\Http\Controllers\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApiController extends BaseController
{

    const DEFAULT_MAX_LIMIT = 1000;

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $statusCode = 200;
    protected $message = '';
    protected $error = false;
    protected $debugInfo = [];
    protected $errorCode = 0;


    /**
     * Function to return an error response.
     *
     * @param $message
     * @return mixed
     */
    public function respondWithError($message)
    {
        $this->error = true;
        $this->message = $message;
        return $this->respond(array());
    }

    /**
     * Function to return an unauthorized response.
     *
     * @param string $message
     * @return mixed
     */
    public function respondUnauthorizedError($message = 'Unauthorized!')
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
    public function respondBadRequestError($message = 'Bad Request!')
    {
        $this->statusCode = Response::HTTP_BAD_REQUEST;
        return $this->respondWithError($message);
    }

    /**
     * Function to return forbidden error response.
     *
     * @param string $message
     *
     * @return mixed
     */
    public function respondForbiddenError($message = 'Forbidden!')
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
    public function respondNotFound($message = 'Resource Not Found')
    {
        $this->statusCode = Response::HTTP_NOT_FOUND;
        return $this->respondWithError($message);
    }

    /**
     * Function to return an internal error response.
     *
     * @param string $message
     * @param null $errors
     *
     * @return mixed
     */
    public function respondInternalError($message = 'Internal Server Error!', $errors = null)
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
    public function respondMethodNotAllowed($message = 'Method not allowed!')
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
    public function respondServiceUnavailable($message = "Service Unavailable!")
    {
        $this->statusCode = Response::HTTP_SERVICE_UNAVAILABLE;
        return $this->respondWithError($message);
    }

    /**
     * Throws a bad request exception with the validator's error messages.
     *
     * @param Validator $validator The validator to get the message from
     *
     * @return mixed
     */
    public function showValidationError($validator)
    {
        $this->error = true;
        $this->statusCode = Response::HTTP_BAD_REQUEST;
        $this->message = implode("|", $validator->errors()->all());
        return $this->respond();
    }

    /**
     * Function to return a created response
     *
     * @param $data array The data to be included
     *
     * @return mixed
     *
     */
    public function respondCreated($data)
    {
        $this->statusCode = Response::HTTP_CREATED;
        return $this->respond($data);
    }

    /**
     * Function to return a response with a message
     *
     * @param $data array The data to be included
     *
     * @param $message string The message to be shown in the meta of the response
     *
     * @return mixed
     */
    public function respondWithMessage($data, $message)
    {
        $this->statusCode = Response::HTTP_OK;
        $this->message = $message;
        return $this->respond($data);
    }

    /**
     * Adds debugging information to the response
     *
     * @param $data
     */
    public function addDebugInfo($data)
    {
        $this->debugInfo[] = $data;
//        if (config('app.debug')) {
//            $this->debugInfo[] = $data;
//        }
    }

    /**
     * Function to return a generic response.
     *
     * @param $data array to be used in response.
     * @param array $headers Headers to b used in response.
     * @return mixed Return the response.
     */
    public function respond($data = [], $headers = [])
    {
        $meta = [
            'meta' => [
                'error' => $this->error,
                'message' => $this->message,
                'status_code' => $this->statusCode,
            ]
        ];
        if (empty($data) && !is_array($data))
            $data = array_merge($meta, ['response' => null]);
        else
            $data = array_merge($meta, ['response' => $data]);

        if (!empty($this->debugInfo)) {
            $data = array_merge($data, ['debug' => $this->debugInfo]);
        }

        return response()->json($data, $this->statusCode, $headers);
    }

    /**
     * Returns a LengthAwarePaginator for an array collection
     *
     * @param Request $request
     * @param array $items
     * @return LengthAwarePaginator
     */
    public function paginate(Request $request, $items) {
        $limit = min(intval($request->get('limit', 10)), self::DEFAULT_MAX_LIMIT);
        $page = (int)$request->get('page', 1);
        $offset = ($page-1) * $limit;
        $items = new LengthAwarePaginator(array_slice($items, $offset, $limit), count($items), $limit, $page);
        return $items;
    }

    /**
     * Responds paginated items
     *
     * @param Request $request
     * @param array|\Illuminate\Contracts\Pagination\LengthAwarePaginator $items
     *
     * @param array $options
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondPagination($request, $items, $options=[])
    {
        if (!($items instanceof LengthAwarePaginator)) {
            $pagination = $this->paginate($request, $items);
        } else {
            $pagination = $items;
        }
        return $this->respond(['pagination' => $this->getPagination($pagination), 'items' => $pagination->items(), 'options' => $options]);
    }

    /**
     * Retrieves the pagination meta in an array format
     *
     * @param LengthAwarePaginator $item
     * @return array
     */
    public function getPagination(LengthAwarePaginator $item) {
        return [
            'total' => $item->total(),
            'current_page' => $item->currentPage(),
            'last_page' => $item->lastPage(),
            'from' => $item->firstItem(),
            'to' => $item->lastItem()
        ];
    }

    public function successJson($message = '', $data = null) {
        return response()->json([
            'error' => false,
            'message' => $message,
            'data' => $data
        ]);
    }
    public function errorJson($message, $data = null) {
        return response()->json([
            'error' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
}
