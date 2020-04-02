<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Xnotif
 */


namespace Amasty\Xnotif\Controller\Email;

use Amasty\Xnotif\Model\Source\Group;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;

abstract class AbstractEmail extends Action
{
    /**
     * @var \Magento\Customer\Model\Session|null
     */
    protected $customerSession = null;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var \Amasty\Xnotif\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Context $context,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\Xnotif\Helper\Config $config
    ) {
        parent::__construct($context);
        $this->customerSessionFactory = $customerSessionFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @return array
     */
    abstract public function getAllowedCustomerGroups();

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    protected function initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product_id');

        return $this->productRepository->getById(
            $productId,
            false,
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $result = parent::dispatch($request);
        $allowedGroups = $this->getAllowedCustomerGroups();

        if (!in_array(Group::ALL_GROUPS, $allowedGroups)
            && !in_array(Group::NOT_LOGGED_IN_VALUE, $allowedGroups)
            && !$this->getCustomerSession()->authenticate($this)
        ) {
            $this->setFlag('', 'no-dispatch', true);

            if (!$this->getCustomerSession()->getBeforeUrl()) {
                $this->getCustomerSession()->setBeforeUrl($this->_redirect->getRefererUrl());
            }
        }

        return $result;
    }

    /**
     * @return \Magento\Customer\Model\Session
     */
    protected function getCustomerSession()
    {
        if ($this->customerSession === null) {
            $this->customerSession = $this->customerSessionFactory->create();
        }

        return $this->customerSession;
    }

    /**
     * @param array $data
     *
     * @throws LocalizedException
     */
    protected function validateGDRP($data)
    {
        if ($this->config->isGDRPEnabled()
            && !$this->config->isLoggedIn()
            && (!isset($data['gdrp']) || !$data['gdrp'])
        ) {
            throw new LocalizedException(__('Please agree to the Privacy Policy'));
        }
    }

    /**
     * @param $email
     *
     * @return mixed
     * @throws LocalizedException
     */
    protected function validateEmail($email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if (!\Zend_Validate::is($email, 'EmailAddress')) {
            throw new LocalizedException(__('Please enter a valid email address.'));
        }

        return $email;
    }
}
