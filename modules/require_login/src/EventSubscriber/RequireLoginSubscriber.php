<?php

namespace Drupal\require_login\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Path\PathMatcher;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\path_alias\AliasManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Route;

/**
 * Login requirement.
 */
class RequireLoginSubscriber implements EventSubscriberInterface {

  /**
   * The event exception boolean.
   *
   * @var bool
   */
  protected $eventException;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The account proxy.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $accountProxy;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcher
   */
  protected $pathMatcher;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The current path stack.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManager
   */
  protected $aliasManager;

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountProxyInterface $account_proxy
   *   The account proxy.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Drupal\Core\Path\PathMatcher $path_matcher
   *   The path matcher.
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path stack.
   * @param \Drupal\path_alias\AliasManager $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory, RequestStack $request_stack, AccountProxyInterface $account_proxy, MessengerInterface $messenger, PathMatcher $path_matcher, CurrentPathStack $current_path, AliasManager $alias_manager, RouteMatchInterface $route_match) {
    $this->eventException = FALSE;
    $this->moduleHandler = $module_handler;
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
    $this->accountProxy = $account_proxy;
    $this->messenger = $messenger;
    $this->pathMatcher = $path_matcher;
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
    $this->routeMatch = $route_match;
  }

  /**
   * Check login authentication enforcement for current request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event response.
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The configuration object.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The HTTP request.
   *
   * @return bool
   *   Return FALSE if authentication isn't required. Otherwise TRUE.
   */
  private function checkLogin(GetResponseEvent $event, ImmutableConfig $config, Request $request) {
    // Check event exception status codes.
    if ($event instanceof GetResponseForExceptionEvent) {
      /** @var \Symfony\Component\HttpKernel\Exception\HttpException $exception */
      $exception = $event->getException();
      switch ($exception->getStatusCode()) {
        case '403':
          if ($config->get('excluded_403')) {
            return FALSE;
          }
          break;

        case '404':
          if ($config->get('excluded_404')) {
            return FALSE;
          }
          break;
      }
    }

    /** @var \Symfony\Component\Routing\Route $route */
    $route = $request->get('_route_object');

    // Default authentication exclusions.
    if ($route instanceof Route && $route->hasRequirement('_user_is_logged_in') && $route->getRequirement('_user_is_logged_in') === 'FALSE') {
      return FALSE;
    }
    $route_name = $request->get('_route');
    $default_checks = [
      // Check if CLI environment.
      (PHP_SAPI === 'cli'),
      // Check system.cron route.
      ($route_name === 'system.cron'),
      // Check system.db_update route (/update.php).
      ($route_name === 'system.db_update'),
      // Check user.* routes (/user/*).
      ($route_name === 'user.register' || $route_name === 'user.pass' || substr($route_name, 0, 10) === 'user.reset'),
      // Check image.style_* routes.
      ($route_name === 'image.style_public' || $route_name === 'image.style_private'),
    ];
    $this->moduleHandler->alter('require_login_authcheck', $default_checks);
    if (in_array(TRUE, $default_checks)) {
      return FALSE;
    }

    // Configurable route name exclusions.
    $excluded_routes = array_filter(preg_split('/\r\n|\r|\n/', $config->get('excluded_routes')));
    if (in_array($route_name, $excluded_routes)) {
      return FALSE;
    }

    // Configurable node type exclusions.
    if ($route_name === 'entity.node.canonical' && ($node_types = $config->get('excluded_node_types'))) {
      if (($node = $this->routeMatch->getParameter('node')) && in_array($node->bundle(), $node_types, TRUE)) {
        return FALSE;
      }
    }

    // Configurable path exclusions.
    $current_path = $this->currentPath->getPath($request);
    $current_path_alias = $this->aliasManager->getAliasByPath($current_path);
    $current_path_parameters = $request->query->all();
    $excluded_paths = array_filter(preg_split('/\r\n|\r|\n/', $config->get('excluded_paths')));
    $excluded_paths[] = $config->get('auth_path');

    foreach ($excluded_paths as $path) {
      $path = trim($path);
      $path_parts = UrlHelper::parse($path);
      $path_parts['path'] = mb_strtolower($path_parts['path']);
      $current_checks = [
        ($this->pathMatcher->matchPath($current_path, $path_parts['path'])),
        ($this->pathMatcher->matchPath($current_path_alias, $path_parts['path'])),
      ];
      if (!empty($path_parts['query'])) {
        if (in_array(TRUE, $current_checks)) {
          // Path matched an exclusion. Now check for matching query parameters.
          if (count(array_intersect($current_path_parameters, $path_parts['query'])) === count($path_parts['query'])) {
            return FALSE;
          }
        }
      }
      elseif (in_array(TRUE, $current_checks)) {
        // Path matched an exclusion. No query parameters to check.
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * Prepare login redirect response.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event response.
   *
   * @return string|null
   *   The redirect URL.
   */
  private function loginRedirect(GetResponseEvent $event) {
    if ($this->accountProxy->getAccount()->isAuthenticated()) {
      // Stop if user is authenticated.
      return NULL;
    }
    $config = $this->configFactory->get('require_login.config');
    $request = $this->requestStack->getCurrentRequest();

    if ($this->checkLogin($event, $config, $request)) {
      // Show access denied warning message.
      if ($message = $config->get('deny_message')) {
        $messenger = $this->messenger;
        $messenger->addMessage($message, $messenger::TYPE_WARNING);
      }

      // Prepare login and destination paths.
      if ($auth_path = $config->get('auth_path')) {
        $redirect_path = "internal:{$auth_path}";
      }
      else {
        $redirect_path = 'internal:/user/login';
      }
      if (empty($destination = $config->get('destination_path'))) {
        $destination = $request->getRequestUri();
      }
      return Url::fromUri($redirect_path, ['query' => ['destination' => $destination]])
        ->toString();
    }

    return NULL;
  }

  /**
   * Login redirect on KernelEvents::EXCEPTION.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event response.
   */
  public function exceptionRedirect(GetResponseEvent $event) {
    // Boolean indicating event exception. Prevents potential infinite
    // redirect loop on KernelEvents::REQUEST.
    $this->eventException = TRUE;

    if ($redirect = $this->loginRedirect($event)) {
      $response = new RedirectResponse($redirect);
      $event->setResponse($response);
    }
  }

  /**
   * Login redirect on KernelEvents::REQUEST.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event response.
   */
  public function requestRedirect(GetResponseEvent $event) {
    if (!$this->eventException && ($redirect = $this->loginRedirect($event))) {
      $response = new RedirectResponse($redirect);
      $event->setResponse($response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::EXCEPTION][] = ['exceptionRedirect'];
    $events[KernelEvents::REQUEST][] = ['requestRedirect'];
    return $events;
  }

}
