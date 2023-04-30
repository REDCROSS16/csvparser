<?php

namespace App\Command;

use App\Entity\StockItem;
use App\Repository\StockItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


class UpdateStockCommand extends Command
{
    private string $projectDir;
    protected static $defaultName = 'app:update-stock';

    public function __construct($projectDir, EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->projectDir = $projectDir;
        parent::__construct();
    }

    public function configure()
    {
        $this->setDescription('Update stock records')
            ->addArgument('markup', InputArgument::OPTIONAL, 'Percentage mrakup', 20)
            ->addArgument('process_date', InputArgument::OPTIONAL, 'Date of the process', date_create()->format('Y-m-d'));
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $processDate = $input->getArgument('process_date');
        $markup = ($input->getArgument('markup') / 100) + 1;

        $supplierProducts = $this->getCsvRowsAsArray($processDate);

        /** @var StockItemRepository $stocItemkInRepo */
        $stocItemkInRepo = $this->em->getRepository(StockItem::class);

        foreach ($supplierProducts as $product) {

            /** @var StockItem $existingStockItem */
            if ($existingStockItem = $stocItemkInRepo->findOneBy(['itemNumber' => $product['item_number']]) ) {

                $this->updateStockItem($existingStockItem, $product, $markup);

                continue;
            }

            $this->createNewStockItem($product, $markup);
        }
        $this->em->flush();

        $io = new SymfonyStyle($input, $output);
        $io->success('It worked');

        return Command::SUCCESS;
    }

    private function getCsvRowsAsArray(string $processDate): array
    {
        $input = $this->projectDir . '/public/files/' . $processDate . '.csv';
        $decoder = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);

        return $decoder->decode(file_get_contents($input), 'csv');
    }

    private function updateStockItem($existingStockItem, $product, $markup): void
    {
        $existingStockItem->setSupplierCost($product['cost']);
        $existingStockItem->setPrice($product['cost'] * $markup);

        $this->em->persist($existingStockItem);
    }

    private function createNewStockItem(mixed $product, $markup)
    {
        $newStockItem = new StockItem();
        $newStockItem
            ->setItemNumber($product['item_number'])
            ->setItemName($product['item_name'])
            ->setItemDescription($product['description'])
            ->setSupplierCost($product['cost'])
            ->setPrice($product['cost'] * $markup);
        $this->em->persist($newStockItem);
    }
}