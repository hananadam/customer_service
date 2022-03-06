<?php
/**
 * NinjaPMS (https://ninjapms.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2021. NinjaPMS LLC (https://ninjapms.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\Http\Controllers;

use App\Models\Account;
use App\Transformers\ArraySerializer;
use App\Transformers\EntityTransformer;
use App\Utils\Ninja;
use App\Utils\Statics;
use App\Utils\Traits\AppSetup;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;

/**
 * Class BaseController.
 */
class BaseController extends Controller
{
    use AppSetup;

    /**
     * Passed from the parent when we need to force
     * includes internally rather than externally via
     * the $_REQUEST 'include' variable.
     *
     * @var array
     */
    public $forced_includes;

    /**
     * Passed from the parent when we need to force
     * the key of the response object.
     * @var string
     */
    public $forced_index;

    /**
     * Fractal manager.
     * @var object
     */
    protected $manager;

    private $first_load = [
        'user.company_user',
        'token.company_user',
        'company.customers',
        
    ];

    private $mini_load = [
        'account',
        'user.company_user',
        'token',
        'company.customers',
    ];

    public function __construct()
    {
        $this->manager = new Manager();

        $this->forced_includes = [];

        $this->forced_index = 'data';
    }

    private function buildManager()
    {
        $include = '';

        if (request()->has('first_load') && request()->input('first_load') == 'true') {
            $include = implode(',', array_merge($this->forced_includes, $this->getRequestIncludes([])));
        } elseif (request()->input('include') !== null) {
            $include = array_merge($this->forced_includes, explode(',', request()->input('include')));
            $include = implode(',', $include);
        } elseif (count($this->forced_includes) >= 1) {
            $include = implode(',', $this->forced_includes);
        }

        $this->manager->parseIncludes($include);

        $this->serializer = request()->input('serializer') ?: EntityTransformer::API_SERIALIZER_ARRAY;

        if ($this->serializer === EntityTransformer::API_SERIALIZER_JSON) {
            $this->manager->setSerializer(new JsonApiSerializer());
        } else {
            $this->manager->setSerializer(new ArraySerializer());
        }
    }

    /**
     * Catch all fallback route
     * for non-existant route.
     */
    public function notFound()
    {
        return response()->json(['message' => ctrans('texts.api_404')], 404)
            ->header('X-API-VERSION', config('ninja.minimum_client_version'))
            ->header('X-APP-VERSION', config('ninja.app_version'));
    }

    /**
     * 404 for the client portal.
     * @return Response 404 response
     */
    public function notFoundClient()
    {
        abort(404, 'Page not found in client portal.');
    }

    /**
     * API Error response.
     * @param string $message The return error message
     * @param int $httpErrorCode 404/401/403 etc
     * @return Response               The JSON response
     * @throws BindingResolutionException
     */
    protected function errorResponse($message, $httpErrorCode = 400)
    {
        $error['error'] = $message;

        $error = json_encode($error, JSON_PRETTY_PRINT);

        $headers = self::getApiHeaders();

        return response()->make($error, $httpErrorCode, $headers);
    }

    protected function refreshResponse($query)
    {
        $user = auth()->user();

        $this->manager->parseIncludes($this->first_load);

        $this->serializer = request()->input('serializer') ?: EntityTransformer::API_SERIALIZER_ARRAY;

        if ($this->serializer === EntityTransformer::API_SERIALIZER_JSON) {
            $this->manager->setSerializer(new JsonApiSerializer());
        } else {
            $this->manager->setSerializer(new ArraySerializer());
        }

        $transformer = new $this->entity_transformer($this->serializer);

        $updated_at = request()->has('updated_at') ? request()->input('updated_at') : 0;

        if ($user->getCompany()->is_large && $updated_at == 0) {
            $updated_at = time();
        }

        $updated_at = date('Y-m-d H:i:s', $updated_at);

        $query->with(
            [
                'company' => function ($query) use ($updated_at, $user) {
                    $query->whereNotNull('updated_at')->with('documents')->with('users');
                },
                'company.customers' => function ($query) use ($updated_at, $user) {
                    $query->where('customers.updated_at', '>=', $updated_at)->with('contacts.company', 'gateway_tokens', 'documents');

                    if (!$user->hasPermission('view_client'))
                        $query->where('customers.user_id', $user->id)->orWhere('customers.assigned_user_id', $user->id);

                }
            ]
        );

        if ($query instanceof Builder) {
            $limit = request()->input('per_page', 20);

            $paginator = $query->paginate($limit);
            $query = $paginator->getCollection();
            $resource = new Collection($query, $transformer, $this->entity_type);
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        } else {
            $resource = new Collection($query, $transformer, $this->entity_type);
        }

        return $this->response($this->manager->createData($resource)->toArray());
    }

    protected function miniLoadResponse($query)
    {
        $user = auth()->user();


        $this->serializer = request()->input('serializer') ?: EntityTransformer::API_SERIALIZER_ARRAY;

        if ($this->serializer === EntityTransformer::API_SERIALIZER_JSON) {
            $this->manager->setSerializer(new JsonApiSerializer());
        } else {
            $this->manager->setSerializer(new ArraySerializer());
        }

        $transformer = new $this->entity_transformer($this->serializer);
        $created_at = request()->has('created_at') ? request()->input('created_at') : 0;

        $created_at = date('Y-m-d H:i:s', $created_at);

        $query->with(
            [
                'company' => function ($query) use ($created_at, $user) {
                    $query->whereNotNull('created_at')->with('documents');
                },
                'company.designs' => function ($query) use ($created_at, $user) {
                    $query->where('created_at', '>=', $created_at)->with('company');

                },
                'company.documents' => function ($query) use ($created_at, $user) {
                    $query->where('created_at', '>=', $created_at);
                },
                'company.groups' => function ($query) use ($created_at, $user) {
                    $query->where('created_at', '>=', $created_at)->with('documents');

                },
                'company.payment_terms' => function ($query) use ($created_at, $user) {
                    $query->where('created_at', '>=', $created_at);

                },
                'company.tax_rates' => function ($query) use ($created_at, $user) {
                    $query->whereNotNull('created_at');

                },
                'company.activities' => function ($query) use ($user) {

                    if (!$user->isAdmin())
                        $query->where('activities.user_id', $user->id);

                }
            ]
        );

        if ($query instanceof Builder) {
            $limit = request()->input('per_page', 20);

            $paginator = $query->paginate($limit);
            $query = $paginator->getCollection();
            $resource = new Collection($query, $transformer, $this->entity_type);
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        } else {
            $resource = new Collection($query, $transformer, $this->entity_type);
        }

        return $this->response($this->manager->createData($resource)->toArray());


    }

    protected function timeConstrainedResponse($query)
    {

        $user = auth()->user();

        if ($user->getCompany()->is_large) {
            $this->manager->parseIncludes($this->mini_load);
            return $this->miniLoadResponse($query);
        } else
            $this->manager->parseIncludes($this->first_load);

        $this->serializer = request()->input('serializer') ?: EntityTransformer::API_SERIALIZER_ARRAY;

        if ($this->serializer === EntityTransformer::API_SERIALIZER_JSON) {
            $this->manager->setSerializer(new JsonApiSerializer());
        } else {
            $this->manager->setSerializer(new ArraySerializer());
        }

        $transformer = new $this->entity_transformer($this->serializer);

        $created_at = request()->has('created_at') ? request()->input('created_at') : 0;

        $created_at = date('Y-m-d H:i:s', $created_at);

        $query->with(
            [
                'company' => function ($query) use ($created_at, $user) {
                    $query->whereNotNull('created_at')->with('documents');
                },
                
                'company.customers' => function ($query) use ($created_at, $user) {
                    $query->where('created_at', '>=', $created_at);


                }
              
            ]
        );

        if ($query instanceof Builder) {
            $limit = request()->input('per_page', 20);

            $paginator = $query->paginate($limit);
            $query = $paginator->getCollection();
            $resource = new Collection($query, $transformer, $this->entity_type);
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        } else {
            $resource = new Collection($query, $transformer, $this->entity_type);
        }

        return $this->response($this->manager->createData($resource)->toArray());


    }

    protected function listResponse($query)
    {


        $this->buildManager();
        $transformer = new $this->entity_transformer(request()->input('serializer'));

        $includes = $transformer->getDefaultIncludes();

        $includes = $this->getRequestIncludes($includes);

        $query->with($includes);
        if (auth()->user() && !auth()->user()->hasPermission('view_' . lcfirst(class_basename($this->entity_type)))) {
            $query->where('user_id', '=', auth()->user()->id);
        }

        if (request()->has('updated_at') && request()->input('updated_at') > 0) {
            $query->where('updated_at', '>=', date('Y-m-d H:i:s', intval(request()->input('updated_at'))));
        }

        if ($this->serializer && $this->serializer != EntityTransformer::API_SERIALIZER_JSON) {
            $this->entity_type = null;
        }
        if ($query instanceof Builder) {

            $limit = request()->input('per_page', 20);
            $paginator = $query->paginate($limit);
            $query = $paginator->getCollection();

            $resource = new Collection($query, $transformer, $this->entity_type);


            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        } else {

            $resource = new Collection($query, $transformer, $this->entity_type);
        }
        return $this->response($this->manager->createData($resource)->toArray());
    }

    protected function response($response)
    {
        $index = request()->input('index') ?: $this->forced_index;

        if ($index == 'none') {
            unset($response['meta']);
        } else {
            $meta = isset($response['meta']) ? $response['meta'] : null;
            $response = [
                $index => $response,
            ];

            if ($meta) {
                $response['meta'] = $meta;
                unset($response[$index]['meta']);
            }

            if (request()->include_static) {
                $response['static'] = Statics::company(auth()->user()->getCompany()->getLocale());
            }
        }

        ksort($response);

        $response = json_encode($response, JSON_PRETTY_PRINT);

        $headers = self::getApiHeaders();

        return response()->make($response, 200, $headers);
    }

    protected function itemResponse($item)
    {
        $this->buildManager();

        $transformer = new $this->entity_transformer(request()->input('serializer'));

        if ($this->serializer && $this->serializer != EntityTransformer::API_SERIALIZER_JSON) {
            $this->entity_type = null;
        }

        $resource = new Item($item, $transformer, $this->entity_type);

        if (auth()->user() && request()->include_static) {
            $data['static'] = Statics::company(auth()->user()->getCompany()->getLocale());
        }

        return $this->response($this->manager->createData($resource)->toArray());
    }

    public static function getApiHeaders($count = 0)
    {
        return [
            'Content-Type' => 'application/json',
            'X-Api-Version' => config('ninja.minimum_client_version'),
            'X-App-Version' => config('ninja.app_version'),
        ];
    }

    protected function getRequestIncludes($data)
    {
        /*
         * Thresholds for displaying large account on first load
         */
        if (request()->has('first_load') && request()->input('first_load') == 'true') {
            if (auth()->user()->getCompany()->is_large && request()->missing('updated_at')) {
                $data = $this->mini_load;
            } else {
                $data = $this->first_load;
            }
        } else {
            $included = request()->input('include');
            $included = explode(',', $included);

            foreach ($included as $include) {
                if ($include == 'clients') {
                    $data[] = 'clients.contacts';
                } elseif ($include) {
                    $data[] = $include;
                }
            }
        }

        return $data;
    }

    public function flutterRoute()
    {
        if ((bool)$this->checkAppSetup() !== false && $account = Account::first()) {
            if (config('ninja.require_https') && !request()->isSecure()) {
                return redirect()->secure(request()->getRequestUri());
            }

            /* Clean up URLs and remove query parameters from the URL*/
            if (request()->has('login') && request()->input('login') == 'true')
                return redirect('/')->with(['login' => "true"]);

            $data = [];

            //pass report errors bool to front end
            $data['report_errors'] = Ninja::isSelfHost() ? $account->report_errors : true;

            //pass referral code to front end
            $data['rc'] = request()->has('rc') ? request()->input('rc') : '';
            $data['build'] = request()->has('build') ? request()->input('build') : '';
            $data['login'] = request()->has('login') ? request()->input('login') : "false";

            if (request()->session()->has('login'))
                $data['login'] = "true";

            $data['user_agent'] = request()->server('HTTP_USER_AGENT');

            $data['path'] = $this->setBuild();

            $this->buildCache();

            return view('index.index', $data);
        }

        return redirect('/setup');
    }

    private function setBuild()
    {
        return 'main.dart.js';
        $build = '';

        if (request()->has('build')) {
            $build = request()->input('build');
        } elseif (Ninja::isHosted()) {
            return 'main.dart.js';
        }

        switch ($build) {
            case 'wasm':
                return 'main.wasm.dart.js';
            case 'foss':
                return 'main.foss.dart.js';
            case 'last':
                return 'main.last.dart.js';
            case 'next':
                return 'main.next.dart.js';
            case 'profile':
                return 'main.profile.dart.js';
            case 'html':
                return 'main.html.dart.js';
            default:
                return 'main.foss.dart.js';

        }
    }

    public function checkFeature($feature)
    {
        if (auth()->user()->account->hasFeature($feature))
            return true;

        return false;
    }

    public function featureFailure()
    {
        return response()->json(['message' => 'Upgrade to a paid plan for this feature.'], 403);
    }
}
