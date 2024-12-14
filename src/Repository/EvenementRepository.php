<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    // Compter les événements futurs
    public function countFutureEvents(): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.dateDebut > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAverageDuration(): ?float
    {
        $events = $this->createQueryBuilder('e')
            ->select('e.dateDebut, e.dateFin')
            ->getQuery()
            ->getResult();
    
        if (empty($events)) {
            return null;
        }
    
        $totalDuration = 0;
        $count = 0;
    
        foreach ($events as $event) {
            if ($event['dateDebut'] instanceof \DateTime && $event['dateFin'] instanceof \DateTime) {
                $duration = $event['dateFin']->diff($event['dateDebut'])->days;
                $totalDuration += $duration;
                $count++;
            }
        }
    
        return $count > 0 ? $totalDuration / $count : null;
    }
    
    public function findPopularEventTypes(): array
{
    return $this->createQueryBuilder('e')
        ->join('e.type', 't') 
        ->select('t.nom as type, COUNT(e.id) as count') 
        ->groupBy('t.nom') 
        ->orderBy('count', 'DESC')
        ->getQuery()
        ->getResult();
}

   
    
}
