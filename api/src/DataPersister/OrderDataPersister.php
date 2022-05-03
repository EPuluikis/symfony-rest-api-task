<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Order;
use App\Entity\User;
use App\Service\OrderNumberGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class OrderDataPersister implements ContextAwareDataPersisterInterface
{
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Order;
    }

    /**
     * @param Order $data
     */
    public function persist($data, array $context = [])
    {
        if (!$data->getOwner()) {
            /** @var User $user */
            $user = $this->security->getUser();
            $data->setOwner($user);
        }

        if ($data->getPlainStatus()) {
            $data->setStatus($data->getPlainStatus());
            $data->erasePlainStatus();
        }

        if (($context['collection_operation_name'] ?? null) == 'post') {
            $data = $this->generateOrderNumber($data);
            $this->increaseOrdersCount($data->getOwner());
        } elseif (!empty($context['previous_data']->getOwner())) {
            $this->increaseOrdersCount($data->getOwner());
            $this->decreaseOrdersCount($context['previous_data']->getOwner());
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }

    public function remove($data, array $context = [])
    {
        $this->decreaseOrdersCount($data->getOwner());

        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }

    public function resumable(array $context = []): bool
    {
        return true;
    }

    private function generateOrderNumber(Order $order): Order
    {
        $order->setOrderNumber(
            OrderNumberGenerator::generateOrderNumber($this->entityManager)
        );

        return $order;
    }

    private function increaseOrdersCount(User $user): void
    {
        $this->entityManager->getRepository(User::class)->increaseOrdersCount($user);
    }

    private function decreaseOrdersCount(User $user): void
    {
        $this->entityManager->getRepository(User::class)->decreaseOrdersCount($user);
    }
}
