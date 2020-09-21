<?php
/**
 * @category  ScandiPWA
 * @package   ScandiPWA\GtmGraphQl
 * @author    Rihards Stasans <info@scandiweb.com>
 * @author    Dmitrijs Voronovs <info@scandiweb.com>
 * @copyright Copyright (c) 2019 Scandiweb, Inc (https://scandiweb.com)
 * @license   http://opensource.org/licenses/OSL-3.0 The Open Software License 3.0 (OSL-3.0)
 */

namespace ScandiPWA\GtmGraphQl\Model\Resolver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;

/**
 * Class GetGtm
 *
 * @package ScandiPWA\GtmGraphQl\Model\Resolver
 */
class GetGtm implements ResolverInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * GetGtm constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get gtm configuration
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     *
     * @return array|Value|mixed
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return [
            'enabled' => !!$this->getConfigData('enabled'),
            'gtm_id' => $this->getConfigData('gtm_id'),
            'events' => [
                'general' => $this->getConfigData('general', 'events'),
                'productImpression' => $this->getConfigData('productImpression', 'events'),
                'productClick' => $this->getConfigData('productClick', 'events'),
                'productDetail' => $this->getConfigData('productDetail', 'events'),
                'addToCart' => $this->getConfigData('addToCart', 'events'),
                'removeFromCart' => $this->getConfigData('removeFromCart', 'events'),
                'checkout' => $this->getConfigData('checkout', 'events'),
                'checkoutOption' => $this->getConfigData('checkoutOption', 'events'),
                'purchase' => $this->getConfigData('purchase', 'events'),
                'userLogin' => $this->getConfigData('userLogin', 'events'),
                'userRegister' => $this->getConfigData('userRegister', 'events'),
                'notFound' => $this->getConfigData('notFound', 'events'),
                'categoryFilters' => $this->getConfigData('categoryFilters', 'events'),
                'additional' => $this->getConfigData('additional', 'events'),
            ]
        ];
    }

    /**
     * Get config data
     *
     * @param $field
     * @param string $section
     * @return bool|mixed
     */
    protected function getConfigData($field, $section = 'general')
    {
        $path = 'pwa_gtm/' . $section . '/' . $field;

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
