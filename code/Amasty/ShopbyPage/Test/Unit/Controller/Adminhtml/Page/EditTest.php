<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyPage
 */


use Amasty\ShopbyPage\Controller\Adminhtml\Page\Edit;
use Amasty\ShopbyPage\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class Edit
 *
 * @see Edit
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class EditTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Edit|MockObject
     */
    private $edit;

    /**
     * @covers Edit::execute
     * @dataProvider dataProvider
     */
    public function testExecute($id, $expected)
    {
        $this->edit = $this->getMockBuilder(Edit::class)
            ->disableOriginalConstructor()
            ->setMethods(['_initAction'])
            ->getMockForAbstractClass();

        $page = $this->createMock(\Magento\Framework\View\Result\Page::class);

        $pageFactory = $this->createMock(\Magento\Framework\View\Result\PageFactory::class);
        $pageFactory->expects($this->any())->method('create')->willReturn($page);
        $this->setProperty($this->edit, '_pageFactory', $pageFactory, Edit::class);

        $request = $this->getObjectManager()
            ->getObject(\Magento\Framework\App\Request\Http::class);
        $request->setParam('id', $id);
        $this->setProperty($this->edit, '_request', $request, Edit::class);

        $redirect = $this->getObjectManager()
            ->getObject(\Magento\Framework\Controller\Result\Redirect::class);

        $redirectFactory = $this->createMock(\Magento\Framework\Controller\Result\RedirectFactory::class);
        $redirectFactory->expects($this->any())->method('create')->willReturn($redirect);
        $this->setProperty($this->edit, 'resultRedirectFactory', $redirectFactory, Edit::class);

        $pageRepository = $this->createMock(\Amasty\ShopbyPage\Model\ResourceModel\PageRepository::class);

        if (!$id) {
            $amPage = $this->createMock(\Amasty\ShopbyPage\Model\Data\Page::class);
            $pageRepository->expects($this->any())->method('get')->willReturn($amPage);

            $coreRegistry = $this->getObjectManager()->getObject(\Magento\Framework\Registry::class);
            $this->setProperty($this->edit, '_coreRegistry', $coreRegistry, Edit::class);
            $resultPage = $this->createPartialMock(
                \Magento\Backend\Model\View\Result\Page::class,
                ['addBreadcrumb']
            );
            $resultPage->method('addBreadcrumb')->willReturn($resultPage);
            $this->edit->expects($this->any())->method('_initAction')->willReturn($resultPage);
            $pageConfig = $this->getObjectManager()->getObject(\Magento\Framework\View\Page\Config::class);
            $this->setProperty(
                $resultPage,
                'pageConfig',
                $pageConfig,
                \Magento\Backend\Model\View\Result\Page::class
            );
        } else {
            $pageRepository->expects($this->any())->method('get')->willReturn(false);
        }
        $this->setProperty($this->edit, '_pageRepository', $pageRepository, Edit::class);

        $session = $this->createPartialMock(\Magento\Backend\Model\Session::class, ['getFormData']);
        $session->expects($this->any())->method('getFormData')->willReturn([]);
        $this->setProperty($this->edit, '_session', $session, Edit::class);

        $result = $this->edit->execute();
        $this->assertInstanceOf(
            $expected,
            $result
        );
    }

    /**
     * Data provider for testExecute
     * @return array
     */
    public function dataProvider()
    {
        return [
            [1, \Magento\Framework\Controller\Result\Redirect::class],
            [null, \Magento\Backend\Model\View\Result\Page::class]
        ];
    }
}
