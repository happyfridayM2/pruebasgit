<?php

namespace IWD\All\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Support
 * @package IWD\All\Model
 */
class Support extends \Magento\Sales\Model\AbstractModel
{
    /**
     * Email
     */
    const SUPPORT_EMAIL = 'extensions@iwdagency.com';

    /**
     * Name
     */
    const SUPPORT_NAME = 'Support';

    /**
     * @var string
     */
    private $result = '';

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    private $config;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory
     */
    private $statesFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerInterface
     */
    private $indexerFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var array
     */
    private $params;

    /**
     * Support constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Indexer\ConfigInterface $config
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory $statesFactory
     * @param \Magento\Framework\Indexer\IndexerInterface $indexerFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Indexer\ConfigInterface $config,
        \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory $statesFactory,
        \Magento\Framework\Indexer\IndexerInterface $indexerFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->moduleList = $moduleList;
        $this->cacheTypeList = $cacheTypeList;
        $this->config = $config;
        $this->statesFactory = $statesFactory;
        $this->indexerFactory = $indexerFactory;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param $params
     */
    public function sendTicket($params)
    {
        $this->initParams($params);
        $this->collectInfo();
        $this->sendEmail();
    }

    /**
     * @param $params
     * @throws \Exception
     */
    private function initParams($params)
    {
        if (!isset($params['email'])
            || !isset($params['name'])
            || !isset($params['type'])
            || !isset($params['description'])
        ) {
            throw new LocalizedException(__('Incorrect params'));
        }

        $from = $this->formatAddress($params['email'], $params['name']);
        $to = $this->formatAddress(self::SUPPORT_EMAIL, self::SUPPORT_NAME);
        $extension = isset($params['extension']) && !empty($params['extension']) ? ': ' . $params['extension'] : '';

        $this->params = [
            'from' => $from,
            'to' => $to,
            'subj' => $params['type'] . $extension,
            'description' => $params['description'],
        ];
    }

    /**
     * @param $email
     * @param $name
     * @return string
     */
    private function formatAddress($email, $name)
    {
        if ($name === '' || $name === null || $name === $email) {
            return $email;
        }

        return sprintf('%s <%s>', $name, $email);
    }

    /**
     * @return void
     */
    private function collectInfo()
    {
        $this->result = '<table>';

        $this->result .= "<tr><td colspan='2'>{$this->params['description']}</td></tr>";

        $this->magentoInfo();
        $this->cacheInfo();
        $this->indexInfo();
        $this->informationAboutExtensions();
        $this->mySqlInfo();
        $this->configInfo();
        $this->extensionsInfo();

        $this->result .= '</table>';
    }

    /**
     * @return void
     */
    private function magentoInfo()
    {
        $this->addRowToTable("Magento information");

        $this->addRowToTable('Magento version', $this->productMetadata->getVersion());
        $this->addRowToTable('Magento version', $this->productMetadata->getEdition());
        $this->addRowToTable('Magento mode', $this->_appState->getMode());
        $this->addRowToTable('Domain', $_SERVER["HTTP_HOST"]);
        $this->addRowToTable('Path', strstr(realpath(__FILE__), 'IWD', true));
    }

    /**
     * @return void
     */
    private function cacheInfo()
    {
        $this->addRowToTable("Magento: Cache Storage Management");

        foreach ($this->cacheTypeList->getTypes() as $type) {
            $this->addRowToTable($type['cache_type'], $type['status']);
        }
    }

    /**
     * @return void
     */
    private function indexInfo()
    {
        $this->addRowToTable("Magento: Index Management");

        $states = $this->statesFactory->create();

        foreach (array_keys($this->config->getIndexers()) as $indexerId) {
            /** @var \Magento\Framework\Indexer\IndexerInterface $indexer */
            $indexer = $this->indexerFactory->load($indexerId);
            foreach ($states->getItems() as $state) {
                /** @var \Magento\Indexer\Model\Indexer\State $state */
                if ($state->getIndexerId() == $indexerId) {
                    $indexer->setState($state);
                    break;
                }
            }

            $info = "STATUS: {$indexer->getState()->getStatus()},<br />
            UPDATE AT: {$indexer->getLatestUpdated()}";

            $this->addRowToTable($indexer->getTitle(), $info);
        }
    }

    /**
     * @return void
     */
    public function informationAboutExtensions()
    {
        $this->addRowToTable('Advanced modules');
        $modules = $this->moduleList->getNames();

        $dispatchResult = new \Magento\Framework\DataObject($modules);
        $this->_eventManager->dispatch(
            'adminhtml_system_config_advanced_disableoutput_render_before',
            ['modules' => $dispatchResult]
        );
        $modules = $dispatchResult->toArray();

        sort($modules);
        foreach ($modules as $moduleName) {
            if (strpos(strtolower($moduleName), 'magento') !== 0) {
                @$info = $this->moduleList->getOne($moduleName);
                $this->addRowToTable($moduleName, @$info['setup_version']);
            }
        }
    }

    /**
     * @return void
     */
    private function mySqlInfo()
    {
        $this->addRowToTable("MySql information");
        preg_match('/[0-9]\.[0-9]+\.[0-9]+/', shell_exec('mysql -V'), $version);
        if (empty($version [0]) || empty($version [0])) {
            $this->addRowToTable('MySql version', 'N/A');
        } else {
            $this->addRowToTable('MySql version', $version [0]);
        }
    }

    /**
     * @return void
     */
    private function configInfo()
    {
        $this->addRowToTable("Configuration");
        $this->addRowToTable('PHP version', phpversion());
        $ini = array('safe_mode', 'memory_limit', 'realpath_cache_ttl', 'allow_url_fopen');

        foreach ($ini as $i) {
            $val = ini_get($i);
            $val = empty($val) ? 'off' : $val;
            $this->addRowToTable($i, $val);
        }
    }

    /**
     * @return void
     */
    private function extensionsInfo()
    {
        $this->addRowToTable("PHP Extensions");
        $extensions = array('curl', 'dom', 'gd', 'hash', 'iconv', 'mcrypt', 'pcre', 'pdo', 'pdo_mysql', 'simplexml');

        foreach ($extensions as $extension) {
            $this->addRowToTable($extension, extension_loaded($extension));
        }
    }

    /**
     * @throws \Exception
     */
    private function sendEmail()
    {
        $from = $this->params['from'];
        $to = $this->params['to'];
        $subj = $this->params['subj'];
        $text = $this->result;

        $un = strtoupper(uniqid(time()));
        $head =
            "Reply-To: $from\n" .
            "Mime-Version: 1.0\n" .
            "Content-Type:multipart/mixed;" .
            "boundary=\"----------" . $un . "\"\n\n";

        $additional =
            "------------" . $un . "\nContent-Type:text/html;\n" .
            "Content-Transfer-Encoding: 8bit\n\n$text\n\n";

        $attachment = $_FILES['attachments'];
        $count = count($attachment['name']);

        for ($i = 0; $i < $count; $i++) {
            $tmpFilePath = $attachment['tmp_name'][$i];
            $fileName = $attachment['name'][$i];
            if ($tmpFilePath != "") {
                $f = fopen($tmpFilePath, "rb");
                $additional .=
                    "------------" . $un . "\n" .
                    "Content-Type: application/octet-stream;" .
                    "name=\"" . basename($fileName) . "\"\n" .
                    "Content-Transfer-Encoding:base64\n" .
                    "Content-Disposition:attachment;" .
                    "filename=\"" . basename($fileName) . "\"\n\n" .
                    chunk_split(base64_encode(fread($f, filesize($tmpFilePath)))) . "\n";
            }
        }

        $result = @mail("$to", "$subj", $additional, $head);
        if (!$result) {
            throw new LocalizedException(__('Email was not send!'));
        }
    }

    /**
     * @param $column1
     * @param string $column2
     */
    private function addRowToTable($column1, $column2 = "")
    {
        if ($column2 === "") {
            $this->result .= '<tr><td colspan="2" align="center"><b>' . $column1 . '</b></td></tr>';
        } else {
            $this->result .= '<tr><td>' . $column1 . '</td><td>' . $column2 . '</td></tr>';
        }
    }
}
