<?php

namespace LCB\Feeds\Console\Command;

use LCB\Feeds\Model\Cache;
use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Layout;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var ObjectManagerInterface
     */
    protected ObjectManagerInterface $objectManager;

    /**
     * @var Layout
     */
    protected Layout $layout;

    /**
     * @var StoreRepositoryInterface $storeRepository
     */
    protected StoreRepositoryInterface $storeRepository;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;

    /**
     * @var State
     */
    protected State $state;

    /**
     * @var AreaList
     */
    protected AreaList $areaList;

    /**
     * @var Emulation
     */
    protected Emulation $emulation;

    /**
     * @var CacheInterface
     */
    protected CacheInterface $cache;

    /**
     * @param ObjectManagerInterface $layout
     * @param StoreRepositoryInterface $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     * @param State $state
     * @param AreaList $areaList
     * @param Emulation $emulation
     * @param CacheInterface $cache
     */
    public function __construct(
            ObjectManagerInterface $objectManager,
            StoreRepositoryInterface $storeRepository,
            StoreManagerInterface $storeManager,
            Filesystem $filesystem,
            DirectoryList $directoryList,
            State $state,
            AreaList $areaList,
            Emulation $emulation,
            CacheInterface $cache
        ) {
        parent::__construct();
        $this->objectManager = $objectManager;
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
            $this->state->setAreaCode(Area::AREA_FRONTEND);
        } catch (\Exception $e) {
            // area code is already set due wrong store architecture
        }

        $areaObject = $this->areaList->getArea(Area::AREA_FRONTEND);
        $areaObject->load(Area::PART_TRANSLATE);

        $storeId = $this->storeRepository->get($storeCode)->getId();
        $this->emulation->startEnvironmentEmulation($storeId, 'frontend', true);
        $this->storeManager->setCurrentStore($storeId);

        $this->layout = $this->objectManager->create(Layout::class);

        $xmlContent = $this->layout->createBlock('LCB\Feeds\Block\Product')
            ->setTemplate("LCB_Feeds::$type.phtml")
            ->toHtml();

        $feedCacheType = strtoupper($type);
        $this->cache->save(
                $xmlContent,
                Cache::CODE . '_' . $feedCacheType,
                [Cache::CODE . '_' . $feedCacheType],
                Cache::LIFETIME
            );

        $this->emulation->stopEnvironmentEmulation();
    }
}
