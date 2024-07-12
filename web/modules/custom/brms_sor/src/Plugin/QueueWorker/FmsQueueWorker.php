<?php declare(strict_types = 1);

namespace Drupal\brms_sor\Plugin\QueueWorker;

use Drupal\brms_sor\Service\FmsSorApi;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines 'brms_sor_fms' queue worker.
 *
 * @QueueWorker(
 *   id = "brms_sor_fms",
 *   title = @Translation("FMS queue worker"),
 *   cron = {"time" = 60},
 * )
 */
final class FmsQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new SystemOfRecord instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected FmsSorApi $client,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('entity_type.manager'),
      $container->get('brms_sor.fms_client'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data): void {
    if (!isset($data['global_id'])) {
      $this->loggerFactory->get('brms_sor')->error('Missing global_id in queue item.');
      return;
    }
    // Call the API update the polygon of the corresponding CFR in the FMS.
    $this->client->updateCfrPolygon($data['global_id'], $data['polygon']);
    // @todo should we keep the item in the queue if the API call fails?
  }

}
