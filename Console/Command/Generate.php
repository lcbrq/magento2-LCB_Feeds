<?php

namespace LCB\Feeds\Console\Command;

use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Command
{

    /**
     * @var string
     */
    const STORE_CODE_ARGUMENT = 'store';

    /**
     * @var string
     */
    const FEED_TYPE_ARGUMENT = 'type';

    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    protected $storeRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \\Magento\Framework\App\AreaList
     */
    protected $areaList;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulation;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @param \Magento\Framework\View\Layout $layout
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param\ Magento\Framework\App\State $state
     * @param \Magento\Framework\App\AreaList $areaList
     * @param \Magento\Store\Model\App\Emulation $emulation
     * @param \Magento\Framework\App\CacheInterface $cache
     */
    public function __construct(
        \Magento\Framework\View\Layout $layout,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\App\State $state,
        \Magento\Framework\App\AreaList $areaList,
        \Magento\Store\Model\App\Emulation $emulation,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        parent::__construct();
        $this->layout = $layout;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->state = $state;
        $this->areaList = $areaList;
        $this->emulation = $emulation;
        $this->cache = $cache;
    }

    /**
     * Add task to Magento 2 commands
     */
    protected function configure()
    {
        $this->setName('lcb:feeds:generate')
            ->setDescription('Generate Feeds')
            ->setDefinition([
                new InputArgument(
                    self::STORE_CODE_ARGUMENT,
                    InputArgument::REQUIRED,
                    'store'
                ),
                new InputArgument(
                    self::FEED_TYPE_ARGUMENT,
                    InputArgument::REQUIRED,
                    'type'
                )
            ]);
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $storeCode = $input->getArgument(self::STORE_CODE_ARGUMENT);
            $type = $input->getArgument(self::FEED_TYPE_ARGUMENT);
            $this->generate($storeCode, $type);
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                $output->writeln($e->getTraceAsString());
            }
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    /**
     * @param string $storeCode
     * @param string $type
     * @return void
     */
    public function generate($storeCode, $type)
    {
        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        } catch (\Exception $e) {
            // area code is already set due wrong store architecture
        }

        $areaObject = $this->areaList->getArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $areaObject->load(\Magento\Framework\App\Area::PART_TRANSLATE);

        $storeId = $this->storeRepository->get($storeCode)->getId();
        $this->emulation->startEnvironmentEmulation($storeId, 'frontend', true);
        $this->storeManager->setCurrentStore($storeId);

        $xmlContent = $this->layout->createBlock('LCB\Feeds\Block\Product')
            ->setTemplate("LCB_Feeds::$type.phtml")
            ->toHtml();

        $feedCacheType = strtoupper($type);
        $this->cache->save(
            $xmlContent,
            \LCB\Feeds\Model\Cache::CODE . '_' . $feedCacheType,
            [\LCB\Feeds\Model\Cache::CODE . '_' . $feedCacheType],
            \LCB\Feeds\Model\Cache::LIFETIME
        );

        $this->emulation->stopEnvironmentEmulation();
    }
}
