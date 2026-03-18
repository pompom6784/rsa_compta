<?php

namespace App\Application\Actions\Book;

use App\Application\Actions\Action;
use App\Domain\Line;
use App\Domain\LineBreakdown;
use App\Domain\CheckDeliveryLine;
use App\Infrastructure\Persistence\CheckDelivery\DbCheckDeliveryRepository;
use App\Infrastructure\Persistence\Line\DbLineRepository;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;

class ListLineEditAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        protected DbLineRepository $lineRepository,
        protected DbCheckDeliveryRepository $checkDeliveryRepository,
        protected ContainerInterface $container
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $line = $this->lineRepository->findLineOfId($this->args['id']);
        if (!$line) {
            return $this->redirect('/livre');
        }
        if (!empty($this->request->getParsedBody()['check_delivery'])) {
            return $this->convertCheckDelivery($line);
        }
        if ($this->request->getParsedBody()) {
            $line->setType($this->request->getParsedBody()['type']);
            $line->setLabel($this->request->getParsedBody()['label']);
            $line->setName($this->request->getParsedBody()['name']);
            $line->setBreakdown($this->request->getParsedBody()['breakdown']);
            foreach (array_keys(LineBreakdown::getBreakdowns()) as $breakdown) {
                $key = 'breakdown' . $breakdown;
                $line->__set($key, self::parseCurrency($this->request->getParsedBody()[$key]));
            }
            $this->lineRepository->save($line);

            return $this->redirect('/livre');
        }
        $vars = [
            'line' => $line,
            'breakdowns' => LineBreakdown::getBreakdowns(),
        ];
        if ($line->getLabel() === "REMISES DE CHEQUES") {
            $checkCount = 0;
            if (\preg_match('/DE\s+([0-9]+)\s+CHQ/', $line->getDescription(), $matches)) {
                $checkCount = $matches[1];
            }
            $vars['check_count'] = $checkCount;
            $vars['check_deliveries'] = $this->checkDeliveryRepository
                ->findByDifference($line->getAmount(), $checkCount, $line->getDate());
        }
        $this->container->get('view')->render($this->response, 'edit_line.html.twig', $vars);
        return $this->response;
    }

    protected function convertCheckDelivery($line): Response
    {
        $checkDelivery = $this->checkDeliveryRepository
            ->findCheckDeliveryOfId($this->request->getParsedBody()['check_delivery']);

        foreach ($checkDelivery->getLines() as $checkDeliveryLine) {
            $newLine = new Line();
            $newLine->setType('CHQ');
            $newLine->setName($checkDeliveryLine->getName());
            $newLine->setLabel($checkDeliveryLine->getLabel());
            $newLine->setDescription(
                "Chèque n°" . $checkDeliveryLine->getCheckNumber() . " remis le " . $checkDelivery
                    ->getDate()->format('d/m/Y')
            );
            $newLine->setDate($checkDelivery->getDate());
            $newLine->setAmount($checkDeliveryLine->getAmount());
            if (strpos($checkDeliveryLine->getLabel(), 'COTISATION') === 0) {
                $newLine->setBreakdown([LineBreakdown::RSA_NAV_CONTRIBUTION]);
                $newLine->breakdownInternalTransfer = $newLine->getAmount();
            } else {
                // Supérieur à 120€, c'est un renouvellement d'avion
                $newLine->setBreakdown([LineBreakdown::PLANE_RENEWAL]);
                $newLine->breakdownPlaneRenewal = 120;
                $newLine->breakdownCustomerFees = $newLine->getAmount() - 120;
                if ($newLine->breakdownCustomerFees > 0) {
                    $newLine->addBreakdown(LineBreakdown::CUSTOMER_FEES);
                }
            }
            $this->lineRepository->save($newLine);
        }
        $this->lineRepository->delete($line);
        $checkDelivery->setConverted(true);
        $this->checkDeliveryRepository->save($checkDelivery);

        return $this->redirect('/livre');
    }

    protected static function parseCurrency(?string $currency): float
    {
        if (empty($currency)) {
            return 0;
        }

        return (float)str_replace(',', '.', preg_replace('/[^-0-9,]/', '', $currency));
    }
}
